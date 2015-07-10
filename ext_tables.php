<?php
defined('TYPO3_MODE') or die();

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
