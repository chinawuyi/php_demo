<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class Prodetail extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
    }

    // 访问方式：  front/prodetail/listdata?data={"callback":"bb","productId":"10003"}
    public function listdata(){
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
        if (!isset($params['productId'])){$this->errorout($params,$callback,'2001','没有产品编号');}
        $params['commentno'] = $this->sysconfig['commentno'];
        if (!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $data['product'] = $this->dba->json_product($params);
        if (sizeof($data['product']) == 0){self::errorout($params,$callback,'2002','产品不能使用');}
        $data['product']['skuattr'] = $this->dba->json_skuattr($params);
        $data['product']['banner'] = $this->dba->json_banner($params);
        $data['product']['related'] = $this->dba->json_related($params);
        $data['product']['comment'] = $this->dba->json_comment($params);
        $data['product']['attrs'] = $this->dba->json_attrs($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
}
