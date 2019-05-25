<?php

class ControllerExtensionModuleFocSSeo extends Controller {

  public function uninstall () {
    $this->load->model('extension/module/focsseo');
    $this->model_extension_module_focsseo->uninstall();
  }

  public function install () {
    $this->load->model('extension/module/focsseo');
    $this->model_extension_module_focsseo->install();
  }

  // prefix languages true/false
  // main language <lang></lang>
  public function index () {
    $data = array();

    $this->language->load('extension/module/focsseo');
    $this->document->setTitle($this->language->get('heading_title'));
    $data['heading_title'] = $this->language->get('heading_title');

    $this->load->model('localisation/language');
    $this->load->model('extension/module/focsseo');

    $data['languages'] = $this->model_localisation_language->getLanguages();

    $data['action'] = $this->url->link('extension/module/focsseo', 'user_token=' . $this->session->data['user_token'], 'SSL');

    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $data['column_left'] = $this->load->controller('common/column_left');

    $data['breadcrumbs'] = $this->breadcrumbs();

    $data['label_url_language_prefix'] = $this->language->get('label_url_language_prefix');
    $data['language_without_prefixes'] = $this->language->get('language_without_prefixes');
    $data['label_language_prefixes'] = $this->language->get('label_language_prefixes');

    $data['entry_disable_common_home'] = $this->language->get('entry_disable_common_home');
    $data['entry_prefix_all'] = $this->language->get('entry_prefix_all');

    $data['text_disabled'] = $this->language->get('text_disabled');

    $data['focsseo'] = $this->model_extension_module_focsseo->loadSettings();

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      $focsseo = isset($this->request->post['focsseo']) ? $this->request->post['focsseo'] : $data['focsseo'];
      $this->model_extension_module_focsseo->saveSettings($focsseo);
      $this->response->redirect($data['action']);
    }

    return $this->response->setOutput($this->load->view('extension/module/focsseo', $data));
  }

  private function breadcrumbs () {
    $breadcrumbs = array();

    $breadcrumbs[] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => false
    );
    $breadcrumbs[] = array(
      'text'      => $this->language->get('text_extension'),
      'href'      => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      'separator' => ' :: '
    );
		$breadcrumbs[] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/focsseo', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => ' :: '
    );

    return $breadcrumbs;
  }

}