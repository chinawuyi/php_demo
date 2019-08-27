<?php

class Discount_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 生成砍价单
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[prodId]</b></td>
	 * <td>商品表的编号</td></tr>
	 * <tr valign="top">
	 * <td><b>params[num]</b></td>
	 * <td>购买数量</td></tr>
	 * @return int 返回砍价单编号
	 */
	public function creatediscount($params) {
		$this->db->where('prodId', $params['prodId']);
		$query = $this->db->get('nh_product');
		$product = $query->row_array();
		$insert = array();
		$this->load->model('dball', 'dball', TRUE);
		$insert['discountNo'] = $this->dball->getNo('DIS' . Date('YmdH'), 2);
		$insert['userId'] = $params['userId'];
		$insert['createTime'] = Date('Y-m-d H:i:s');
		$insert['endTime'] = Date('Y-m-d H:i:s', strtotime('+24 hour'));
		$insert['prodId'] = $params['prodId'];
		$insert['prodName'] = $product['name'];
		$insert['thumbnailImage'] = $product['listimage'];
		$insert['count'] = 1;
		if ($product['DISP_TYPE'] == '0') {
			$insert['price'] = $product['salePrice'];
		} else {
			$insert['price'] = $product['PROMOTION_PRICE'];
		}
		$insert['settlementPrice'] = $product['settlementPrice'];
		$insert['tax'] = $product['TAX'];
		$insert['amount'] = $insert['price'] * $insert['count'];
		$insert['lastamount'] = $insert['amount'];
		$insert['status'] = 1;
		$insert['total'] = 0;
		$insert['FROM_ID'] = $product['FROM_ID'];
		$insert['IS_DISABLED'] = 0;
		$insert['createdatetime'] = Date('Y-m-d H:i:s');
		$insert['createuser'] = $params['userId'];
		$this->db->insert('nh_discount', $insert);
		$insert['Id'] = $this->db->insert_id();
		return $insert['Id'];
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 提供砍价单本身的信息
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[id]</b></td>
	 * <td>砍价单表的Id</td></tr>
	 * @return mixed 砍价单对象
	 */
	public function discountdata($params) {
		$this->db->where('Id', $params['id']);
		//$this->db->where('userId', $params['userId']);
		$this->db->select('Id,lastamount,userId,prodId,createTime,endTime,status,total,PAYSTATUS');
		$query = $this->db->get('nh_discount');
		$result = $query->row_array();
		//把价格转为元，并保留两位小数
		$result['lastamount'] = round($result['lastamount']/100.00,2).'';
		return $result;
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 提供砍价单用户的信息
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[id]</b></td>
	 * <td>砍价单表的Id</td></tr>
	 * @return mixed 砍价单对象
	 */
	public function discountuserdata($params) {
		$query = $this->db->query('select Id,userName from nh_user where Id=(select userId from nh_discount where Id='.$params['id'].')');
		$result = $query->row_array();
		return $result;
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 提供当前登录用户的信息
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[id]</b></td>
	 * <td>砍价单表的Id</td></tr>
	 * @return mixed 砍价单对象
	 */
	public function userdata($params) {
		$this->db->where('Id', $params['userId']);
		$this->db->where('IS_DISABLED', 0);
		$this->db->select('Id,userName');
		$query = $this->db->get('nh_user');
		$result = $query->row_array();
		return $result;
	}



	/**
	 * (PHP 4, PHP 5)<br/>
	 * 提供砍价单明细砍价记录
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[id]</b></td>
	 * <td>砍价单表的Id</td></tr>
	 * @return mixed 砍价单明细对象
	 */
	public function recorddata($params) {
		$this->db->where('discountId', $params['id']);
		$this->db->select('userId,userName,discount,price,createTime');
		$query = $this->db->get('nh_discount_detail');
		$result = $query->result_array();
		//把价格转为元，并保留两位小数
		foreach($result as $key=>$value){
			$result[$key]['discount'] = round($result[$key]['discount']/100.00,2).'';
			$result[$key]['price'] = round($result[$key]['price']/100.00,2).'';
		}
		return $result;
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 我的砍价单列表
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[status]</b></td>
	 * <td>砍价单状态 1 doing，0 finish</td></tr>
	 * @return mixed 返回砍价单列表对象
	 */
	public function listdata($params) {
		$this->db->where('userId', $params['userId']);
		$this->db->where('status', $params['status']);
		$this->db->where('PAYSTATUS', $params['PAYSTATUS']);
		$this->db->where('IS_DISABLED', 0);
		$this->db->select('Id,prodId,prodName,amount,lastamount,thumbnailImage,PAYSTATUS');
		$this->db->order_by('createdatetime','desc');
		$query = $this->db->get('nh_discount');
		$result = $query->result_array();
		//把价格转为元，并保留两位小数
		foreach($result as $key=>$value){
			$result[$key]['amount'] = round($result[$key]['amount']/100.00,2).'';
			$result[$key]['lastamount'] = round($result[$key]['lastamount']/100.00,2).'';
		}
		return $result;
	}

	public function listfinishdata(){
		$query = $this->db->query('select Id,prodId,prodName,amount,lastamount,thumbnailImage,PAYSTATUS from nh_discount where IS_DISABLED=0 and (status<>1 or PAYSTATUS<>0) order by createdatetime desc ');
		$result = $query->result_array();
		//把价格转为元，并保留两位小数
		foreach($result as $key=>$value){
			$result[$key]['amount'] = round($result[$key]['amount']/100.00,2).'';
			$result[$key]['lastamount'] = round($result[$key]['lastamount']/100.00,2).'';
		}
		return $result;
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * 砍价
	 * @param mixed $params <p>参数对象</p>
	 * <tr valign="top">
	 * <td>参数中的对象</td>
	 * <td>说明</td></tr>
	 * <tr valign="top">
	 * <td><b>params[userId]</b></td>
	 * <td>登录用户表的<i>Id</i></td></tr>
	 * <tr valign="top">
	 * <td><b>params[id]</b></td>
	 * <td>砍价单</td></tr>
	 * @return mixed 砍掉的金额
	 */
	public function discount($params) {
		// 获得砍价单
		$this->db->where('Id',$params['id']);
		$this->db->where('status',1);
		$query = $this->db->get('nh_discount');
		if ($query->num_rows() == 0){
			$result = array();
			$result['code'] = '3002';
			$result['msg'] = '砍价已经结束！';
			return $result;
		}
		else {
			$discount = $query->row_array();
			if($discount['userId']==$params['userId']){
				$result = array();
				$result['code'] = '4001';
				$result['msg'] = '不能帮自己砍价！';
				return $result;
			}
			// 获得砍价规则
			/*$this->db->where('products',$discount['prodId']);
			$this->db->where('type','砍价');
			$query = $this->db->get('nh_promotion_act');*/
			$query = $this->db->query("select * from nh_promotion_act where type='砍价' and IS_DISABLED=0 and (products=-1 or products='".$discount['prodId']."')");
			$rules = $query->row_array();
			if ($this->_checkend($discount,$rules)){
				$sql = "UPDATE nh_discount SET status = 0 WHERE Id=".$params['id'];
				$this->db->query($sql);
				$result = array();
				$result['code'] = '3002';
				$result['msg'] = '砍价已经结束！';
				return $result;
			}
		}
		$this->db->where('discountId', $params['id']);
		$this->db->where('userId', $params['userId']);
		$query = $this->db->get('nh_discount_detail');
		if ($query->num_rows() > 0) {
			$result = array();
			$result['code'] = '3001';
			$result['msg'] = '你已经帮助朋友砍过价了！';
			return $result;
		}
		$query = $this->db->get_where('nh_user', array('Id' => $params['userId']));
		$user = $query->row_array();
		$insert = array();
		$insert['discountId'] = $discount['Id'];
		$insert['userId'] = $params['userId'];
		$insert['userName'] = $user['userName'];
		$insert['discount'] = $this->_discountprice($discount,$rules);
		$insert['price'] = $discount['lastamount'] - $insert['discount'];
		$insert['createTime'] = Date('Y-m-d H:i:s');
		$insert['IS_DISABLED'] = 0;
		$insert['createdatetime'] = Date('Y-m-d H:i:s');
		$insert['createuser'] = $params['userId'];
		$this->db->insert('nh_discount_detail',$insert);
		$discount['lastamount'] = $insert['price'];
		$discount['total'] = $discount['total'] + 1;
		$discount['modifydatetime'] = Date('Y-m-d H:i:s');
		$discount['modifyuser'] = $params['userId'];
		$this->db->where('Id',$discount['Id']);
		$discountId = $discount['Id'];
		unset($discount['Id']);
		$this->db->update('nh_discount',$discount);
		//更新砍价单状态
		$sql = "select * from nh_promotion_act where type='砍价' and IS_DISABLED=0 and (products=-1 or products='".$discount['prodId']."')";
		$query = $this->db->query($sql);
		$query = $query->row_array();
		if($query['bargainnumber']<=$discount['total']){
			$discount_update = array();
			$discount_update['status'] = 0;
			$this->db->where('Id',$discountId);
			$this->db->update("nh_discount",$discount_update);
		}
		//把价格转为元，并保留两位小数
		return round($insert['discount']/100.00,2).'';
	}

	// 判断本次砍价是否结束
	private function _checkend($discount,$rules){
		if ($discount['total'] >= $rules['bargainnumber']){
			return true;
		}
		if (strtotime($discount['endTime'])<=strtotime(date('Y-m-d H:i:s'))){
			return true;
		}
		return false;
	}

	// 根据砍价定义，计算本次砍价金额
	private function _discountprice($discount,$rules){
		if ($rules['bargaintype'] == '定额'){
			return $rules['bargainprice']/$rules['bargainnumber'];
		}
		else if ($rules['bargaintype'] == '递减'){
			return $rules['bargainprice']/((1+$rules['bargainnumber'])*$rules['bargainnumber']/2)*($rules['bargainnumber']-$discount['total']);
		}
		else {return 0;}
	}
	//取消砍价单
	public function discountcancel($params) {
		$sql = "update nh_discount set PAYSTATUS=2,status=0 where Id=".$params['discountId'];
		return $this->db->query($sql);
	}
}