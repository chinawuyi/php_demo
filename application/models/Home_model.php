<?php
class Home_model extends CI_Model {

    public function __construct()
    {
    parent::__construct();
            $this->load->database();
    }

    public function json_banner($params)
    {
        $sql = "SELECT name,img,url FROM nh_banner "
                . "WHERE IS_DISABLED=0 AND type='首页广告' AND status=1 AND starttime<now() AND endtime > now() ORDER BY seq";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        foreach($result as $key=>$item){
            if(!(strpos($item['img'],'ttp:') > 0)){
                $result[$key]['img'] = base_url().$result[$key]['img'];
            }
        }
        return $result;
    }
    
    public function json_data($params)
    {
        $sql = "SELECT np.prodId as id,np.name,np.content as des,np.thumbnail as img,round(price/100,2) as price,round(salePrice/100,2) as salePrice,"
                . " round(np.PROMOTION_PRICE/100,2) as promotionpice, np.DISP_TYPE as status  "
                . " FROM nh_product_controll npa, nh_product np "
                . " WHERE np.status = 1 AND np.USE_TYPE='启用' AND np.TO_TYPE='农行内网' AND np.IS_DISABLED=0 AND np.prodId=np.groupId AND npa.IS_DISABLED = 0 "
                . " AND np.prodId=npa.prodId AND npa.name='".$params['type']."' "
                . " ORDER BY npa.content";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

}
