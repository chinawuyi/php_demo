<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . "libraries/Frontpage_Controller.php";

class Order extends Frontpage_Controller {

    public function __construct() {
        $this->debugflag = 0;
        parent::__construct();
        $this->cachetime = 0;
    }

    // front/order/orderselect?data={"callback":"bb","cartId":["6"]}
    public function orderSelect() {
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
        if (!isset($params['cartId'])) {
            $this->errorout($params, $callback, '2001', '没有购物车编号');
        }
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->orderSelect($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/orderselectbyprodid?data={"callback":"bb","prodId":"490979","num":"2"}
    public function orderselectbyprodid() {
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
            $this->errorout($params, $callback, '2001', '没有商品编号');
        }
        if (!isset($params['num'])) {
            $this->errorout($params, $callback, '2002', '没有购买数量');
        }
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->orderSelectByprodId($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }
	
	// front/order/orderselectbydiscountid?data={"callback":"bb","discountId":"11","num":"2"}
    public function orderselectbydiscountid() {
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
        if (!isset($params['discountId'])) {
            $this->errorout($params, $callback, '2001', '没有砍价单编号');
        }
        if (!isset($params['num'])) {
            $this->errorout($params, $callback, '2002', '没有购买数量');
        }
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->orderSelectBydiscountId($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }
	

    // front/order/createorder?data={"callback":"bb","cartId":["1"],"address":"1","buyerMsg":"test","postFee":"100","cuppons":[]}
    public function createorder() {
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
        if (!isset($params['cartId'])) {
            $this->errorout($params, $callback, '2001', '没有购物车编号');
        }
        if (!isset($params['address'])) {
            $this->errorout($params, $callback, '2001', '没有选择收货地址');
        }
        $params['userId'] = $this->sessioninfo['userId'];
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->createOrder($params);
        $key = "SHOPCART-USER-NUM-" . $params['userId'];
        $this->cache->delete($key);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/createordernyprodid?data={"callback":"bb","prodId":"490979","num":"2","address":"31","buyerMsg":"test","postFee":"100","cuppons":[],"certNo":"12312312312312312312"}
    public function createordernyprodid() {
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
            $this->errorout($params, $callback, '2001', '没有商品编号');
        }
        if (!isset($params['address'])) {
            $this->errorout($params, $callback, '2002', '没有选择收货地址');
        }
        if (!isset($params['certNo'])) {
            $this->errorout($params, $callback, '2003', '没有输入身份证号');
        }
        $params['userId'] = $this->sessioninfo['userId'];
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->createOrderByprodId($params);
        $key = "SHOPCART-USER-NUM-" . $params['userId'];
        $this->cache->delete($key);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }
	
	// front/order/createorderbydiscountid?data={"callback":"bb","discountId":"17","num":"2","address":"31","buyerMsg":"test","postFee":"100","cuppons":[],"certNo":"12312312312312312312"}
    public function createorderbydiscountid() {
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
        if (!isset($params['discountId'])) {
            $this->errorout($params, $callback, '2001', '没有砍价单编号');
        }
        if (!isset($params['address'])) {
            $this->errorout($params, $callback, '2002', '没有选择收货地址');
        }
        if (!isset($params['certNo'])) {
            $this->errorout($params, $callback, '2003', '没有输入身份证号');
        }
        $params['userId'] = $this->sessioninfo['userId'];
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->createOrderBydiscountId($params);
        $key = "SHOPCART-USER-NUM-" . $params['userId'];
        $this->cache->delete($key);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    //对已经生成的订单进行支付
    // front/order/payorder?data={"callback":"bb","orderNo":"20151218140005"}
    public function payorder() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['orderNo'])) {
            $this->errorout($params, $callback, '2001', '没有选择订单');
        }
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->payOrder($params['orderNo'],$params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    //对已经生成的订单进行二维码支付
    // front/order/payorderbynative?data={"callback":"bb","orderNo":"20151218140005"}
    public function payorderbynative() {
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
        if (!isset($params['userId'])) {
            $params['userId'] = $this->sessioninfo['userId'];
        }
        // modify start
        if (!isset($params['orderNo'])) {
            $this->errorout($params, $callback, '2001', '没有选择订单');
        }
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $client = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'orderprocess');
        $data['info'] = $client->payOrder($params['orderNo'],$params,true);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/orderlist?data={"callback":"bb","status":""}
    public function orderlist() {
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
        $data['orders'] = $this->dba->orderlist($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/orderdetail?data={"callback":"bb","orderId":"1"}
    public function orderdetail() {
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
        if (!isset($params['orderId'])) {
            $this->errorout($params, $callback, '2001', '没有订单编号');
        }
        $data['orders'] = $this->dba->orderdetail($params);
        if (sizeof($data['orders']['order']) == 0) {
            self::errorout($params, $callback, '3001', '无此订单');
        }
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/ordercancel?data={"callback":"bb","orderId":"17"}
    public function ordercancel() {
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
        if (!isset($params['orderId'])) {
            self::errorout($params, $callback, '2001', '没有订单编号');
        }
        $data['info'] = $this->dba->ordercancel($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

    // front/order/orderreceive?data={"callback":"bb","orderId":"39"}
    public function orderreceive() {
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
        if (!isset($params['orderId'])) {
            self::errorout($params, $callback, '2001', '没有订单编号');
        }
        $data['info'] = $this->dba->orderreceive($params);
        // modify end
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams, $data, !$this->debugflag);
    }

}
