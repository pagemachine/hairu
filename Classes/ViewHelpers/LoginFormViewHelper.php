<?php

declare(strict_types=1);

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

use PAGEmachine\Hairu\LoginType;
use PAGEmachine\Hairu\ViewHelpers\Form\AbstractAuthenticationFormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class LoginFormViewHelper extends AbstractAuthenticationFormViewHelper
{
    /**
     * List of JavaScript code snippets to invoke on form submit
     *
     * @var array
     */
    protected $submitJavaScriptCode = [];

    /**
     * List of additional hidden form fields
     *
     * @var array
     */
    protected $additionalHiddenFields = [];

    /**
     * Gets additional code for login forms based on the
     * TYPO3_CONF_VARS/EXTCONF/felogin/loginFormOnSubmitFuncs hook
     *
     * Will be invoked just before the render method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $parameters = [];

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] ?? [] as $callback) {
            $result = GeneralUtility::callUserFunction($callback, $parameters, $this);

            if (isset($result[0])) {
                $this->submitJavaScriptCode[] = $result[0];
            }

            if (isset($result[1])) {
                $this->additionalHiddenFields[] = $result[1];
            }
        }
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('userStoragePageUid', 'int', 'Storage page uid where user records are located');
    }

    /**
     * Render the form.
     *
     * @return string rendered form
     */
    public function render()
    {
        $this->setFormActionUri();
        $this->setFormMethod();
        $this->setFormOnSubmit();

        $content = $this->renderHiddenLoginTypeField(LoginType::LOGIN);
        $content .= $this->renderHiddenUserStoragePageUidField($this->arguments['userStoragePageUid']);
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
    protected function setFormOnSubmit()
    {
        $this->tag->addAttribute('onsubmit', implode(';', $this->submitJavaScriptCode));
    }

    /**
     * Renders a hidden form field indicating the storage pid of user records
     *
     * @param int $userStoragePageUid Storage page uid where user records are located
     * @return string
     */
    protected function renderHiddenUserStoragePageUidField($userStoragePageUid)
    {
        return LF . '<input type="hidden" name="pid" value="' . $userStoragePageUid . '" />' . LF;
    }

    /**
     * Renders additional hidden form fields
     *
     * @return string
     */
    protected function renderAdditionalHiddenFields()
    {
        return LF . implode(LF, $this->additionalHiddenFields);
    }
}
