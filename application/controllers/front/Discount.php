<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Discount extends Frontpage_Controller {

    public function __construct() {
        parent::__construct();
        //$this->output->enable_profiler(FALSE);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 生成砍价单
     * @param mixed $url <p>调用路径</p>
     * <p>front/discount/creatediscount?data={"callback":"bb","prodId":"490525"}</p>
     * @return mixed 砍价单对象
     */
    public function creatediscount() {
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
        if (!isset($params['prodId'])) {
            $this->errorout($params, $callback, '2001', '没有选择商品');
        }
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        $params['id'] = $this->dba->creatediscount($params);
        $data = self::_getprodetail($data, $params['prodId']);
        $data['disinfo'] = $this->dba->discountdata($params);
        $data['disrecord'] = $this->dba->recorddata($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    private function _getprodetail($data, $prodId) {
        $this->load->model('front/prodetail_model', 'dbb');
        $params = array();
        $params['productId'] = $prodId;  // prodetail
        $params['commentno'] = $this->sysconfig['commentno'];
        $params['userId'] = $this->sessioninfo['userId'];
        $data['product'] = $this->dbb->json_product($params);
        $data['product']['skuattr'] = $this->dbb->json_skuattr($params);
        $data['product']['banner'] = $this->dbb->json_banner($params);
        $data['product']['related'] = $this->dbb->json_related($params);
        $data['product']['comment'] = $this->dbb->json_comment($params);
        $data['product']['attrs'] = $this->dbb->json_attrs($params);        // modify end
        return $data;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提供砍价单本身的信息
     * @param mixed $url <p>调用路径</p>
     * <p>front/discount/discountdata?data={"callback":"bb","id":"11","prodId":"490525"}</p>
     * @return mixed 砍价单对象
     */
    public function discountdata() {
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
        if (!isset($params['prodId'])) {
            $this->errorout($params, $callback, '2001', '没有选择商品');
        }
        if (!isset($params['id'])) {
            $this->errorout($params, $callback, '2002', '没有指定砍价单');
        }
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        $data = self::_getprodetail($data, $params['prodId']);
        $data['disinfo'] = $this->dba->discountdata($params);
        $data['disrecord'] = $this->dba->recorddata($params);
        //获取砍价单用户信息
        $data['disuserdata']['disuser'] = $this->dba->discountuserdata($params);
        //获取当前登录用户信息
        $data['disuserdata']['user'] = $this->dba->userdata($params);
        //获取微信ticket
        $sysconfig = $this->config->item('cfg-system');
        $sysconfig = $sysconfig['abccall'];
        $num = rand(1,100);
        $data['ticket'] = file_get_contents($sysconfig['TicketUrl'].'?t='.$num);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 我的砍价单列表
     * @param mixed $url <p>调用路径</p>
     * <p>front/discount/listdata?data={"callback":"bb"}</p>
     * @return mixed 砍价单列表对象
     */
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        $params['status'] = '1';
		$params['PAYSTATUS'] = '0';
        $data['listdata']['doing'] = $this->dba->listdata($params);
        $data['listdata']['finish'] = $this->dba->listfinishdata();
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 砍价
     * @param mixed $url <p>调用路径</p>
     * <p>front/discount/discount?data={"callback":"bb","id":"11"}</p>
     * @return mixed 砍价单列表对象
     */
    public function discount() {
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
        if (!isset($params['id'])) {
            $this->errorout($params, $callback, '2002', '没有指定砍价单');
        }
        $data = array();
        $this->load->model($this->modelpath, 'dba');
        $data['discountprice'] = $this->dba->discount($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }
	
	// front/discount/discountcancel?data={"callback":"bb","discountId":"17"}
    public function discountcancel() {
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
			//$params['userId'] =13;
        }
        // modify start
        if (!isset($params['discountId'])) {
            self::errorout($params, $callback, '2001', '没有砍价单编号');
        }
        $data['info'] = $this->dba->discountcancel($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

}
