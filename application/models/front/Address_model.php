<?php
class Address_model extends CI_Model {

    public function __construct()
    {
            parent::__construct();
            $this->load->database();
    }

    public function json_data($params)
    {
       $sql = "SELECT Id as id,username as name,Mobile as mobile,address, province,city,zone as district, street, CERTNO,zip,`default` "
                . "FROM nh_user_address WHERE IS_DISABLED=0 AND userId = ".$params['userId']." ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    
    public function json_adddata($params) {
        $insert = array();
        $insert['userId'] = $params['userId'];
        $insert['userName'] = $params['name'];
        $insert['address'] = str_replace("—","-",$params['address']);
        $insert['Mobile'] = $params['mobile'];
        $insert['zone'] = $params['district'];
        $insert['province'] = $params['province'];
        $insert['city'] = $params['city'];
        $insert['street'] = $params['street'];
        $insert['regionId'] = $params['regionId'];
        $insert['zip'] = $params['zip'];
        $insert['CERTNO'] = $params['CERTNO'];
        $query = $this->db->get_where('nh_user_address',array("userId"=>$params['userId'],"IS_DISABLED"=>0));
        if ($query->num_rows() === 0){$insert['default'] = 1;}
        else {$insert['default'] = 0;}
        $insert['IS_DISABLED'] = 0;
        $insert['createdatetime'] = Date('Y-m-d H:i:s');
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['createuser'] = $params['userId'];
        $insert['modifyuser'] = $params['userId'];
        $query = $this->db->insert('nh_user_address',$insert);
        $result = array();
        $result['id'] = $this->db->insert_id();//wuyi
        $result['name'] = $insert['userName']; //wuyi
        $result['address'] = $insert['address'];
        return $result;
    }
    
    public function json_modifydata($params) {
        $insert = array();
        $insert['userId'] = $params['userId'];
        $insert['userName'] = $params['name'];
        $insert['address'] = str_replace("—","-",$params['address']);
        $insert['Mobile'] = $params['mobile'];
        $insert['zone'] = $params['district'];
        $insert['province'] = $params['province'];
        $insert['city'] = $params['city'];
        $insert['street'] = $params['street'];
        $insert['regionId'] = $params['regionId'];
        $insert['zip'] = $params['zip'];
        $insert['CERTNO'] = $params['CERTNO'];
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['modifyuser'] = $params['userId'];
        $this->db->where('Id',$params['id']);
        $query = $this->db->update('nh_user_address',$insert);
        return 0;
    }
    
    public function json_defaultdata($params) {
        $this->db->query('UPDATE nh_user_address SET `default` = 0 WHERE userId='.$params['userId']);
        $this->db->query('UPDATE nh_user_address SET `default` = 1 WHERE Id='.$params['id']);
        return 0;
    }
    
    public function json_detaildata($params) {
        $this->db->where('IS_DISABLED',0);
        $this->db->where('Id',$params['id']);
        $query = $this->db->get('nh_user_address');
        $data = $query->row_array();
        $this->db->where('id',$data['regionId']);
        $query = $this->db->get('nh_provincecityjson');
        $result = $query->row_array();
        $data['path'] = $result['path'];
        return $data;
    }

    public function json_deldata($params)//wuyi 
    {
        $data = array();
        $data['IS_DISABLED'] = 1;
        $this->db->where(array('Id'=>$params['id']));
        $this->db->update('nh_user_address',$data);
        return 0;
    }
}
