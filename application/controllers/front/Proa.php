<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Proa extends Frontpage_Controller {

    public function __construct() {
        $this->debugflag = 0;
        parent::__construct();
    }

    // 访问方式：  front/proa/listdata?data={"callback":"bb","dataname":"L-最新","search":"18.5 ","catalog":"135530","indextype":"销量，价格，评价","indexorder":"desc,asc"}
    public function listdata() {
        $params = $this->input->post_get(NULL, TRUE);
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data'])) {
            $params = json_decode($params['data'], true);
        } else {
            show_404();
        }
        if (isset($params['callback'])) {
            $callback = $params['callback'];
        } else {
            $callback = 'test';
        }
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        if (isset($params['dataname'])){
            $tag = strtoupper(substr($params['dataname'], 0,1));
            if ($tag === 'S'){
                $params['search'] = substr($params['dataname'],2);
            }
            if ($tag === 'C'){
                $params['catalog'] = substr($params['dataname'],2);
            }
            if ($tag === 'L'){
                $params['label'] = substr($params['dataname'],2);
            }
        }
        $params['perpageno'] = $this->sysconfig['perpageno'];
        $data['products'] = array();
        $tmp = $this->dba->json_count($params);
        $data['products']['totalNo'] = $tmp['num'];
        if (isset($params['pageNo'])) {
            $pageno = $params['pageNo'];
        } else {
            $pageno = 0;
        }
        $data['products']['pageNo'] = $pageno;
        $data['products']['perPage'] = $this->sysconfig['perpageno'];
        $data['products']['list'] = $this->dba->json_data($params);
        if (isset($params['dataname'])) {
            $data['dataname'] = $params['dataname'];
        }
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, true);
    }
    // 访问方式：  front/proa/checklogin?data={"callback":"bb"}
    public function checklogin(){
        $params = $this->input->post_get(NULL, TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data'])) {
            $params = json_decode($params['data'], true);
        } else {
            show_404();
        }
        if (isset($params['callback'])) {
            $callback = $params['callback'];
        } else {
            $callback = 'test';
        }
        $data = array();
        if(isset($this->sessioninfo['isLogin'])&&1==$this->sessioninfo['isLogin'])
            $data["isLogin"] = true;
        else
            $data["isLogin"] = false;
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, true);

    }

}
