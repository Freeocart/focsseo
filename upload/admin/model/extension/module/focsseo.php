<?php

class ModelExtensionModuleFocsseo extends Model {

  const SETTINGS_GROUP = 'focsseo';
  const SETTINGS_GROUP_KEY = 'focsseo_data';

  public function install () {
    $this->load->model('setting/setting');
    $this->model_setting_setting->editSetting(self::SETTINGS_GROUP, array(
      self::SETTINGS_GROUP_KEY => $this->defaultSettings()
    ));
  }

  public function uninstall () {
    $this->load->model('setting/setting');
    $this->model_setting_setting->deleteSetting(self::SETTINGS_GROUP);
  }

  public function defaultSettings () {
    return array(
      'mode' => 'disabled'
    );
  }

  public function saveSettings ($settings) {
    $this->load->model('setting/setting');
    $settings = array_replace_recursive($this->defaultSettings(), $settings);
    $this->model_setting_setting->editSettingValue(self::SETTINGS_GROUP, self::SETTINGS_GROUP_KEY, $settings);
  }

  public function loadSettings () {
    $this->load->model('setting/setting');
    $settings = $this->model_setting_setting->getSetting(self::SETTINGS_GROUP);

    if (is_null($settings) || !isset($settings[self::SETTINGS_GROUP_KEY])) {
      return $this->defaultSettings();
    }
    else {
      return array_replace_recursive($this->defaultSettings(), $settings[self::SETTINGS_GROUP_KEY]);
    }
  }

}