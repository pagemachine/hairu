<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
  'PAGEmachine.Hairu',
  'Auth',
  array(
    'Authentication' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
  ),
  array(
    'Authentication' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
  )
);

if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['hairu_token'])) {

  $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['hairu_token'] = array(
    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
    'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
    'groups' => array(
      'system',
    ),
  );
}

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
  'PAGEmachine\\Hairu\\Controller\\AuthenticationController',
  'afterLogin',
  'PAGEmachine\\Hairu\\Slots\\RedirectUrlSlot',
  'processRedirect'
);
$signalSlotDispatcher->connect(
  'PAGEmachine\\Hairu\\Controller\\AuthenticationController',
  'afterLogout',
  'PAGEmachine\\Hairu\\Slots\\RedirectUrlSlot',
  'processRedirect'
);
unset($signalSlotDispatcher);
