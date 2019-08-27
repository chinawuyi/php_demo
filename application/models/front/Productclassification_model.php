<?php
class Productclassification_model extends CI_Model {

    private $userid;

    public function __construct()
    {
    parent::__construct();
            $this->load->database();
    }

    public function json_data()
    {
        $sql = "SELECT catName as name,catImg as imgurl,catId as id FROM nh_categories "
                . "WHERE IS_DISABLED=0 AND USE_TYPE='启用' ORDER BY SEQ DESC";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
	
}
