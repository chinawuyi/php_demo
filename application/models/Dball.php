<?php
class Dball extends CI_Model {

	private $userid;
	
	public function __construct()
	{
     	parent::__construct();
		$this->load->database();
		$this->userid = $this->session->userdata('userId');
	}


    /**
     * 从数据字典表获取需要的信息
     * @return array $result
     */
    public function getDictData($typename="",$name ="")
    {
		if ($typename == "") {
				$sqlStr = "SELECT id as id, seq, seqno, name, value FROM sys_dictdata WHERE type='字典类型' ORDER BY seq";
		}
		else {
			if ($name == ""){
				$sqlStr = "SELECT id as id, seq, seqno, name, value FROM sys_dictdata WHERE type='".$typename."' ORDER BY seq";
			}
			else {
				$sqlStr = "SELECT id as id, seq, seqno, name, value FROM sys_dictdata WHERE type='".$typename."' and name='".$name."'";
			}
		}
		$query = $this->db->query($sqlStr);
		return $query->result_array();
    }
	
	/**
     * 保养类型
     * @return array $result
     */
    public function getMaintenanceData()
    {
		
		$sqlStr = "SELECT id as id, MAINTENANCE_TYPE as name FROM jit_maintenance WHERE IS_DISABLED = 0";
		$query = $this->db->query($sqlStr);
		return $query->result_array();
    }
    
    /**
     * 从配置字典表获取需要的信息
     * @return array $result
     */
    public function getConfData($typename="",$name ="")
    {
		if ($typename == "") {
				$sqlStr = "SELECT type FROM sys_configdata WHERE group by type";
		}
		else {
			if ($name == ""){
				$sqlStr = "SELECT id as id, seq, seqno, name, value FROM sys_configdata WHERE type='".$typename."' ORDER BY seq";
			}
			else {
				$sqlStr = "SELECT id as id, seq, seqno, name, value FROM sys_configdata WHERE type='".$typename."' and name='".$name."'";
			}
		}
		$query = $this->db->query($sqlStr);
		return $query->result_array();
    }
    
    /**
     * 获取编号
     * @param string $type  编号的固定开始文字
     * @param integer $num  编号的最加流水号长度,默认2位
     * @return array $result
     */
    public function getNo($type,$num=2){
    	$CI =& get_instance();
        $switch = true;
        while ($switch){
            $sql = "SELECT * FROM sys_configdata WHERE type='编号生成' AND name = '".$type."'";
            $query = $this->db->query($sql);
            $result = $query->result_array();
            if (sizeof($result) == 0){
                    $result = $type.str_pad('1', $num,'0',STR_PAD_LEFT);
                    $CI->load->helper('guid_helper');
                    $data['id'] = guid();
                    $data['type'] = '编号生成';
                    $data['seqno'] = $type;
                    $data['value'] = '1';
                    $data['name'] = $type;
                    $data["createdatetime"] = date('Y-m-d G:i:s');
                    $data["createuser"] = $this->userid;
                    $this->db->insert('sys_configdata',$data);
            }
            else {
                    $no = $result[0]['value'] + 1;
                    $data['value'] = $no;
                    $data["modifydatetime"] = date('Y-m-d G:i:s');
                    $data["modifyuser"] = $this->userid;
                    $this->db->where('id',$result[0]['id']);
                    $this->db->update('sys_configdata',$data);
                    $result = $type.str_pad($no,$num,'0',STR_PAD_LEFT);
            }
            $insert=array();
            $insert['ID'] = $result;
            $switch = !$this->db->insert('sys_key',$insert);
        }
    	return $result;
    }
    
   /**
     * 获取某个UUID,对应表的对应字段
     * @param string $uuid  表的UUID
     * @param string $targettable  表名 默认为oa_project
     * @param string $getField 表字段 默认为projectno
     * @return array $result
     */
    public function getField($uuid,$targettable='oa_project',$getField='projectno'){
    	$CI =& get_instance();
    	$sql = "SELECT $getField FROM $targettable WHERE uuid='".$uuid."'";
    	$query = $this->db->query($sql);
    	$result = $query->row_array();
    	return $result;
    }
    
   /**
     * 增加任务
     * @param string $type  增加任务类型
     * @param string $uuid  来源表的id
     * @return array $result
     */
    public function addtask($type,$uuid){
    	if (self::_taskcheck($type,$uuid)) return;
    	$query = $this->db->get_where('sys_taskcontrol',array('type'=>$type));
    	$controllist = $query->result_array();
    	foreach ($controllist as $control){
    		$query = $this->db->get_where($control['src_table'],array($control['src_id']=>$uuid));
    		$src = $query->row_array();
    		$query = $this->db->query("SHOW FIELDS FROM ".$control['src_table']);
    		$srcfield = $query->result_array();
    		foreach ($srcfield as $fields) {
    			$control['tgt_sql'] = str_replace("$".$fields['Field'], $src[$fields['Field']], $control['tgt_sql']);	
    			$control['title'] = str_replace("$".$fields['Field'], $src[$fields['Field']], $control['title']);	
    			$control['uri'] = str_replace("$".$fields['Field'], $src[$fields['Field']], $control['uri']);	
    		} 
    		$query = $this->db->query($control['tgt_sql']);
    		$targets = $query->result_array();
    		foreach ($targets as $target) {
    			$data = array();
				$data['type'] = $type;    			
    			$data['memberid'] = $target[$control['tgt_member']];
    			$data['title'] = $control['title'];
    			$data['uri'] = $control['uri'];
    			$data['targetid'] = $uuid;
    			$data['target'] = $control['targetmodule'];
    			$data = self::_taskdata($data);	
    			$this->db->insert('oa_task',$data);
    		}
    	}
    }
    
    private function _taskcheck($type,$targetid){
    	$this->db->where('targetid',$targetid);
    	$this->db->where('type',$type);
    	$query = $this->db->get('oa_task');
    	if ($query->num_rows() > 0) return true;		
    	else return false;
    }
    
    private function _taskdata($data){
    	$no = 'TSK'.date("Ym-");
    	$this->load->helper('guid_helper');
		$data['uuid'] = guid();
		$data['seqno'] = self::getNo($no,3);
    	$data['taskdate'] = date('Y-m-d G:i:s');
    	$data['state'] = '0';
		$date['createdatetime'] = date('Y-m-d G:i:s');
    	$data['createuser'] = $this->userid;
    	return  $data;
    }

    /**
     * 从基础数据表获取需要的信息
	 * @param string $table 目的表
	 * @param string $disp 查询结果列
	 * @param string $where 附加条件
     * @return array $result
     */
	public function getbasedata($table,$disp="*",$where=""){
		$sqlStr = "SELECT $disp FROM $table WHERE (`IS_DISABLED` =0) ";
		if (strlen($where) > 0)
			$sqlStr .= " AND (".$where.")" ;
		$query = $this->db->query($sqlStr);
		return $query->result_array();
	}
	
	public function getstatus(){
		$sql = "select distinct ADJUST_STATUS as id,ADJUST_STATUS as name from jit_dispatch_status where ADJUST_STATUS<>''";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

    /**
     * 短信第一次注册
	 * @param string $table 目的表
	 * @param string $disp 查询结果列
	 * @param string $where 附加条件
     * @return array $result
     
	public function getbasedata($table,$disp="*",$where=""){
		$sqlStr = "SELECT $disp FROM $table WHERE (`IS_DISABLED` =0) ";
		if (sizeof($where) > 0)
			$sqlStr .= " AND (".$where.")" ;
		$query = $this->db->query($sqlStr);
		return $query->result_array();
	}*/
	
}
