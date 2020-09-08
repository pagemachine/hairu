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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        if (class_exists(Context::class)) {
            return (bool)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }

        // @extensionScannerIgnoreLine
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
        $frontendController->fe_user->setAndSaveSessionData('dummy', true);
        $frontendController->fe_user->createUserSession($user->_getCleanProperties());
        $frontendController->fe_user->setAndSaveSessionData('dummy', true);
    }

    /**
     * Invalidate all sessions of a frontend user
     *
     * @param DomainObjectInterface $user
     */
    public function invalidateUserSessions(DomainObjectInterface $user)
    {
        $userAuthentication = $this->getFrontendController()->fe_user;
        $sessionManager = GeneralUtility::makeInstance(SessionManager::class);
        $sessionBackend = $sessionManager->getSessionBackend('FE');
        $sessionManager->invalidateAllSessionsByUserId($sessionBackend, (int)$user->getUid(), $userAuthentication);
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
