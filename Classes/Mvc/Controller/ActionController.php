<?php
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

class ActionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
  protected function initializeActionMethodValidators() {

    parent::initializeActionMethodValidators();

    $frameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
    $actionArgumentValidation = !empty($frameworkConfiguration['mvc']['validation'][$this->request->getControllerName()][$this->request->getControllerActionName()])
      ? $frameworkConfiguration['mvc']['validation'][$this->request->getControllerName()][$this->request->getControllerActionName()]
      : array();

    // Dynamically add argument validators
    foreach ($actionArgumentValidation as $argumentName => $validators) {

      try {

        $argumentValidator = $this->arguments->getArgument($argumentName)->getValidator();
      } catch (\TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException $e) {

        continue;
      }

      $validatorConjunction = $this->validatorResolver->createValidator('TYPO3.CMS.Extbase:Conjunction');

      foreach ($validators as $validatorConfiguration) {

        if (isset($validatorConfiguration['type'])) {

          $validatorType = $validatorConfiguration['type'];
          $validatorOptions = isset($validatorConfiguration['options']) ? $validatorConfiguration['options'] : array();
          $validator = $this->validatorResolver->createValidator($validatorType, $validatorOptions);
          $validatorConjunction->addValidator($validator);
        }
      }

      if (count($validatorConjunction)) {

        $argumentValidator->addValidator($validatorConjunction);
      }
    }
  }
}
