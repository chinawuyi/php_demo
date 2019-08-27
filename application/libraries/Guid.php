<?php
// 三段
// 一段是微秒 一段是地址 一段是随机数
class Guid
{
	function Guid()
	{
		$this->getGuid();
	}
	//
	function getGuid()
	{
           $charid = strtoupper(md5(uniqid(mt_rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
            return $uuid;
        }
        
        function toString()
        {
           $charid = strtoupper(md5(uniqid(mt_rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);// "}"
            return $uuid;
        }

		function newGuid(){
			$Guid = new Guid();
			return $Guid;
		}
}
?>