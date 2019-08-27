<?php

class Proa_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function json_data($params) {
        if (isset($params['pageNo'])) {
            $pageno = $params['pageNo'];
        } else {
            $pageno = 0;
        }
        $recstart = $pageno * $params['perpageno'];
        $perpageno = $params['perpageno'];
        if (!isset($params['dataname'])) {
            $limit = " limit $recstart,$perpageno";
        } else {
            $limit = "";
        }
        $sql = $this->_select($params) . $this->_from($params) . $this->_where($params) . $this->_order($params) . $limit;
        $query = $this->db->query($sql);
        $result = $query->result_array();
        //print_r($result);exit;
        return $result;
    }

    private function _searchToSQL($search) {
        if ($search == "") {
            return "";
        }
        $sql = '';
        $search = str_replace('，', ',', $search);
        $search = str_replace('　', ',', $search);
        $search = str_replace(' ', ',', $search);
        $list = explode(',', $search);
        $ss = " AND (";
        foreach ($list as $item) {
            if ($item <> '') {
                $ss .= " (name like '%" . $item . "%') OR ";
            }
        }
        $ss = substr($ss, 0, -3) . ") ";
        $sql .= $ss;
        return $sql;
    }

    private function _select($params) {
        $select = " select np.name,np.prodId as id,np.content as des,np.listimage as img,np.thumbnail,round(np.price/100) as price, "
                . " round(np.salePrice/100) as salePrice,round(np.PROMOTION_PRICE/100) as promotionprice, "
                . " if(np.DISP_TYPE > 0,np.PROMOTION_PRICE,np.salePrice) as indexprice,nps.inventory as storeNum, "
                . " (nps.ordernum+IFNULL(nps.backnum,0)) as orderNum, "
                . " np.DISP_TYPE as status,if((nps.inventory-nps.ordernum) > 0,0,1) as outOfStock,np.STARTTIME,np.ENDTIME";
        return $select;
    }

    private function _from($params) {
        if (isset($params['label'])) {
            $from = " FROM nh_product_store nps,nh_product_controll npcl, nh_product np"
                    . " LEFT JOIN (select prodId ,AVG(star) as star from nh_product_comment where IS_DISABLED=0 group by prodId) npc on np.prodId=npc.prodId";
        } else {
            $from = " FROM nh_product_store nps, nh_product np"
                    . " LEFT JOIN (select prodId ,AVG(star) as star from nh_product_comment where IS_DISABLED=0 group by prodId) npc on np.prodId=npc.prodId";
        }
        return $from;
    }

    private function _where($params) {
        $where = " WHERE (np.prodId=nps.prodId) AND (np.status =1) AND (np.IS_DISABLED = 0) AND (np.USE_TYPE='启用') AND (np.TO_TYPE='农行内网') AND (np.prodId=np.groupId)";
        if (isset($params['label'])) {
            $where .= " AND (npcl.prodId=np.prodId) ";
            $where .= " AND (npcl.name='" . $params['label'] . "') AND (npcl.IS_DISABLED = 0) ";
        }
        if (isset($params['search']) && $params['search'] !== '') {
            $where .= $this->_searchToSQL($params['search']);
        }
        if (isset($params['catalog']) && $params['catalog'] !== '') {
            $where .= " AND (categories =" . $params['catalog'] . ")";
        }
        return $where;
    }

    private function _order($params) {
        if (isset($params['indextype']) && ($params['indextype'] != "")) {
            if ($params['indextype'] == '销量') {
                $order = " ORDER BY nps.ordernum " . $params['indexorder'];
            }
            if ($params['indextype'] == '价格') {
                $order = " ORDER BY if(np.DISP_TYPE > 0,np.PROMOTION_PRICE,np.salePrice) " . $params['indexorder'];
            }
            if ($params['indextype'] == '评价') {
                $order = " ORDER BY ifnull(npc.star,0) " . $params['indexorder'];
            }
        } else {
            if (isset($params['label'])) {
                $order = " ORDER BY npcl.content DESC ";
            } else {
                $order = " ORDER BY np.SEQ DESC, np.modifydatetime DESC ";
            }
        }
        return $order;
    }

    public function json_count($params) {
        $sql = "SELECT COUNT(*) as num " . $this->_from($params) . $this->_where($params);
        $query = $this->db->query($sql);
        $result = $query->row_array();
        return $result;
    }

}
