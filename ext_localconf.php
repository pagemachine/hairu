<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
  'PAGEmachine.Hairu',
  'Login',
  array(
    'Login' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset',
  ),
  array(
    'Login' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset',
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
