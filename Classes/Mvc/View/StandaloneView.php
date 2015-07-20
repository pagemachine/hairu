<?php

namespace PAGEmachine\Hairu\Mvc\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class StandaloneView extends \TYPO3\CMS\Fluid\View\TemplateView {

  public function __construct($controllerObjectName = 'PAGEmachine\Hairu\Controller\AuthenticationController') {
    parent::__construct();

    /* @var $request \TYPO3\CMS\Extbase\Mvc\Web\Request */
    $request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
    $request->setRequestURI(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
    $request->setBaseURI(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
    // Set correct extension context
    $request->setControllerObjectName($controllerObjectName);
    /** @var $controllerContext \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext */
    $controllerContext = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext');
    $controllerContext->setRequest($request);
    $uriBuilder = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder');
    $uriBuilder->setRequest($request);
    $controllerContext->setUriBuilder($uriBuilder);
    /** @var $renderingContext \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext */
    $renderingContext = $this->objectManager->get('TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface');
    $renderingContext->setControllerContext($controllerContext);
    $this->setRenderingContext($renderingContext);
  }

  // From extbase ActionController

  /**
   * Handles the path resolving for *rootPath(s)
   * singular one is deprecated and will be removed two versions after 6.2
   * if deprecated setting is found, use it as the very last fallback target
   *
   * numerical arrays get ordered by key ascending
   *
   * @param array $extbaseFrameworkConfiguration
   * @param string $setting parameter name from TypoScript
   * @param string $deprecatedSetting parameter name from TypoScript
   *
   * @return array
   */
  protected function getViewProperty($extbaseFrameworkConfiguration, $setting, $deprecatedSetting = '') {
    $values = array();

    if (
            !empty($extbaseFrameworkConfiguration['view'][$setting]) && is_array($extbaseFrameworkConfiguration['view'][$setting])
    ) {
      $values = \TYPO3\CMS\Extbase\Utility\ArrayUtility::sortArrayWithIntegerKeys($extbaseFrameworkConfiguration['view'][$setting]);
      $values = array_reverse($values, TRUE);
    }

    // @todo remove handling of deprecatedSetting two versions after 6.2
    if (
            isset($extbaseFrameworkConfiguration['view'][$deprecatedSetting]) && strlen($extbaseFrameworkConfiguration['view'][$deprecatedSetting]) > 0
    ) {
      $values[] = $extbaseFrameworkConfiguration['view'][$deprecatedSetting];
    }

    return $values;
  }

  public function initializeView() {
    parent::initializeView();

    // Resolve paths
    /* @var $configurationManager \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface */
    $configurationManager = $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
    $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $this->controllerContext->getRequest()->getControllerExtensionName(),
            $this->controllerContext->getRequest()->getPluginName()
    );

    $paths = array('template', 'layout', 'partial');
    foreach ($paths as $path) {
      $viewFunctionName = 'set' . \ucfirst($path) . 'RootPaths';
      $deprecatedSetting = $path . 'RootPath';
      $setting = $path . 'RootPaths';
      $parameter = $this->getViewProperty($extbaseFrameworkConfiguration, $setting, $deprecatedSetting);
      // no need to bother if there is nothing to set
      if ($parameter) {
        $this->$viewFunctionName($parameter);
      }
    }

    $settingService = $this->objectManager->get('PAGEmachine\Hairu\Service\SettingService');
    $this->assign('settings', $settingService->getSettings());
  }

  /**
   * Sets the format of the current request (default format is "html")
   *
   * @param string $format
   * @return void
   */
  public function setFormat($format) {
    $this->controllerContext->getRequest()->setFormat($format);
  }

  /**
   * Returns the format of the current request (defaults is "html")
   *
   * @return string $format
   */
  public function getFormat() {
    return $this->controllerContext->getRequest()->getFormat();
  }

  /**
   * Returns the UriBuilder used by this view
   *
   * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
   */
  public function getUriBuilder() {
    return $this->controllerContext->getUriBuilder();
  }

}
