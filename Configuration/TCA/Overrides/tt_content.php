<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PAGEmachine.Hairu',
    'Auth',
    'LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.auth'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['hairu_auth'] = 'select_key,recursive';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PAGEmachine.Hairu',
    'Password',
    'LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.password'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['hairu_updatepw'] = 'select_key,recursive';
