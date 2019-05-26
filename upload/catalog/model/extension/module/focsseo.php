<?php

class ModelExtensionModuleFOCSSeo extends Model {

  const SETTINGS_GROUP = 'focsseo';
  const SETTINGS_GROUP_KEY = 'focsseo_data';

  protected $languages = null;
  protected $settings = array();
  protected $settings_loaded = false;

  /*
    Returns languages list once
  */
  public function getLanguages () {
    if (is_null($this->languages)) {
      $this->load->model('localisation/language');
      $this->languages = $this->model_localisation_language->getLanguages();
    }

    return $this->languages;
  }

  /*
    Loads module settings once
  */
  protected function loadSettings () {
    $this->load->model('setting/setting');
    $this->settings = $this->model_setting_setting->getSetting(self::SETTINGS_GROUP);
    $this->settings_loaded = true;
  }

  /*
    Get setting value or return default
  */
  public function getSetting ($setting, $default = false) {
    if (!$this->settings_loaded) {
      $this->loadSettings();
      return $this->getSetting($setting, $default);
    }

    if (isset($this->settings[self::SETTINGS_GROUP_KEY][$setting])) {
      return $this->settings[self::SETTINGS_GROUP_KEY][$setting];
    }

    return $default;
  }

  /*
    Get url prefix by language_id
    If there is no defined prefix, or language is non-prefixed - returns empty string
  */
  public function getUrlPrefix ($language_id) {
    $mode = $this->getSetting('mode', 'disabled');
    $prefixes = $this->getSetting('language_prefix');

    $prefix = '';

    switch ($mode) {
      case 'disabled':
      case $language_id:
        break;
      case 'prefix_all':
      default:
        if (!empty($prefixes[$language_id])) {
          $prefix = '/' . $prefixes[$language_id];
        }
        break;
    }

    return $prefix;
  }

  /*
    Parse route and get language_id and prefix
  */
  public function getLanguageInfoFromRoute ($route) {
    $prefixes = $this->getSetting('language_prefix');
    foreach ($prefixes as $language_id => $prefix) {
      if (trim($prefix) != '' && strpos($route, strtolower($prefix)) === 0) {
        return array(
          'prefix' => $prefix,
          'language_id' => $language_id
        );
      }
    }

    return null;
  }

  /*
    Returns language objects with injected prefix
  */
  public function getLanguageFromRoute ($route) {
    $language_info = $this->getLanguageInfoFromRoute($route);

    if (!is_null($language_info)) {
      $languages = $this->getLanguages();

      foreach ($languages as $language) {
        if ($language['language_id'] == $language_info['language_id']) {
          $language['prefix'] = $language_info['prefix'];
          return $language;
        }
      }
    }

    return null;
  }

  public function getRedirectUrl ($language_code) {
    if (isset($this->session->data['fsseo_current_route'])
        && isset($this->session->data['fsseo_current_param'])
    ) {
      $current_route = $this->session->data['fsseo_current_route'];
      $current_param = $this->session->data['fsseo_current_param'];

      foreach ($this->getLanguages() as $lang) {
        if ($lang['code'] == $language_code) {
          $this->config->set('config_language_id', $lang['language_id']);
        }
      }
      $redirect = $this->url->link($current_route, $current_param);

      return $redirect;
    }

    return false;
  }
  /*
    Check is module installed
  */
  public function isEnabled () {
    return $this->getSetting('module_enabled', false);
  }

}