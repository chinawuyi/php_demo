<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH."libraries/Frontpage_Controller.php";

class User extends Frontpage_Controller {

    public function __construct()
    {
            $this->debugflag = 0;
            parent::__construct();
    }
	
    // 访问方式： front/user/detaildata?data={"callback":"bb"}
    public function detaildata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['user']= $this->dba->detaildata($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
 
    // 访问方式： front/user/mydata?data={"callback":"bb"}
    public function mydata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['user']= $this->dba->mydata($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    
    // 访问方式： front/user/commentdata?data={"callback":"bb","orderNo":"OD20151210071736-0001"}
    public function commentdata(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
            {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        if (!isset($params['orderNo'])) { $this->errorout($params, $callback, '9002', '参数错误'); }
        $data = array();
        $this->load->model($this->modelpath,'dba');
        $params['userId'] = $this->sessioninfo['userId'];
        $data['user']= $this->dba->commentdata($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式： front/user/signin?data={"callback":"bb","action":"获取,提交"}
    public function signin(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        if (!isset($params['action'])) { $this->errorout($params, $callback, '2001', '没有设定操作类型'); }
        $data = array();
        $this->load->model($this->modelpath,'dba');
        if(!isset($params['userId']))
            $params['userId'] = $this->sessioninfo['userId'];
        $data['signinfo']= $this->dba->signin($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
    // 访问方式： front/user/pointlist?data={"callback":"bb"}
    public function pointlist(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $data = array();
        $this->load->model($this->modelpath,'dba');
        if(!isset($params['userId']))
            $params['userId'] = $this->sessioninfo['userId'];
        $data['signinfo']= $this->dba->pointlist($params);
        $data['userpoint'] = $this->dba->userpoint($params);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式： front/user/getticket?data={"callback":"bb"}
    public function getticket(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $url = 'http://abc.qy.vfengche.cn/AbcCompany/home/getTicket';
        $data['ticket'] = file_get_contents($url);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式： front/user/getticketby?data={"callback":"bb"}
    public function getticketby(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $secret = "76e3d2f5f4213141f193b94a913c32c5";
        $appid = "wx5025c10f602da57e";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $token = file_get_contents($url);
        $token = json_decode($token,true);
        log_message("info","access token:".print_r($token,true));
        if(isset($token["errcode"])) {
            $this->errorout($params, $callback, '2001',$token["errcode"].':'.$token["errmsg"]);
        }
        $token = $token["access_token"];
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
        $js_ticket = file_get_contents($url);
        $js_ticket = json_decode($js_ticket,true);
        log_message("info","js token:".print_r($token,true));
        if(isset($js_ticket["errcode"])&&0!=$js_ticket["errcode"]) {
            $this->errorout($params, $callback, '2001',$js_ticket["errcode"].':'.$js_ticket["errmsg"]);
        }
        $ticket = $js_ticket["ticket"];
        $data['ticket'] = $ticket;
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }

    // 访问方式： front/user/logout?data={"callback":"bb"}
    public function logout(){
        $params = $this->input->post_get(NULL,TRUE);
        $this->cachetime = 0;
        $this->outCache($params);
        $cacheparams = $params;
        if (isset($params['data']))
        {$params = json_decode($params['data'],true);}
        else {
            show_404();
        }
        if (isset($params['callback'])){
            $callback = $params['callback'];
        }
        else { $callback = 'test';}
        $this->sessioninfo['isLogin'] = 0;
        unset($this->sessioninfo['userId']);
        $this->session->set_userdata('sessioninfo', $this->sessioninfo);
        $data['callback'] = $callback;
        $this->jsonOut($cacheparams,$data,!$this->debugflag);
    }
}
