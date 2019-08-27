<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pinzhi365 extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('cfg-pinzhi365',true);
		$this->params = $this->config->item('cfg-pinzhi365'); 
                $this->load->database();
                set_time_limit(0);
	}

 
        //推送订单
        public function putorder($orderNo){
            return;
            if ($orderNo === ''){
                echo "NO ORDER NO";return false;
            }
            $this->db->where('IS_DISABLED',0);
            $this->db->where('orderno',$orderNo);
            $query = $this->db->get('nh_order');
            if ($query->num_rows() === 0){
                echo "NO THIS ORDER";return false;
            }
            $result = $query->row_array();
            $put = array();
            $put['orderNo'] = $result['orderNo'];
            $put['createTime'] = strtotime($result['createTime'])*1000;
            $put['buyerMsg'] = $result['buyerMsg'];
            if ($put['buyerMsg'] === ""){$put['buyerMsg'] = ' ';}
            $put['postFee'] = $result['postFee'];
            $put['invoiceNo'] = $result['certNo'];
            $put['needInvoice'] = $result['needInvoice'];
            $put['invoiceName'] = $result['invoiceName'];
            if ($put['invoiceName'] === ""){$put['invoiceName'] = ' ';}
            $this->db->where('IS_DISABLED',0);
            $this->db->where('orderno',$orderNo);
            $query = $this->db->get('nh_order_items');
            $result = $query->result_array();
            $put['items'] = array();
            foreach($result as $item){
                $pp = array();
                $pp['no'] = $item['no'];
                $pp['prodId'] = $item['prodId'];
                $pp['count'] = $item['count'];
                $pp['price'] = $item['price'];
                $pp['settlementPrice'] = $item['settlementPrice'];
                $pp['prodName'] = $item['prodName'];
                array_push($put['items'],$pp);
            }
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
            $put['receiver']['iRegionId'] = 100002;
            //print_r($put);exit;
            $data = self::call('order.put',$put);
            //print_r($data);exit;
            if ($data['result'] === 0){
                foreach ($data['orders'] as $item){
                    $update = array();
                    $update['orderId'] = $item['orderId'];
                    $update['applystatus'] = '已支付';
                    $update['sendstatus'] = '待发货';
                    $update['modifydatetime'] = Date('Y-m-d H:i:s');
                    $update['modifyuser'] = 'sys';
                    $this->db->where('orderNo',$item['orderNo']);
                    $this->db->where('IS_DISABLED',0);
                    $this->db->update('nh_order',$update);
                    //$update['amount'] = $item['amount'];
                    //$update['postFee'] = $item['postFee'];
                }
            }
            return true;
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
}

include_once APPPATH.'libraries/phprpc/phprpc_server.php';

$server = new PHPRPC_Server();  
$server->add('putorder',new Pinzhi365());//添加允许远程访问的方法  
$server->start();//开始

