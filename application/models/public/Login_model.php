<?php

class Login_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
        $this->sessioninfo = $this->session->userdata('sessioninfo');
        $this->config->load('cfg-system', true);
        $this->abccall = $this->config->item('cfg-system');
        $this->abccall = $this->abccall['abccall'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 根据传入id，确认是否直接登录
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[key]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return boolean 是否成功登录
     */
    public function checkLogin($params) {
    	if(""==$params['openId'])
    		return false;
        $sql = "SELECT * FROM nh_user WHERE openId='" . $params['openId'] . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $insert = array();
            $insert['modifydatetime'] = Date('Y-m-d H:i:s');
            $insert['lastIp'] = $this->input->server('REMOTE_ADDR');
            $insert['lastTime'] = Date('Y-m-d H:i:s');
            $insert['modifyuser'] = 'sys';
            $this->db->where('Id', $result['Id']);
            $this->db->update('nh_user', $insert);
            $this->sessioninfo['isLogin'] = 1;
            $this->sessioninfo['userId'] = $result['Id'];
            $this->session->set_userdata('sessioninfo', $this->sessioninfo);
            log_message('debug', 'login info->' . print_r($this->sessioninfo, true));
            //更新用户数据
            $loginId = $result['userAccount'];
            $this->_updateUser($loginId);
            return true;
        } else {
            return false;
        }
    }

    private function _updateUser($loginId) {
        $config = $this->config->item('cfg-system');
        $config = $config['uc'];
        //判断登录账号是否存在
        $postdata["loginID"] = $loginId;
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
        if(!isset($parse["@attributes"])){
            return;
        }

        if(0==$parse["@attributes"]["success"]){
            return;
        } else {
            log_message("info", "user:" . print_r($parse, true));
            $user = (array)$parse["user"];
            if (0 == $user["isUsable"]) {
                return;
            }
            //更新user数据
            $update = array();
            if ("" != $user["realName"])
                $update["userName"] = (string)$user["realName"];
            $update["mobile"] = $user["mobilePhone"];
            $update["point"] = $user["deposit"];
            $update['modifydatetime'] = Date('Y-m-d H:i:s');
            $update['modifyuser'] = 'sys';
            //更新用户
            $this->db->where("userAccount", $loginId);
            $this->db->update("nh_user", $update);
        }
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * 通过白名单接口进行验证
     * @param mixed $params <p>传入的参数对象</p>
     * <tr valign="top">
     * <td>参数中的对象</td>
     * <td>说明</td></tr>
     * <tr valign="top">
     * <td><b>params[key]</b></td>
     * <td>登录用户表的<i>Id</i></td></tr>
     * @return boolean 是否成功登录
     */
    public function auth($params){
        // 通过白名单接口进行验证
        $data = 'userName=' . $this->abccall['WJCallName'] . '&userPwd=' . $this->abccall['WJCallPwd'] .
                '&userId=' . $params['key'];
        // 对data进行公私钥加密，openssl
        // 乐享私钥
        $pi_key = openssl_pkey_get_private(file_get_contents(APPPATH . '/libraries/rsa/rsa_private_key.pem'));
        //print_r(openssl_pkey_get_details($pi_key));exit;
        // 农行公钥
        $pu_key = openssl_pkey_get_public(file_get_contents(APPPATH . '/libraries/rsa/pub.crt'));
        //print_r(openssl_pkey_get_details($pu_key));exit;
        $encrypted = "";
        openssl_public_encrypt($data, $encrypted, $pu_key);
        $encrypted = urlencode(base64_encode($encrypted));
        //print_r($encrypted);exit;
        $url = $this->abccall['ABChinaUserUrl'] . $encrypted;
        log_message('debug', '白名单接口发送url :' . $url);
        //print_r($url);exit;
        include_once APPPATH . "libraries/Requests.php";
        Requests::register_autoloader();
        $response = Requests::get($url);
        log_message('debug', '白名单接口接收密文 :' . $response->body);
        //print_r($response->body);exit;
        // 接口报错
        if (substr($response->body, 0, 5) === '<?xml') {
            print_r('白名单接口报错,错误信息-->'.$response->body);
            exit;
        }
        // 对result进行解密
        $decrypted = "";
        openssl_private_decrypt(base64_decode($response->body), $decrypted, $pi_key);
        if ($decrypted === '') {
            return false;
        }
        try {
            if ($decrypted === '40004') {
                return false;
            }
            $list = explode('&', $decrypted);
            $data = array();
            foreach ($list as $val) {
                $ll = explode('=', $val);
                $data[$ll[0]] = $ll[1];
            }
            if ($data['id'] === '') {
                return false;
            } else {
                $insert = array();
                $insert['userNo'] = $data['id'];
                $sql = "SELECT * FROM nh_user WHERE userNo='" . $insert['userNo'] . "'";
                $query = $this->db->query($sql);
                $insert['lastIp'] = $this->input->server('REMOTE_ADDR');
                $insert['lastTime'] = Date('Y-m-d H:i:s');
                if ($query->num_rows() === 0) {
                    $insert['userName'] = $data['userName'];
                    $insert['userAccount'] = $data['mobile'];
                    $insert['passwd'] = MD5('123456');
                    $insert['gender'] = $data['gender'];
                    $insert['COMPANY'] = $data['departmentName'];
                    $insert['mobile'] = $data['mobile'];
                    $insert['createdatetime'] = Date('Y-m-d H:i:s');
                    $insert['createuser'] = 'sys';
                    $this->db->insert('nh_user', $insert);
                    $this->sessioninfo['userId'] = $this->db->insert_id();
                } else {
                    $result = $query->row_array();
                    $this->sessioninfo['userId'] = $result['Id'];
                    $insert['modifydatetime'] = Date('Y-m-d H:i:s');
                    $insert['modifyuser'] = 'sys';
                    $this->db->where('userNo', $insert['userNo']);
                    $this->db->update('nh_user', $insert);
                }
                $this->sessioninfo['isLogin'] = 1;
                $this->session->set_userdata('sessioninfo', $this->sessioninfo);
                log_message('debug', 'login info->' . print_r($this->sessioninfo, true));
                return true;
            }
        } catch (Exception $e) {
            log_message('debug', '白名单接口错误信息 :' . $e->getMessage());
            return false;
        }
    }
}
