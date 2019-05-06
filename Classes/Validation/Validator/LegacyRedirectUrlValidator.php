<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Validation\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Custom redirect URL validator for TYPO3v8
 *
 * @deprecated will be removed with support for TYPO3v8
 */
class LegacyRedirectUrlValidator extends RedirectUrlValidator
{
    /**
     * Determines whether the URL matches a domain known to TYPO3.
     *
     * @param string $url Absolute URL which needs to be checked
     * @return bool Whether the URL is considered to be local
     */
    protected function isInLocalDomain(string $url): bool
    {
        if (!GeneralUtility::isValidUrl($url)) {
            return false;
        }

        $parsedUrl = parse_url($url);

        if ($parsedUrl['scheme'] === 'http' || $parsedUrl['scheme'] === 'https') {
            $host = $parsedUrl['host'];
            // Removes the last path segment and slash sequences like /// (if given):
            $path = preg_replace('#/+[^/]*$#', '', $parsedUrl['path']);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_domain');
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            $localDomains = $queryBuilder->select('domainName')
                ->from('sys_domain')
                ->execute()
                ->fetchAll();

            if (is_array($localDomains)) {
                foreach ($localDomains as $localDomain) {
                    // strip trailing slashes (if given)
                    $domainName = rtrim($localDomain['domainName'], '/');

                    if (GeneralUtility::isFirstPartOfStr($host . $path . '/', $domainName . '/')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
