<?php
namespace PAGEmachine\Hairu\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Mathias Brodala <mbrodala@pagemachine.de>, PAGEmachine AG
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use PAGEmachine\Hairu\LoginType;
use PAGEmachine\Hairu\Mvc\Controller\ActionController;

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
   * @var \PAGEmachine\Hairu\Service\SettingService
   * @inject
   */
  protected $settingService;

  /**
   * @var \PAGEmachine\Hairu\Service\PasswordResetService
   * @inject
   */
  protected $passwordResetService;

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
   * Initialize all actions
   *
   * @return void
   */
  protected function initializeAction() {

    // Make global form data (as expected by the CMS core) available
    $formData = GeneralUtility::_GET();
    ArrayUtility::mergeRecursiveWithOverrule($formData, GeneralUtility::_POST());
    $this->request->setArgument('formData', $formData);

    // Get merged settings
    $this->settings = $this->settingService->getSettings();
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
    /* @var $user \PAGEmachine\Hairu\Domain\Model\FrontendUser */
    $user = $this->frontendUserRepository->findOneByUsername($username);
    // Forbid password reset if there is no password or password property,
    // e.g. if the user has not completed a special registration process
    // or is supposed to authenticate in some other way
    $password = $user->getPassword();

    if (TRUE === empty($password)) {
      $this->logger->error('Failed to initiate password reset for user "' . $username . '": no password present');
      $this->addLocalizedFlashMessage('resetPassword.failed.nopassword', NULL, FlashMessage::ERROR);
      $this->redirect('showPasswordResetForm');
    }

    $hash = $this->passwordResetService->generatePasswordResetHash($user);

    $mailSent = FALSE;

    try {
      $mailSent = $this->passwordResetService->sendMail($user, $hash);
    } catch (\Swift_SwiftException $e) {
      $this->logger->error($e->getMessage);
    }

    if ($mailSent) {
      $this->addLocalizedFlashMessage('resetPassword.started', array($user->getEmail()), FlashMessage::INFO);
    } else {
      $this->addLocalizedFlashMessage('resetPassword.failed.sending', array($user->getEmail()), FlashMessage::ERROR);
    }

    $this->redirect('showPasswordResetForm');
  }

  /**
   * Initialize complete password reset
   *
   * @return void
   */
  protected function initializeCompletePasswordResetAction() {
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

          if ($this->settingService->getSettingValue('passwordReset.loginOnSuccess')) {
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

    $loginPageUid = $this->settingService->getSettingValue('login.page');
    $this->redirect('showLoginForm', NULL, NULL, NULL, $loginPageUid);
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
  protected function addLocalizedFlashMessage($translationKey, array $translationArguments = NULL, $severity) {

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
}
