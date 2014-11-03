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
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use PAGEmachine\Hairu\LoginType;

class LoginController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

  /**
   * @var \PAGEmachine\Hairu\Authentication\AuthenticationService
   * @inject
   */
  protected $authenticationService;

  /**
   * Initialize all actions
   *
   * @return void
   */
  protected function initializeAction() {

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

    $view->assign('formData', $this->request->getArgument('formData'));
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
    $loginFailed = FALSE;
    $logoutSuccessful = FALSE;

    if (isset($formData['logintype'])) {

      switch ($formData['logintype']) {

        case LoginType::LOGIN:

          $loginFailed = TRUE;
          break;

        case LoginType::LOGOUT:

          $logoutSuccessful = TRUE;
          break;
      }
    }

    list($submitJavaScript, $additionalHiddenFields) = $this->getAdditionalLoginFormCode();

    $this->view->assignMultiple(array(
      'logintype' => LoginType::LOGIN,
      'submitJavaScript' => $submitJavaScript,
      'additionalHiddenFields' => $additionalHiddenFields,
      'loginFailed' => $loginFailed,
      'logoutSuccessful' => $logoutSuccessful,
    ));
  }

  /**
   * Logout form view
   *
   * @return void
   */
  public function showLogoutFormAction() {

    $formData = $this->request->getArgument('formData');
    $loginSuccessful = $this->authenticationService->isUserAuthenticated()
      && isset($formData['logintype'])
      && $formData['logintype'] === LoginType::LOGIN;

    $this->view->assignMultiple(array(
      'logintype' => LoginType::LOGOUT,
      'loginSuccessful' => $loginSuccessful,
      'user' => $this->authenticationService->getAuthenticatedUser(),
    ));
  }

  /**
   * Gets additional code for login forms based on the
   * TYPO3_CONF_VARS/EXTCONF/felogin/loginFormOnSubmitFuncs hook
   *
   * @return array Array containing code for submit JavaScript
   *                     and additional hidden fields
   */
  protected function getAdditionalLoginFormCode() {

    $submitJavaScript = array();
    $additionalHiddenFields = array();

    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])) {

      $parameters = array();

      foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $callback) {

        $result = GeneralUtility::callUserFunction($callback, $parameters, $this);

        if (isset($result[0])) {

          $submitJavaScript[] = $result[0];
        }

        if (isset($result[1])) {

          $additionalHiddenFields[] = $result[1];
        }
      }
    }

    $submitJavaScript = implode(';', $submitJavaScript);
    $additionalHiddenFields = implode('LF', $additionalHiddenFields);

    return array($submitJavaScript, $additionalHiddenFields);
  }
}
