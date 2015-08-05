<?php
namespace PAGEmachine\Hairu;

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

class LoginType extends \TYPO3\CMS\Core\Type\Enumeration {

  /**
   * Indicates a login
   */
  const LOGIN = 'login';

  /**
   * Indicates a logout
   */
  const LOGOUT = 'logout';
}
