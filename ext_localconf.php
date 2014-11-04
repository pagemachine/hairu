<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
  'PAGEmachine.Hairu',
  'Login',
  array(
    'Login' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
  ),
  array(
    'Login' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
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
