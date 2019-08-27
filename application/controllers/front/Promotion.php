<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Promotion extends Frontpage_Controller {

    public function __construct()
    {
        parent::__construct();
        //$this->output->enable_profiler(FALSE);
     }

     // front/promotion/bannerclassdata?data={"callback":"bb","type":"首页广告"}
    public function bannerclassdata(){
        $params = $this->input->post_get(NULL,TRUE);
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
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['type'])) {$this->errorout($params,$callback,'2001','没有广告类型');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $data['banner']= $this->dba->bannerdata($params);
        $data['type'] = $params['type'];
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);        
    }
    
     // front/promotion/labelclassdata?data={"callback":"bb","label":"特惠"}
    public function labelclassdata(){
        $params = $this->input->post_get(NULL,TRUE);
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
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['label'])) {$this->errorout($params,$callback,'2001','没有活动标签');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $data['promotion']= $this->dba->promotiondata($params);
        $data['label'] = $params['label'];
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);        
    }

    // front/promotion/homebanner?data={"callback":"bb"}
    public function homebanner(){
        $params = $this->input->post_get(NULL,TRUE);
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
        if(!isset($params['userId']))
        {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $data['promotion']= $this->dba->homebanner($params);
        $data['label'] = $params['label'];
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // front/promotion/homebannerpersonal?data={"callback":"bb","action":"获取，提交"}
    public function homebannerpersonal(){
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
        if(!isset($params['userId']))
        {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['action'])) {$this->errorout($params,$callback,'2001','没有设定动作');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $data['promotion']= $this->dba->homebannerpersonal($params);
        $data['label'] = $params['label'];
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
}
