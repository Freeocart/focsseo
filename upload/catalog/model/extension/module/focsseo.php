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
  public function getUrlPrefix ($language_id, $addSlash = true) {
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
          $prefix = ($addSlash ? '/' : '') . $prefixes[$language_id];
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

  public function parseUrl ($url, $lang_id) {
    if (isset($this->request->server['HTTPS'])
        && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))
    ) {
      $baseUrl = $this->config->get('config_ssl');
    }
    else {
      $baseUrl = $this->config->get('config_url');
    }

    $relativePath = str_replace($baseUrl, '', $url);

    $language = $this->getLanguageInfoFromRoute($relativePath);

    $relativePath = trim(preg_replace(preg_quote('/' . $language['prefix'] . '/'), '', $relativePath, 1), '/');
    $parsed = parse_url(str_replace('&amp;', '&', $relativePath));

    // has GET
    if (isset($parsed['query'])) {
      parse_str($parsed['query'], $query);

      // has route=...
      if (isset($query['route'])) {
        $route = $query['route'];
        unset($query['route']);

        return $this->url->link($route, http_build_query($query), $this->request->server['HTTPS']);
      }
    }
    else {
      return $this->url->link('common/home', '', $this->request->server['HTTPS']);
    }
  }

  public function getRedirectUrl ($language_code, $redirect) {
    $previousLang = $this->config->get('config_language_id');

    foreach ($this->getLanguages() as $lang) {
      if ($lang['code'] == $language_code) {
        if ($lang['language_id'] === $previousLang) {
          return $redirect;
        }
        $this->config->set('config_language_id', $lang['language_id']);
      }
    }

    return $this->parseUrl($redirect, $previousLang);
  }
  /*
    Check is module installed
  */
  public function isEnabled () {
    return $this->getSetting('module_enabled', false);
  }

}