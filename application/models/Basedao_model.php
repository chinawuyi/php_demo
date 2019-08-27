<?php
class Basedao_model extends CI_Model {

	private $table;
	private $keyId;

	public function __construct()
	{
     	parent::__construct();
		$this->load->database();
	}

	public function getTable(){
		return $this->table;
	}

	public function setTable($table)
	{
		$this->table = $table;
	}

	public function getKeyId(){
		return $this->keyId;
	}
	
	public function setKeyId($keyId)
	{
		$this->keyId = $keyId;
	}
	
	/**
	 * 获取记录
	 * @param string $id, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function get($id = FALSE,$transaction = true)
	{
		$result = Ousuclass::getJsonResult();
		try {
			$result = Ousuclass::getJsonResult();
			$result["msg"] = "查询成功!";
			if ($id === FALSE) {
				$query = $this->db->get($this->table);
				$result["obj"] = $query->result_array();
				$result["success"] = true;
			}
			else {
				$query = $this->db->get_where($this->table, array($this->keyId => $id));
				$row = $query->result_array();
				$result["success"] = true;
				if ($query->num_rows() >0)
					$result["obj"] = $row[0];
				else 
					$result["success"] = false;
			}
			log_message("debug","[$this->table]获取记录数 : ".$query->num_rows());
			return $result;
		} catch (Exception $e) {
			$err =$e->getMessage(); 
			log_message("error","[$this->table]获得数据错误 : $err" );
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}
	
	/**
	 * 查询
	 * @param string $sql,boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function find($sql,$transaction = true)
	{
		$result = array();
		try {
			$query = $this->db->query($sql);
			log_message("debug","查询数据库 : [$sql] 结果[".sizeof($query->row_array())."]行");
			$result = Ousuclass::getJsonResult();
			$result["success"] = true;
			$result["msg"] = "查询成功!";
			$result["obj"] = $query->result_array();
			return $result;
		} catch (Exception $e) {
			$err =$e->getMessage(); 
			log_message("error","[$this->table]查询数据错误 : [$sql] \r\n			ERROR : $err" );
			$result = Ousuclass::getJsonResult();
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}
	
	/**
	 * 执行SQL命令,无返回
	 * @param string $sql,boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function execSql($sql,$transaction = true)
	{
		$result = array();
		try {
			$query = $this->db->query($sql);
			log_message("debug","执行SQL语句 : [$sql] ");
			$result = Ousuclass::getJsonResult();
			$result["success"] = true;
			$result["msg"] = "查询成功!";
			$result["obj"] = null;
			return $result;
		} catch (Exception $e) {
			$err =$e->getMessage(); 
			log_message("error","[$this->table]执行SQL语句  : [$sql] \r\n			ERROR : $err" );
			$result = Ousuclass::getJsonResult();
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}
	/**
	 * 添加记录
	 * @param array $dataInfo, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function addData($dataInfo,$transaction = true)
	{
                if (sizeof($dataInfo) === 0){
                        $result = Ousuclass::getJsonResult();
                        $result["success"] = true;
                        $result["msg"] = "数据插入成功";
                        $result["obj"] = $dataInfo;
                        return $result;
                }
		if ($transaction)
			$this->db->trans_start();
		$result = array();
		try {
			$this->db->insert($this->table, $dataInfo);
                        $dataInfo[$this->keyId] = $this->db->insert_id();
			$info = json_encode($dataInfo);
			if ($transaction){
				$this->db->trans_complete();
				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					log_message("error","[$this->table]插入错误 : $err" );
					$result = Ousuclass::getJsonResult();
					$result["success"] = false;
					$result["msg"] = $err;
					return $result;
				}
			}
			log_message("debug","[$this->table]插入成功 : $info");
			$result = Ousuclass::getJsonResult();
			$result["success"] = true;
			$result["msg"] = "添加成功";
			$result["obj"] = $dataInfo;
			return $result;
		} catch (Exception $e) {
			if ($transaction)
				$this->db->rollBack();
			$err =$e->getMessage(); 
			log_message("error","[$this->table]插入错误 : $err" );
			$result = Ousuclass::getJsonResult();
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}
	/**
	 * 更新记录
	 * @param array $dataInfo, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function saveData($dataInfo,$transaction = true)
	{	
                if (sizeof($dataInfo) === 0){
                        $result = Ousuclass::getJsonResult();
                        $result["success"] = true;
                        $result["msg"] = "数据更新成功";
                        $result["obj"] = $dataInfo;
                        return $result;
                }
                if ($transaction)
                        $this->db->trans_start();
                $result = array();
                try {
                        $this->db->where($this->keyId, $dataInfo[$this->keyId]);
                        $this->db->update($this->table, $dataInfo); 
                        $info = json_encode($dataInfo);
                        log_message("debug","[$this->table]更新成功 : $info");
                        if ($transaction){
                                $this->db->trans_complete();
                                if ($this->db->trans_status() === FALSE) {
                                        $this->db->trans_rollback();
                                        log_message("error","[$this->table]更新错误 : $err" );
                                        $result = Ousuclass::getJsonResult();
                                        $result["success"] = false;
                                        $result["msg"] = $err;
                                        return $result;
                                }
                        }
                        $result = Ousuclass::getJsonResult();
                        $result["success"] = true;
                        $result["msg"] = "数据更新成功";
                        $result["obj"] = $dataInfo;
                        return $result;
                } catch (Exception $e) {
                        if ($transaction)
                                $this->db->rollBack();
                        $err =$e->getMessage(); 
                        log_message("error","[$this->table]更新错误 : $err" );
                        $result = Ousuclass::getJsonResult();
                        $result["success"] = false;
                        $result["msg"] = $err;
                        return $result;
                }
	}
	/**
	 * 批量删除
	 * @param string $idString, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public function deleteData($idString,$transaction = true)
	{
                if (strlen($idString) === 0){
                        $result = Ousuclass::getJsonResult();
                        $result["success"] = true;
                        $result["msg"] = "数据删除成功";
                        $result["obj"] = $dataInfo;
                        return $result;
                }
                if ($transaction)
			$this->db->trans_start();
		$result = array();
		try {
			$data = explode(",",$idString);
			//log_message("debug","跟踪-->".json_encode($data));
			for ($i=0;$i<sizeof($data);$i++){
				// oa_,sys_,user_表不实现软删除,其他表实现软删除
				$list = explode("_",$this->table);
				if (in_array($list[0],array("oa","sys","user")))
					$this->db->delete($this->table, array($this->keyId => $data[$i]));
				else {
					$this->db->where($this->keyId, $data[$i]);
					$dataInfo['IS_DISABLED'] = 1;
					$this->db->update($this->table, $dataInfo);
				}
			}
			$info = json_encode($idString);
			log_message("debug","[$this->table]删除成功 : $info");
			if ($transaction){
				$this->db->trans_complete();
				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					log_message("error","[$this->table]删除错误 : $err" );
					$result = Ousuclass::getJsonResult();
					$result["success"] = false;
					$result["msg"] = $err;
					return $result;
				}
			}
			$result = Ousuclass::getJsonResult();
			$result["success"] = true;
			$result["msg"] = "删除成功!";
			return $result;
		} catch (Exception $e) {
			if ($transaction)
				$this->db->rollBack();
			$err = $e->getMessage();
			log_message("error","[$this->table]删除错误 : $err" );
			$result = Ousuclass::getJsonResult();
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}

	/**
	 * 服务器端dataTable分页实现
	 * @param string $sqlStr 数据源的sql语句
	 * @param array $aColumns 页面显示字段，不从数据库读的字段用' '表示。 eg: $aColumns = array('id', 'username', ' ', 'pwd');
	 * @return array $result
	 */
	public function pagingServer($aColumns, $sqlStr)
	{
		try {
			//Paging
			$sLimit = "";
			if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
				//$sLimit = "LIMIT " . mysql_real_escape_string( $_GET['iDisplayStart'] ) . ", " . mysql_real_escape_string( $_GET['iDisplayLength'] );
				$sLimit = "LIMIT " . addslashes( $_GET['iDisplayStart'] ) . ", " . addslashes( $_GET['iDisplayLength'] );
			}
			//Ordering
			$sOrder = "";
			if ( isset( $_GET['iSortCol_0'] ) ) {
				$sOrder = "ORDER BY ";
				for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
					if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
						//$sOrder .= $aColumns[ intval( $_GET['iSortCol_' . $i] ) ] . " " . mysql_real_escape_string( $_GET['sSortDir_' . $i] ) . ", ";
						$sOrder .= $aColumns[ intval( $_GET['iSortCol_' . $i] ) ] . " " . addslashes( $_GET['sSortDir_' . $i] ) . ",";
					}
				}
				$sOrder = substr_replace( $sOrder, "", -1 ); //demo内写法
				//$sOrder = trim(substr_replace( $sOrder, "", -5 ));
				if ( $sOrder == "ORDER BY" ) {
					$sOrder = "";
				}
			}
			
			//Filtering
			$sWhere = "";
			if ( isset($_GET['sSearch']) &&$_GET['sSearch'] != "" ) {
				$sWhere = "WHERE (";
				for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
					//$sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string( $_GET['sSearch'] ) . "%' OR ";
					if (strpos(strtolower($aColumns[$i]),'time') > 0) continue;
					if (strpos(strtolower($aColumns[$i]),'date') > 0) continue;
					if (strpos(strtolower($aColumns[$i]),'stamp') > 0) continue;
					$sWhere .= $aColumns[$i] . " LIKE '%" . addslashes( $_GET['sSearch'] ) . "%' OR ";
				}
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
			}
			// Individual column filtering	
			for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
				if ( isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '' ) {
					if ( $sWhere == "" ) {
						$sWhere = "WHERE ";
					} else {
						$sWhere .= " AND ";
					}
					//$sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
					$sWhere .= $aColumns[$i] . " LIKE '%" . addslashes($_GET['sSearch_' . $i]) . "%' ";
				}
			}
			//SQL queries
			$sTable = "($sqlStr) t";
			$sQuery = "SELECT " . str_replace(" , ", "", implode(", ", $aColumns)) . " FROM $sTable $sWhere $sOrder $sLimit";
			$rTemp = $this->db->query($sQuery);
			$rResult = $rTemp->result_array();
			
			$sQuery1 = "SELECT COUNT(*) total FROM $sTable";
			$rTemp = $this->db->query($sQuery1);
			$iTotal = $rTemp->row(0)->total;

			$sQuery1 = "SELECT COUNT(*) total FROM $sTable $sWhere";
			$rTemp = $this->db->query($sQuery1);
			$iFilteredTotal = $rTemp->row(0)->total;
			
			foreach($rResult as $key=>$val){
				$rResult[$key]['DT_RowId'] = $val[$aColumns[0]];
			}

			//Output
			$resultdata = array(
					"sEcho" => intval(isset( $_GET['sEcho'] )?$_GET['sEcho']:'1'),
					"iTotalRecords" => $iTotal,
					"iTotalDisplayRecords" => $iFilteredTotal,
					//"iTotalDisplayRecords" => $iTotal,
					"aaData" => $rResult
			);
			$result = Ousuclass::getJsonResult();
			log_message("debug","[$this->table]服务器分析查询 : [$sQuery]");
			$result["success"] = true;
			$result["msg"] = "查询成功!";
			$result["obj"] = $resultdata;
			return $result;
		} catch (Exception $e) {
			$err =$e->getMessage(); 
			log_message("error","[$this->table]服务器分析查询错误 : [$sQuery] \r\n			ERROR : $err" );
			$result = Ousuclass::getJsonResult();
			$result["success"] = false;
			$result["msg"] = $err;
			return $result;
		}
	}

}
