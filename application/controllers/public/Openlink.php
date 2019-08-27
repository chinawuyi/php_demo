<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Openlink extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->sessioninfo = $this->session->userdata('sessioninfo');

        $this->url_module = $this->uri->segment(1, $this->router->default_controller);
        $this->url_model = $this->uri->segment(2, 'index');
        $this->url_method = $this->uri->segment(3, 'index');
    }

    public function index(){
        $params = $this->input->post_get(NULL,TRUE);
        if ((!isset($params['key'])) || ($params['key'] === '')) {
            show_404();
        }
        if("zeront"==$params['key']) {
            redirect(base_url('/html/home.html'));
        } else {
            show_404();
        }
    }

}
