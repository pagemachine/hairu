<?php
declare(strict_types = 1);
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

use PAGEmachine\Hairu\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Service for authentication tasks
 */
class AuthenticationService implements SingletonInterface
{
    /**
     * @var FrontendUserRepository $frontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * Returns whether any user is currently authenticated
     *
     * @return bool
     */
    public function isUserAuthenticated(): bool
    {
        return $this->getFrontendController()->loginUser;
    }

    /**
     * Returns the currently authenticated user
     *
     * @return FrontendUser
     */
    public function getAuthenticatedUser()
    {
        return $this->frontendUserRepository->findByIdentifier($this->getFrontendController()->fe_user->user['uid']);
    }

    /**
     * Authenticates a frontend user
     *
     * @param DomainObjectInterface $user
     * @return void
     */
    public function authenticateUser(DomainObjectInterface $user)
    {
        $frontendController = $this->getFrontendController();
        $frontendController->fe_user->createUserSession($user->_getCleanProperties());
        $frontendController->fe_user->loginSessionStarted = true;
        $frontendController->fe_user->user = $frontendController->fe_user->fetchUserSession();
        $frontendController->loginUser = true;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
