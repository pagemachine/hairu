<?php
namespace PAGEmachine\Hairu\Validation\Validator;

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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator for equality of two values
 */
class EqualValidator extends AbstractValidator {

  /**
   * @var array
   */
  protected $supportedOptions = array(
    'equalTo' => array(NULL, 'Another value to compare with', 'mixed', TRUE),
    'strict' => array(FALSE, 'TRUE for strict comparison (including type), FALSE otherwise', 'boolean'),
    'negate' => array(FALSE, 'TRUE to validate against not equal, FALSE for equal', 'boolean'),
  );

  /**
   * Checks if the given value is (not) equal to another value
   *
   * If at least one error occurred, the result is FALSE.
   *
   * @param mixed $value The value that should be validated
   * @return boolean TRUE if the value is valid, FALSE if an error occurred
   */
  public function isValid($value) {

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

      return FALSE;
    }

    return TRUE;
  }
}
