<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * 
 * 递归 输出 JSON 对象
 * 
 */
class Recursivejson
{
	//被处理的数据
	private $data = array();
	//起始PID
	private $start = 0 ;
	//子元素的 数组名称
	private $childstring = 'child';
	//数据中的 字段名 pid
	private $parentId = 'pid';
	//数据中的 字段名 id
	private $thisId = 'id';
	//返回类型
	private $returntype = 'json';
	
	public function initialize($options)
	{
		foreach($options as $k => $v)
		{
			$this->$k = $v;
		}
	}
	
	public function recursiveout()
	{
		if($this->returntype == 'json')
		{
			return json_encode($this->_build_tree($this->start));
		}
		else
		{
			return $this->_build_tree($this->start);
		}
	}
	private function _build_tree($pid = 0)
	{
		$childs = $this->_findchilds($this->data,$pid);
		if(empty($childs))
		{
			return null;
		}
		foreach($childs as $k=>$v)
		{
			$rescurTree = $this->_build_tree($v[$this->thisId]);
			if(null != $rescurTree)
			{
				$childs[$k][$this->childstring] = $rescurTree;
			}
		}
		return $childs;
	}
	private function _findchilds(&$arr,$pid)
	{
		$childs = array();
		foreach($arr as $k=>$v)
		{
			if($v[$this->parentId]==$pid)
			{
				$childs[] = $v;
			}
		}
		return $childs;
	}
}