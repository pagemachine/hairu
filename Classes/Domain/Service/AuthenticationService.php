<?php
namespace PAGEmachine\Hairu\Domain\Service;

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

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

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
   * @param DomainObjectInterface $user
   * @return void
   */
  public function authenticateUser(DomainObjectInterface $user) {

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
