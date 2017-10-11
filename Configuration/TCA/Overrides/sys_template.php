<?php
defined('TYPO3_MODE') or die();

// Static template
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'hairu',
    'Configuration/TypoScript',
    'Hairu'
);
