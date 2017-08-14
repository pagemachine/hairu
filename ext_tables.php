<?php
defined('TYPO3_MODE') or die();

// PageTS extensions
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:hairu/Configuration/TSconfig/page.ts">'
);
