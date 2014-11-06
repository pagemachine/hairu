<?php
namespace PAGEmachine\Hairu\Domain\Service;

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

use PAGEmachine\Hairu\Domain\Model\FrontendUser;

class AuthenticationService implements \TYPO3\CMS\Core\SingletonInterface {

  /**
   * @var \PAGEmachine\Hairu\Domain\Repository\FrontendUserRepository
   * @inject
   */
  protected $frontendUserRepository;

  /**
   * Returns whether any user is currently authenticated
   *
   * @return boolean
   */
  public function isUserAuthenticated() {

    return $this->getFrontendController()->loginUser;
  }

  /**
   * Returns the currently authenticated user
   *
   * @return FrontendUser
   */
  public function getAuthenticatedUser() {

    return $this->frontendUserRepository->findByIdentifier($this->getFrontendController()->fe_user->user['uid']);
  }

  /**
   * Authenticates a frontend user
   *
   * @param FrontendUser $user
   * @return void
   */
  public function authenticateUser(FrontendUser $user) {

    $frontendController = $this->getFrontendController();
    $frontendController->fe_user->createUserSession($user->_getCleanProperties());
    $frontendController->fe_user->loginSessionStarted = TRUE;
    $frontendController->fe_user->user = $frontendController->fe_user->fetchUserSession();
    $frontendController->loginUser = TRUE;
  }

  /**
   * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
   */
  protected function getFrontendController() {

    return $GLOBALS['TSFE'];
  }
}
