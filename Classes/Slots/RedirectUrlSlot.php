<?php
namespace PAGEmachine\Hairu\Slots;

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
