<?php

class Promotion_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function bannerdata($params)
    {
        $sql = "SELECT name,img,url FROM nh_banner "
            . "WHERE IS_DISABLED=0 AND type='" . $params['type'] . "' AND status=1 AND starttime<now() AND endtime > now() ORDER BY seq desc";
        log_message('debug','bannerdata sql ->'.$sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        log_message('debug','bannerdata data ->'.print_r($result,true));
		foreach($result as $key=>$item) {
            $result[$key]["products"] = $this->_getproductbylabel($item["name"]);
        }
        return $result;
    }
	
	private function _getproductbylabel($label){
        $select = " select np.name,np.prodId as id,np.content as des,np.listimage as img,np.thumbnail,round(np.price/100) as price, "
                . " round(np.salePrice/100) as salePrice,round(np.PROMOTION_PRICE/100) as promotionprice, "
                . " if(np.DISP_TYPE > 0,np.PROMOTION_PRICE,np.salePrice) as indexprice,nps.inventory as storeNum, "
                . " (nps.ordernum+IFNULL(nps.backnum,0)) as orderNum, "
                . " np.DISP_TYPE as status,if((nps.inventory-nps.ordernum) > 0,0,1) as outOfStock,np.STARTTIME,np.ENDTIME";
        $from = " FROM nh_product_store nps,nh_product_controll npcl, nh_product np"
            . " LEFT JOIN (select prodId ,AVG(star) as star from nh_product_comment where IS_DISABLED=0 group by prodId) npc on np.prodId=npc.prodId";
        $where = " WHERE (np.prodId=nps.prodId) AND (np.status =1) AND (np.IS_DISABLED = 0) AND (np.USE_TYPE='启用') AND (np.TO_TYPE='农行内网') AND (np.prodId=np.groupId)";
        $where .= " AND (npcl.prodId=np.prodId) ";
        $where .= " AND (npcl.name='" . $label . "') AND (npcl.IS_DISABLED = 0) ";
        $order = " ORDER BY np.SEQ DESC, np.modifydatetime DESC ";
        $limit = " limit 0,6 ";
        if("秒杀" == $label)
            $limit = " limit 0,4 ";
        $sql = $select.$from.$where.$order.$limit;
        log_message('debug','_getproductbylabel sql ->'.$sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        log_message('debug','_getproductbylabel data ->'.print_r($result,true));
        return $result;
    }

    public function promotiondata($params)
    {
        $sql = "SELECT np.*  "
            . " FROM nh_product_controll npa, nh_product np "
            . " WHERE np.status = 1 AND np.USE_TYPE='启用' AND np.TO_TYPE='农行内网' AND np.IS_DISABLED=0 AND np.prodId=np.groupId AND npa.IS_DISABLED = 0 "
            . " AND np.prodId=npa.prodId AND npa.name='" . $params['label'] . "' "
            . " ORDER BY npa.content";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

    public function homebanner($params)
    {
        $sql = "SELECT * FROM nh_banner WHERE IS_DISABLED=0 AND (type='首页弹屏' OR type='首页弹屏个人') order BY  modifydatetime desc";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        } else {
            return $query->row_array();
        }
    }

    public function homebannerpersonal($params)
    {
        if ($params['action'] === '获取') {
            $sql = "SELECT userId FROM nh_user_controll WHERE type='首页弹屏个人' AND userId=" . $params['userId'];
            $query = $this->db->query($sql);
            if ($query->num_rows() == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $insert = array();
            $insert['type'] = "首页弹屏个人";
            $insert['userId'] = $params['userId'];
            $insert['IS_DISABLED'] = 0;
            $insert['createdatetime'] = date('Y-m-d H:i:s');
            $insert['createuser'] = 'sys';
            $this->db->insert('nh_user_controll', $insert);
            return true;
        }
    }
}
