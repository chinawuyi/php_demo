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

        //测试函数
        public function test_productdetail($prodId=500432){
            $input= array();
            $input['prodId'] = $prodId;
            $result = self::call('product.getDetail',$input);
            print_r($result);
            
        }
        
        public function test_productlist($cardId=-1){
            // 商品列表测试
            $input= array();
            $input['catId'] = $cardId;
            $input['pageNo'] = 1;
            $input['pageSize'] = 100;
            $result = self::call('product.list',$input);
            print_r($result);exit;
            echo 'OK';
        }

        //同步服务器数据函数
        //同步所有产品信息
        public function sync_product($page=1){
            $input= array();
            $input['catId'] = -2;
            $input['pageNo'] = $page;
            $input['pageSize'] = 10;
            $data = self::call('product.list',$input);
            foreach($data['products'] as $item){
                $detail = array();
                $detail['prodId'] = $item;
                $result = self::call('product.getDetail',$detail);
                $dd = array();
                $dd[0] = $result;
                self::process('nh_product','prodId',$this->params["product.getDetail"]["output"],$dd);
/*                $this->db->where('prodId',$detail['prodId']);
                $query = $this->db->get('nh_product');
                $row = $query->row_array();
                include_once APPPATH."libraries/Requests.php";
                Requests::register_autoloader();
                $response = Requests::post($row['content'], array(), array());
                $data = array();
                $data['content'] = $response->body;
                $this->db->update('nh_product',$data,array('prodId'=>$detail['prodId']));  */
            }
            $this->db->query("UPDATE nh_product SET groupId=prodId WHERE IS_DISABLED=0 AND groupId=0");
            echo "SYNC-OK SYNC [".sizeof($data['products'])."] PRODUCT";
        }
        
        //同步单个产品信息
        public function sync_one($prodId){
            $detail = array();
            $detail['prodId'] = $prodId;
            $result = self::call('product.getDetail',$detail);
            $data = array();
            $data[0] = $result;
            self::process('nh_product','prodId',$this->params["product.getDetail"]["output"],$data);
            $this->db->query("UPDATE nh_product SET groupId=prodId WHERE IS_DISABLED=0 AND groupId=0");
            echo "SYNC-OK";
        }

        //批量同步订单状态
        public function sync_orderstatus(){
            $sql = "SELECT orderId FROM nh_order WHERE IS_DISABLED=0 AND applystatus='已支付'";
            $query = $this->db->query($sql);
            $result = $query->result_array();
            $detail = array();
            $detail['orderIds'] = array();
            foreach($result as $item)
                {array_push($detail['orderIds'],$item['orderId']);}
            $result = self::call('order.getStatus',$detail);
            $updates = array();
            foreach($result['orders'] as $item){
                $update = array();
                $update['orderNo'] = $item['orderNo'];
                if ($item['status'] == 1){
                    $update['orderstatus'] = '已取消';
                }
                if ($item['status'] == 7){                          // 1，已取消，0，2，5 已支付，7，已发货  4 已牵手
                    $update['sendstatus'] = '待签收';
                }
                if ($item['status'] == 4){                          // 1，已取消，0，2，5 已支付，7，已发货  4 已牵手
                    $update['orderstatus'] = '已完成';
                    $update['sendstatus'] = '已签收';
                }
                array_push($updates,$update);
            }
            if (sizeof($updates)>0){
                $this->db->update_batch('nh_order',$updates,'orderNo');
            }
            echo "SYNC-OK";
        }

        //批量同步快递单信息
        public function sync_orderexpress(){
            $detail = array();
            $detail['orderIds'] = array();
            array_push($detail['orderIds'],'151208120242');
            $result = self::call('order.getExpress',$detail);
            $inserts = array();
            $updates = array();
            foreach($result['orders'] as $item){
                $this->db->where('IS_DISABLED',0);
                $this->db->where('orderId',$item['orderId']);
                $this->db->where('no',$item['no']);
                $query = $this->db->get('nh_order_expresses');
                if ($query->num_rows() === 0){
                    $insert = array();
                    $insert['orderId'] = $item['orderId'];
                    $insert['no']      = $item['no'];
                    $insert['company'] = $item['company'];
                    $insert['status'] = 0;
                    $insert['IS_DISABLED'] = 0;
                    $insert['createdatetime'] = Date('Y-m-d H:i:s');
                    $insert['createuser'] = 'sys';
                    array_push($inserts,$insert);
                }
                else {
                    $update = array();
                    $update['orderId'] = $item['orderId'];
                    $update['no']      = $item['no'];
                    $update['company'] = $item['company'];
                    $update['IS_DISABLED'] = 0;
                    $update['modifydatetime'] = Date('Y-m-d H:i:s');
                    $update['modifyuser'] = 'sys';
                    array_push($updates,$update);
                }
            }
            if (sizeof($inserts)>0){
                $this->db->insert_batch('nh_order_expresses',$inserts);
            }
            if (sizeof($updates)>0){
                $this->db->update_batch('nh_order_expresses',$updates,'no');
            }
            echo "SYNC-OK";
        }
        
        //批量同步快递信息
        public function sync_getexpress(){
            $sql = "SELECT no,company FROM nh_order_expresses WHERE IS_DISABLED=0";
            $query = $this->db->query($sql);
            $detail = $query->result_array();
            $result = self::call('express.get',$detail);
            foreach($result as $item){
                $this->db->where('no',$item['no']);
                $this->db->where('company',$item['company']);
                $query = $this->db->get('nh_order_expresses');
                $row = $query->row_array();
                $rowid = $row['Id'];
                $orderId = $row['orderId'];
                $sql = "DELETE FROM nh_order_expresses WHERE Id=$rowid";
                $this->db->query($sql);
                $sql = "DELETE FROM nh_order_expresses_items WHERE expresses_id=$rowid";
                $this->db->query($sql);
                $insert = array();
                $insert['orderId'] = $orderId;
                $insert['no'] = $item['no'];
                $insert['company'] = $item['company'];
                $insert['IS_DISABLED'] = 0;
                $insert['createdatetime'] = Date('Y-m-d H:i:s');
                $insert['createuser'] = 'sys';
                $this->db->insert('nh_order_expresses',$insert);
                $ids = $this->db->insert_id();
                $inserts = array();
                foreach($item['items'] as $val){
                    $insert = array();
                    $insert['expresses_id'] = $ids;
                    $insert['t'] = $val['t'];
                    $insert['context'] = $val['context'];
                    $insert['areaCode'] = $val['areaCode'];
                    $insert['areaName'] = $val['areaName'];
                    $insert['IS_DISABLED'] = 0;
                    $insert['createdatetime'] = Date('Y-m-d H:i:s');
                    $insert['createuser'] = 'sys';
                    array_push($inserts,$insert);
                }
                if (sizeof($inserts)>0){
                    $this->db->insert_batch('nh_order_expresses_items',$inserts);
                }
            }
            print_r($result);exit;
            echo "SYNC-OK";
        }
        
        //推送订单
        public function test_putorder($orderNo){
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
        
        //同步产品大类
        public function sync_catalog(){
            // 商品大类测试
            //$this->db->where('FROM_TYPE','品质365');
            //$this->db->delete('nh_categories');
            $result = self::call('category.list');
            $data[0] = $result;
            self::process('nh_categories','catId',$this->params["category.list"]["output"],$data);
            echo 'SYNC OK';
        }

        // 回调函数
        //回调快递单状态
        public function express_put(){
            $data = self::checkmsg('express_put');
            log_message('debug', 'express_put->'.print_r($data,true));
            foreach ($data['orders'] as $item){
                $this->db->where('orderId',$item['orderId']);
                $this->db->where('IS_DISABLED',0);
                $query = $this->db->get('nh_order');
                if ($query->num_rows()===0){
                    log_message('ERROR',"回调接口express_put调用出错，没有找到对应Orderid:-->".$item['orderId']);
                    echo "NO SUCH ORDER";continue;
                }
                $result = $query->row_array();
                $insert = array();
                $insert['orderId'] = $result['orderId'];
                foreach($item['expresses'] as $val){
                    $insert['no'] = $val['no'];
                    $insert['company'] = $val['company'];
                    $insert['status'] = 0;
                    $insert['IS_DISABLED'] = 0;
                    $insert['createdatetime'] = Date('Y-m-d H:i:s');
                    $insert['createuser'] = 'sys';
                    $this->db->insert('nh_product_expresses',$insert);
                }
            }
            echo 'OK';
        }
        
        //回调产品状态
        public function product_updateDetail(){
            $data = self::checkmsg('product_updateDetail');
            log_message('debug', 'product_updateDetail->'.print_r($data,true));
            foreach($data['products'] as $item){
                $this->db->where('IS_DISABLED',0);
                $this->db->where('prodId',$item['prodId']);
                $update = array();
                $update['name'] = $item['name'];
                //$update['status'] = $item['status'];
                $update['price'] = $item['price'];
                $update['salePrice'] = $item['salePrice'];
                $update['point'] = $item['point'];
                $update['inventory'] = $item['inventory'];
                $update['modifydatetime'] = Date('Y-m-d H:i:s');
                $update['modifyuser'] = 'sys';
                $this->db->update('nh_product',$update);
            }
            echo 'OK';
        }

        // 通用函数
        // 回调函数用接口信息校验
        private function checkmsg($name){
            $item = $this->input->post(null,false);
            log_message('DEBUG', '回调接口参数 ->'.print_r($item,true));
            //log_message('DEBUG', 'MID设置 ->'.print_r($this->params['mid'],true));
            if (!isset($item['method'])){self::error(1002,'方法不存在');}
            if ($item['method'] !== $name){self::error(1002,'方法调用不正确');}
            if (!isset($item['mid'])){self::error(1003,'MID不存在');}
            if ($item['mid'] != $this->params['mid']){self::error(1003,'MID不正确');}
            if (!isset($item['t'])){self::error(1004,'接口无时间戳');}
            if (abs($item['t']/1000 - time())>(5*60)){self::error(1004,'时间戳超过5分钟');}
            $signdata = 'method='.$item['method'].'&mid='.$item['mid'].'&param='.$item['param'].'&t='.$item['t'].'&skey='.$this->params['skey'];
            if ($item['sign'] !== MD5($signdata)){self::error(1001,'信息可能被篡改,验签不通过!');}
            $data = json_decode(urldecode($item['param']),true); 
            return $data;
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
            include_once APPPATH."libraries/Requests.php";
            Requests::register_autoloader();
            $response = Requests::post($url, array(), $item);
            log_message('DEBUG', "REMOTE CALL pinzhi365 response ->".print_r($response,true));
            $result = json_decode($response->body,true);
            return $result;
        }
        
        // 回调接口返回错误信息
        private function error($code=1001,$msg=''){
            $error = array();
            $error['error']=array();
            $error['error']['code'] = $code;
            $error['error']['msg'] = $msg;
            log_message('ERROR', '回调接口访问错误->code='.$code.'  msg->'.$msg);
            echo json_encode($error,256);exit;
        }
        
        // 根据cfg-pingzhi365的定义，解析接口数据，影响数据库
        private function process($table,$keyfield,$define,$data,$parent=array()) {
            //print_r("table -- ".$table." -- define".json_encode($define));
            //print_r("-- data -- ".json_encode($data)." -- parent --".json_encode($parent));
            //print_r($data);exit;
            //print_r($define);exit;
            foreach($data as $item){
                $insert = array();
                foreach($define as $key=>$val){
                    $keylist = explode('.',$key);
                    if (\sizeof($keylist) === 1) {$keylist[1] = $keylist[0];}
                    if ($val === 'array'){continue;}                                          // 先忽略
                    if ($keylist[0] === ''){
                        $insert[$keylist[1]] = $item;
                        $insert['IS_DISABLED'] = 0;
                        $insert['createdatetime'] = Date('Y-m-d H:i:s');
                        $insert['createuser'] = 'sys';
                        continue;}           // 直接数组
                    if (($val === 'parent')&&(isset($parent[$keylist[0]]))){                 // 父节点数据,没有不产生
                        $insert[$keylist[1]] = $parent[$keylist[0]];
                    }     
                    if (!isset($item[$keylist[0]])){continue;}                                // 无数据，不产生记录
                    if ($val === 'int'){$insert[$keylist[1]] = $item[$keylist[0]];}           // 整形
                    if ($val === 'string'){$insert[$keylist[1]] = $item[$keylist[0]];}        // 字符串
                    if ($val === 'children'){                                                 // 树结构
                        $list = explode('.',$key);
                        if (\sizeof($list) === 1) $list[1] = $list[0];
                        if (sizeof($item[$list[0]]) > 0) {
                            self::process($table,$keyfield,$define,$item[$list[0]],$item);
                        }
                    }
                    if (is_array($val)){// 子节点
                        $list = explode('.',$key);
                        if (\sizeof($list) === 1) $list[1] = $list[0];
                        self::process($list[1],$list[2],$define[$key],$item[$list[0]],$item);
                    }
                }
                $insert['IS_DISABLED'] = 0;
                $insert['modifydatetime'] = Date('Y-m-d H:i:s');
                $insert['modifyuser'] = 'sys';
                $insert['createdatetime'] = Date('Y-m-d H:i:s');
                $insert['createuser'] = 'sys';
                //echo 'keyfield-->'.$keyfield.'--->';
                //echo $table.'-->'.print_r($insert,true);exit;
                $keylist = explode('-',$keyfield);
                if (isset($insert[$keylist[0]])){ 
                    foreach($keylist as $key){$this->db->where($key,$insert[$key]);}
                    $query = $this->db->get($table);
                    if (0 === $query->num_rows())
                        {
                            $this->db->insert($table,$insert);
                            //echo $table.'-->'.print_r($insert,true);
                        }
                    else {
                        foreach($keylist as $key){$this->db->where($key,$insert[$key]);}
                        $this->db->update($table,$insert);
                        //echo $table.'-->'.print_r($insert,true);
                    }
                }
                //if (sizeof($insert) > 3)
                    //echo $table.'-->'.print_r($insert,true);
                    //$this->db->replace($table,$insert);
            }
        }
        
}
