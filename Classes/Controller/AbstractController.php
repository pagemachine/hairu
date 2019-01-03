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

use PAGEmachine\Hairu\Domain\Repository\FrontendUserRepository;
use PAGEmachine\Hairu\Domain\Service\AuthenticationService;
use PAGEmachine\Hairu\Domain\Service\PasswordService;
use PAGEmachine\Hairu\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract methods and properties to used in both controllers
 */
abstract class AbstractController extends ActionController
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
     * @var AuthenticationService $authenticationService
     */
    protected $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     */
    public function injectAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @var PasswordService $passwordService
     */
    protected $passwordService;

    /**
     * @param PasswordService $passwordService
     */
    public function injectPasswordService(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Shorthand helper for adding localized flash messages
     *
     * @param string $translationKey
     * @param array $translationArguments
     * @param int $severity
     * @param string $messageTitle
     */
    protected function addLocalizedFlashMessage(string $translationKey, array $translationArguments = null, int $severity = FlashMessage::OK, string $messageTitle = '')
    {
        $this->addFlashMessage(
            LocalizationUtility::translate(
                $translationKey,
                $this->request->getControllerExtensionName(),
                $translationArguments
            ),
            ($messageTitle != '' ? LocalizationUtility::translate($messageTitle, $this->request->getControllerExtensionName(), $translationArguments) : ''),
            $severity
        );
    }

    /**
     * A template method for displaying custom error flash messages, or to
     * display no flash message at all on errors. Override this to customize
     * the flash message in your action controller.
     *
     * @return string|bool The flash message or FALSE if no flash message should be set
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }
}
