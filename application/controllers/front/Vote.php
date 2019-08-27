<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Vote extends Frontpage_Controller {

    public function __construct() {
        parent::__construct();
        //$this->output->enable_profiler(FALSE);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 生成砍价单
     * @param mixed $url <p>调用路径</p>
     * <p>front/vote/vote?data={"callback":"bb","action":"获取，提交","prodId":"1,2,3"}</p>
     * @return mixed 砍价单对象
     */
    public function vote() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['action'])) {
            $this->errorout($params, $callback, '2001', '没有提交动作');
        }
        if("提交"==$params['action']&&(!isset($params['prodId']))){
            $this->errorout($params, $callback, '2001', '没有商品编号');
        }
        if("提交"==$params['action']) {
            $prodId = $params['prodId'];
            $prodId = explode(",",$prodId);
            if(count($prodId)>3){
                $this->errorout($params, $callback, '2001', '最多只能为三个商品投票！');
            }
        }
        $params['date'] = date("Y-m-d H:i:s");
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        $data['voteproducts'] = $this->dba->voteproducts($params);
        $data['votedetail'] = $this->dba->votedetail($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

}
