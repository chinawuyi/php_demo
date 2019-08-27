<?php
class Collect_model extends CI_Model {

    private $userid;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
    }

    public function json_data($params)
    {
       $sql = "SELECT np.name,round(np.salePrice/100,2) as price,np.status,np.listimage as img,np.prodId as id "
                . "FROM nh_collect ns,nh_product np "
                . "WHERE ns.IS_DISABLED=0 AND np.prodId=ns.prodId AND ns.userId = ".$params['userId']." ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    
    public function json_adddata($params) {
        $insert = array();
        $insert['prodId'] = $params['prodId'];
        $insert['userId'] = $params['userId']; //WUYI
        $insert['IS_DISABLED'] = 0;
        $insert['createdatetime'] = Date('Y-m-d H:i:s');
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['createuser'] = $params['userId'];
        $insert['modifyuser'] = $params['userId'];
        $query = $this->db->insert('nh_collect',$insert);
        $insertid = $this->db->insert_id();
        $sql = "UPDATE nh_user SET collectnum=collectnum + 1 WHERE Id=".$params['userId'];
        $this->db->query($sql);
        $key = "COLLECT-PRODUCT-NUM-".$params['prodId'];
        $num = $this->cache->get($key);
        if ($num === false){ $num = self::getcount($params);}
        $num = $num + 1;
        $this->cache->save($key,$num,24*60*60);
        self::getlist($params);
        return $num;
    }
    
    public function json_deldata($params)
    {
        $data = array();
        $data['IS_DISABLED'] = 1;
        $this->db->where('prodId',$params['prodId']);
        $this->db->where('userId',$params['userId']);
        $this->db->update('nh_collect',$data);
        $sql = "UPDATE nh_user SET collectnum=collectnum - 1 WHERE Id=".$params['userId'];
        $this->db->query($sql);
        $key = "COLLECT-PRODUCT-NUM-".$params['prodId'];
        $num = $this->cache->get($key);
        if ($num === false){ $num = self::getcount($params);}
        $num = $num - 1;
        $this->cache->save($key,$num,24*60*60);
        self::getlist($params);
        return $num;
    }
    
    public function getcount($params){
        $query = $this->db->query("SELECT count(*) as nums FROM nh_collect WHERE IS_DISABLED=0  AND prodId=".$params['prodId']);
        $data = $query->row_array();
        $num = $data['nums'];
        $key = "COLLECT-PRODUCT-NUM-".$params['prodId'];
        $this->cache->save($key,$num,24*60*60);
        return $num;
    }
    
    public function getlist($params){
        $query = $this->db->query("SELECT userId FROM nh_collect WHERE IS_DISABLED=0 AND prodId=".$params['prodId']);
        $data = $query->result_array();
        $list = array();
        foreach($data as $item){$list[$item['userId']] = 1;}
        $key = "COLLECT-PRODUCT-LIST-".$params['prodId'];
        $this->cache->save($key,$list,24*60*60);
        return isset($list[$params['userId']]);
    }
}
