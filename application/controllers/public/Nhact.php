<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Nhact extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->sessioninfo = $this->session->userdata('sessioninfo');
        $this->config->load('cfg-system', true);
        $this->abccall = $this->config->item('cfg-system');
        $this->abccall = $this->abccall['abccall'];
        $this->url_module = $this->uri->segment(1, $this->router->default_controller);
        $this->url_model = $this->uri->segment(2, 'index');
        $this->url_method = $this->uri->segment(3, 'index');
    }

    public function updateusernum()
    {
        // 各状态位重置
        $this->load->database();
        $sql = "UPDATE nh_user SET waitpay=0,waitsend=0,waitreceive=0,waitevaluate=0,aftersale=0,cartnum=0,collectnum=0,cupponnum=0 WHERE IS_DISABLED=0";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_order WHERE IS_DISABLED=0 AND applystatus='未支付' AND aftersalestatus='无售后' AND orderstatus='已生成' GROUP BY userId) no "
            . " SET nu.waitpay=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_order WHERE IS_DISABLED=0 AND applystatus='已支付' AND aftersalestatus='无售后' AND ((sendstatus='待发货')OR(sendstatus='无物流')) GROUP BY userId) no "
            . " SET nu.waitsend=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_order WHERE IS_DISABLED=0 AND applystatus='已支付' AND aftersalestatus='无售后' AND sendstatus='待签收' GROUP BY userId) no "
            . " SET nu.waitreceive=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_order WHERE IS_DISABLED=0 AND (orderstatus='已完成') AND aftersalestatus='无售后' AND (commentstatus='未评价') "
            . "GROUP BY userId) no  SET nu.waitevaluate=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_order WHERE IS_DISABLED=0 AND ((aftersalestatus <>'无售后') && (aftersalestatus <>'已售后')) "
            . "GROUP BY userId) no  SET nu.aftersale=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        // 去掉购物车中没有上线的产品
        $sql = "UPDATE nh_shopcart SET IS_DISABLED=1 WHERE prodId in (SELECT prodId FROM nh_product WHERE `status`=0 OR USE_TYPE='不用' OR TO_TYPE <> '农行内网')";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_shopcart WHERE IS_DISABLED=0 "
            . "GROUP BY userId) no  SET nu.cartnum=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_collect WHERE IS_DISABLED=0 "
            . "GROUP BY userId) no  SET nu.collectnum=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        $sql = "UPDATE nh_user nu,(SELECT userId,count(*) as total FROM nh_cuppon WHERE IS_DISABLED=0 "
            . "GROUP BY userId) no  SET nu.cupponnum=no.total WHERE nu.Id=no.userId";
        $this->db->query($sql);
        // 24小时未支付，订单关闭
        // 先退回库存
        $sql = "UPDATE nh_product_store nps,nh_order_items noi,nh_order nop SET nps.ordernum = nps.ordernum - noi.count "
            . " WHERE nop.orderNo=noi.orderNo AND noi.prodId=nps.prodId AND nop.applystatus='未支付' and nop.orderstatus='已生成' AND nop.createTime < DATE_SUB(CURDATE(),INTERVAL 24 HOUR)";
        $this->db->query($sql);
        // 在修改状态
        $sql = "UPDATE nh_order SET orderstatus='已关闭' WHERE applystatus='未支付' and orderstatus='已生成' AND createTime < DATE_SUB(CURDATE(),INTERVAL 24 HOUR)";
        $this->db->query($sql);
        // 超过15天，没有签收，自动签收
        $sql = "update  nh_order nod,nh_order_expresses noe set nod.sendstatus='已签收',nod.orderstatus='已完成',nod.commentstatus='未评价' "
            . " where nod.orderId = noe.orderId and nod.sendstatus='待签收' and nod.aftersalestatus ='无售后' and noe.`status`='shutdown'  "
            . " and noe.createdatetime < DATE_SUB(CURDATE(),INTERVAL 15 DAY)";
        $this->db->query($sql);
        // 批量替换问题数据
        $sql = "UPDATE nh_product SET content = REPLACE(content,'101.200.144.69','101.200.130.91') ";
        $this->db->query($sql);
        $sql = "UPDATE nh_product SET content = REPLACE(content,'nh-admin.entropytao.com','nonghang.entropytao.com') ";
        $this->db->query($sql);
        // 批量处理待发货超过30天，订单关闭
        $sql = "UPDATE nh_order SET orderstatus='已关闭',sendstatus='退回',aftersalestatus='已退款' WHERE applystatus='已支付' and orderstatus='已生成' and sendstatus='待发货' AND createTime < DATE_SUB(CURDATE(),INTERVAL 30 DAY)";
        $this->db->query($sql);
        echo 'ok';
    }

    public function updateexpress()
    {
        $this->load->database();
        $sql = "SELECT * FROM nh_order_expresses WHERE status is null || (status <> 'shutdown')";
        $query = $this->db->query($sql);
        $express = $query->result_array();
        foreach ($express as $item) {
            include_once APPPATH . 'libraries/phprpc/phprpc_client.php';
            $client = new PHPRPC_Client($this->cfgsystem['pzrpc'] . 'express');
            $result = $client->getExpress($item['no']);
            $update = array();
            $update['modifyuser'] = 'sys';
            if (sizeof($result) === 0) {
                $update['status'] = '';
                $update['modifydatetime'] = Date('Y-m-d H:i:s');
            } else {
                $update['company'] = $result[0]['DELIVERYNAME'];
                $update['status'] = $result[0]['EXPRESSSTATE'];
                $update['modifydatetime'] = $result[0]['UPDATETIME'];
                $update['EXPRESSINFO'] = $result[0]['EXPRESSINFO'];
            }
            $this->db->where('Id', $item['Id']);
            $this->db->update('nh_order_expresses', $update);
        }
        echo 'OK';
    }

    public function process_express_abort($no)
    {
        $this->load->database();
        $sql = "SELECT * FROM nh_order_expresses WHERE no = '" . $no . "'";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $this->load->library('express');
        $express = new Express();
        foreach ($result as $item) {
            $data = $express->getorder($item["company"], $item['no']);
            $update = array();
            if (isset($data['data'])) {
                $update['EXPRESSINFO'] = json_encode($data["data"]);
            }
            $update['status'] = $data['status'];
            $this->db->where('orderId', $item['orderId']);
            $this->db->update('nh_order_expresses', $update);
        }
        echo "OK";
    }

    public function jsversion()
    {
        //get version
        $this->_getversion();
        //get update js file list
        $this->_getfilelist();
        //遍历html
        $dir = APPPATH . "../html/";
        log_message("info", "open dir:" . $dir);
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..' || strpos($file, '.') === -1) {
                    continue;
                }
                $tmp = explode(".", $file);
                if ('html' != $tmp[count($tmp) - 1]) {
                    continue;
                }
                $this->_setFileVersion($dir . $file);
            }
        }

    }

    private function _setFileVersion($file)
    {
        log_message("info", "start set version--->file:" . $file);
        //get content
        $content = file_get_contents($file);
        $content = preg_replace_callback("/<script.*?><\/script>/", function ($matches) {
            return $this->_pregCallback($matches[0]);
        }, $content);
        //set content
        file_put_contents($file, $content);
        log_message("info", "set version success--->file:" . $file);
    }

    private function _pregCallback($str)
    {
        //get update js file list
        $replacelist = $this->filelist;
        $version = $this->version;
        $version_arr = explode("=", $version);
        $version_name = $version_arr[0];
        $version_num = $version_arr[1];
        foreach ($replacelist as $jsfile) {
            log_message("info", "preg callback---start replace---js file:" . $jsfile);
            $pattern = '/(<script.*?src=\'.*)(.' . $jsfile . '.*)(\'><\/script>)/';
            if (!preg_match($pattern, $str, $third)) {
                log_message("info", "preg callback---replace not found---pattern:" . $pattern);
                continue;
            }
            log_message("info", "preg callback---replace---result:" . print_r($third, true));
            $arr = explode('?', $third[2]);
            $len = count($arr);
            if (1 == $len) {
                //没有问号
                $arr[0] .= "?" . $version;
            } else {
                if (preg_match('/(^|\&)' . $version_name . '=(.*?)($|\&)/', $arr[1])) {
                    //version已经存在
                    $arr[1] = preg_replace('/(^|\&)' . $version_name . '=(.*?)($|\&)/', '$1' . $version . '$3', $arr[1]);
                    $arr[0] .= '?' . $arr[1];
                } else {
                    //version不存在
                    $arr[0] .= '?' . $arr[1] . '&' . $version;
                }
            }
            $str = $third[1] . $arr[0] . $third[3];
            log_message("info", "preg callback---end replace---js file:" . $jsfile);
        }
        return $str;
    }

    private function _getversion()
    {
        $this->load->database();
        $sql = "select * from sys_dictdata where type='字典类型' and name='版本控制'";
        $query = $this->db->query($sql);
        $version = "version=1.0";
        if (0 == $query->num_rows()) {
            $this->load->helper('guid_helper');
            $insert = array();
            $insert['type'] = "字典类型";
            $insert['name'] = "版本控制";
            $insert['seqno'] = "BBKZ";
            $insert['seq'] = "1000";
            $insert['value'] = $version;
            $insert['id'] = guid();
            $this->db->insert('sys_dictdata', $insert);
        } else {
            $query = $query->row_array();
            $version = $query['value'];
        }
        log_message("info", "get js version:" . $version);
        $this->version = $version;
    }

    private function _getfilelist()
    {
        $this->load->database();
        $sql = "select * from sys_dictdata where type='字典类型' and name='版本控制文件'";
        $query = $this->db->query($sql);
        $filelist = "js";
        if (0 == $query->num_rows()) {
            $this->load->helper('guid_helper');
            $insert = array();
            $insert['type'] = "字典类型";
            $insert['name'] = "版本控制文件";
            $insert['seqno'] = "BBKZWJ";
            $insert['seq'] = "1001";
            $insert['value'] = $filelist;
            $insert['id'] = guid();
            $this->db->insert('sys_dictdata', $insert);
        } else {
            $query = $query->row_array();
            $filelist = $query['value'];
        }
        $filelist = explode(",", $filelist);
        log_message("info", "get js file list:" . print_r($filelist, true));
        $this->filelist = $filelist;
    }


}
