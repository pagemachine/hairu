<?php
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

use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Redirects to an URL defined via request
 */
class RedirectUrlSlot {

  /**
   * Performs a redirect if possible
   *
   * @param RequestInterface $request
   * @param array $settings
   * @return void
   */
  public function processRedirect(RequestInterface $request, array $settings) {

    $formData = $request->getArgument('formData');
    $redirectUrl = NULL;

    // May be set via config.typolinkLinkAccessRestrictedPages_addParams
    if (!empty($formData['return_url'])) {

      $redirectUrl = $formData['return_url'];
    }

    // May be set by anything
    if (!empty($formData['redirect_url'])) {

      $redirectUrl = $formData['redirect_url'];
    }

    if ($redirectUrl !== NULL) {

      HttpUtility::redirect($redirectUrl);
    }
  }
}
