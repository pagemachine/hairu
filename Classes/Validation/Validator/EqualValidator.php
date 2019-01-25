<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Validation\Validator;

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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator for equality of two values
 */
class EqualValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'equalTo' => [null, 'Another value to compare with', 'mixed', true],
        'strict' => [false, 'TRUE for strict comparison (including type), FALSE otherwise', 'boolean'],
        'negate' => [false, 'TRUE to validate against not equal, FALSE for equal', 'boolean'],
    ];

    /**
     * Checks if the given value is (not) equal to another value
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     */
    public function isValid($value)
    {
        $otherValue = $this->options['equalTo'];
        $valueIsValid = $this->options['strict'] ? $value === $otherValue : $value == $otherValue;
        $errorMessageTranslationKey = 'validator.equal.invalid';

        if ($this->options['negate']) {
            $valueIsValid = !$valueIsValid;
            $errorMessageTranslationKey = 'validator.equal.negate.invalid';
        }

        if (!$valueIsValid) {
            $this->addError(
                $this->translateErrorMessage(
                    $errorMessageTranslationKey,
                    'hairu'
                ),
                1415185288
            );
        }
    }
}
