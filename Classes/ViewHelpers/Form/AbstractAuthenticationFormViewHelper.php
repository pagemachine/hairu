<?php
namespace PAGEmachine\Hairu\ViewHelpers\Form;

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
