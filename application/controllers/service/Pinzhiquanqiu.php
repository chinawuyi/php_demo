<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pinzhiquanqiu extends CI_Controller {

    public function __construct()
    {
            parent::__construct();
            $this->config->load('cfg-pinzhiquanqiu',true);
            $this->params = $this->config->item('cfg-pinzhiquanqiu'); 
            $this->load->database();
            set_time_limit(0);
    }

    // 调用远程接口的信息验签
    private function call($method,$input=array()){
        $item = array();
        $item['method'] = $method;
        $item['mid'] = $this->params['mid'];
        date_default_timezone_set('Asia/Shanghai');
        $item['t'] = time()*1000;
        $item['param'] = json_encode($input,JSON_UNESCAPED_UNICODE); 
        if ($this->params[$method]['input'] === NULL){
            $signdata = 'method='.$item['method'].'&mid='.$item['mid'].'&t='.$item['t'].'&skey='.$this->params['skey'];
            unset($item['param']);
        }
        else
            {$signdata = 'method='.$item['method'].'&mid='.$item['mid'].'&param='.$item['param'].'&t='.$item['t'].'&skey='.$this->params['skey'];}
        $item['sign'] = MD5($signdata);
        $url = $this->params['url'].$this->params[$method]['action'];
        //print_r($signdata);exit;
        log_message('DEBUG', "REMOTE CALL pinzhi365 method=".$method." name【".$this->params[$method]['name']."】,url ->".$url);
        log_message('DEBUG', "REMOTE CALL pinzhi365 item=".print_r($item,true));
        include_once APPPATH."libraries/Requests.php";
        Requests::register_autoloader();
        $response = Requests::post($url, array(), $item);
        log_message('DEBUG', "REMOTE CALL pinzhi365 response ->".print_r($response,true));
        $result = json_decode($response->body,true);
        return $result;
    }
        
    private function errorput($data){
        if (isset($data['error'])) {
            $result = array();
			$result['result'] = 0;
            $result['success'] = false;
            $result['msg'] = $data['error']['msg'];
            $result['code'] = $data['error']['code'];
            if (($result['code'] === '1006')&&(isset($data['error']['orderId']))){
                $result['success'] = true;
				$result['result'] = 1;
                $result['orderId'] = $data['error']['orderId'];
            }
            return $result;
        }
        else return $data;
    }
    
     /*
     *   public function putorder($params)
     *   input params string $orderNo 
     *   output params boolean : true,false
     */
    public function putorder($orderNo){
        if ($orderNo === ''){
            echo "NO ORDER NO";return array('success'=>false,'msg'=>'没有定义订单编号');
        }
        $insert = array();
        $insert['ID'] = 'PUTORDER'.$orderNo;
        $result = $this->db->insert('sys_key',$insert);
        if ($result === false) {return false;}
        $this->db->where('IS_DISABLED',0);
        $this->db->where('orderno',$orderNo);
        $query = $this->db->get('nh_order');
        if ($query->num_rows() === 0){
            echo "NO THIS ORDER";return array('success'=>false,'msg'=>'本订单不存在');
        }
        $result = $query->row_array();
        $put = array();
        $put['orderNo'] = $result['orderNo'];
        $put['createTime'] = $result['createTime'];
        $put['buyerMsg'] = $result['buyerMsg'];
        if ($put['buyerMsg'] === ""){$put['buyerMsg'] = ' ';}
        $put['postFee'] = $result['postFee'];
        $put['tax'] = $result['TAXFEE'];
        $put['needInvoice'] = $result['needInvoice'];
        $put['invoiceName'] = $result['invoiceName'];
        $put['amount'] = $result['amount'];
        $put['merchantId'] = $this->params['mid'];
        $put['merchantName'] = $this->params['mname'];
        $certNo = $result['certNo'];
        if ($put['invoiceName'] === ""){$put['invoiceName'] = ' ';}
        $this->db->where('IS_DISABLED',0);
        $this->db->where('orderno',$orderNo);
        $query = $this->db->get('nh_order_items');
        $result = $query->result_array();
        $put['items'] = array();
        foreach($result as $item){
            $pp = array();
            $pp['no'] = $item['no'];
            $pp['prodId'] = $item['FROM_ID'];
            $pp['count'] = $item['count'];
            $pp['price'] = $item['price'];
            $pp['amount'] = $item['amount'];
            $pp['tax'] = $item['tax'];
            $pp['settlementPrice'] = $item['settlementPrice'];
            $pp['prodName'] = $item['prodName'];                                          //$item['prodName'];
            if (sizeof($pp['prodName'])===0)$pp['prodName'] = ' ';
            array_push($put['items'],$pp);
        }
        // 需要增加根据订单总金额重新计算订单明细的各个商品的价格
        $this->db->where('IS_DISABLED',0);
        $this->db->where('orderno',$orderNo);
        $query = $this->db->get('nh_order_receiver');
        $result = $query->row_array();
        $put['receiver'] = array();
        $put['receiver']['name'] = $result['name'];
        $put['receiver']['phone'] = $result['phone'];
        $put['receiver']['state'] = $result['state'];
        $put['receiver']['city'] = $result['city'];
        $put['receiver']['district'] = $result['district'];
        $put['receiver']['address'] = $result['address'];
        $put['receiver']['zip'] = $result['zip'];
        $put['receiver']['certNo'] = $certNo;
        //print_r($put);exit;
        $data = self::call('order.put',$put);
        $result = self::errorput($data);
        if ($result['result'] === 0){return $result;}
		else{
			$update = array();
            $update['orderId'] = $result['orderId'];
            $update['sendstatus'] = '待发货';
            $update['modifydatetime'] = Date('Y-m-d H:i:s');
            $update['modifyuser'] = 'sys';
            $this->db->where('orderNo',$item['orderNo']);
            $this->db->where('IS_DISABLED',0);
            $this->db->update('nh_order',$update);
        }
        $result = array();
        $result['success'] = true;
        $result['msg'] = '推送成功'.$item['orderNo'];
        $result['code'] = '0000';
        return $result;
    }

}

include_once APPPATH.'libraries/phprpc/phprpc_server.php';

$server = new PHPRPC_Server();  
$server->add('putorder',new Pinzhiquanqiu());//添加允许远程访问的方法  
$server->start();//开始

