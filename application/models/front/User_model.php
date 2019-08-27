<?php

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function detaildata($params) {
        $this->db->select('userName,userAccount,gender,mobile,userImg');
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('Id', $params['userId']);
        $query = $this->db->get('nh_user');
        $result = $query->row_array();
        if(null==$result["userName"]||""==$result["userName"])
            $result["userName"] = $result["userAccount"];
        log_message('debug', 'usermode->info:' . print_r($result, true));
        return $result;
    }

    public function mydata($params) {
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('Id', $params['userId']);
        $query = $this->db->get('nh_user');
        $data = $query->row_array();
        if(null==$data["userName"]||""==$data["userName"])
            $data["userName"] = $data["userAccount"];
        $data['waitPayNums'] = $data['waitpay'];
        $data['waitSendNums'] = $data['waitsend'];
        $data['waitAcceptNums'] = $data['waitreceive'];
        $data['waitComNums'] = $data['waitevaluate'];
        $data['afterSaleNums'] = $data['aftersale'];
        $data['cartNums'] = $data['cartnum'];
        $data['favoriteNums'] = $data['collectnum'];
        $data['preferenceNums'] = $data['cupponnum'];
        $data['point'] = $data['point'];
        log_message('debug', 'usermode->info:' . print_r($data, true));
        return $data;
    }

    public function commentdata($params) {
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('orderNo', $params['orderNo']);
        $this->db->where('userId', $params['userId']);
        $query = $this->db->get('nh_product_comment');
        $data = $query->result_array();
        foreach ($data as $key => $item) {
            $sql = "SELECT image as listimage,prodName as name,round(price/100,2) as price "
                    . " FROM nh_order_items WHERE orderNo='" . $params['orderNo'] . "' AND prodId='" . $item['prodId'] . "'";
            $query = $this->db->query($sql);
            $data[$key]['products'] = $query->result_array();
            $this->db->where('IS_DISABLED', 0);
            $this->db->where('commentId', $item['Id']);
            $query = $this->db->get('nh_product_comment_pic');
            $data[$key]['comment_pics'] = $query->result_array();
            $this->db->where('IS_DISABLED', 0);
            $this->db->where('commentId', $item['Id']);
            $query = $this->db->get('nh_product_comment_reply');
            $data[$key]['comment_reply'] = $query->result_array();
        }
        return $data;
    }

    public function signin($params){
        if('获取'==$params['action']){
            $this->db->where('IS_DISABLED', 0);
            $this->db->where('userId', $params['userId']);
            $this->db->where('signDate', date("Y-m-d"));
            $this->db->where('type', '每日签到');
            $point_query = $this->db->get('nh_user_point');
            $result = array();
            $this->db->where('type', '签到周期');
            $query = $this->db->get('sys_dictdata');
            $rules = $query->row_array();
            $points = explode(',',$rules['value']);
            $daynum = $rules['name'];

            $this->db->where('IS_DISABLED', 0);
            $this->db->where('Id', $params['userId']);
            $query = $this->db->get('nh_user');
            $user = $query->row_array();


            if(0==$point_query->num_rows()){
                $this->db->where('IS_DISABLED', 0);
                $this->db->where('userId', $params['userId']);
                $this->db->where('signDate', date("Y-m-d",strtotime('-1 day')));
                $this->db->where('type', '每日签到');
                $next_point_query = $this->db->get('nh_user_point');
                $result['nextpoint'] = 0;
                if(0==$next_point_query->num_rows())
                    $user['SIGNDAYS'] = 0;
                if($user['SIGNDAYS']<$daynum){
                    $result['point'] = $points[$user['SIGNDAYS']];
                } else {
                    $result['point'] = $points[0];
                }
                $result['isLogin'] = false;
                return $result;
            }
            else{
                $result['point'] = 0;
                //$user['SIGNDAYS'] = $user['SIGNDAYS'] + 1;
                if($user['SIGNDAYS']<$daynum){
                    $result['nextpoint'] = $points[$user['SIGNDAYS']];
                } else {
                    $result['nextpoint'] = $points[0];
                }
                $result['isLogin'] = true;
                return $result;
            }

        } else {
            $this->db->where('IS_DISABLED', 0);
            $this->db->where('userId', $params['userId']);
            $this->db->where('signDate', date("Y-m-d"));
            $this->db->where('type', '每日签到');
            $query = $this->db->get('nh_user_point');
            if(0!=$query->num_rows()){
                $result['code'] = 1002;
                $result['msg'] ='您今天已经签到！';
                return $result;
            }
            $signday = true;
            $this->db->where('IS_DISABLED', 0);
            $this->db->where('userId', $params['userId']);
            $this->db->where('signDate', date("Y-m-d",strtotime('-1 day')));
            $this->db->where('type', '每日签到');
            $query = $this->db->get('nh_user_point');
            if(0==$query->num_rows()){
                $signday = false;
            }
            //获取积分
            $point = 0;
            $nextpoint = 0;
            $days = 1;
            $this->db->where('type', '签到周期');
            $query = $this->db->get('sys_dictdata');
            if(0!=$query->num_rows()){
                $rules = $query->row_array();
                $this->db->where('IS_DISABLED', 0);
                $this->db->where('Id', $params['userId']);
                $query = $this->db->get('nh_user');
                $user = $query->row_array();
                $points = explode(',',$rules['value']);
                $daynum = $rules['name'];
                if(!$signday)
                    $user['SIGNDAYS'] = 0;
                $nextday = $user['SIGNDAYS'] + 1;
                if($user['SIGNDAYS']<$daynum){
                    $point = $points[$user['SIGNDAYS']];
                    $days = $user['SIGNDAYS'] = $user['SIGNDAYS'] + 1;
                } else {
                    $point = $points[0];
                    $days = $user['SIGNDAYS'] = 1;
                }
                if($nextday<$daynum){
                    $nextpoint = $points[$user['SIGNDAYS']];
                } else {
                    $nextpoint = $points[0];
                }
                $update = array();
                $update['SIGNDAYS'] = $user['SIGNDAYS'];
                $update['point'] = $user['point'] + $point;
                //更新user表
                $this->db->update('nh_user',$update);
            } else {
                $result['code'] = 1003;
                $result['msg'] ='暂无签到活动！';
                return $result;
            }

            $insert = array();
            $time = time();
            $insert['userId'] = $params['userId'];
            $insert['type'] = '每日签到';
            $insert['point'] = $point;
            $insert['createdatetime'] = $insert['operateTime'] = date("Y-m-d H:i:s",$time);
            $insert['signDate'] = date("Y-m-d",$time);
            $insert['desc'] = '您于'.date("Y年m月d日H时i分",$time).'成功连续签到'.$days.'天，获得'.$point.'积分。';
            $insert['createuser'] = 'sys';
            $this->db->insert('nh_user_point', $insert);
            $result['code'] = 1001;
            $result['msg'] ='签到成功！';
            $result['point'] = $point;
            $result['nextpoint'] = $nextpoint;
            $result['isLogin'] = true;
            return $result;

        }
    }

    public function pointlist($params){
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('userId', $params['userId']);
        $this->db->order_by('createdatetime','DESC');
        $query = $this->db->get('nh_user_point');
        return $query->result_array();
    }

    public function userpoint($params){
        $this->db->where('IS_DISABLED', 0);
        $this->db->where('Id', $params['userId']);
        $query = $this->db->get('nh_user');
        return $query->row_array();
    }

}
