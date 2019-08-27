<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Ousuclass {

	public static function getJsonResult(){
		return array(
			"success" => false, //是否成功
			"msg" => "", //提示信息
			"obj" => null //其他信息
		);
	}
	
	public static function logger($info = ""){
		log_message("debug","跟踪----->".$info);
	}
	
	public static function idsChange($ids){
		$ex=explode(',',$ids);
		foreach ($ex as $key=>$value)
			$ex[$key] = "'".$value."'";
		return implode(',', $ex);
	}
	
	public static function getSessionInfo(){
		return array(
			"isLogin" => false, 
			"webtitle" => "建正OA",
			"userId" => "",         //用户ID
			"loginName" => "",      //用户登录名称
			"fullName" => "",       //用户姓名
			"ip" => "",             //IP地址
			"webtitle" => "",       //网页标题
			"rights" => array(),	//权限表
			"user_agent" => "",     //客户端浏览器类型
			"memberno" => "",		// 职员编号
			"membername" => "",		// 职员姓名
			"workrole" => "",       // 工作角色
			"deptrole" => "",       // 部门角色
			"emprole" => "",         // 职员角色
			"wk_role" => "",		// 审核角色
			"userrights" => array(),// 用户权限
			"department" => ""		// 部门
		);
	}
	
	/**
	 * 将二维数组,字典数据转换为option
	 * @return String $result
	 */
	public static function all_html_option($mArr,$key="name",$value="name",$selected ="")
	{
		$result = "<option></option>";
		foreach($mArr as $arr){	
			if ($selected == $arr[$key]){
				$result .="<option selected value=\"".$arr[$key]."\">".$arr[$value]."</option>";
			}
			else {
				$result .="<option value=\"".$arr[$key]."\">".$arr[$value]."</option>";
			}
		}
		return $result;
	}
	
	/**
	 * 将二维数组,字典数据转换为option
	 * @return String $result
	 */
	public static function all_html_optionsingle($mArr,$key="name",$value="name",$selected ="")
	{
		$result = "<option></option>";
		foreach($mArr as $arr){	
			if ($selected == $arr[$key]){
				$result .="<option selected value='".$arr[$key]."'>".$arr[$value]."</option>";
			}
			else {
				$result .="<option value='".$arr[$key]."'>".$arr[$value]."</option>";
			}
		}
		return $result;
	}
	
	public static function arrayreplace($str,$config,$oldfield,$newfield){
		$list = explode(',', $str);
		foreach ($config as $arr) {
			if (in_array($arr[$oldfield],$list)) 
				foreach($list as $key=>$value)
					if ($value == $arr[$oldfield]) $list[$key] = $arr[$newfield];
		}
		$str = implode(',', $list);
		return $str;
	}
}

/* End of file Ousuclass.php */