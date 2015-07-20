<?php

namespace PAGEmachine\Hairu\Service;

use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class SettingService implements \TYPO3\CMS\Core\SingletonInterface {

  /**
   * @var array
   */
  protected $settings;

  /**
   * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
   */
  protected $configurationManager;

  /**
   * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
   * @return void
   */
  public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
    $this->configurationManager = $configurationManager;
    $originalSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'Hairu', 'Auth');

    $defaultSettings = array(
      'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
      'login' => array(
        'page' => $this->getFrontendController()->id,
      ),
      'passwordReset' => array(
        'loginOnSuccess' => FALSE,
        'mail' => array(
          'from' => MailUtility::getSystemFromAddress(),
          'subject' => 'Password reset request',
          'html' => FALSE
        ),
        'page' => $this->getFrontendController()->id,
        'token' => array(
          'lifetime' => 86400, // 1 day
        ),
      ),
    );

    $settings = $defaultSettings;
    ArrayUtility::mergeRecursiveWithOverrule($settings, $originalSettings, TRUE, FALSE);
    $this->settings = $settings;
  }

  /**
   * @return array
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Shorthand helper for getting setting values with optional default values
   *
   * Any setting value is automatically processed via stdWrap if configured.
   *
   * @param string $settingPath Path to the setting, e.g. "foo.bar.qux"
   * @param mixed $defaultValue Default value if no value is set
   * @return mixed
   */
  public function getSettingValue($settingPath, $defaultValue = NULL) {

    $value = ObjectAccess::getPropertyPath($this->settings, $settingPath);
    $stdWrapConfiguration = ObjectAccess::getPropertyPath($this->settings, $settingPath . '.stdWrap');

    if ($stdWrapConfiguration !== NULL) {
      $value = $this->getFrontendController()->cObj->stdWrap($value, $stdWrapConfiguration);
    }

    // Change type of value to type of default value if possible
    if (!empty($value) && $defaultValue !== NULL) {
      settype($value, gettype($defaultValue));
    }

    $value = !empty($value) ? $value : $defaultValue;

    return $value;
  }

  public function setSettingValue($settingPath, $settingValue) {
    $this->settings = ArrayUtility::setValueByPath($this->settings, $settingPath, $settingValue, '.');
  }

  /**
   * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
   */
  protected function getFrontendController() {
    return $GLOBALS['TSFE'];
  }

}