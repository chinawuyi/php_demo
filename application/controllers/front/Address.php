<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Address extends Frontpage_Controller {

    public function __construct() {
        $this->debugflag = 0;
        parent::__construct();
    }

    // 访问方式： front/address/listdata?data={"callback":"bb"}
    public function listdata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        $data['address'] = $this->dba->json_data($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式：  front/address/adddata?data={"callback":"bb","name":"测试","mobile":"12345678901","province":"浙江","city":"宁波","district":"北仑区","street":"大庆南路171号","address":"浙江省宁波市北仑区大庆南路171号","zip":"100001"}
    public function adddata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['name'])) {
            $this->errorout($params, $callback, '2001', '没有输入收货人');
        }
        if (!isset($params['mobile'])) {
            $this->errorout($params, $callback, '2001', '没有输入手机');
        }
        if (!isset($params['province'])) {
            $this->errorout($params, $callback, '2001', '没有输入省');
        }
        if (!isset($params['city'])) {
            $this->errorout($params, $callback, '2001', '没有输入市');
        }
        if (!isset($params['district'])) {
            $this->errorout($params, $callback, '2001', '没有输入区县');
        }
        if (!isset($params['CERTNO'])) {
            $this->errorout($params, $callback, '2001', '没有输入身份证号');
        }
        $data['info'] = $this->dba->json_adddata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式：  front/address/defaultdata?data={"callback":"bb","id":"1"}
    public function defaultdata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['id'])) {
            $this->errorout($params, $callback, '2001', '没有选择默认收货人');
        }
        // modify end
        $data['info'] = $this->dba->json_defaultdata($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式：  front/address/detaildata?data={"callback":"bb","id":"29"}
    public function detaildata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['id'])) {
            $this->errorout($params, $callback, '2001', '没有选择默认收货人');
        }
        $data['info'] = $this->dba->json_detaildata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式：  front/address/adddata?data={"callback":"bb","id":"1","name":"测试","mobile":"12345678901","province":"浙江","city":"宁波","district":"北仑区","street":"大庆南路171号","address":"浙江省宁波市北仑区大庆南路171号","zip":"100001"}
    public function modifydata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['name'])) {
            $this->errorout($params, $callback, '2001', '没有输入收货人');
        }
        if (!isset($params['mobile'])) {
            $this->errorout($params, $callback, '2001', '没有输入手机');
        }
        if (!isset($params['province'])) {
            $this->errorout($params, $callback, '2001', '没有输入省');
        }
        if (!isset($params['city'])) {
            $this->errorout($params, $callback, '2001', '没有输入市');
        }
        if (!isset($params['district'])) {
            $this->errorout($params, $callback, '2001', '没有输入区县');
        }
        $data['info'] = $this->dba->json_modifydata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // 访问方式：  front/address/deldata?data={"callback":"bb","id":1}
    public function deldata() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['id'])) {
            $this->errorout($params, $callback, '2001', '没有选择收货地址');
        }
        $data['info'] = $this->dba->json_deldata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

}
