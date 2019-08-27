<?php

class Order_model extends CI_Model {

    private $userid;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function ordercancel($params) {
        $sql = "UPDATE nh_order SET orderstatus='已取消', modifydatetime=now(),modifyuser='sys' WHERE Id=" . $params['orderId'];
        $this->db->query($sql);
        $sql = "UPDATE nh_product_store nps,nh_order_items noi,nh_order nop SET nps.ordernum = nps.ordernum - noi.count "
                . " WHERE nop.orderNo=noi.orderNo AND noi.prodId=nps.prodId AND nop.Id=" . $params['orderId'];
        $this->db->query($sql);
        $sql = "UPDATE nh_user SET waitpay=waitpay-1 WHERE Id=" . $params['userId'];
        return $this->db->query($sql);
    }

    public function orderreceive($params) {
        $sql = "UPDATE nh_order SET sendstatus='已签收',orderstatus='已完成',commentstatus='未评价', modifydatetime=now(), "
                . " modifyuser='" . $params['userId'] . "' WHERE Id=" . $params['orderId'];
        $this->db->query($sql);
        $sql = "UPDATE nh_user SET waitreceive=waitreceive-1,waitevaluate=waitevaluate+1 WHERE Id=" . $params['userId'];
        return $this->db->query($sql);
    }

    public function orderlist($params) {
        $this->db->select('Id as orderId,orderNo,amount,createTime,applystatus,orderstatus,sendstatus,commentstatus,aftersalestatus ');
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('userId', $params['userId']);
        if (isset($params['status']) && ($params['status'] !== "")) {
            if ($params['status'] === '待付款') {
                $this->db->where('applystatus', '未支付');
                $this->db->where('orderstatus', '已生成');
                $this->db->where('aftersalestatus', '无售后');
            }
            if ($params['status'] === '待发货') {
                $where = " applystatus = '已支付' AND ((sendstatus = '无物流') OR (sendstatus = '待发货')) AND aftersalestatus='无售后' ";
                $this->db->where($where);
            }
            if ($params['status'] === '待收货') {
                $this->db->where('sendstatus', '待签收');
                $this->db->where('aftersalestatus', '无售后');
            }
            if ($params['status'] === '待评价') {
                $this->db->where('commentstatus', '未评价');
                $this->db->where('aftersalestatus', '无售后');
            }
            if ($params['status'] === '已评价') {
                $this->db->where('commentstatus', '已评价');
                $this->db->where('aftersalestatus', '无售后');
            }
            if ($params['status'] === '售后') {
                $where = "(aftersalestatus <>'无售后') && (aftersalestatus <>'已售后')";
                $this->db->where($where);
            }
        }
        $this->db->order_by('createTime desc');
        $query = $this->db->get('nh_order');
        $result = $query->result_array();
        foreach ($result as $key => $item) {
            $sql = "SELECT noi.prodName as name,noi.count as num,round(noi.price/100,2) as price,round(noi.tax/100,2) as tax,noi.image as img "
                    . " FROM nh_order_items noi "
                    . "WHERE noi.IS_DISABLED=0 AND noi.orderNo='" . $item['orderNo'] . "'";
            $query = $this->db->query($sql);
            $rr = $query->result_array();
            $result[$key]['products'] = $rr;
            $result[$key]['amount'] = round($result[$key]['amount'] / 100, 2);
            $result[$key]['status'] = self::statuschg($item);
        }
        return $result;
    }

    private function statuschg($params) {
        if ($params['aftersalestatus'] !== '无售后') {
            return $params['aftersalestatus'];
        }
        if ($params['orderstatus'] === '已取消') {
            return '已取消';
        }
        if ($params['orderstatus'] === '已关闭') {
            return '已关闭';
        }
        if ($params['applystatus'] === '未支付') {
            return '待付款';
        }
        if ($params['sendstatus'] === '待发货') {
            return '待发货';
        }
        if ($params['sendstatus'] === '无物流') {
            return '待发货';
        }
        if ($params['sendstatus'] === '待签收') {
            return '待收货';
        }
        if ($params['commentstatus'] === '未评价') {
            return '待评价';
        }
        if ($params['commentstatus'] === '已评价') {
            return '已评价';
        }
        return '已完成';
    }

    public function orderdetail($params) {
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('Id', $params['orderId']);
        $query = $this->db->get('nh_order');
        $result = array();
        if ($query->num_rows() === 0) {
            $result['order'] = array();
            return $result;
        } else {
            // 订单记录
            $result['order'] = $query->row_array();
            $result['order']['status'] = self::statuschg($result['order']);
            $result['order']['amount'] = round($result['order']['amount'] / 100, 2);
            $result['order']['postFee'] = round($result['order']['postFee'] / 100, 2);
            $result['order']['TAXFEE'] = round($result['order']['TAXFEE'] / 100, 2);
            $orderNo = $result['order']['orderNo'];
            $orderId = $result['order']['orderId'];
            // 优惠券
            $this->db->where('orderNo', $orderNo);
            $this->db->where('IS_DISABLED', 0);
            $query = $this->db->get('nh_order_cuppon');
            $result['cuppons'] = $query->result_array();
            // 物流信息
            $this->db->where('orderId', $orderId);
            $this->db->where('IS_DISABLED', 0);
            $query = $this->db->get('nh_order_expresses');
            $result['expresses'] = array();
            if($query->num_rows()>0) {
                $items = array();
                $query = $query->row_array();
                $express = $query['EXPRESSINFO'];
                $express = json_decode($express,true);
                foreach($express as $exp) {
                    $items[] = array("t"=>$exp['time'],"context"=>$exp['context']);
                }
                $result['expresses'][0] = array("items"=>$items,"company"=>$query["company"],"orderId"=>$query["orderId"],"no"=>$query["no"]);
            }
            // 支付信息
            $this->db->select('orderNo,finNo,Amount,paydate');
            $this->db->where('orderNo', $orderNo);
            $this->db->where('status', 1);
            $query = $this->db->get('nh_order_pay');
            $result['pays'] = $query->result_array();
            // 商品信息
            $sql = "SELECT noi.*,noi.image as listimage FROM nh_order_items noi WHERE noi.orderNo='" . $orderNo . "' AND noi.IS_DISABLED=0 ";
            $query = $this->db->query($sql);
            $result['items'] = $query->result_array();
            foreach ($result['items'] as $key => $item) {
                $result['items'][$key]['amount'] = round($result['items'][$key]['amount'] / 100, 2);
                $result['items'][$key]['price'] = round($result['items'][$key]['price'] / 100, 2);
                $result['items'][$key]['tax'] = round($result['items'][$key]['tax'] / 100, 2);
            }
            // 收货信息
            $this->db->where('orderNo', $orderNo);
            $this->db->where('IS_DISABLED', 0);
            $query = $this->db->get('nh_order_receiver');
            $result['receiver'] = $query->row_array();
            return $result;
        }
    }
}
