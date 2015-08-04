<?php

namespace PAGEmachine\Hairu\Service;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Philipp Kerling <pkerling@casix.org>
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
 * ************************************************************* */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @api
 */
class PasswordResetService implements \TYPO3\CMS\Core\SingletonInterface {

  /**
   * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
   * @inject
   */
  protected $objectManager;

  /**
   * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
   */
  protected $tokenCache;

  /**
   * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
   * @inject
   */
  protected $hashService;

  /**
   * @var \PAGEmachine\Hairu\Service\SettingService
   * @inject
   */
  protected $settingService;

  /**
   * @param \TYPO3\CMS\Core\Cache\CacheManager $cacheManager
   * @return void
   */
  public function injectCacheManager(\TYPO3\CMS\Core\Cache\CacheManager $cacheManager) {
    $this->tokenCache = $cacheManager->getCache('hairu_token');
  }

  public function generatePasswordResetHash(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user) {
    $tokenLifetime = $this->settingService->getSettingValue('passwordReset.token.lifetime');
    $hash = \md5(GeneralUtility::generateRandomBytes(64));
    $token = array(
      'uid' => $user->getUid(),
      'hmac' => $this->hashService->generateHmac($user->getPassword()),
    );

    // Remove possibly existing reset tokens and store new one
    $this->tokenCache->flushByTag($user->getUid());
    $this->tokenCache->set($hash, $token, array($user->getUid()), $tokenLifetime);

    return $hash;
  }

  public function sendMail(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user, $hash, $templateAction = 'passwordResetMail') {
    /* @var $view \PAGEmachine\Hairu\Mvc\View\StandaloneView */
    $view = $this->objectManager->get('PAGEmachine\Hairu\Mvc\View\StandaloneView');
    $view->initializeView();
    /*if (NULL !== $overrideTemplatePath) {
      $templateRootPaths = $view->getTemplateRootPaths();
      array_unshift($templateRootPaths, $overrideTemplatePath);
      $view->setTemplateRootPaths($templateRootPaths);
    }*/

    $hashUri = $view->getUriBuilder()->reset()
      ->setTargetPageUid($this->settingService->getSettingValue('passwordReset.page'))
      ->setUseCacheHash(FALSE)
      ->setCreateAbsoluteUri(TRUE)
      ->uriFor('showPasswordResetForm', array(
        'hash' => $hash,
      ));
    $tokenLifetime = $this->settingService->getSettingValue('passwordReset.token.lifetime');
    $expiryDate = new \DateTime(sprintf('now + %d seconds', $tokenLifetime));
    $view->assignMultiple(array(
      'user' => $user,
      'hash' => $hash, // Allow for custom URI in Fluid
      'hashUri' => $hashUri,
      'expiryDate' => $expiryDate,
    ));

    /* @var $message \TYPO3\CMS\Core\Mail\MailMessage */
    $message = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
    $message
      ->setFrom($this->settingService->getSettingValue('passwordReset.mail.from'))
      ->setTo($user->getEmail())
      ->setSubject($this->settingService->getSettingValue('passwordReset.mail.subject'));

    $view->setFormat('txt');
    $message->setBody($view->render($templateAction), 'text/plain');

    if ($this->settingService->getSettingValue('passwordReset.mail.html')) {
      $view->setFormat('html');
      $message->addPart($view->render($templateAction), 'text/html');
    }

    return ((integer) 1 === $message->send());
  }

}
