<?php
namespace PAGEmachine\Hairu\ViewHelpers\Form;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use PAGEmachine\Hairu\LoginType;

abstract class AbstractAuthenticationFormViewHelper extends AbstractTagBasedViewHelper {

  /**
   * @var string
   */
  protected $tagName = 'form';

  /**
   * @return void
   */
  public function initializeArguments() {

    $this->registerTagAttribute('enctype', 'string', 'MIME type with which the form is submitted');
    $this->registerTagAttribute('method', 'string', 'Transfer type (GET or POST)');
    $this->registerTagAttribute('name', 'string', 'Name of form');
    $this->registerUniversalTagAttributes();
  }

  /**
   * Sets the "action" attribute of the form tag
   *
   * @return void
   */
  protected function setFormActionUri() {

    if ($this->hasArgument('actionUri')) {

      $formActionUri = $this->arguments['actionUri'];
    } else {

      $formActionUri = $this->controllerContext->getUriBuilder()
        ->reset()
        ->setTargetPageUid($this->arguments['pageUid'])
        ->setTargetPageType($this->arguments['pageType'])
        ->setNoCache($this->arguments['noCache'])
        ->setUseCacheHash(!$this->arguments['noCacheHash'])
        ->setSection($this->arguments['section'])
        ->setCreateAbsoluteUri($this->arguments['absolute'])
        ->setArguments((array) $this->arguments['additionalParams'])
        ->setAddQueryString($this->arguments['addQueryString'])
        ->setArgumentsToBeExcludedFromQueryString((array) $this->arguments['argumentsToBeExcludedFromQueryString'])
        ->setFormat($this->arguments['format'])
        ->build();
    }

    $this->tag->addAttribute('action', $formActionUri);
  }

  /**
   * Sets the "method" attribute of the form tag
   *
   * @return void
   */
  protected function setFormMethod() {

    if (strtolower($this->arguments['method']) === 'get') {

      $this->tag->addAttribute('method', 'get');
    } else {

      $this->tag->addAttribute('method', 'post');
    }
  }

  /**
   * Renders a hidden form field indicating the given login type
   *
   * @param string $loginType Login type, one of \PAGEmachine\Hairu\LoginType
   * @return string
   */
  protected function renderHiddenLoginTypeField($loginType) {

    $loginType = LoginType::cast($loginType); // Ensure valid value

    return LF . '<input type="hidden" name="logintype" value="' . $loginType .'" />' . LF;
  }
}
