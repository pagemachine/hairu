<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'PAGEmachine.Hairu',
    'Auth',
    [
        'Authentication' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
    ],
    [
        'Authentication' => 'showLoginForm, showLogoutForm, showPasswordResetForm, startPasswordReset, completePasswordReset',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'PAGEmachine.Hairu',
    'Password',
    [
        'Password' => 'showPasswordUpdateForm,updatePassword',
    ],
    [
        'Password' => 'showPasswordUpdateForm,updatePassword',
    ]
);

// Cache configuration
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['hairu_token'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['hairu_token'] = [
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'groups' => [
            'system',
        ],
    ];
}

// Logging configuration
if (empty($GLOBALS['TYPO3_CONF_VARS']['LOG']['PAGEmachine']['Hairu']['writerConfiguration'])) {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['PAGEmachine']['Hairu']['writerConfiguration'] = [
        // DEBUG and higher severity
        \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => 'typo3temp/logs/hairu.log',
            ],
        ],
    ];
}

call_user_func(function () {
    // New content element wizard icon
    $icons = [
        'hairu-wizard-icon' => 'login.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:hairu/Resources/Public/Icons/' . $path]
        );
    }

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \PAGEmachine\Hairu\Controller\AuthenticationController::class,
        'afterLogin',
        \PAGEmachine\Hairu\Slots\RedirectUrlSlot::class,
        'processRedirect'
    );
    $signalSlotDispatcher->connect(
        \PAGEmachine\Hairu\Controller\AuthenticationController::class,
        'afterLogout',
        \PAGEmachine\Hairu\Slots\RedirectUrlSlot::class,
        'processRedirect'
    );
});
