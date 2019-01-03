<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Mvc\Controller;

/*
 * This file is part of the PAGEmachine Hairu project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController as ExtbaseActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

class ActionController extends ExtbaseActionController
{
    /**
     * Adds the needed validators to the Arguments:
     *
     * - Validators checking the data type from the @param annotation
     * - Custom validators specified with validate annotations.
     * - Model-based validators (validate annotations in the model)
     * - Custom model validator classes
     * - Validators from framework configuration
     *
     * @return void
     */
    protected function initializeActionMethodValidators()
    {
        parent::initializeActionMethodValidators();

        $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $actionArgumentValidation = $frameworkConfiguration['mvc']['validation'][$this->request->getControllerName()][$this->request->getControllerActionName()] ?? [];

        // Dynamically add argument validators
        foreach ($actionArgumentValidation as $argumentName => $validators) {
            try {
                $argumentValidator = $this->arguments->getArgument($argumentName)->getValidator();
            } catch (NoSuchArgumentException $e) {
                continue;
            }

            $validatorConjunction = $this->validatorResolver->createValidator('TYPO3.CMS.Extbase:Conjunction');

            foreach ($validators as $validatorConfiguration) {
                if (isset($validatorConfiguration['type'])) {
                    $validator = $this->validatorResolver->createValidator($validatorConfiguration['type'], $validatorConfiguration['options'] ?? []);
                    $validatorConjunction->addValidator($validator);
                }
            }

            if (count($validatorConjunction)) {
                $argumentValidator->addValidator($validatorConjunction);
            }
        }
    }
}
