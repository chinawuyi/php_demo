<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Shoppingcart extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
    }
	
    // 访问方式： front/shoppingcart/listdata?data={"callback":"bb"}
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
        $params['userId'] = $this->sessioninfo['userId'];
        $data['cart']= $this->dba->json_data($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
    // new访问方式：  front/shoppingcart/adddata?data={"callback":"bb","num":"1","prodId":10001}
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
        if (!isset($params['prodId'])) {$this->errorout($params,$callback,'2001','没有选择商品');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['info']= $this->dba->json_adddata($params);
        if ($data['info'] === -1) {$this->errorout($params,$callback,'3001','没有找到这个产品');}
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // new访问方式：  front/shoppingcart/adddatabatch?data={"callback":"bb","products":[{"num":"1","prodId";10001},{"num":"1","prodId";10002}]}
    public function adddatabatch(){
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
        if (!isset($params['products'])) {$this->errorout($params,$callback,'2001','没有选择商品');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['info']= $this->dba->json_adddatabatch($params);
        if ($data['info'] === -1) {$this->errorout($params,$callback,'3001','没有找到这个产品');}
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式：  front/shoppingcart/modifynumdata?data={"callback":"bb","id":"10003","nums":"1"}
    public function modifynumdata(){
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
        if (!isset($params['id'])) {$this->errorout($params,$callback,'2001','没有选择商品');}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['info']= $this->dba->modifynum($params);
        if ($data['info'] === -1) {$this->errorout($params,$callback,'3001','没有找到这个产品');}
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    
    
    // 访问方式：  front/shoppingcart/deldata?data={"callback":"bb","cartId":10003}
    public function deldata(){
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
        // modify start
        if (!isset($params['cartId'])) {$this->errorout($params,$callback,'2001','没有选择商品');}
        $params['userId'] = $this->sessioninfo['userId'];
        $data['info']= $this->dba->json_deldata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
    // 访问方式：  front/shoppingcart/getcount?data={"callback":"bb";} // 本人购物车数
    public function getcount(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
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
        if(!isset($this->sessioninfo['userId'])||''==$this->sessioninfo['userId']){
        	$data['info']= 0;
        } else {
        	if(!isset($params['userId']))
	            {$params['userId'] = $this->sessioninfo['userId'];}
	        $key = "SHOPCART-USER-NUM-".$params['userId'];
	        $num = $this->cache->get($key);
	        $num = false;
	        if ($num === false){
	            $this->load->model($this->modelpath,'dba');
	            $num = $this->dba->getcount($params);
	        }
	        $data['info']= $num;
        }
        
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);    
    }

}
