<?php
class Cuppon_model extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->database();
    }

    public function json_data($params)
    {
		
        $this->db->select("Id,name,des,amount,date_format(startDate,'%Y-%m-%d') as startDate,date_format(endDate,'%Y-%m-%d') as endDate,
        if(now()>startDate&&now()<endDate,1,0) as status");
        $this->db->where('IS_DISABLED',0);
        $this->db->where('userID',$params['userId']);
        $this->db->order_by('status desc');
        $query = $this->db->get('nh_cuppon');
        $result = $query->result_array();
        return $result;
    }
    
    public function json_adddata($params) {
        $this->db->where('IS_DISABLED',0);
        $this->db->where('password',$params['password']);
        $query = $this->db->get('nh_cuppon');
        if ($query->num_rows() === 0){
            $result = array();
            $result['code'] = 1001;
            $result['msg'] ='无此代金券';
            return $result;
        }
        $row =$query->row_array();
        if ($row['status'] === '已分配'){
            if ($row['userId'] === $params['userId']){
                $result = array();
                $result['code'] = 1002;
                $result['msg'] ='你已登记该优惠券';
                return $result;
            }
            else {
                $result = array();
                $result['code'] = 1002;
                $result['msg'] ='该优惠券已经被其他人登记';
                return $result;
            }
        }
        if ($row['status'] === '已使用'){
            if ($row['userId'] === $params['userId']){
                $result = array();
                $result['code'] = 1002;
                $result['msg'] ='该优惠券已经被你使用';
                return $result;
            }
            else {
                $result = array();
                $result['code'] = 1002;
                $result['msg'] ='该优惠券已经被其他人使用';
                return $result;
            }
        }
        $row = $query->row_array();
        $update = array();
        $update['status'] ='已分配';
        $update['userId'] =$params['userId'];
        $update['modifydatetime'] = Date('Y-m-d H:i:s');
        $update['modifyuser'] = $params['userId'];
        $this->db->where('Id',$row['Id']);
        $query = $this->db->update('nh_cuppon',$update);
        $sql = "UPDATE nh_user SET cupponnum = cupponnum + 1 WHERE Id=".$params['userId'];
        $this->db->query($sql);
        $result = array();
        $result['code'] = 0;
        $result['id'] = $row['Id'];
        $result['amount'] = $row['amount'];
        return $result;
    }

}
