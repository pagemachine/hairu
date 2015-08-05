<?php
namespace PAGEmachine\Hairu\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
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
class LoginFormViewHelper extends AbstractAuthenticationFormViewHelper {

  /**
   * List of JavaScript code snippets to invoke on form submit
   *
   * @var array
   */
  protected $submitJavaScriptCode = array();

  /**
   * List of additional hidden form fields
   *
   * @var array
   */
  protected $additionalHiddenFields = array();

  /**
   * Gets additional code for login forms based on the
   * TYPO3_CONF_VARS/EXTCONF/felogin/loginFormOnSubmitFuncs hook
   *
   * Will be invoked just before the render method.
   *
   * @return void
   */
  public function initialize() {

    parent::initialize();

    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])) {

      $parameters = array();

      foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $callback) {

        $result = GeneralUtility::callUserFunction($callback, $parameters, $this);

        if (isset($result[0])) {

          $this->submitJavaScriptCode[] = $result[0];
        }

        if (isset($result[1])) {

          $this->additionalHiddenFields[] = $result[1];
        }
      }
    }
  }

  /**
   * Render the form.
   *
   * @param integer $userStoragePageUid Storage page uid where user records are located
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
  public function render($userStoragePageUid, $pageUid = NULL, $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $format = '', array $additionalParams = array(), $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array(), $actionUri = NULL) {

    $this->setFormActionUri();
    $this->setFormMethod();
    $this->setFormOnSubmit();

    $content = $this->renderHiddenLoginTypeField(LoginType::LOGIN);
    $content .= $this->renderHiddenUserStoragePageUidField($userStoragePageUid);
    $content .= $this->renderAdditionalHiddenFields();
    $content .= $this->renderChildren();

    $this->tag->setContent($content);

    return $this->tag->render();
  }

  /**
   * Sets the "onsubmit" attribute of the form tag
   *
   * @return void
   */
  protected function setFormOnSubmit() {

    $this->tag->addAttribute('onsubmit', implode(';', $this->submitJavaScriptCode));
  }

  /**
   * Renders a hidden form field indicating the storage pid of user records
   *
   * @param integer $userStoragePageUid Storage page uid where user records are located
   * @return string
   */
  protected function renderHiddenUserStoragePageUidField($userStoragePageUid) {

    return LF . '<input type="hidden" name="pid" value="' . $userStoragePageUid .'" />' . LF;
  }

  /**
   * Renders additional hidden form fields
   *
   * @return string
   */
  protected function renderAdditionalHiddenFields() {

    return LF . implode(LF, $this->additionalHiddenFields);
  }
}
