<?php
namespace PAGEmachine\Hairu\Domain\Service;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Service for password-related tasks
 */
class PasswordService implements \TYPO3\CMS\Core\SingletonInterface {

  /**
   * Applies transformations to a given plain text password, e.g. hashing
   *
   * @param string $password
   * @return string
   */
  public function applyTransformations($password) {

    if (ExtensionManagementUtility::isLoaded('saltedpasswords')) {

      if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')) {

        $saltingInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
        $password = $saltingInstance->getHashedPassword($password);
      }
    }

    return $password;
  }
}
