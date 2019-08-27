<?php

class Vote_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	/**
 * (PHP 4, PHP 5)<br/>
 * 获取投票商品信息
 * @param mixed $params <p>参数对象</p>
 * <tr valign="top">
 * <td>参数中的对象</td>
 * <td>说明</td></tr>
 * <tr valign="top">
 * <td><b>params[userId]</b></td>
 * <td>登录用户表的<i>Id</i></td></tr>
 * @return mixed 投票商品信息
 */
	public function voteproducts($params) {
		$sql = "select * from nh_vote where IS_DISABLED=0 and starttime<='".$params['date']."' and endtime>='".$params['date']."'";
		$vote = $this->db->query($sql);
		if($vote->num_rows()==0) {
			$result['code'] = 1003;
			$result['msg'] ='暂无投票活动！';
			return $result;
		}
		$vote = $vote->row_array();
		$this->db->where("voteId",$vote['Id']);
		$this->db->where("IS_DISABLED",0);
		$product = $this->db->get('nh_votes_products');
		$product = $product->result_array();
		$total = 0;
		foreach($product as $key=>$pro) {
			$this->db->where("IS_DISABLED",0);
			$this->db->where("voteId",$vote['Id']);
			$this->db->where("prodId",$pro["prodId"]);
			$query = $this->db->get("nh_votes_detail");
			$product[$key]["per"] = $query->num_rows();
			$total += $product[$key]["per"];
		}
		if($total > 0) {
			foreach($product as $key=>$pro){
				$product[$key]["per"] = (round($product[$key]["per"]/$total,2))*100;
			}
		}
		return $product;
	}


	/**
	 * (PHP 4, PHP 5)<br/>
	 * 获取用户投票内容
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * @return mixed 投票商品信息
	 */
	public function votedetail($params) {
		$sql = "select * from nh_vote where IS_DISABLED=0 and starttime<='".$params['date']."' and endtime>='".$params['date']."'";
		$vote = $this->db->query($sql);
		if($vote->num_rows()==0) {
			$result['code'] = 1003;
			$result['msg'] ='暂无投票活动！';
			return $result;
		}
		$vote = $vote->row_array();
		if("提交"==$params["action"]) {
			$productIds = array();
			$this->db->where("voteId",$vote['Id']);
			$this->db->where("IS_DISABLED",0);
			$product = $this->db->get('nh_votes_products');
			$product = $product->result_array();
			foreach($product as $pro)
				$productIds[] = $pro['prodId'];
			$prodIds = $params["prodId"];
			$prodIds = explode(",",$prodIds);
			foreach($prodIds as $prodId) {
				if(!in_array($prodId,$productIds)){
					$result['code'] = 1003;
					$result['msg'] ='有商品不在此次投票活动范围内！';
					return $result;
				}
				$insert = array();
				$insert['voteId'] = $vote['Id'];
				$insert['prodId'] = $prodId;
				$insert['userId'] = $params['userId'];
				$insert['createuser'] = 'sys';
				$insert['createdatetime'] = $params['date'];
				$this->db->insert('nh_votes_detail',$insert);
			}


		}
		$this->db->where("voteId",$vote['Id']);
		$this->db->where("IS_DISABLED",0);
		$this->db->where("userId",$params['userId']);
		$detail = $this->db->get('nh_votes_detail');
		return $detail->result_array();
	}


}