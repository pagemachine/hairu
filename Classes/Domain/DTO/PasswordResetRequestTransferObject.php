<?php
namespace PAGEmachine\Hairu\Domain\DTO;

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

use PAGEmachine\Hairu\Domain\Model\FrontendUser;

/**
 * Data Transfer Object for the slot "beforePasswordResetMailSend".
 */
class PasswordResetRequestTransferObject extends AbstractMailDataTransferObject {
	/**
	 * @var	\PAGEmachine\Hairu\Domain\Model\FrontendUser
	 */
	protected $user = null;

	/**
	 * @var	string
	 */
	protected $hash = '';

	/**
	 * @var	string
	 */
	protected $hashUri = '';

	/**
	 * @var	\DateTime
	 */
	protected $expiryDate = null;

	/**
	 * @param	FrontendUser	$user
	 */
	public function setUser(FrontendUser $user) {
		$this->user = $user;
	}

	/**
	 * @return	FrontendUser
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param	string	$hash
	 */
	public function setHash($hash) {
		$this->hash = $hash;
	}

	/**
	 * @return	string
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * @param	string	$hashUri
	 */
	public function setHashUri($hashUri) {
		$this->hashUri = $hashUri;
	}

	/**
	 * @return	string
	 */
	public function getHashUri() {
		return $this->hashUri;
	}

	/**
	 * @param	\DateTime	$expiryDate
	 */
	public function setExpiryDate(\DateTime $expiryDate) {
		$this->expiryDate = $expiryDate;
	}

	/**
	 * @return	\DateTime
	 */
	public function getExpiryDate() {
		return $this->expiryDate;
	}
}
