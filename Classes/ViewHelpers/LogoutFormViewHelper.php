<?php
declare(strict_types = 1);

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
class LogoutFormViewHelper extends AbstractAuthenticationFormViewHelper
{
    /**
     * Render the form.
     *
     * @return string rendered form
     */
    public function render()
    {
        $this->setFormActionUri();
        $this->setFormMethod();

        $content = $this->renderHiddenLoginTypeField(LoginType::LOGOUT);
        $content .= $this->renderChildren();

        $this->tag->setContent($content);

        return $this->tag->render();
    }
}
