<?php
namespace PAGEmachine\Hairu\Domain\Repository;

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

class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository {

  /**
   * Replaces an existing object with the same identifier by the given object
   *
   * @param object $modifiedObject The modified object
   * @return void
   */
  public function update($modifiedObject) {

    $this->persistenceManager->update($modifiedObject);
  }
}
