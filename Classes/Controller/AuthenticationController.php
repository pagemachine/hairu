<?php
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
use PAGEmachine\Hairu\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AuthenticationController extends ActionController {

  /**
   * @var \PAGEmachine\Hairu\Domain\Repository\FrontendUserRepository
   * @inject
   */
  protected $frontendUserRepository;

  /**
   * @var \PAGEmachine\Hairu\Domain\Service\AuthenticationService
   * @inject
   */
  protected $authenticationService;

  /**
   * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
   * @inject
   */
  protected $hashService;

  /**
   * @var \PAGEmachine\Hairu\Domain\Service\PasswordService
   * @inject
   */
  protected $passwordService;

  /**
   * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
   * @inject
   */
  protected $signalSlotDispatcher;

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
   * @return void
   */
  public function injectLogManager(\TYPO3\CMS\Core\Log\LogManager $logManager) {

    $this->logger = $logManager->getLogger(__CLASS__);
  }

  /**
   * @param \TYPO3\CMS\Core\Cache\CacheManager $cacheManager
   * @return void
   */
  public function injectCacheManager(\TYPO3\CMS\Core\Cache\CacheManager $cacheManager) {

    $this->tokenCache = $cacheManager->getCache('hairu_token');
  }

  /**
   * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
   * @return void
   */
  public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {

    parent::injectConfigurationManager($configurationManager);

    $defaultSettings = array(
      'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
      'login' => array(
        'page' => $this->getFrontendController()->id,
      ),
      'passwordReset' => array(
        'loginOnSuccess' => FALSE,
        'mail' => array(
          'from' => MailUtility::getSystemFromAddress(),
          'subject' => 'Password reset request',
        ),
        'page' => $this->getFrontendController()->id,
        'token' => array(
          'lifetime' => 86400, // 1 day
        ),
      ),
    );

    $settings = $defaultSettings;
    ArrayUtility::mergeRecursiveWithOverrule($settings, $this->settings, TRUE, FALSE);
    $this->settings = $settings;
  }

  /**
   * Initialize all actions
   *
   * @return void
   */
  protected function initializeAction() {

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
  protected function initializeView(ViewInterface $view) {

    $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

    $view->assignMultiple(array(
      'formData' => $this->request->getArgument('formData'),
      'storagePid' => $frameworkConfiguration['persistence']['storagePid'],
    ));
  }

  /**
   * Login form view
   *
   * @return void
   */
  public function showLoginFormAction() {

    if ($this->authenticationService->isUserAuthenticated()) {

      $this->forward('showLogoutForm');
    }

    $formData = $this->request->getArgument('formData');

    if (isset($formData['logintype'])) {

      switch ($formData['logintype']) {

        case LoginType::LOGIN:

          $this->addLocalizedFlashMessage('login.failed', NULL, FlashMessage::ERROR);
          break;

        case LoginType::LOGOUT:

          $this->emitAfterLogoutSignal();

          $this->addLocalizedFlashMessage('logout.successful', NULL, FlashMessage::INFO);
          break;
      }
    }
  }

  /**
   * Logout form view
   *
   * @return void
   */
  public function showLogoutFormAction() {

    $formData = $this->request->getArgument('formData');
    $user = $this->authenticationService->getAuthenticatedUser();

    if ($this->authenticationService->isUserAuthenticated()
        && isset($formData['logintype'])
        && $formData['logintype'] === LoginType::LOGIN) {

      $this->emitAfterLoginSignal();

      $this->addLocalizedFlashMessage('login.successful', array($user->getUsername()), FlashMessage::OK);
    }

    $this->view->assignMultiple(array(
      'user' => $user,
    ));
  }

  /**
   * Password reset form view
   *
   * @param string $hash Identification hash of a password reset token
   * @param boolean $start TRUE when starting the reset process, FALSE otherwise
   * @return void
   */
  public function showPasswordResetFormAction($hash = NULL, $start = FALSE) {

    if ($start) {

      $this->addLocalizedFlashMessage('resetPassword.start', NULL, FlashMessage::INFO);
    }

    if ($hash !== NULL) {

      if ($this->tokenCache->get($hash) !== FALSE) {

        $this->addLocalizedFlashMessage('resetPassword.hints', NULL, FlashMessage::INFO);

        $this->view->assign('hash', $hash);

        if (class_exists('TYPO3\\CMS\\Rsaauth\\RsaEncryptionEncoder')) {

          $rsaEncryptionEncoder = $this->objectManager->get('TYPO3\\CMS\\Rsaauth\\RsaEncryptionEncoder');

          if ($rsaEncryptionEncoder->isAvailable()) {

            $rsaEncryptionEncoder->enableRsaEncryption();
          }
        }
      } else {

        $this->addLocalizedFlashMessage('resetPassword.failed.invalid', NULL, FlashMessage::ERROR);
      }
    }
  }

  /**
   * Start password reset
   *
   * @param string $username Username of a user
   * @return void
   *
   * @validate $username NotEmpty
   */
  public function startPasswordResetAction($username) {

    $user = $this->frontendUserRepository->findOneByUsername($username);
    // Forbid password reset if there is no password or password property,
    // e.g. if the user has not completed a special registration process
    // or is supposed to authenticate in some other way
    $userPassword = ObjectAccess::getPropertyPath($user, 'password');

    if ($userPassword === NULL) {

      $this->logger->error('Failed to initiate password reset for user "' . $username . '": no password present');
      $this->addLocalizedFlashMessage('resetPassword.failed.nopassword', NULL, FlashMessage::ERROR);
      $this->redirect('showPasswordResetForm');
    }

    $userEmail = ObjectAccess::getPropertyPath($user, 'email');

    if (empty($userEmail)) {

      $this->logger->error('Failed to initiate password reset for user "' . $username . '": no email address present');
      $this->addLocalizedFlashMessage('resetPassword.failed.noemail', NULL, FlashMessage::ERROR);
      $this->redirect('showPasswordResetForm');
    }

    $hash = md5(GeneralUtility::generateRandomBytes(64));
    $token = array(
      'uid' => $user->getUid(),
      'hmac' => $this->hashService->generateHmac($userPassword),
    );
    $tokenLifetime = $this->getSettingValue('passwordReset.token.lifetime');

    // Remove possibly existing reset tokens and store new one
    $this->tokenCache->flushByTag($user->getUid());
    $this->tokenCache->set($hash, $token, array($user->getUid()), $tokenLifetime);

    $expiryDate = new \DateTime(sprintf('now + %d seconds', $tokenLifetime));
    $hashUri = $this->uriBuilder
      ->setTargetPageUid($this->getSettingValue('passwordReset.page'))
      ->setUseCacheHash(FALSE)
      ->setCreateAbsoluteUri(TRUE)
      ->uriFor('showPasswordResetForm', array(
        'hash' => $hash,
      ));
    $this->view->assignMultiple(array(
      'user' => $user,
      'hash' => $hash, // Allow for custom URI in Fluid
      'hashUri' => $hashUri,
      'expiryDate' => $expiryDate,
    ));

    $message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
    $message
      ->setFrom($this->getSettingValue('passwordReset.mail.from'))
      ->setTo($userEmail)
      ->setSubject($this->getSettingValue('passwordReset.mail.subject'));

    $this->request->setFormat('txt');
    $message->setBody($this->view->render('passwordResetMail'), 'text/plain');
    $this->request->setFormat('html');
    $message->addPart($this->view->render('passwordResetMail'), 'text/html');
    $mailSent = FALSE;

    $this->emitBeforePasswordResetMailSendSignal($message);

    try {

      $mailSent = $message->send();
    } catch (\Swift_SwiftException $e) {

      $this->logger->error($e->getMessage);
    }

    if ($mailSent) {

      $this->addLocalizedFlashMessage('resetPassword.started', NULL, FlashMessage::INFO);
    } else {

      $this->addLocalizedFlashMessage('resetPassword.failed.sending', NULL, FlashMessage::ERROR);
    }

    $this->redirect('showPasswordResetForm');
  }

  /**
   * Initialize complete password reset
   *
   * @return void
   */
  protected function initializeCompletePasswordResetAction() {

    if (class_exists('TYPO3\\CMS\\Rsaauth\\RsaEncryptionDecoder')) {

      $rsaEncryptionDecoder = $this->objectManager->get('TYPO3\\CMS\\Rsaauth\\RsaEncryptionDecoder');

      if ($rsaEncryptionDecoder->isAvailable()) {

        $this->request->setArguments($rsaEncryptionDecoder->decrypt($this->request->getArguments()));
      }
    }

    // Password repeat validation needs to be added manually here to access the password value
    $passwordRepeatArgumentValidator = $this->arguments->getArgument('passwordRepeat')->getValidator();
    $passwordsEqualValidator = $this->validatorResolver->createValidator('PAGEmachine.Hairu:EqualValidator', array(
      'equalTo' => $this->request->getArgument('password'),
    ));
    $passwordRepeatArgumentValidator->addValidator($passwordsEqualValidator);
  }

  /**
   * Complete password reset
   *
   * @param string $hash Identification hash of a password reset token
   * @param string $password New password of the user
   * @param string $passwordRepeat Confirmation of the new password
   * @return void
   *
   * @validate $password NotEmpty
   * @validate $passwordRepeat NotEmpty
   */
  public function completePasswordResetAction($hash, $password, $passwordRepeat) {

    $token = $this->tokenCache->get($hash);

    if ($token !== FALSE) {

      $user = $this->frontendUserRepository->findByIdentifier($token['uid']);

      if ($user !== NULL) {

        if ($this->hashService->validateHmac($user->getPassword(), $token['hmac'])) {

          $user->setPassword($this->passwordService->applyTransformations($password));
          $this->frontendUserRepository->update($user);
          $this->tokenCache->remove($hash);

          if ($this->getSettingValue('passwordReset.loginOnSuccess')) {

            $this->authenticationService->authenticateUser($user);
            $this->addLocalizedFlashMessage('resetPassword.completed.login', NULL, FlashMessage::OK);
          } else {

            $this->addLocalizedFlashMessage('resetPassword.completed', NULL, FlashMessage::OK);
          }
        } else {

          $this->addLocalizedFlashMessage('resetPassword.failed.expired', NULL, FlashMessage::ERROR);
        }
      } else {

        $this->addLocalizedFlashMessage('resetPassword.failed.invalid', NULL, FlashMessage::ERROR);
      }
    } else {

      $this->addLocalizedFlashMessage('resetPassword.failed.expired', NULL, FlashMessage::ERROR);
    }

    $loginPageUid = $this->getSettingValue('login.page');
    $this->redirect('showLoginForm', NULL, NULL, NULL, $loginPageUid);
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
  protected function getSettingValue($settingPath, $defaultValue = NULL) {

    $value = ObjectAccess::getPropertyPath($this->settings, $settingPath);
    $stdWrapConfiguration = ObjectAccess::getPropertyPath($this->settings, $settingPath . '.stdWrap');

    if ($stdWrapConfiguration !== NULL) {

      $value = $this->getFrontendController()->cObj->stdWrap($value, $stdWrapConfiguration);
    }

    // Change type of value to type of default value if possible
    if (!empty($value) && $defaultValue !== NULL) {

      settype($value, gettype($defaultValue));
    }

    $value = !empty($value) ? $value : $defaultValue;

    return $value;
  }

  /**
   * A template method for displaying custom error flash messages, or to
   * display no flash message at all on errors. Override this to customize
   * the flash message in your action controller.
   *
   * @return string The flash message or FALSE if no flash message should be set
   */
  protected function getErrorFlashMessage() {
    return FALSE;
  }

  /**
   * Shorthand helper for adding localized flash messages
   *
   * @param string $translationKey
   * @param array $translationArguments
   * @param integer $severity
   */
  protected function addLocalizedFlashMessage($translationKey, array $translationArguments = NULL, $severity = FlashMessage::OK) {

    $this->addFlashMessage(
      LocalizationUtility::translate(
        $translationKey,
        $this->request->getControllerExtensionName(),
        $translationArguments
      ),
      '',
      $severity
    );
  }

  /**
   * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
   */
  protected function getFrontendController() {

    return $GLOBALS['TSFE'];
  }

  /**
   * Emits a signal after a user has logged in
   *
   * @return void
   */
  protected function emitAfterLoginSignal() {

    $this->signalSlotDispatcher->dispatch(
      __CLASS__,
      'afterLogin',
      array(
        $this->request,
        $this->settings
      )
    );
  }

  /**
   * Emits a signal after a user has logged out
   *
   * @return void
   */
  protected function emitAfterLogoutSignal() {

    $this->signalSlotDispatcher->dispatch(
      __CLASS__,
      'afterLogout',
      array(
        $this->request,
        $this->settings
      )
    );
  }

  /**
   * Emits a signal before a password reset mail is sent
   *
   * @param MailMessage $message
   * @return void
   */
  protected function emitBeforePasswordResetMailSendSignal(MailMessage $message) {

    $this->signalSlotDispatcher->dispatch(
      __CLASS__,
      'beforePasswordResetMailSend',
      array(
        $message,
      )
    );
  }
}
