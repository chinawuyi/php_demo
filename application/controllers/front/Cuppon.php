<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Cuppon extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
            $this->cachetime = 0;
    }
	
    // 访问方式： front/cuppon/listdata?data={"callback":"bb"}
    public function listdata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        $data['cuppon']= $this->dba->json_data($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
    // 访问方式：  front/cuppon/adddata?data={"callback":"bb","password":"12371923719279128313"}
    public function adddata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        $data['info']= $this->dba->json_adddata($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

}
