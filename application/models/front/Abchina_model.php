<?php
class Abchina_model extends CI_Model {

	private $userid;
	
	public function __construct()
	{
     	parent::__construct();
		$this->load->database();
	}


    /**
     * 获得3个大类的产品数据
     * @return array $result
     */
    public function catalog($pageno=1,$pagelength=5)
    {
		$sql = "SELECT * FROM nh_categories";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		foreach ($data as $key=>$row){
			$sql = "SELECT * FROM nh_product WHERE USE_TYPE='启用' and status = 1 and categories = ".$row['catId']." LIMIT $pageno,$pagelength";
			$query = $this->db->query($sql);
			$data[$key]['products'] = $query->result_array();
		}
		return $data;
    }
	
}
