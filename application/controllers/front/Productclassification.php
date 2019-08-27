<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Productclassification extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
    }
	
    // 访问方式：  front/productclassification/listdata?data={"callback":"bb"}
    public function listdata(){
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
        $data['category'] =$this->dba->json_data($params); 
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,true);
    }
}
