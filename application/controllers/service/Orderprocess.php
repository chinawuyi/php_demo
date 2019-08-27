<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Orderprocess extends CI_Controller
{
    /*  9101,没有用户编号
     *  9101,没有购物车编号
     *  9102,没有商品编号
     *  9103,没有购买数量
     *  9104,没有订单号
     *  9105,该订单不存在
     *  9106,没有找到对应商品明细,商品已过期
     *  9107,商品【AAA】库存不足！
     */

    public function __construct()
    {
        parent::__construct();
        $this->config->load('cfg-system', true);
        $this->sysconfig = $this->config->item('cfg-system');
        $this->sysconfig = $this->sysconfig['system'];
        include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
        $this->load->database();
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提供购物车ID数组，构成订单要素，并计算促销
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[cartId]</b></td>
     * <td>购物车表的ID，数组</td></tr>
     * @return array 订单的明细对象
     */
    public function orderSelect($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['cartId'])) {
                $this->_JSONRESULT("没有购物车编号", "9101");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembycardid($params['userId'], $params['cartId']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                            // 进行促销处理
            $orderdata = $this->_getAddress($orderdata, $params);                              // 获得默认收货地址
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $orderdata = $this->_getCuppon($orderdata, $params);                        // 获得可用代金券
            $orderdata['cartId'] = $params['cartId'];
            return $orderdata;
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提供商品ID，构成订单要素，并计算促销
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[prodId]</b></td>
     * <td>商品表的prodId</td></tr>
     * @return array 订单的明细对象
     */
    public function orderSelectByprodId($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['prodId'])) {
                $this->_JSONRESULT("没有商品编号", "9102");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembyprodId($params['userId'], $params['prodId'], $params['num']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                             // 进行促销处理
            $orderdata = $this->_getAddress($orderdata, $params);                              // 获得默认收货地址
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $orderdata = $this->_getCuppon($orderdata, $params);                        // 获得可用代金券
            return $orderdata;
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提供砍价单编号，构成订单要素，并计算促销
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[discountId]</b></td>
     * <td>砍价单表的Id</td></tr>
     * @return array 订单的明细对象
     */
    public function orderSelectBydiscountId($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['discountId'])) {
                $this->_JSONRESULT("没有砍价单编号", "9102");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembydiscountId($params['userId'], $params['discountId'], $params['num']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                             // 进行促销处理
            $orderdata = $this->_getAddress($orderdata, $params);                              // 获得默认收货地址
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $orderdata = $this->_getCuppon($orderdata, $params);                        // 获得可用代金券
            return $orderdata;
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * 根据购物车ID，数组，生成订单
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[cartId]</b></td>
     * <td>购物车Id，数组</td></tr>
     * @return mixed 生成结果，
     */
    public function createOrder($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['cartId'])) {
                $this->_JSONRESULT("没有购物车编号", "9101");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembycardid($params['userId'], $params['cartId']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $orderdata = $this->_checkcoin($orderdata, $params);                       // 检查金币数
            $orderdata = $this->_checkdiscount($orderdata,$params);                      //秒杀商品限购检查
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                             // 进行促销处理
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $cuppon = $this->_addCupponData($params);                      // 变更优惠券使用信息
            $orderdata['amount'] = $orderdata['amount'] + $orderdata['tax'] + $orderdata['freight'];
            if ($cuppon['cuppon'] > $orderdata['preference']) {
                $orderdata['amount'] = $orderdata['amount'] - $orderdata['preference'];
            } else {
                $orderdata['amount'] = $orderdata['amount'] - $cuppon['cuppon'];
            }
            $order = $this->_insertOrder($orderdata, $params);                                   // 插入订单记录
            $order['cuppon'] = $cuppon;
            $params['orderNo'] = $order['orderNo'];
            $order['cart']['num'] = $this->_delCartData($params);                                                   // 删除购物车相关记录
            $order['receiver'] = $this->_addReceiveData($params);                    // 添加收货信息
            $this->_updateOrderStatus($order, $params);                             //  更新状态和各种数量
            return $this->_realPay($order);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据商品编号，生成订单
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[prodId]</b></td>
     * <td>商品编号</td></tr>
     * <tr valign="top">
     * <td><b>params[num]</b></td>
     * <td>购买数量</td></tr>
     * @return mixed 生成结果，
     */
    public function createOrderByprodId($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['prodId'])) {
                $this->_JSONRESULT("没有商品编号", "9102");
            }
            if (!isset($params['num'])) {
                $this->_JSONRESULT("没有购买数量", "9103");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembyprodId($params['userId'], $params['prodId'], $params['num']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $orderdata = $this->_checkcoin($orderdata, $params);                       // 检查金币数
            $orderdata = $this->_checkdiscount($orderdata,$params);                      //秒杀商品限购检查
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                             // 进行促销处理
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $cuppon = $this->_addCupponData($params);                      // 变更优惠券使用信息
            $orderdata['amount'] = $orderdata['amount'] + $orderdata['tax'] + $orderdata['freight'];
            if ($cuppon['cuppon'] > $orderdata['preference']) {
                $orderdata['amount'] = $orderdata['amount'] - $orderdata['preference'];
            } else {
                $orderdata['amount'] = $orderdata['amount'] - $cuppon['cuppon'];
            }
            $order = $this->_insertOrder($orderdata, $params);                                   // 插入订单记录
            $order['cuppon'] = $cuppon;
            $params['orderNo'] = $order['orderNo'];
            $order['receiver'] = $this->_addReceiveData($params);                    // 添加收货信息
            $this->_updateOrderStatus($order, $params);                             //  更新状态和各种数量
            return $this->_realPay($order);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }
	
	/**
     * (PHP 4, PHP 5)<br/>
     * 根据商品编号，生成订单
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[discounId]</b></td>
     * <td>砍价单编号</td></tr>
     * <tr valign="top">
     * <td><b>params[num]</b></td>
     * <td>购买数量</td></tr>
     * @return mixed 生成结果，
     */
    public function createOrderBydiscountId($params)
    {
        try {
            if (!isset($params['userId'])) {
                $this->_JSONRESULT("没有用户编号", "9100");
            }
            if (!isset($params['discounId'])) {
                $this->_JSONRESULT("没有砍价单编号", "9102");
            }
            if (!isset($params['num'])) {
                $this->_JSONRESULT("没有购买数量", "9103");
            }
            $orderdata = array();
            $orderdata['products'] = $this->_qryorderitembydiscountId($params['userId'], $params['discountId'], $params['num']);  // 提取订单明细
            $orderdata = $this->_checkventory($orderdata, $params);                       // 检查可卖数
            $orderdata = $this->_checkcoin($orderdata, $params);                       // 检查金币数
            $orderdata = $this->_checkdiscount($orderdata,$params);                      //秒杀商品限购检查
            $promotion = new PHPRPC_Client($this->sysconfig['rpcroot'] . 'promotion');
            $orderdata = $promotion->promotion($orderdata);                             // 进行促销处理
            $orderdata = $this->_chgOrderMoney($orderdata);                              // 分转为元
            $cuppon = $this->_addCupponData($params);                      // 变更优惠券使用信息
            $orderdata['amount'] = $orderdata['amount'] + $orderdata['tax'] + $orderdata['freight'];
            if ($cuppon['cuppon'] > $orderdata['preference']) {
                $orderdata['amount'] = $orderdata['amount'] - $orderdata['preference'];
            } else {
                $orderdata['amount'] = $orderdata['amount'] - $cuppon['cuppon'];
            }

            $order = $this->_insertOrder($orderdata, $params);                                   // 插入订单记录
            $order['cuppon'] = $cuppon;
            $params['orderNo'] = $order['orderNo'];
            $order['receiver'] = $this->_addReceiveData($params);                    // 添加收货信息
            $this->_updateOrderStatus($order, $params);                             //  更新状态和各种数量
            return $this->_realPay($order);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }


    private function _getNonceStr($length=32){
        $str = null;
        $src_str = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($src_str);
        for($i = 0;$i < $length;$i++) {
            $str .= $src_str[rand(0,$max)];
        }
        return $str;
    }

    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = null;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * 	作用：生成签名
     */
    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $config = $this->config->item('cfg-system');
        $config = $config['wxshop'];
        $String = $String."&key=".$config['key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }


    private function _arrtoxml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 	作用：将xml转为array
     */
    private function _xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    private function _curlget($url){
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOP_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $token = curl_exec($ch);
        curl_close($ch);
        return $token;
    }

    private function _realPayByQr($order) {
        //二维码支付
        $config = $this->config->item('cfg-system');
        $config = $config['wxshop'];
        $insert = array();
        $insert['orderNo'] = $order['orderNo'];
        $insert['userId'] = $order['userId'];
        $insert['finNo'] = $this->getNo('FN' . Date('YmdH'), 4);
        $insert['Amount'] = round($order["amount"] / 100, 2);
        $insert['orderdate'] = $order['createTime'];
        $insert['fncreateTime'] = Date('Y-m-d H:i:s');
        $insert['TrxType'] = "二维码支付";
        $insert['PayType'] = "微信二维码";
        $insert['status'] = '0';

        $params = array();
        //微信参数
        $params['appid'] = $config['appid'];//品质全球APPID
        $params['mch_id'] = $config['mch_id'];//微信支付分配的商户号
        $params['device_info'] = "WEB";//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB"
        $params['nonce_str'] = $this->_getNonceStr(32);//随机字符串，不长于32位
        $params['body'] = $order['item'][0]['ProductName'].'等';//商品或支付单简要描述
        $params['out_trade_no'] = $insert['finNo'];//商户系统内部的订单号,32个字符内、可包含字母
        $params['product_id'] = $insert['finNo'];//trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
        $params['total_fee'] = $order["amount"];//订单总金额，单位为分
        $params['spbill_create_ip'] = $config['server_IP'];//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP
        $params['notify_url'] = $config['notify_url'];//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $params['trade_type'] = 'NATIVE';//
        $params['sign'] = $this->getSign($params);//签名
        //调用接口
        $xml = $this->_arrtoxml($params,0,1);
        log_message("info","native pay ---> xml data:".print_r($xml,true));
        $ch = curl_init();
        curl_setopt($ch, CURLOP_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_URL, $config['unifiedorder']);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        $parse = $this->_xmlToArray($data);
        log_message("info","native pay return data:".print_r($parse,true));
        if(!("SUCCESS" == $parse["return_code"])) {
            log_message("info","native pay return failed -->".$parse["return_msg"]."--->data:".print_r($params,true));
            $this->_JSONRESULT($parse["return_msg"], '9104');
        }
        if(!("SUCCESS" == $parse["result_code"])) {
            log_message("info","native pay result failed -->".$parse["err_code"].":".$parse["err_code_des"]."--->data:".print_r($params,true));
            $this->_JSONRESULT($parse["err_code"].":".$parse["err_code_des"], '9104');
        }

        //添加交易信息
        $insert["iRspRef"] = $parse["prepay_id"];
        $this->db->insert('nh_order_pay', $insert);
        //生成二维码图片
        $png = $this->_getqrpng($parse['code_url']);
        $result = array();
        $result["url"] = $parse['code_url'];
        $result['png'] = $png;

        $re = array();
        $re["success"] = true;
        $re["code"] = "0001";
        $re["msg"] = "获取成功";
        $re["data"] = $result;
        log_message("info","realpaybyqr back:".print_r($re,true));
        return $re;
    }

    private function _realPayByWeixin($order,$openId) {
        $config = $this->config->item('cfg-system');
        $config = $config['wxshop'];
        $insert = array();
        $insert['orderNo'] = $order['orderNo'];
        $insert['userId'] = $order['userId'];
        $insert['finNo'] = $this->getNo('FN' . Date('YmdH'), 4);
        $insert['Amount'] = round($order["amount"] / 100, 2);
        $insert['orderdate'] = $order['createTime'];
        $insert['fncreateTime'] = Date('Y-m-d H:i:s');
        $insert['TrxType'] = "在线支付";
        $insert['PayType'] = "微信";
        $insert['status'] = '0';


        //get open id
        $this->db->where("Id",$order['userId']);
        $query = $this->db->get("nh_user");
        if(0==$query->num_rows()) {
            $this->_JSONRESULT("该用户不存在！", '9104');
        }
        $user = $query->row_array();
        $params = array();
        //微信参数
        $params['appid'] = $config['appid'];//品质全球APPID
        $params['mch_id'] = $config['mch_id'];//微信支付分配的商户号
        $params['device_info'] = "WEB";//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB"
        $params['nonce_str'] = $this->_getNonceStr(32);//随机字符串，不长于32位
        $params['body'] = $order['item'][0]['ProductName'].'等';//商品或支付单简要描述
        $params['out_trade_no'] = $insert['finNo'];//商户系统内部的订单号,32个字符内、可包含字母
        $params['total_fee'] = $order["amount"];//订单总金额，单位为分
        //$params['total_fee'] = 1;//test
        $params['spbill_create_ip'] = $this->input->server('REMOTE_ADDR');//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP
        $params['notify_url'] = $config['notify_url'];//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $params['trade_type'] = $config['trade_type'];//
        $params['openid'] = $openId;//trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。
        //$params['openid'] = "oppHutzXRCcBWGGj1nUBv3078zY8";//test
        $params['sign'] = $this->getSign($params);//签名
        //调用接口
        $xml = $this->_arrtoxml($params,0,1);
        log_message("info","weixin pay ---> xml data:".print_r($xml,true));
        $ch = curl_init();
        curl_setopt($ch, CURLOP_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_URL, $config['unifiedorder']);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        $parse = $this->_xmlToArray($data);
        log_message("info","weixin pay return data:".print_r($parse,true));
        if(!("SUCCESS" == $parse["return_code"])) {
            log_message("info","weixin pay return failed -->".$parse["return_msg"]."--->data:".print_r($params,true));
            $this->_JSONRESULT($parse["return_msg"], '9104');
        }
        if(!("SUCCESS" == $parse["result_code"])) {
            log_message("info","weixin pay result failed -->".$parse["err_code"].":".$parse["err_code_des"]."--->data:".print_r($params,true));
            $this->_JSONRESULT($parse["err_code"].":".$parse["err_code_des"], '9104');
        }

        $result = array();
        $result["appId"] = $params['appid'];
        $result["timeStamp"] = time();
        $result['nonce_str'] = $parse["nonce_str"];//随机字符串，不长于32位
        $result["package"]  = "prepay_id=".$parse["prepay_id"];
        $result["signType"] = "MD5";
        $result['paySign'] = $this->getSign($result);//签名

        //添加交易信息
        $insert["iRspRef"] = $parse["prepay_id"];
        $this->db->insert('nh_order_pay', $insert);

        //获取ticket
        $token = $this->_curlget($config['tokenUrl']);
        //$token = file_get_contents($url);
        log_message("info","access token:".print_r($token,true));
        $token = json_decode($token,true);
        log_message("info","access token:".print_r($token,true));
        if(isset($token["errcode"])) {
            $this->_JSONRESULT($token["errcode"].':'.$token["errmsg"], '9104');
        }
        $token = $token["access_token"];
        $js_ticket = $this->_curlget($config['ticketUrl'].$token);
        $js_ticket = json_decode($js_ticket,true);
        log_message("info","js token:".print_r($js_ticket,true));
        if(isset($js_ticket["errcode"])&&0!=$js_ticket["errcode"]) {
            $this->_JSONRESULT($js_ticket["errcode"].':'.$js_ticket["errmsg"], '9104');
        }
        $ticket = $js_ticket["ticket"];
        $re = array();
        $re["success"] = true;
        $re["code"] = "0001";
        $re["msg"] = "获取成功";
        $re["data"] = $result;
        $re["ticket"] = $ticket;
        log_message("info","realpay back:".print_r($re,true));
        return $re;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据订单数据，生成支付流水，调用支付
     * @param mixed $order <p>订单对象</p>
     * @return mixed 生成支付结果，
     */
    private function _realPay($order,$isPay = false)
    {
        if($isPay) {


        } else {
            $config = $this->config->item('cfg-system');
            $config = $config['wxshop'];
            $insert = array();
            $insert['orderNo'] = $order['orderNo'];
            $insert['userId'] = $order['userId'];
            $insert['finNo'] = $order['orderNo'];
            $insert['Amount'] = round($order["amount"] / 100, 2);
            $insert['orderdate'] = $order['createTime'];
            $insert['fncreateTime'] = Date('Y-m-d H:i:s');
            $insert['TrxType'] = "在线支付";
            $insert['PayType'] = "微信";
            $insert['status'] = '0';


            //get open id
            $this->db->where("Id",$order['userId']);
            $query = $this->db->get("nh_user");
            if(0==$query->num_rows()) {
                $this->_JSONRESULT("该用户不存在！", '9104');
            }
            $user = $query->row_array();
            if((!isset($user["openId"]))||""==$user["openId"]) {

                //扫码支付
                $params = array();
                //微信参数
                $params['appid'] = $config['appid'];//品质全球APPID
                $params['mch_id'] = $config['mch_id'];//微信支付分配的商户号
                $params['device_info'] = "WEB";//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB"
                $params['nonce_str'] = $this->_getNonceStr(32);//随机字符串，不长于32位
                $params['body'] = $order['item'][0]['ProductName'].'等';//商品或支付单简要描述
                $params['out_trade_no'] = $insert['finNo'];//商户系统内部的订单号,32个字符内、可包含字母
                $params['product_id'] = $insert['finNo'];//trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
                $params['total_fee'] = $order["amount"];//订单总金额，单位为分
                //$params['total_fee'] = 1;//test
                $params['spbill_create_ip'] = $config['server_IP'];//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP
                $params['notify_url'] = $config['notify_url'];//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
                $params['trade_type'] = 'NATIVE';//
                //$params['openid'] = "oppHutzXRCcBWGGj1nUBv3078zY8";//test
                $params['sign'] = $this->getSign($params);//签名
                //调用接口
                $xml = $this->_arrtoxml($params,0,1);
                log_message("info","native pay ---> xml data:".print_r($xml,true));
                $ch = curl_init();
                curl_setopt($ch, CURLOP_TIMEOUT, 30);
                curl_setopt($ch,CURLOPT_URL, $config['unifiedorder']);
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
                //设置header
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                //要求结果为字符串且输出到屏幕上
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //post提交方式
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                //运行curl
                $data = curl_exec($ch);
                curl_close($ch);
                $parse = $this->_xmlToArray($data);
                log_message("info","native pay return data:".print_r($parse,true));
                if(!("SUCCESS" == $parse["return_code"])) {
                    log_message("info","native pay return failed -->".$parse["return_msg"]."--->data:".print_r($params,true));
                    $this->_JSONRESULT($parse["return_msg"], '9104');
                }
                if(!("SUCCESS" == $parse["result_code"])) {
                    log_message("info","native pay result failed -->".$parse["err_code"].":".$parse["err_code_des"]."--->data:".print_r($params,true));
                    $this->_JSONRESULT($parse["err_code"].":".$parse["err_code_des"], '9104');
                }


                //添加交易信息
                $insert["iRspRef"] = $parse["prepay_id"];
                $this->db->insert('nh_order_pay', $insert);
                //生成二维码图片
                $png = $this->_getqrpng($parse['code_url']);
                $result = array();
                $result["url"] = $parse['code_url'];
                $result['png'] = $png;

                $re = array();
                $re["success"] = true;
                $re["code"] = "0001";
                $re["msg"] = "获取成功";
                $re["isOpenId"] = false;
                $re["data"] = $result;
                log_message("info","realpay back:".print_r($re,true));
                return $re;
            } else {
                $params = array();
                //微信参数
                $params['appid'] = $config['appid'];//品质全球APPID
                $params['mch_id'] = $config['mch_id'];//微信支付分配的商户号
                $params['device_info'] = "WEB";//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB"
                $params['nonce_str'] = $this->_getNonceStr(32);//随机字符串，不长于32位
                $params['body'] = $order['item'][0]['ProductName'].'等';//商品或支付单简要描述
                $params['out_trade_no'] = $insert['finNo'];//商户系统内部的订单号,32个字符内、可包含字母
                $params['total_fee'] = $order["amount"];//订单总金额，单位为分
                //$params['total_fee'] = 1;//test
                $params['spbill_create_ip'] = $this->input->server('REMOTE_ADDR');//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP
                $params['notify_url'] = $config['notify_url'];//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
                $params['trade_type'] = $config['trade_type'];//
                $params['openid'] = $user["openId"];//trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。
                //$params['openid'] = "oppHutzXRCcBWGGj1nUBv3078zY8";//test
                $params['sign'] = $this->getSign($params);//签名
                //调用接口
                $xml = $this->_arrtoxml($params,0,1);
                log_message("info","weixin pay ---> xml data:".print_r($xml,true));
                $ch = curl_init();
                curl_setopt($ch, CURLOP_TIMEOUT, 30);
                curl_setopt($ch,CURLOPT_URL, $config['unifiedorder']);
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
                //设置header
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                //要求结果为字符串且输出到屏幕上
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //post提交方式
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                //运行curl
                $data = curl_exec($ch);
                curl_close($ch);
                $parse = $this->_xmlToArray($data);
                log_message("info","weixin pay return data:".print_r($parse,true));
                if(!("SUCCESS" == $parse["return_code"])) {
                    log_message("info","weixin pay return failed -->".$parse["return_msg"]."--->data:".print_r($params,true));
                    $this->_JSONRESULT($parse["return_msg"], '9104');
                }
                if(!("SUCCESS" == $parse["result_code"])) {
                    log_message("info","weixin pay result failed -->".$parse["err_code"].":".$parse["err_code_des"]."--->data:".print_r($params,true));
                    $this->_JSONRESULT($parse["err_code"].":".$parse["err_code_des"], '9104');
                }

                $result = array();
                $result["appId"] = $params['appid'];
                $result["timeStamp"] = time();
                $result['nonce_str'] = $parse["nonce_str"];//随机字符串，不长于32位
                $result["package"]  = "prepay_id=".$parse["prepay_id"];
                $result["signType"] = "MD5";
                $result['paySign'] = $this->getSign($result);//签名

                //添加交易信息
                $insert["iRspRef"] = $parse["prepay_id"];
                $this->db->insert('nh_order_pay', $insert);

                //获取ticket
                $token = $this->_curlget($config['tokenUrl']);
                //$token = file_get_contents($url);
                log_message("info","access token:".print_r($token,true));
                $token = json_decode($token,true);
                log_message("info","access token:".print_r($token,true));
                if(isset($token["errcode"])) {
                    $this->_JSONRESULT($token["errcode"].':'.$token["errmsg"], '9104');
                }
                $token = $token["access_token"];
                $js_ticket = $this->_curlget($config['ticketUrl'].$token);
                $js_ticket = json_decode($js_ticket,true);
                log_message("info","js token:".print_r($js_ticket,true));
                if(isset($js_ticket["errcode"])&&0!=$js_ticket["errcode"]) {
                    $this->_JSONRESULT($js_ticket["errcode"].':'.$js_ticket["errmsg"], '9104');
                }
                $ticket = $js_ticket["ticket"];
                $re = array();
                $re["success"] = true;
                $re["code"] = "0001";
                $re["msg"] = "获取成功";
                $re["data"] = $result;
                $re["ticket"] = $ticket;
                log_message("info","realpay back:".print_r($re,true));
                return $re;
            }

        }

    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提供外部直接调用支付接口
     * @param string $order <p>订单编号</p>
     * @params array $params <p>userId:用户ID</p>
     * @return mixed 生成支付结果，
     */
    public function payorder($orderNo = "",$params = array(),$isNative = false)
    {
        try {
            $this->_checkdate();
            if ("" === $orderNo) {
                $this->_JSONRESULT("没有订单号！", '9104');
            }
            if(!isset($params["userId"])){
                $this->_JSONRESULT("没有用户ID！", '9104');
            }
            //订单信息
            $sql = "select * from nh_order where orderNo='$orderNo' and IS_DISABLED=0";
            $query = $this->db->query($sql);
            if ($query->num_rows() === 0) {
                $this->_JSONRESULT("该订单不存在！", '9105');
            }
            $order = $query->row_array();
            $sql = "select * from nh_order_items where orderNo='$orderNo' and IS_DISABLED=0";
            $query = $this->db->query($sql);
            if (0 == $query->num_rows()) {
                $this->_JSONRESULT("订单明细不存在！", '9106');
            }
            $order['items'] = $query->result_array();
            $sql = "select * from nh_product np where np.IS_DISABLED=0 and np.prodId in (select prodId from nh_order_items where orderNo='$orderNo' and IS_DISABLED=0)";
            $query = $this->db->query($sql);
            $orderdata["products"] = $query->result_array();
            //未支付订单不需要做秒杀限购检查和可卖数检查
            //$orderdata = $this->_checkventory($orderdata,$params);//检查可卖数
            //$orderdata = $this->_checkdiscount($orderdata,$params);//秒杀商品限购检查
            return $this->_realPay($order,$isNative);
        } catch (Exception $e) {
            $err = $e->getMessage();
            $error = json_decode($err, true);
            if ($error === null) {
                $result = array();
                $result['success'] = false;
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                return $result;
            } else {
                return $error;
            }
        }
    }

    private function _getqrpng($url) {
        include_once APPPATH . "libraries/qr/qrlib.php";

        $config = $this->config->item('cfg-system');
        $config = $config['qr'];
        $tpath = $config['path'];
        $path = dirname(dirname(dirname(dirname(__FILE__)))) . $tpath;
        if (!file_exists($path))
            mkdir($path);
        $filename = "qrpng".md5($url.'|'.time()).'.png';
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 10;//生成图片大小
        log_message("info","create qr png-->filename:".$filename."-->path:".$path);
        QRcode::png($url,$path.$filename,$errorCorrectionLevel,$matrixPointSize,2);
        return $tpath.$filename;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 检查可卖数
     * @param mixed $orderdata <p>订单明细对象</p>
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return array 订单的明细对象
     */
    private function _checkventory($orderdata, $params)
    {
        $this->_checkdate();
        foreach ($orderdata['products'] as $key => $item) {
            $sql = "SELECT * FROM nh_product_store WHERE prodId=" . $item['prodId'];
            $query = $this->db->query($sql);
            $row = $query->row_array();
            if (($row['inventory'] - $row['ordernum']) < $item['orderNum']) {
                $this->_throwerror('商品【' . $item['name'] . '】库存不足！', '9107');
            } else {
                $orderdata['products'][$key]['inventory'] = $row['inventory'] - $row['ordernum'];
            }
        }
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 检查金币数
     * @param mixed $orderdata <p>订单明细对象</p>
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return array 订单的明细对象
     */
    private function _checkcoin($orderdata, $params)
    {
        $coin = 0;
        foreach ($orderdata['products'] as $key => $item) {
            $coin += $item['COIN'];
        }
        $this->db->where('Id',$params['userId']);
        $query = $this->db->get('nh_user');
        if(0==$query->num_rows()){
            $this->_throwerror('用户不存在', '9107');
        }
        $user = $query->row_array();
        if($user['COIN']<$coin){
            $this->_throwerror('抱歉，你的金币数量不够，无法购买！', '9107');
        }
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 秒杀商品限购检查
     * @param mixed $orderdata <p>订单明细对象</p>
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return array 订单的明细对象
     */
    private function _checkdiscount($orderdata, $params)
    {
        log_message('info','check discount start:');
        log_message('info','products:'.print_r($orderdata['products'],true));
        foreach ($orderdata['products'] as $key => $item) {
            if(3==$item['DISP_TYPE']){
                $sql = 'select count(*) as num from nh_order
                        where userId='.$params['userId'].' 
                        and IS_DISABLED=0 and orderNo in (select orderNO from nh_order_items where prodId='.$item['prodId'].' and IS_DISABLED=0)
                        and createTime between "'.$item['STARTTIME'].'" and "'.$item['ENDTIME'].'"';
                log_message('info','order sql:'.$sql);
                $order = $this->db->query($sql);
                $order = $order->row_array();
                log_message('info','order num:'.print_r($order,true));
                $sql = 'select value from sys_dictdata where type="秒杀限购" and name="'.$item['prodId'].'"';
                log_message('info','num sql:'.$sql);
                $tnum = $this->db->query($sql);
                $num = 1;
                if($tnum->num_rows()>0){
                    $tnum = $tnum->row_array();
                    $num = $tnum['value'];
                }
                if(($order['num']+$item["orderNum"])>$num){
                    $this->_throwerror('秒杀商品每个ID限购'.$num.'个（包括未支付订单），不可重复购买。', '9107');
                }
            }
        }
        log_message('info','check discount end:');
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 生成订单的默认收货地址
     * @param mixed $orderdata <p>订单明细对象</p>
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return array 订单的明细对象
     */
    private function _getAddress($orderdata, $params)
    {
        $sql = "SELECT * FROM nh_user_address WHERE IS_DISABLED=0 AND `default` = 1 AND userId=" . $params['userId'];
        $query = $this->db->query($sql);
        $data = $query->result_array();
        $orderdata['address'] = $data;
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 将订单对象中的所有设计金额的从分转为元
     * @param mixed $orderdata <p>订单对象</p>
     * @return array 订单对象
     */
    private function _chgOrderMoney($orderdata)
    {
        $orderdata['amount'] = round($orderdata['amount'] / 100, 2);
        $orderdata['tax'] = round($orderdata['tax'] / 100, 2);
        $orderdata['freight'] = round($orderdata['freight'] / 100, 2);
        $orderdata['preference'] = round($orderdata['preference'] / 100, 2);
        foreach ($orderdata['products'] as $key => $value) {
            $orderdata['products'][$key]['price'] = round($orderdata['products'][$key]['price'] / 100, 2);
            $orderdata['products'][$key]['salePrice'] = round($orderdata['products'][$key]['salePrice'] / 100, 2);
            $orderdata['products'][$key]['settlementPrice'] = round($orderdata['products'][$key]['settlementPrice'] / 100, 2);
            $orderdata['products'][$key]['PROMOTION_PRICE'] = round($orderdata['products'][$key]['PROMOTION_PRICE'] / 100, 2);
            $orderdata['products'][$key]['TAX'] = round($orderdata['products'][$key]['TAX'] / 100, 2);
            $orderdata['products'][$key]['orderPrice'] = round($orderdata['products'][$key]['orderPrice'] / 100, 2);
            $orderdata['products'][$key]['orderAmount'] = round($orderdata['products'][$key]['orderAmount'] / 100, 2);
            $orderdata['products'][$key]['orderTax'] = round($orderdata['products'][$key]['orderTax'] / 100, 2);
            $orderdata['products'][$key]['orderPost'] = round($orderdata['products'][$key]['orderPost'] / 100, 2);
        }
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 提取所有可用优惠券
     * @param mixed $orderdata <p>订单对象</p>
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return array 订单对象
     */
    private function _getCuppon($orderdata, $params)
    {
        $sql = "SELECT nc.* "
            . "FROM nh_cuppon nc,nh_user nu WHERE nc.IS_DISABLED =0 AND nc.userId=nu.Id "
            . "AND nc.startDate < curdate() AND nc.endDate > curdate() AND nc.status='已分配' "
            . "AND nc.userId=" . $params['userId'];
        $query = $this->db->query($sql);
        $orderdata['coupons'] = $query->result_array();
        return $orderdata;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据购物车ID，联动商品表，计算商品，价格，税率
     * @param int $userId <p>登录用户表的Id</p>
     * @param int[] $cartId <p>购物表的Id，数组</p>
     * @return array 订单的明细对象
     */
    private function _qryorderitembycardid($userId, $cartId)
    {
        $ids = implode(',', $cartId);
        //check OVERSEAFLAG
        $sql = "select distinct np.OVERSEAFLAG
                from nh_shopcart ns,nh_product np
                where ns.prodId=np.prodId and np.IS_DISABLED=0 AND ns.userId=$userId AND ns.Id in (" . $ids . ")";
        $query = $this->db->query($sql);
        if($query->num_rows()>1) {
            $this->_throwerror('跨境商品和非跨境商品无法同时结算！', '9107');
        }
        $sql = "SELECT np.*,"
            . " np.TAX/np.salePrice as ordertaxrate, if(np.DISP_TYPE=0,salePrice,PROMOTION_PRICE) as orderPrice,"
            . " ns.num as orderNum,nps.inventory-nps.ordernum as storenum "
            . " FROM nh_shopcart ns,nh_product np,nh_product_store nps "
            . " WHERE np.prodId=nps.prodId AND ns.prodId=np.prodId and np.IS_DISABLED=0 "
            . " AND ns.userId=$userId AND ns.Id in (" . $ids . ")";
        $query = $this->db->query($sql);
        $data = $query->result_array();
        if (sizeof($data) === 0) {
            $this->_throwerror("没有找到对应商品明细", "9106");
        }
        return $data;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据购物车ID，联动商品表，计算商品，价格，税率
     * @param int $userId <p>登录用户表的Id</p>
     * @param int $prodId <p>商品表的prodId编号</p>
     * @param int $num <p> 购买数量</p>
     * @return array 订单的明细对象
     */
    private function _qryorderitembyprodId($userId, $prodId, $num)
    {
        $sql = "SELECT np.*,"
            . " np.TAX/np.salePrice as ordertaxrate,if(np.DISP_TYPE=0,salePrice,PROMOTION_PRICE) as orderPrice,"
            . " $num as orderNum,nps.inventory-nps.ordernum as storenum "
            . " FROM nh_product np,nh_product_store nps "
            . " WHERE np.prodId=nps.prodId and np.IS_DISABLED=0 AND np.prodId=$prodId";
        $query = $this->db->query($sql);
        $data = $query->result_array();
        if (sizeof($data) === 0) {
            $this->_throwerror("没有找到对应商品明细", "9107");
        }
        return $data;
    }
	
	/**
     * (PHP 4, PHP 5)<br/>
     * 根据砍价单ID，联动商品表，计算商品，价格，税率
     * @param int $userId <p>登录用户表的Id</p>
     * @param int $discountId <p>商品表的discountId编号</p>
     * @param int $num <p> 购买数量</p>
     * @return array 订单的明细对象
     */
    private function _qryorderitembydiscountId($userId, $discountId, $num)
    {
        $sql = "SELECT np.*,"
            . " np.TAX/np.salePrice as ordertaxrate,nd.lastamount as orderPrice,"
            . " $num as orderNum,nps.inventory-nps.ordernum as storenum "
            . " FROM nh_product np,nh_product_store nps,nh_discount nd "
            . " WHERE np.prodId=nps.prodId and np.IS_DISABLED=0 AND np.prodId=nd.prodId and nd.Id=$discountId";
        $query = $this->db->query($sql);
        $data = $query->result_array();
        if (sizeof($data) === 0) {
            $this->_throwerror("没有找到对应商品明细", "9107");
        }
        return $data;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 返回错误信息，通过中断，直接送到controller，返回Ajax的调用，前台显示错误信息
     * @param string $msg <p>错误信息</p>
     * @param int $code [option] <p>错误信息编号</p>
     * @param mixed $obj [option] <p>
     * 传送的数据对象
     * </p>
     * @throws 直接发出自定义错误，编号0
     */
    private function _throwerror($msg, $code = '9001', $obj = array())
    {
        $data = array();
        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['obj'] = $obj;
        $msg = json_encode($data);
        throw new Exception($msg, 0, null);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据订单对象和参数对象，生成订单记录
     * @param mixed $orderdata <p>订单对象</p>
     * @param mixed $params <p>参数对象
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td>
     * </tr>
     * <tr valign="top">
     * <td><b>params[userId]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td><b>params[buyerMsg]</b></td>
     * <td>购买者留言</td></tr>
     * <tr valign="top">
     * <td><b>params[certNo]</b></td>
     * <td>购买者身份证信息</td></tr></p>
     * @return mixed 订单信息
     */
    private function _insertOrder($orderdata, $params)
    {
        $order = array();
        $order['orderNo'] = $this->getNo('WXPO' . Date('YmdH'), 3);
        $order['userId'] = $params['userId'];
        $order['createTime'] = Date('Y-m-d H:i:s');
        $order['buyerMsg'] = $params['buyerMsg'];
        $order['postFee'] = $orderdata['freight']*100;                         // 先根据计算填入运费，以后选择地区的话，从前台导入
        $order['amount'] = $orderdata['amount']*100;
        $order['TAXFEE'] = $orderdata['tax']*100;
        $order['certNo'] = $params['certNo'];
        $order['needInvoice'] = 0;
        $order['invoiceName'] = '';
        $order['createdatetime'] = Date('Y-m-d H:i:s');
        $order['modifydatetime'] = Date('Y-m-d H:i:s');
        $order['createuser'] = $params['userId'];
        $order['modifyuser'] = $params['userId'];
        $query = $this->db->insert("nh_order", $order);
        $orderid = $this->db->insert_id();
        $order['Id'] = $orderid;
        $order['items'] = array();
        // 插入订单明细
        $no = 1;
        foreach ($orderdata['products'] as $item) {
            $insert = array();
            $insert['orderNo'] = $order['orderNo'];
            $insert['no'] = $no;
            $no += 1;
            $insert['prodId'] = $item['prodId'];
            $insert['count'] = $item['orderNum'];
            $insert['price'] = $item['orderPrice']*100;
            $insert['settlementPrice'] = $item['settlementPrice']*100;
            $insert['tax'] = $item['orderTax']*100;
            $insert['prodName'] = $item['name'];
            $insert['amount'] = $item['orderAmount']*100;
            $insert['FROM_ID'] = $item['FROM_ID'];
            $insert['image'] = $item['listimage'];
            $insert['status'] = '';
            $insert['IS_DISABLED'] = 0;
            $insert['createdatetime'] = Date('Y-m-d H:i:s');
            $insert['modifydatetime'] = Date('Y-m-d H:i:s');
            $insert['createuser'] = $params['userId'];
            $insert['modifyuser'] = $params['userId'];
            $this->db->insert('nh_order_items', $insert);
            array_push($order['items'], $insert);
        }
        return $order;
    }

    private function _checkdate(){
        $now = strtotime("now");
        $new_tax_start = strtotime("2016-04-07 17:30:00");
        $new_tax_end = strtotime("2016-04-07 18:30:00");
        if($now>$new_tax_start&&$now<$new_tax_end) {
            $this->_throwerror("由于海关新税率调整，请于2016年4月7日18点30分以后下单，敬请谅解！", "9103");
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 删除购物车数据
     * @param mixed $params <p>参数对象</p>
     * <tr valign="top">
     * <td colspan='2'>参数中的对象</td>
     * <td colspan='2'>说明</td></tr>
     * <tr valign="top">
     * <td colspan="2"><b>params[userId]</b></td>
     * <td colspan='2'>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[cartId]</b></td>
     * <td colspan='2'>购物车编号，数组</td></tr>
     * @return int 使用的购物车数量
     */
    private function _delCartData($params)
    {
        $ids = implode(',', $params['cartId']);
        $sql = "UPDATE nh_shopcart SET IS_DISABLED=1 WHERE Id in ($ids)";
        $this->db->query($sql);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
        $key = "SHOPCART-USER-NUM-" . $params['userId'];
        if (isset($this->cache)) {
            $this->cache->delete($key);
        }
        return sizeof($params['cartId']);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 添加收货地址信息
     * @param mixed $params <p>参数对象</p>
     * <tr valign="top">
     * <td colspan='2'>参数中的对象</td>
     * <td colspan='2'>说明</td></tr>
     * <tr valign="top">
     * <td colspan="2"><b>params[userId]</b></td>
     * <td colspan='2'>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[address]</b></td>
     * <td colspan='2'>收货地址表的Id</td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[orderNo]</b></td>
     * <td colspan='2'>订单的orderNo</td></tr>
     * @return mixed 收货地址信息
     */
    private function _addReceiveData($params)
    {
        $sql = "SELECT * FROM nh_user_address WHERE Id=" . $params['address'];
        $query = $this->db->query($sql);
        $result = $query->result_array();
        foreach ($result as $item) {
            $insert = array();
            $insert['orderNo'] = $params['orderNo'];
            $insert['name'] = $item['userName'];
            $insert['phone'] = $item['Mobile'];
            $insert['state'] = $item['province'];
            $insert['city'] = $item['city'];
            $insert['district'] = $item['zone'];
            $insert['address'] = $item['address'];
            $insert['regionId'] = $item['regionId'];
            $insert['zip'] = $item['zip'];
            $insert['IS_DISABLED'] = 0;
            $insert['createdatetime'] = Date('Y-m-d H:i:s');
            $insert['modifydatetime'] = Date('Y-m-d H:i:s');
            $insert['createuser'] = $params['userId'];
            $insert['modifyuser'] = $params['userId'];
            $this->db->insert('nh_order_receiver', $insert);
            $insert['id'] = $this->db->insert_id();
        }
        return $insert;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据选择的代金券，更新订单和代金券表
     * @param mixed $params <p>参数对象</p>
     * <tr valign="top">
     * <td colspan='2'>参数中的对象</td>
     * <td colspan='2'>说明</td></tr>
     * <tr valign="top">
     * <td colspan="2"><b>params[userId]</b></td>
     * <td colspan='2'>登录用户表的<i>Id</i></td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[cuppons]</b></td>
     * <td colspan='2'>用户使用的代金券Id</td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[orderNo]</b></td>
     * <td colspan='2'>订单的orderNo</td></tr>
     * <tr valign="top">
     * <td colspan='2'><b>params[preference]</b></td>
     * <td colspan='2'>本订单可用代金券金额</td></tr>
     * @return mixed 扣减代金券金额和张数
     */
    private function _addCupponData($params)
    {
        if (sizeof( $params['cuppons']) > 0) {
            $ids = implode(',', $params['cuppons']);
            $sql = "SELECT * FROM nh_cuppon WHERE IS_DISABLED =0 AND userId=" . $params['userId'] . " AND Id in ($ids)";
            $query = $this->db->query($sql);
            $result = $query->result_array();
            $cuppon = 0;
            foreach ($result as $key => $item) {
                $insert = array();
                $insert['orderNo'] = $params['orderNo'];
                $insert['name'] = $item['name'];
                $insert['des'] = $item['des'];
                $insert['price'] = $item['price'];
                $cuppon += $item['price'];
                $insert['startDate'] = $item['startDate'];
                $insert['endDate'] = $item['endDate'];
                $insert['IS_DISABLED'] = 0;
                $insert['createdatetime'] = Date('Y-m-d H:i:s');
                $insert['modifydatetime'] = Date('Y-m-d H:i:s');
                $insert['createuser'] = $params['userId'];
                $insert['modifyuser'] = $params['userId'];
                $this->db->insert('nh_order_cuppon', $insert);
                $result[$key]['use'] = 1;
                if ($cuppon > $params['preference']) {
                    $cuppon = $params['preference'];
                    break;
                }
            }
            $cupponnum = 0;
            foreach ($result as $item) {
                if (!isset($item['use'])) {
                    continue;
                }
                $data = array();
                $data['status'] = '已使用';
                $this->db->where("Id", $item['Id']);
                $cupponnum = $cupponnum + 1;
                $this->db->update('nh_cuppon', $data);
            }
        } else {
            $cupponnum = 0;
            $cuppon = 0;
        }
        $result = array();
        $result['cuppon'] = $cuppon;
        $result['cupponnum'] = $cupponnum;
        return $result;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据订单情况，更新相关字段数据
     * @param mixed $order <p>订单对象</p>
     * @param mixed $params <p>参数对象</p>
     * <tr valign="top">
     * <td colspan='2'>参数中的对象</td>
     * <td colspan='2'>说明</td></tr>
     * <tr valign="top">
     * <td colspan="2"><b>params[userId]</b></td>
     * <td colspan='2'>登录用户表的<i>Id</i></td></tr>
     */
    private function _updateOrderStatus($order, $params)
    {
        // 更新用户订单数
        if (isset($order['cart'])) {
            $sql = "UPDATE nh_user SET waitpay = waitpay + 1, cartnum = cartnum - " . $order['cart']['num'] . ", "
                . "  cupponnum = cupponnum - " . $order['cuppon']['cupponnum'] . "  "
                . "  WHERE Id=" . $params['userId'];
            $this->db->query($sql);
        } else {
            $sql = "UPDATE nh_user SET waitpay = waitpay + 1, "
                . "  cupponnum = cupponnum - " . $order['cuppon']['cupponnum'] . "  "
                . "  WHERE Id=" . $params['userId'];
            $this->db->query($sql);
        } 

		//更新砍价单状态
		if(isset($params['discountId'])){
			$sql = "update nh_discount set PAYSTATUS=1,status=0 where Id=".$params["discountId"];
			$this->db->query($sql);
		}
        // 更新商品库存表
        foreach ($order['items'] as $item) {
            $sql = "UPDATE nh_product_store SET ordernum = ordernum + " . $item['count'] . " WHERE prodId=" . $item['prodId'];
            $this->db->query($sql);
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 返回错误信息结构
     * @param stirng $msg <p>错误信息</p>
     * @param string $code <p>错误编号</p>
     * @param mixed $obj <p>错误对象</p>
     */
    private function _JSONRESULT($msg, $code = '9001', $obj = array())
    {
        $result = array();
        $result['success'] = false;
        $result['msg'] = $msg;
        $result['code'] = $code;
        $result['obj'] = $obj;
        return $result;
    }

    /**
     * 获取编号
     * @param string $type 编号的固定开始文字
     * @param integer $num 编号的最加流水号长度,默认2位
     * @return array $result
     */
    public function getNo($type, $num = 2)
    {
        $switch = true;
        while ($switch) {
            $sql = "SELECT * FROM sys_configdata WHERE type='编号生成' AND name = '" . $type . "'";
            $query = $this->db->query($sql);
            $result = $query->result_array();
            if (sizeof($result) == 0) {
                $result = $type . str_pad('1', $num, '0', STR_PAD_LEFT);
                $this->load->helper('guid_helper');
                $data['id'] = guid();
                $data['type'] = '编号生成';
                $data['seqno'] = $type;
                $data['value'] = '1';
                $data['name'] = $type;
                $data["createdatetime"] = date('Y-m-d G:i:s');
                $data["createuser"] = 'sys';
                $this->db->insert('sys_configdata', $data);
            } else {
                $no = $result[0]['value'] + 1;
                $data['value'] = $no;
                $data["modifydatetime"] = date('Y-m-d G:i:s');
                $data["modifyuser"] = 'sys';
                $this->db->where('id', $result[0]['id']);
                $this->db->update('sys_configdata', $data);
                $result = $type . str_pad($no, $num, '0', STR_PAD_LEFT);
            }
            $insert = array();
            $insert['ID'] = $result;
            $switch = !$this->db->insert('sys_key', $insert);
        }
        return $result;
    }

}

include_once APPPATH . 'libraries/phprpc/phprpc_server.php';

$server = new PHPRPC_Server();
$server->add('createOrder', new Orderprocess()); //添加允许远程访问的方法  
$server->add('createOrderByprodId', new Orderprocess()); //添加允许远程访问的方法  
$server->add('createOrderBydiscountId', new Orderprocess()); //添加允许远程访问的方法 
$server->add('orderSelect', new Orderprocess()); //添加允许远程访问的方法  
$server->add('orderSelectByprodId', new Orderprocess()); //添加允许远程访问的方法  
$server->add('orderSelectBydiscountId', new Orderprocess()); //添加允许远程访问的方法 
$server->add('payOrder', new Orderprocess()); //添加允许远程访问的方法  
$server->start(); //开始

