<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Nhportal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->sessioninfo = $this->session->userdata('sessioninfo');
        $this->config->load('cfg-system', true);
        $this->abccall = $this->config->item('cfg-system');
        $this->abccall = $this->abccall['abccall'];
        $this->url_module = $this->uri->segment(1, $this->router->default_controller);
        $this->url_model = $this->uri->segment(2, 'index');
        $this->url_method = $this->uri->segment(3, 'index');
        $this->modelpath = $this->url_module . '/' . $this->url_model . '_model';
    }

    private function _jump($url) {
        $params = $this->input->post_get(NULL, TRUE);
        if ((!isset($params['openId'])) || ($params['openId'] === '')) {
            if (isset($params['code']) && ($params['code'] != '')) {
                $params["openId"] = $this->_getopenid($params["code"]);
            }
        }
        log_message("info","login:".print_r($params,true));
        if(""!=$params["openId"]) {
        	$this->sessioninfo['openId'] = $params["openId"];
	        $this->session->set_userdata('sessioninfo', $this->sessioninfo);
	        // 检查是否已经登录过的用户
	        $this->load->model('public/login_model', 'dba');
	        if ($this->dba->checkLogin($params)) {
	            $this->load->database();
	            $this->db->where("type","字典类型");
	            $this->db->where("name","缓存更新");
	            $query = $this->db->get("sys_dictdata");
	            $num = 1;
	            if($query->num_rows()>0){
	                $query = $query->row_array();
	                $num = $query["value"];
	            }
	            redirect(base_url($url.'?a='.$num));
	        } else {
	            //if ($this->dba->auth($params)) {
	            // redirect(base_url($url));
	            //} else {
	            redirect(base_url('/html/login.html'));
	            //}
	        }
        } else {
        	redirect(base_url('/html/login.html'));
        }
        
    }

    private function _curlget($url){
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $token = curl_exec($ch);
        curl_close($ch);
        return $token;
    }

    private function _getopenid($code) {
        $config = $this->config->item('cfg-system');
        $config = $config['wxshop'];
        $appid = $config['appid'];
        $secret = $config['secret'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $token = $this->_curlget($url);
        $token = json_decode($token,true);
        log_message("info","code to openid:".print_r($token,true));
        if(isset($token["errcode"])) {
            return "";
        }
        return $token["openid"];
    }

    public function jumphome(){
        $params = $this->input->post_get(NULL, TRUE);
        if ((!isset($params['openId'])) || ($params['openId'] === '')) {
            if (isset($params['code']) && ($params['code'] != '')) {
                $params["openId"] = $this->_getopenid($params["code"]);
            }
        }
        log_message("info","jumphome:".print_r($params,true));
        if(""!=$params["openId"]) {
        	$this->sessioninfo['openId'] = $params["openId"];
	        $this->session->set_userdata('sessioninfo', $this->sessioninfo);
	        //登录
	        $this->load->model('public/login_model', 'dba');
	        $this->dba->checkLogin($params);
        }
        $this->load->database();
        $this->db->where("type","字典类型");
        $this->db->where("name","缓存更新");
        $query = $this->db->get("sys_dictdata");
        $num = 1;
        if($query->num_rows()>0){
            $query = $query->row_array();
            $num = $query["value"];
        }
        redirect(base_url('/html/home.html?a='.$num));
    }


    // 访问方式： public/nhportal/adduser?data={"callback":"bb","loginId":"test","password":"123456","tpassword":"123456","realName":"测试","mobilePhone":"138XXXXXXXX","code":1234}
    public function adduser(){
        $params = $this->input->post_get(NULL,TRUE);
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        $callback = "";
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $data["callback"] = $callback;
        if (!isset($params['mobilePhone'])) { self::_resultOut($data,"没有设定手机号码！"); }
        if (!isset($params['loginId'])) {self::_resultOut($data,"没有设定用户名！"); }
        if (!isset($params['password'])) {self::_resultOut($data,"没有设定用户密码！");}
        if (!isset($params['tpassword'])) {self::_resultOut($data,"没有设定用户确认密码！！");}
        if (!isset($params['realName'])) {self::_resultOut($data,"没有设定用户姓名！");}
        if (!isset($params['code'])) {self::_resultOut($data,"没有设定验证码！");}
        if ($params['tpassword']!=$params['password']) {self::_resultOut($data,"两次密码不一致！");}

        $config = $this->config->item('cfg-system');
        $config = $config['uc'];

        //判断验证码是否正确
        if(!isset($this->sessioninfo['sendcode'])||!(isset($this->sessioninfo['sendexpiretime']))||(time()>$this->sessioninfo['sendexpiretime'])) {
            self::_resultOut($data,"验证码已过期！请重新获取！");
        }
        $code = $this->sessioninfo['sendcode'];
        if($code!=$params['code']) {
            self::_resultOut($data,"验证码不正确！");
        }
        //删除session中的验证码
        unset($this->sessioninfo['sendexpiretime']);
        unset($this->sessioninfo['sendcode']);
        $this->session->set_userdata('sessioninfo', $this->sessioninfo);

        //判断登录账号是否存在
        $postdata["loginID"] = $params['loginId'];
        $postdata = http_build_query($postdata);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($config['getUserByLoginID'], false, $context);
        $result = str_replace("GBK","UTF-8",$result);
        $parse = (array)(simplexml_load_string($result));
        if(!isset($parse["@attributes"])){self::_resultOut($data,"服务器繁忙，请稍后再试！");}
        if(1==$parse["@attributes"]["success"]){
            self::_resultOut($data,"用户名已存在！");
        }
        //判断登录账号是否存在--end

        $_xml = "<?xml version=\"1.0\" encoding=\"GBK\"?><request method=\"add\"><user><id></id><loginId>".$params['loginId']."</loginId><password>".md5($params['password'])."</password><realName>".$params['realName']."</realName><isUsable>1</isUsable><mobilePhone>".$params['mobilePhone']."</mobilePhone><createDate>".date("Y-m-d H:i:s")."</createDate></user></request>";
        $postdata = array();
        $postdata['data'] = $_xml;
        $postdata = http_build_query($postdata);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($config['addOrUpdateUser'], false, $context);
        log_message("info","parse:".print_r($this->_xmlToArray($result),true));
        $result = str_replace("GBK","UTF-8",$result);
        $parse = (array)(simplexml_load_string($result));
        if(!isset($parse["@attributes"])){self::_resultOut($data,"服务器繁忙，请稍后再试！");}
        if(0==$parse["@attributes"]["success"]){self::_resultOut($data,"注册失败！");}
        $data["success"] = true;
        $data["msg"] = "注册成功！";
        $data["returnValue"] = array("code"=>'0001',"des"=>"注册成功！");
        $user = (array)($parse["user"]);
        //插入nh_user表
        $this->load->database();
        $insert = array();
        $insert["userNo"] = $params['loginId'];
        $insert["userAccount"] = $params['realName'];
        if(isset($this->sessioninfo['openId'])&&""!=$this->sessioninfo['openId'])
            $insert["openId"] = $this->sessioninfo['openId'];
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['lastIp'] = $this->input->server('REMOTE_ADDR');
        $insert['lastTime'] = Date('Y-m-d H:i:s');
        $insert['modifyuser'] = 'sys';
        $insert['Id'] = $user["id"];
        $this->db->insert("nh_user",$insert);
        $this->sessioninfo['isLogin'] = 1;
        $this->sessioninfo['userId'] = $user["id"];
        $this->session->set_userdata('sessioninfo', $this->sessioninfo);
        log_message('debug', 'login info->' . print_r($this->sessioninfo, true));
        unset($data["callback"]);
        $data = json_encode($data);
        $data = $callback.'('.$data.')';
        echo $data;
    }

    // 访问方式： public/nhportal/sendmessage?data={"callback":"bb","phone":"182XXXXXX"}
    public function sendmessage(){
        $params = $this->input->post_get(NULL,TRUE);
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        if (!isset($params['phone'])) {return array('success' => false, 'msg' => '没有设定手机号码！'); }
        $data = array();
        $code = rand(1000,9999);
        $sysconfig = $this->config->item('cfg-system');
        $sysconfig = $sysconfig['sendsms'];
        $postdata = array();
        $postdata["sname"] = $sysconfig['username'];
        $postdata["spwd"] = $sysconfig['password'];
        $postdata["scorpid"] = $sysconfig['scorpid'];
        $postdata["sphones"] = $params["phone"];
        $postdata["smsg"] = '【品质全球】验证码:'.$code.'，请于5分钟内正确输入！';
        $postdata = http_build_query($postdata);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        log_message("info","send message:".print_r($params,true)."--->ip:".$this->input->server('REMOTE_ADDR')."--->time:".Date('Y-m-d H:i:s'));
        $context = stream_context_create($options);
        file_get_contents($sysconfig['url'], false, $context);
        $this->sessioninfo['sendcode'] = $code;
        $this->sessioninfo['sendexpiretime'] = strtotime("+10 minutes");
        $this->session->set_userdata('sessioninfo', $this->sessioninfo);
        $data['returnValue'] = array("code"=>"0001","des"=>"");
        $data = json_encode($data);
        $data = $callback.'('.$data.')';
        echo $data;
    }

    /**
     * 	作用：将xml转为array
     */
    private function _xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    // 访问方式： public/nhportal/login?data={"callback":"bb","loginId":"test","password":"123456"}
    public function wxshoplogin() {
        $params = $this->input->post_get(NULL,TRUE);
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
		//log_message("info","wxshoplogin data:".print_r($data,true));
        $data = array();
        $data["callback"] = $params['callback'];
        if (!isset($params['loginId'])) {self::_resultOut($data,"没有设定用户名！"); }
        if (!isset($params['password'])) {self::_resultOut($data,"没有设定密码！"); }
        //鍒ゆ柇鐧诲綍璐﹀彿鏄惁瀛樺湪
        $this->load->database();
        $this->db->where("userAccount",$params["loginId"]);
        $query = $this->db->get("nh_user");
        $user = $query->row_array();
        $row = $query->num_rows();
        if($row < 1){
            self::_resultOut($data,"用户名不存在");
        } else {
            //判断是否登录成功
            if(strtoupper(md5($params["password"]))!=strtoupper($user["passwd"])) {
                self::_resultOut($data,"用户或密码错误！");
            }
            $this->sessioninfo['isLogin'] = 1;
            $this->sessioninfo['userId'] = $user['Id'];
            $this->session->set_userdata('sessioninfo', $this->sessioninfo);
            log_message('debug', 'login info->' . print_r($this->sessioninfo, true));
            $data['returnValue'] = array("code"=>"0001","des"=>"登录成功！");
            $data = json_encode($data);
            $data = $callback.'('.$data.')';
            echo $data;
        }
    }

    public function jump($url) {
        $params = $this->input->post_get(NULL, TRUE);
        unset($params['key']);
        $str = "";
        foreach ($params as $key => $item) {
            $str .= '&' . $key . '=' . $item;
        }
        if (strlen($str) > 0) {
            $url .= '.html?' . substr($str, 1);
        } else {
            $url .= '.html';
        }
        $this->_jump('/html/' . $url);
    }

    private function _arrtoxml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    private function _checkSign($tmpData)
    {
        $tmp_sign = $tmpData['sign'];
        unset($tmpData['sign']);
        $sign = $this->getSign($tmpData);
        if ($tmp_sign == $sign) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 	作用：生成签名
     */
    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $sysconfig = $this->config->item('cfg-system');
        $sysconfig = $sysconfig['wxshop'];
        $String = $String."&key=".$sysconfig['key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = null;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    private function _resultOut($data,$msg=""){
        if("" != $msg) {
            $data["code"] = '1001';
            $data["msg"] = $msg;
            $data["success"] = false;
            $data["returnValue"] = array("code"=>'1001',"des"=>$msg);
        }
        $callback = $data["callback"];
        unset($data["callback"]);
        $data = json_encode($data);
        $data = $callback.'('.$data.')';
        echo $data;
        exit;
    }
}
