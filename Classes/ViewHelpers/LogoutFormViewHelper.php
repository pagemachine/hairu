<?php
namespace PAGEmachine\Hairu\ViewHelpers;

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

use PAGEmachine\Hairu\LoginType;
use PAGEmachine\Hairu\ViewHelpers\Form\AbstractAuthenticationFormViewHelper;

/**
 * Login form view helper. Generates a <form> tag.
 *
 * = Example =
 *
 * A user storage page uid has to be specified in every case:
 *
 * <code title="Example">
 * <h:loginForm userStoragePageUid="...">...</h:loginForm>
 * </code>
 *
 * Most of the other arguments are the same as in <f:form/>
 */
class LogoutFormViewHelper extends AbstractAuthenticationFormViewHelper {

  /**
   * Render the form.
   *
   * @param integer $pageUid Target page uid
   * @param integer $pageType Target page type
   * @param boolean $noCache set this to disable caching for the target page. You should not need this.
   * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
   * @param string $section The anchor to be added to the action URI (only active if $actionUri is not set)
   * @param string $format The requested format (e.g. ".html") of the target page (only active if $actionUri is not set)
   * @param array $additionalParams additional action URI query parameters that won't be prefixed like $arguments (overrule $arguments) (only active if $actionUri is not set)
   * @param boolean $absolute If set, an absolute action URI is rendered (only active if $actionUri is not set)
   * @param boolean $addQueryString If set, the current query parameters will be kept in the action URI (only active if $actionUri is not set)
   * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the action URI. Only active if $addQueryString = TRUE and $actionUri is not set
   * @param string $actionUri can be used to overwrite the "action" attribute of the form tag
   * @return string rendered form
   */
  public function render($pageUid = NULL, $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $format = '', array $additionalParams = array(), $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array(), $actionUri = NULL) {

    $this->setFormActionUri();
    $this->setFormMethod();

    $content = $this->renderHiddenLoginTypeField(LoginType::LOGOUT);
    $content .= $this->renderChildren();

    $this->tag->setContent($content);

    return $this->tag->render();
  }
}
