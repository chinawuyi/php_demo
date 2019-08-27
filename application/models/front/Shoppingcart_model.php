<?php
class Shoppingcart_model extends CI_Model {

    private $userid;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
    }

    public function json_data($params)
    {
       $sql = "SELECT ns.Id as rowId,ns.name,round(ns.price/100,2) as price,ns.thumbnail as img,ns.num,ns.prodId,np.OVERSEAFLAG "
                . "FROM nh_shopcart ns,nh_product np "
                . "WHERE np.status = 1 AND np.USE_TYPE = '启用' AND np.IS_DISABLED=0 "
               . " AND ns.IS_DISABLED=0 AND np.prodId=ns.prodId AND ns.userId = ".$params['userId']." ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        foreach($result as $key => $item){
            $sql = "SELECT name,value FROM nh_product_specs WHERE prodId=".$item['prodId'];
            $query = $this->db->query($sql);
            $data = $query->result_array();
            $result[$key]['spces'] = $data;
        }
        return $result;
    }
    
    public function json_adddata($params) {
        $prodId = $params['prodId'];
        $query  = $this->db->get_where('nh_shopcart',array('prodId' => $prodId ,'userId' => $params['userId'],'IS_DISABLED' => 0));
        if ($query->num_rows() === 0) {
            $sql = "SELECT * FROM nh_product WHERE prodId=".$prodId." AND IS_DISABLED=0";
            $query = $this->db->query($sql);
            $data = $query->row_array();
            $insert = array();
            $insert['prodId'] = $prodId;
            $insert['userId'] = $params['userId'];
            $insert['name'] = $data['name'];
            $insert['price'] = $data['salePrice'];
            if ($data['status'] === 1){$insert['price'] = $data['PROMOTION_PRICE'];}
            $insert['num'] = $params['num'];
            $insert['thumbnail'] = $data['listimage'];
            $insert['IS_DISABLED'] = 0;
            $insert['createdatetime'] = Date('Y-m-d H:i:s');
            $insert['modifydatetime'] = Date('Y-m-d H:i:s');
            $insert['createuser'] = $params['userId'];
            $insert['modifyuser'] = $params['userId'];
            $query = $this->db->insert('nh_shopcart',$insert);
            $id = $this->db->insert_id();
            // 更新缓冲的购物车数量
            $sql = "UPDATE nh_user SET cartnum=cartnum + 1 WHERE Id=".$params['userId'];
            $this->db->query($sql);
            $key = "SHOPCART-USER-NUM-".$params['userId'];
            $num = $this->cache->get($key);
            if ($num === false){ 
                $num = self::getcount($params);
            }
            else {
                $num = $num + 1;
            }
            $this->cache->save($key,$num,24*60*60);
            // 返回购物车id和数量
            $sql = "SELECT cartnum FROM nh_user WHERE Id=".$params['userId'];
            $query = $this->db->query($sql);
            $data = $query->row_array();
            $result = array();
            $result['id'] = $id;
            $result['cartnum'] = $data['cartnum'];
            return $result;
        }
        else {      
            $rowId = $query->row_array();
            $rowId = $rowId['Id'];
            $sql = "UPDATE nh_shopcart SET num = num + ".$params['num'].", modifydatetime=now(),modifyuser='".$params['userId']."'"
                    . " WHERE userId=".$params['userId']." AND prodId=".$prodId;
            $this->db->query($sql);
            $sql = "SELECT cartnum FROM nh_user WHERE Id=".$params['userId'];
            $query = $this->db->query($sql);
            $data = $query->row_array();
            $result = array();
            $result['id'] = $rowId;
            $result['cartnum'] = $data['cartnum'];
            return $result;
        }
    }

    public function json_adddatabatch($params) {
        foreach($params["products"] as $product) {
            $prodId = $product['prodId'];
            $query  = $this->db->get_where('nh_shopcart',array('prodId' => $prodId ,'userId' => $params['userId'],'IS_DISABLED' => 0));
            if ($query->num_rows() === 0) {
                $sql = "SELECT * FROM nh_product WHERE prodId=".$prodId." AND IS_DISABLED=0";
                $query = $this->db->query($sql);
                $data = $query->row_array();
                $insert = array();
                $insert['prodId'] = $prodId;
                $insert['userId'] = $params['userId'];
                $insert['name'] = $data['name'];
                $insert['price'] = $data['salePrice'];
                if ($data['status'] === 1){$insert['price'] = $data['PROMOTION_PRICE'];}
                $insert['num'] = $product['num'];
                $insert['thumbnail'] = $data['listimage'];
                $insert['IS_DISABLED'] = 0;
                $insert['createdatetime'] = Date('Y-m-d H:i:s');
                $insert['modifydatetime'] = Date('Y-m-d H:i:s');
                $insert['createuser'] = $params['userId'];
                $insert['modifyuser'] = $params['userId'];
                $query = $this->db->insert('nh_shopcart',$insert);
                $id = $this->db->insert_id();
                // 更新缓冲的购物车数量
                $sql = "UPDATE nh_user SET cartnum=cartnum + 1 WHERE Id=".$params['userId'];
                $this->db->query($sql);
                $key = "SHOPCART-USER-NUM-".$params['userId'];
                $num = $this->cache->get($key);
                if ($num === false){
                    $num = self::getcount($params);
                }
                else {
                    $num = $num + 1;
                }
                $this->cache->save($key,$num,24*60*60);

            }
            else {
                $rowId = $query->row_array();
                $rowId = $rowId['Id'];
                $sql = "UPDATE nh_shopcart SET num = num + ".$product['num'].", modifydatetime=now(),modifyuser='".$params['userId']."'"
                    . " WHERE userId=".$product['userId']." AND prodId=".$prodId;
                $this->db->query($sql);
                $sql = "SELECT cartnum FROM nh_user WHERE Id=".$params['userId'];
                $query = $this->db->query($sql);
                $data = $query->row_array();
                $result = array();
                $result['id'] = $rowId;
                $result['cartnum'] = $data['cartnum'];
                return $result;
            }
        }
        // 返回购物车id和数量
        $sql = "SELECT cartnum FROM nh_user WHERE Id=".$params['userId'];
        $query = $this->db->query($sql);
        $data = $query->row_array();
        $result = array();
        $result['id'] = $id;
        $result['cartnum'] = $data['cartnum'];
        return $result;

    }
    
    public function json_deldata($params)
    {
        $data = array();
        $data['IS_DISABLED'] = 1;
        $this->db->where('Id',$params['cartId']);
        $this->db->where('userId',$params['userId']);
        $this->db->update('nh_shopcart',$data);
        $sql = "UPDATE nh_user SET cartnum=cartnum - 1 WHERE Id=".$params['userId'];
        $this->db->query($sql);
        $key = "SHOPCART-USER-NUM-".$params['userId'];
        $num = $this->cache->get($key);
        if ($num === false){ 
            $num = self::getcount($params);
        }
        else
        {
            $num = $num - 1;
        }
        $this->cache->save($key,$num,24*60*60);
        return $num;
    }
    
    public function modifynum($params)
    {
        $data = array();
        $data['num'] = $params['nums'];
        $this->db->where('prodId',$params['id']);
        $this->db->where('userId',$params['userId']);
        $this->db->update('nh_shopcart',$data);
        return array('proId'=>$params['id'],'num'=>$data['num']);
    }
    
    public function getcount($params){
        $query = $this->db->query("SELECT count(*) as nums FROM nh_shopcart WHERE IS_DISABLED=0  AND userId=".$params['userId']);
        $data = $query->row_array();
        $num = $data['nums'];
        $key = "SHOPCART-USER-NUM-".$params['userId'];
        $this->cache->save($key,$num,24*60*60);
        return $num;
    }
}
