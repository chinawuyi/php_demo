<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Home extends Frontpage_Controller {

    public function __construct()
    {
        parent::__construct();
        //$this->output->enable_profiler(FALSE);
     }

    public function homedata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            echo "TYPE ERROR";exit;
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['banners'] =$this->dba->json_banner($params); 
        $data['products'] =array(); 
        $params['type'] = '最新';
        $data['products']['new'] =$this->dba->json_data($params); 
        $params['type'] = '最热';
        $data['products']['hot'] =$this->dba->json_data($params); 
        $params['type'] = '特惠';
        $data['products']['preference'] =$this->dba->json_data($params); 
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
}
