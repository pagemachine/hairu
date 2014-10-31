<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
  'PAGEmachine.Hairu',
  'Login',
  'LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.login'
);

// Static template
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
  'hairu',
  'Configuration/TypoScript',
  'Hairu'
);

// PageTS extensions
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
  '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:hairu/Configuration/PageTS/main.ts">'
);
