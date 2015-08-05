<?php
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
 * Validator for frontend users
 */
class ValidFrontendUserValidator extends AbstractValidator {

  /**
   * @var \PAGEmachine\Hairu\Domain\Repository\FrontendUserRepository
   * @inject
   */
  protected $frontendUserRepository;

  /**
   * @var array
   */
  protected $supportedOptions = array(
    'property' => array('', 'The property to use for frontend user lookup', 'string', TRUE)
  );

  /**
   * Checks if the given value is a valid frontend user.
   *
   * If at least one error occurred, the result is FALSE.
   *
   * @param mixed $value The value that should be validated
   * @return boolean TRUE if the value is valid, FALSE if an error occurred
   */
  public function isValid($value) {

    $countMethod = 'countBy' . ucfirst($this->options['property']);
    $count = $this->frontendUserRepository->$countMethod($value);

    if ($count === 0) {

      $this->addError(
        $this->translateErrorMessage(
          'validator.validFrontendUser.invalid',
          'hairu',
          array($value)
        ),
        1415096884
      );

      return FALSE;
    }

    return TRUE;
  }
}
