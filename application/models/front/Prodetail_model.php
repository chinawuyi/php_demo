<?php
class Prodetail_model extends CI_Model {

    private $userid;

    public function __construct()
    {
            parent::__construct();
            $this->load->database();
    }

    public function json_product($params)
    {
        $add = "";
        $date = strtotime("now");
        if($date>strtotime("2016-08-29 00:00:00")&&$date<strtotime("2016-09-06 00:00:00"))
            $add = "由于2016年9月4日-5日在中国杭州召开G20峰会，为确保“G20峰会”期间各方安全，提供良好的与会环境，消除安全隐患。因此，即日起杭州地区以及安徽黄山地区快递暂停收发。感谢各位会员的支持和谅解。预计快递将于峰会结束后恢复正常。<br/>";
        $sql = "SELECT np.name,np.prodId as id,concat('".$add."',np.content) as des,0 as praises,round(np.price/100,2) as price,np.listimage, "
                . " round(np.salePrice/100,2) as salePrice,round(np.PROMOTION_PRICE/100,2) as promotionprice, "
                . " round(if(np.DISP_TYPE=1,np.PROMOTION_PRICE,np.salePrice)*np.TAX/np.salePrice/100,2) as tax, "
                . " np.DISP_TYPE as status,nps.inventory-nps.ordernum as inventory,nps.inventory as storeNum,nps.ordernum as orderNum, "
                . " if(nps.inventory-nps.ordernum>0,0,1) as outOfStock,np.STARTTIME,np.ENDTIME "
                . " from nh_product np,nh_product_store nps "
                . " WHERE  np.USE_TYPE='启用' AND np.prodId=nps.prodId AND np.IS_DISABLED=0 "
                . " AND np.prodId=".$params['productId'];
        if (!isset($params['test']))
            {$sql .= " AND np.status=1";}
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    
    public function json_skuattr($params){
        $sql = "SELECT nps.*,np.listimage,np.inventory,np.salePrice,np.PROMOTION_PRICE,np.DISP_TYPE FROM nh_product_specs nps,nh_product np "
                . "WHERE nps.IS_DISABLED=0 AND nps.prodId=np.prodId and np.IS_DISABLED=0  "
                . " AND nps.groupId=".$params['productId'];
        $query = $this->db->query($sql);
        $data = $query->result_array();
        $result = array();
        foreach ($data as $item){
            if (!isset($result[$item['prodId']])) {$result[$item['prodId']] = array();}
            $result[$item['prodId']]['prodId'] = $item['prodId'];
            $result[$item['prodId']]['groupId'] = $item['groupId'];
            $result[$item['prodId']]['inventory'] = $item['inventory'];
            if ($item['DISP_TYPE'] == 0){$result[$item['prodId']]['price'] = round($item['salePrice']/100,2);}
            if ($item['DISP_TYPE'] > 0){$result[$item['prodId']]['price'] = round($item['PROMOTION_PRICE']/100,2);}
            $result[$item['prodId']][$item['name']] = $item['value'];
            if ( $item['pic'] !== null) {
                $result[$item['prodId']]['pic'] = $item['pic'];
            }
            else {$result[$item['prodId']]['pic'] = $item['listimage'];}
        }
        return $result;
    }

    public function json_banner($params)
    {
        $sql = " SELECT pictures as img FROM nh_product_pic WHERE prodId =".$params['productId']." AND IS_DISABLED=0";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function json_attrs($params)
    {
        $sql = " SELECT name,content FROM nh_product_attrs WHERE prodId =".$params['productId']." AND IS_DISABLED=0";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function json_related($params)
    {
        $sql = "SELECT npc.content FROM nh_product np,nh_product_controll npc "
                . "WHERE np.USE_TYPE='启用' AND np.IS_DISABLED=0  AND np.prodId=np.groupId "
                . " AND np.prodId=npc.prodId AND npc.`name`='关联产品' AND np.prodId=".$params['productId'];
        if (!isset($params['test']))
            {$sql .= " AND np.status=1";}
        $query = $this->db->query($sql);
        if ($query->num_rows() === 0){
            return array();
        }
        $result = $query->row_array();
        $sql = "SELECT np.prodId as id,np.name as name,np.listimage as img,round(if(DISP_TYPE=1,PROMOTION_PRICE,salePrice)/100,2) as price  "
                . "FROM nh_product np,nh_product_controll npc "
                . "WHERE np.status=1 AND np.USE_TYPE='启用' AND np.IS_DISABLED=0  AND np.prodId=np.groupId "
                . "AND np.prodId=npc.prodId AND npc.`name`='关联产品' AND npc.content='".$result['content']."' "
                . " AND np.prodId<>".$params['productId'];
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function json_comment($params){
        $sql = "SELECT count(*) as num "
                . "FROM nh_product_comment npc,nh_user nu "
                . "WHERE npc.userId=nu.Id AND npc.prodId=".$params['productId'];
        $query = $this->db->query($sql);
        $result = $query->row_array();
        $data = array();
        $data['totalnum'] = $result['num'];
        $sql = "SELECT nu.userName as username, npc.comTime as comtime,npc.content "
                . "FROM nh_product_comment npc,nh_user nu "
                . "WHERE npc.userId=nu.Id AND npc.prodId=".$params['productId']." LIMIT 0,".$params['commentno']." ";
        $query = $this->db->query($sql);
        $data['list'] = $query->result_array();
        return $data;
    }
}
