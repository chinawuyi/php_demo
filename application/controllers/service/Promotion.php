<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据促销规则，对订单对象进行促销处理
     * @param mixed $orderdata <p>订单对象</p>
     * @param mixed $rules <p>促销规则</p>
     * @param mixed $obj [option] <p>
     * 传送的数据对象
     * @return mixed 返回订单对象
     * </p>
     * @throws 直接发出自定义错误，编号0
     */
    public function promotion($orderdata) {
        $sql = "SELECT * FROM nh_promotion_act WHERE IS_DISABLED=0 order by seq desc";
        $query = $this->db->query($sql);
        $rules = $query->result_array();
        $ordersave = $orderdata;
        $result = array();
        $fieldjudge = array();
        $fieldjudge['数量'] = "orderNum";
        $fieldjudge['单价'] = "orderPrice";
        $fieldjudge['金额'] = "orderAmount";
        $fieldjudge['税金'] = "orderTax";
        $fieldjudge['运费'] = "orderPost";
        $fieldjudge['积分'] = "orderPoint";
        $orderdata = self::_calcitem($rules, $orderdata, '数量', $fieldjudge);                // 对产品明细进行促销计算，数量计算
        $orderdata = self::_calcitem($rules, $orderdata, '单价', $fieldjudge);                // 对产品明细进行促销计算，单价计算
        $orderdata = self::_recalcitem($orderdata, $fieldjudge);                              // 根据促销结果，重新计算税金 
        $orderdata = self::_calcitem($rules, $orderdata, '金额', $fieldjudge);                // 对产品明细进行促销 计算，金额计算
        $orderdata = self::_calcitem($rules, $orderdata, '税金', $fieldjudge);                // 对产品明细进行促销 计算，税金计算
        $orderdata = self::_calcitem($rules, $orderdata, '运费', $fieldjudge);                // 对产品明细进行促销 计算，运费计算
        $fieldtotal = array();
        $fieldtotal['订单总金额'] = "amount";
        $fieldtotal['订单总税金'] = "tax";
        $fieldtotal['订单总运费'] = "freight";
        $fieldtotal['订单总积分'] = "point";
        $fieldtotal['订单代金券'] = "preference";
        $orderdata = self::_recalcall($orderdata, $fieldtotal, $fieldjudge);                              // 根据明细，汇总各种总金额
        $orderdata = self::_calcall($rules, $orderdata, $fieldtotal);                              // 根据促销规则，计算汇总金额的促销规则 
        return $orderdata;
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
    private function _throwerror($msg, $code = '9001', $obj = array()) {
        $data = array();
        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['obj'] = $obj;
        $msg = json_encode($data);
        throw new Exception($msg, 0, null);
    }

    // 提供对各种订单明细的促销处理，需要先数量，后金额
    private function _calcitem($rules, $orderdata, $type, $fieldjudge) {
        foreach ($rules as $key => $item) {
            if ($item['type'] !== '单品') {
                continue;
            }
            if ($type !== $item['targettype']) {
                continue;
            }
            foreach ($orderdata["products"] as $key1 => $item1) {
                // 本商品，或者所有商品有效的规则
                if (($item['products'] != '-1') && ($item['products'] != $item1['prodId'])) {
                    continue;
                }
                $data = $item1[$fieldjudge[$item['targettype']]];
                if (self::_judgrange($data, $item['judgment'], $item['range'])) {
                    $data = $item1[$fieldjudge[$item['calcfield']]];
                    $orderdata["products"][$key1][$fieldjudge[$item['target']]] = self::_calcitemone($data, $item['calc'], $item['value']);
                }
            }
        }
        return $orderdata;
    }

    // 根据提供值，进行计算
    private function _calcitemone($value, $calc, $definevalue) {
        if ($calc === '=') {
            $value = $definevalue;
        }
        if ($calc === '+') {
            $value = $value + $definevalue;
        }  // 加固定值
        if ($calc === '-') {
            $value = $value - $definevalue;
        }   // 减固定值
        if ($calc === '*') {
            $value = $value * $definevalue;
        }   // 乘是原数据乘以系数
        if ($calc === '/') {
            $value = $value / $definevalue;
        }   // 除是原数据除以系数
        return $value;
    }

    // 根据促销规则计算后的商品价格，重新根据税率，计算税金
    private function _recalcall($orderdata, $fieldtotal, $fieldjudge) {
        $orderdata[$fieldtotal['订单总金额']] = 0;
        $orderdata[$fieldtotal['订单总税金']] = 0;
        $orderdata[$fieldtotal['订单总运费']] = 0;
        $orderdata[$fieldtotal['订单总积分']] = 0;
        $orderdata[$fieldtotal['订单代金券']] = 0;
        foreach ($orderdata["products"] as $key => $item) {
            $orderdata[$fieldtotal['订单总金额']] += $item[$fieldjudge['金额']];
            $orderdata[$fieldtotal['订单总税金']] += $item[$fieldjudge['税金']];
            $orderdata[$fieldtotal['订单总积分']] += $item[$fieldjudge['积分']];
            $orderdata[$fieldtotal['订单总运费']] += $item[$fieldjudge['运费']];
        }
        return $orderdata;
    }

    // 根据促销规则计算后的商品价格，重新根据税率，计算税金
    private function _recalcitem($orderdata, $fieldjudge) {
        foreach ($orderdata["products"] as $key => $item) {
            $orderdata["products"][$key][$fieldjudge['金额']] = $item[$fieldjudge['数量']] * $item[$fieldjudge['单价']];
            $orderdata["products"][$key][$fieldjudge['税金']] = $orderdata["products"][$key][$fieldjudge['金额']] * $item['ordertaxrate'];
            $orderdata["products"][$key][$fieldjudge['运费']] = 0;
            $orderdata["products"][$key][$fieldjudge['积分']] = 0;
        }
        return $orderdata;
    }

    // 提供对各种订单明细的促销处理，需要先数量，后金额
    private function _calcall($rules, $orderdata, $fieldtotal) {
        foreach ($rules as $key => $item) {
            if ($item['type'] !== '总计') {
                continue;
            }
            $data = $orderdata[$fieldtotal[$item['targettype']]];
            if (self::_judgrange($data, $item['judgment'], $item['range'])) {
                $data = $orderdata[$fieldtotal[$item['calcfield']]];
                $orderdata[$fieldtotal[$item['target']]] = self::_calcitemone($data, $item['calc'], $item['value']);
            }
        }
        return $orderdata;
    }

    // 判断当前记录是否符合规则范围
    private function _judgrange($orderdata, $judgment, $rang) {
        if (($judgment === '=') && ($orderdata === $rang)) {
            return true;
        }
        if (($judgment === '>') && ($orderdata > $rang)) {
            return true;
        }
        if (($judgment === '<') && ($orderdata < $rang)) {
            return true;
        }
        if (($judgment === '>=') && ($orderdata >= $rang)) {
            return true;
        }
        if (($judgment === '<=') && ($orderdata <= $rang)) {
            return true;
        }
        if (($judgment === '<>') && ($orderdata <> $rang)) {
            return true;
        }
        return false;
    }

}

include_once APPPATH . 'libraries/phprpc/phprpc_server.php';

$server = new PHPRPC_Server();
$server->add('promotion', new Promotion()); //添加允许远程访问的方法  
$server->start(); //开始