<?php

declare(strict_types=1);

namespace PAGEmachine\Hairu\Slots;

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

use PAGEmachine\Hairu\Validation\Validator\RedirectUrlValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Redirects to an URL defined via request
 */
class RedirectUrlSlot
{
    /**
     * @var RedirectUrlValidator $redirectUrlValidator
     */
    protected $redirectUrlValidator;

    public function __construct()
    {
        $this->redirectUrlValidator = GeneralUtility::makeInstance(RedirectUrlValidator::class);
    }

    /**
     * Performs a redirect if possible
     *
     * @param RequestInterface $request
     * @return void
     */
    public function processRedirect(RequestInterface $request)
    {
        $formData = $request->getArgument('formData');
        $redirectUrl = null;

        // May be set by anything
        if (!empty($formData['redirect_url'])) {
            $redirectUrl = $formData['redirect_url'];
        }

        // May be set via config.typolinkLinkAccessRestrictedPages_addParams
        if (!empty($formData['return_url'])) {
            $redirectUrl = $formData['return_url'];
        }

        if ($redirectUrl !== null && $this->redirectUrlValidator->isValid($redirectUrl)) {
            HttpUtility::redirect($redirectUrl);
        }
    }
}
