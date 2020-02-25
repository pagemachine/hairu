<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Controller;

/*
 * This file is part of the PAGEmachine Hairu project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use PAGEmachine\Hairu\LoginType;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Rsaauth\RsaEncryptionDecoder;
use TYPO3\CMS\Rsaauth\RsaEncryptionEncoder;

/**
 * Controller for authentication tasks
 */
class AuthenticationController extends AbstractController
{
    /**
     * @var HashService $hashService
     */
    protected $hashService;

    /**
     * @param HashService $hashService
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @var TypoScriptService $typoScriptService
     */
    protected $typoScriptService;

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * @var Dispatcher $signalSlotDispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $tokenCache;

    /**
     * @param \TYPO3\CMS\Core\Log\LogManager $logManager
     */
    public function injectLogManager(LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param \TYPO3\CMS\Core\Cache\CacheManager $cacheManager
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->tokenCache = $cacheManager->getCache('hairu_token');
    }

    /**
     * Initialize all actions
     *
     * @return void
     */
    protected function initializeAction()
    {
        // Apply default settings
        $defaultSettings = [
            'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
            'login' => [
                'page' => $this->getFrontendController()->id,
            ],
            'passwordReset' => [
                'loginOnSuccess' => false,
                'mail' => [
                    'from' => MailUtility::getSystemFromAddress(),
                    'subject' => 'Password reset request',
                ],
                'page' => $this->getFrontendController()->id,
                'token' => [
                    'lifetime' => 86400, // 1 day
                ],
            ],
        ];

        $settings = $defaultSettings;
        ArrayUtility::mergeRecursiveWithOverrule($settings, $this->settings, true, false);
        $this->settings = $settings;

        // Make global form data (as expected by the CMS core) available
        $formData = GeneralUtility::_GET();
        ArrayUtility::mergeRecursiveWithOverrule($formData, GeneralUtility::_POST());
        $this->request->setArgument('formData', $formData);
    }

    /**
     * Initialize all views
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $view->assignMultiple([
            'formData' => $this->request->getArgument('formData'),
            'storagePid' => $frameworkConfiguration['persistence']['storagePid'],
        ]);
    }

    /**
     * Login form view
     *
     * @return void
     */
    public function showLoginFormAction()
    {
        if ($this->authenticationService->isUserAuthenticated()) {
            $this->forward('showLogoutForm');
        }

        $formData = $this->request->getArgument('formData');

        if (isset($formData['logintype'])) {
            switch ($formData['logintype']) {
                case LoginType::LOGIN:
                    $this->addLocalizedFlashMessage(
                        'login.failed.message',
                        null,
                        FlashMessage::ERROR,
                        'login.failed.title'
                    );
                    break;

                case LoginType::LOGOUT:
                    $this->emitAfterLogoutSignal();

                    $this->addLocalizedFlashMessage('logout.successful', null, FlashMessage::INFO);
                    break;
            }
        }
    }

    /**
     * Logout form view
     *
     * @return void
     */
    public function showLogoutFormAction()
    {
        $formData = $this->request->getArgument('formData');
        $user = $this->authenticationService->getAuthenticatedUser();

        if ($this->authenticationService->isUserAuthenticated() && ($formData['logintype'] ?? null) === LoginType::LOGIN) {
            $this->emitAfterLoginSignal();

            $this->addLocalizedFlashMessage('login.successful', [$user->getUsername()], FlashMessage::OK);
        }

        $this->view->assignMultiple([
            'user' => $user,
        ]);
    }

    /**
     * Password reset form view
     *
     * @param string $hash Identification hash of a password reset token
     * @param bool $start TRUE when starting the reset process, FALSE otherwise
     * @return void
     */
    public function showPasswordResetFormAction(string $hash = null, bool $start = false)
    {
        if ($start) {
            $this->addLocalizedFlashMessage('resetPassword.start', null, FlashMessage::INFO);
        }

        if ($hash !== null) {
            if ($this->tokenCache->get($hash) !== false) {
                $this->addLocalizedFlashMessage('resetPassword.hints', null, FlashMessage::INFO);

                $this->view->assign('hash', $hash);

                if (class_exists(RsaEncryptionEncoder::class)) {
                    $rsaEncryptionEncoder = $this->objectManager->get(RsaEncryptionEncoder::class);

                    if ($rsaEncryptionEncoder->isAvailable()) {
                        $rsaEncryptionEncoder->enableRsaEncryption();
                    }
                }
            } else {
                $this->addLocalizedFlashMessage('resetPassword.failed.invalid', null, FlashMessage::ERROR);
            }
        }
    }

    /**
     * Start password reset
     *
     * @param string $username Username of a user
     * @return void
     */
    public function startPasswordResetAction(string $username)
    {
        $user = $this->frontendUserRepository->findOneByUsername($username);

        if ($user) {
            // Forbid password reset if there is no password or password property,
            // e.g. if the user has not completed a special registration process
            // or is supposed to authenticate in some other way
            $userPassword = ObjectAccess::getPropertyPath($user, 'password');

            if ($userPassword === null) {
                $this->logger->error('Failed to initiate password reset for user "' . $username . '": no password present');
                $this->addLocalizedFlashMessage('resetPassword.failed.nopassword', null, FlashMessage::ERROR);
                $this->redirect('showPasswordResetForm');
            }

            $userEmail = ObjectAccess::getPropertyPath($user, 'email');

            if (empty($userEmail)) {
                $this->logger->error('Failed to initiate password reset for user "' . $username . '": no email address present');
                $this->addLocalizedFlashMessage('resetPassword.failed.noemail', null, FlashMessage::ERROR);
                $this->redirect('showPasswordResetForm');
            }

            $hash = GeneralUtility::makeInstance(Random::class)->generateRandomHexString(64);
            $token = [
                'uid' => $user->getUid(),
                'hmac' => $this->hashService->generateHmac($userPassword),
            ];
            $tokenLifetime = $this->getSettingValue('passwordReset.token.lifetime');

            // Remove possibly existing reset tokens and store new one
            $this->tokenCache->flushByTag($user->getUid());
            $this->tokenCache->set($hash, $token, [$user->getUid()], $tokenLifetime);

            $expiryDate = new \DateTime(sprintf('now + %d seconds', $tokenLifetime));
            $hashUri = $this->uriBuilder
                ->setTargetPageUid($this->getSettingValue('passwordReset.page'))
                ->setCreateAbsoluteUri(true)
                ->uriFor('showPasswordResetForm', [
                    'hash' => $hash,
                ]);
            $this->view->assignMultiple([
                'user' => $user,
                'hash' => $hash, // Allow for custom URI in Fluid
                'hashUri' => $hashUri,
                'expiryDate' => $expiryDate,
            ]);

            $message = $this->objectManager->get(MailMessage::class);
            $message
                ->setFrom(MailUtility::parseAddresses($this->getSettingValue('passwordReset.mail.from')))
                ->setTo($userEmail)
                ->setSubject($this->getSettingValue('passwordReset.mail.subject'));

            $setViewFormat = function ($format) {
                // TYPO3v8+
                if (method_exists($this->view, 'getTemplatePaths')) {
                    $this->view->getTemplatePaths()->setFormat($format);
                } else { // TYPO3v7
                    $this->request->setFormat($format);
                }
            };

            $setViewFormat('txt');
            $message->setBody($this->view->render('passwordResetMail'), 'text/plain');

            if ($this->getSettingValue('passwordReset.mail.addHtmlPart')) {
                $setViewFormat('html');
                $message->addPart($this->view->render('passwordResetMail'), 'text/html');
            }

            $mailSent = false;

            $this->emitBeforePasswordResetMailSendSignal($message);

            try {
                $mailSent = $message->send();
            } catch (\Swift_SwiftException $e) {
                $this->logger->error($e->getMessage);
            }

            if (!$mailSent) {
                $this->addLocalizedFlashMessage('resetPassword.failed.sending', null, FlashMessage::ERROR);
                $this->redirect('showPasswordResetForm');
            }
        }

        $this->addLocalizedFlashMessage('resetPassword.started', null, FlashMessage::INFO);
        $this->redirect('showPasswordResetForm');
    }

    /**
     * Initialize complete password reset
     *
     * @return void
     */
    protected function initializeCompletePasswordResetAction()
    {
        if (class_exists(RsaEncryptionDecoder::class)) {
            $rsaEncryptionDecoder = $this->objectManager->get(RsaEncryptionDecoder::class);

            if ($rsaEncryptionDecoder->isAvailable()) {
                $this->request->setArguments($rsaEncryptionDecoder->decrypt($this->request->getArguments()));
            }
        }

        // Password repeat validation needs to be added manually here to access the password value
        $passwordRepeatArgumentValidator = $this->arguments->getArgument('passwordRepeat')->getValidator();
        $passwordsEqualValidator = $this->validatorResolver->createValidator('PAGEmachine.Hairu:EqualValidator', [
            'equalTo' => $this->request->getArgument('password'),
        ]);
        $passwordRepeatArgumentValidator->addValidator($passwordsEqualValidator);
    }

    /**
     * Complete password reset
     *
     * @param string $hash Identification hash of a password reset token
     * @param string $password New password of the user
     * @param string $passwordRepeat Confirmation of the new password
     * @return void
     */
    public function completePasswordResetAction(string $hash, string $password, string $passwordRepeat)
    {
        $token = $this->tokenCache->get($hash);

        if ($token !== false) {
            $user = $this->frontendUserRepository->findByIdentifier($token['uid']);

            if ($user !== null) {
                if ($this->hashService->validateHmac($user->getPassword(), $token['hmac'])) {
                    $user->setPassword($this->passwordService->applyTransformations($password));
                    $this->frontendUserRepository->update($user);

                    $this->authenticationService->invalidateUserSessions($user);
                    $this->tokenCache->remove($hash);

                    if ($this->getSettingValue('passwordReset.loginOnSuccess')) {
                        $this->authenticationService->authenticateUser($user);
                        $this->addLocalizedFlashMessage('resetPassword.completed.login', null, FlashMessage::OK);
                    } else {
                        $this->addLocalizedFlashMessage('resetPassword.completed', null, FlashMessage::OK);
                    }
                } else {
                    $this->addLocalizedFlashMessage('resetPassword.failed.expired', null, FlashMessage::ERROR);
                }
            } else {
                $this->addLocalizedFlashMessage('resetPassword.failed.invalid', null, FlashMessage::ERROR);
            }
        } else {
            $this->addLocalizedFlashMessage('resetPassword.failed.expired', null, FlashMessage::ERROR);
        }

        $loginPageUid = $this->getSettingValue('login.page');
        $this->redirect('showLoginForm', null, null, null, $loginPageUid);
    }

    /**
     * Shorthand helper for getting setting values with optional default values
     *
     * Any setting value is automatically processed via stdWrap if configured.
     *
     * @param string $settingPath Path to the setting, e.g. "foo.bar.qux"
     * @param mixed $defaultValue Default value if no value is set
     * @return mixed
     */
    protected function getSettingValue(string $settingPath, $defaultValue = null)
    {
        $value = ObjectAccess::getPropertyPath($this->settings, $settingPath);

        if (!empty($value['stdWrap'])) {
            $stdWrap = $this->typoScriptService->convertPlainArrayToTypoScriptArray($value['stdWrap']);
            $value = $this->getFrontendController()->cObj->stdWrap($value['_typoScriptNodeValue'], $stdWrap);
        }

        // Change type of value to type of default value if possible
        if (!empty($value) && $defaultValue !== null) {
            settype($value, gettype($defaultValue));
        }

        $value = !empty($value) ? $value : $defaultValue;

        return $value;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Emits a signal after a user has logged in
     *
     * @return void
     */
    protected function emitAfterLoginSignal()
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'afterLogin',
            [
                $this->request,
                $this->settings,
            ]
        );
    }

    /**
     * Emits a signal after a user has logged out
     *
     * @return void
     */
    protected function emitAfterLogoutSignal()
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'afterLogout',
            [
                $this->request,
                $this->settings,
            ]
        );
    }

    /**
     * Emits a signal before a password reset mail is sent
     *
     * @param MailMessage $message
     * @return void
     */
    protected function emitBeforePasswordResetMailSendSignal(MailMessage $message)
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'beforePasswordResetMailSend',
            [
                $message,
            ]
        );
    }
}
