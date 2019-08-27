<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Collect extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
    }
	
    // 访问方式： front/collect/listdata?data={"callback":"bb"}
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
        // modify start
        $data['cart']= $this->dba->json_data($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
    // 访问方式：  front/collect/adddata?data={"callback":"bb","prodId":"10003"}
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
        // modify start
        if (!isset($params['prodId'])) {$this->errorout($params,$callback,'2001','没有选择商品');}
        $data['info']= $this->dba->json_adddata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式：  front/collect/deldata?data={"callback":"bb","prodId":10003}
    public function deldata(){
        // fixed
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
        // modify start
        if (!isset($params['prodId'])) {$this->errorout($params,$callback,'2001','参数错误');}
        $data['info']= $this->dba->json_deldata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

     // 访问方式：  front/collect/getcount?data={"callback":"bb","prodId":10003} // 摸个商品的收藏数
    public function getcount(){
        // fixed
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
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
        if (!isset($params['prodId'])) {$this->errorout($params,$callback,'2001','参数错误');}
        $data = array();
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        $key = "COLLECT-PRODUCT-NUM-".$params['prodId'];
        $num = $this->cache->get($key);
        if ($num === false){
            $this->load->model($this->modelpath,'dba');
            $num = $this->dba->getcount($params);
        }
        $dd['nums'] = $num;
        $key = "COLLECT-PRODUCT-LIST-".$params['prodId'];
        $list = $this->cache->get($key);
        if ($list === false){
            $this->load->model($this->modelpath,'dba');
            $dd['OwnCollect'] = $this->dba->getlist($params);
        }
        else $dd['OwnCollect'] = isset($list[$params['userId']]);
        $data['info']= $dd;
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);    
    }

}
