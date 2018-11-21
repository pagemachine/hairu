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

use PAGEmachine\Hairu\LoginType;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

abstract class AbstractAuthenticationFormViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'form';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('pageUid', 'int', 'Target page uid');
        $this->registerArgument('object', 'mixed', 'Object to use for the form. Use in conjunction with the "property" attribute on the sub tags');
        $this->registerArgument('pageType', 'int', 'Target page type', false, 0);
        $this->registerArgument('noCache', 'bool', 'set this to disable caching for the target page. You should not need this.', false, false);
        $this->registerArgument('noCacheHash', 'bool', 'set this to suppress the cHash query parameter created by TypoLink. You should not need this.', false, false);
        $this->registerArgument('section', 'string', 'The anchor to be added to the action URI (only active if $actionUri is not set)', false, '');
        $this->registerArgument('format', 'string', 'The requested format (e.g. ".html") of the target page (only active if $actionUri is not set)', false, '');
        $this->registerArgument('additionalParams', 'array', 'additional action URI query parameters that won\'t be prefixed like $arguments (overrule $arguments) (only active if $actionUri is not set)', false, []);
        $this->registerArgument('absolute', 'bool', 'If set, an absolute action URI is rendered (only active if $actionUri is not set)', false, false);
        $this->registerArgument('addQueryString', 'bool', 'If set, the current query parameters will be kept in the action URI (only active if $actionUri is not set)', false, false);
        $this->registerArgument('argumentsToBeExcludedFromQueryString', 'array', 'arguments to be removed from the action URI. Only active if $addQueryString = TRUE and $actionUri is not set', false, []);
        $this->registerArgument('addQueryStringMethod', 'string', 'Method to use when keeping query parameters (GET or POST, only active if $actionUri is not set', false, 'GET');
        $this->registerArgument('fieldNamePrefix', 'string', 'Prefix that will be added to all field names within this form. If not set the prefix will be tx_yourExtension_plugin');
        $this->registerArgument('actionUri', 'string', 'can be used to overwrite the "action" attribute of the form tag');

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
    protected function setFormActionUri()
    {
        if ($this->hasArgument('actionUri')) {
            $formActionUri = $this->arguments['actionUri'];
        } else {
            $formActionUri = $this->getUriBuilder()
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
    protected function setFormMethod()
    {
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
    protected function renderHiddenLoginTypeField($loginType)
    {
        $loginType = LoginType::cast($loginType); // Ensure valid value

        return LF . '<input type="hidden" name="logintype" value="' . $loginType . '" />' . LF;
    }

    /**
     * Get the UriBuilder
     *
     * @return UriBuilder
     */
    private function getUriBuilder()
    {
        if ($this->renderingContext instanceof RenderingContext) { // TYPO3v9+
            return $this->renderingContext->getControllerContext()->getUriBuilder();
        }

        return $this->controllerContext->getUriBuilder();
    }
}
