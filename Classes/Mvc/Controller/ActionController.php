<?php
namespace PAGEmachine\Hairu\Mvc\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Mathias Brodala <mbrodala@pagemachine.de>, PAGEmachine AG
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
