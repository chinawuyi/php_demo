<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Comment extends Frontpage_Controller {

    public function __construct() {
        $this->debugflag = 0;
        parent::__construct();
    }

    // 访问方式： front/comment/listdata?data={"callback":"bb","productId":"10001"}
    public function listdata() {
        $params = $this->input->post_get(NULL,TRUE);
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
        $this->load->model($this->modelpath, 'dba');
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['productId'])) {$this->errorout($params, $callback, '2001', '没有选择商品');}
        $data['comment'] = $this->dba->json_data($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式： front/comment/basedata?data={"callback":"bb","productId":"10001"}
    public function basedata() {
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
        $this->load->model($this->modelpath, 'dba');
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['productId'])) {$this->errorout($params, $callback, '2001', '没有选择商品');}
        $data['comment'] = $this->dba->basedata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式： front/comment/adddata?data={"callback":"CallBack.addData","commentId":"39","content":"1111"}
    public function addreply() {
        $params = $this->input->post_get(NULL, TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
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
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['commentId'])) {$this->errorout($params, $callback, '2001', '没有选择评价');}
        if (!isset($params['content'])) {$this->errorout($params, $callback, '2001', '没有输入回复信息');}
        $data['info'] = $this->dba->addreply($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($params, $data, !$this->debugflag);
    }

    // 访问方式： front/comment/adddata?data={"callback":"bb","orderNo":"20151213110001","productId":"1001","content":"11111","star":"4","pics":["aa.jpg"]}
    public function adddata() {
        $params = $this->input->post_get(NULL, TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
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
        if(!isset($params['userId']))
            {$params['userId'] = $this->sessioninfo['userId'];}
        // modify start
        if (!isset($params['orderNo'])) {$this->errorout($params, $callback, '2001', '没有选择订单');}
        if (!isset($params['productId'])) {$this->errorout($params, $callback, '2001', '没有选择商品');}
        if (!isset($params['star'])) {$this->errorout($params, $callback, '2001', '选择星级');}
        $data['info'] = $this->dba->adddata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($params, $data, !$this->debugflag);
    }

}
