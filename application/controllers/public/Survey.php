<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Survey extends CI_Controller {

    public function __construct()
    {
            parent::__construct();
            $this->load->helper('url');
            $this->load->library('session');
            $this->sessioninfo = $this->session->userdata('sessioninfo');
            $this->config->load('cfg-system',true);
            $this->abccall = $this->config->item('cfg-system'); 
            $this->abccall = $this->abccall['abccall'];
            $this->url_module = $this->uri->segment(1,$this->router->default_controller);
            $this->url_model = $this->uri->segment(2,'index');
            $this->url_method = $this->uri->segment(3,'index');
    }

    public function index(){
        $params = $this->input->post_get(NULL,TRUE);
        if ((!isset($params['key']))||($params['key'] === '')) {
            redirect(base_url('/html/login.html'));
        }
        // 检查是否已经登录过的用户
        $this->load->model('public/login_model','dba');
        if ($this->dba->checkLogin($params)){
            redirect(base_url('/survey/'));
        }
        else {
            if ($this->dba->auth($params)){
                redirect(base_url('/survey/'));
            }
            else {
                redirect(base_url('/html/login.html'));
            }
        }
    }

    public function addsurvey()
    {
        if((!isset($this->sessioninfo['isLogin']))||($this->sessioninfo['isLogin'] === 0)){
            echo "noright";exit;
        }
        $qlid = 1;  //本次调查问卷id
        $params = $this->input->post_get('data');
        $params = json_decode($params,true);
        date_default_timezone_set('Asia/Shanghai');
        $time = $_SERVER['REQUEST_TIME'];
        $time = Date('Y-m-d H:i:s',$time);
        $this->load->database();
        $userId = $this->sessioninfo['userNo'];
        $insert = array();
        $insert['pid'] = 0;
        $insert['qlid'] = $qlid;
        $insert['userId'] = $userId;
        $insert['ctime'] = $time;
        $this->db->insert('nh_questionnaire',$insert);
        $insert['pid'] = $this->db->insert_id();
        $inserts = array();
        foreach($params as $row){
            $insert['aid'] = null;
            $insert['msg'] = null;
            $insert['qid'] = $row['qid'];
            if (isset($row['msg'])) {
                $insert['msg'] = $row['msg'];
            }
            if (isset($row['answer'])){
                $aid = explode('|',rtrim($row['answer'],'|'));
                foreach($aid as $val){
                    $insert['aid'] = $val;
                    array_push($inserts,$insert);
                }
            }
            else{
                array_push($inserts,$insert);
            }
        }
        if ($this->db->insert_batch('nh_questionnaire',$inserts)){
            echo 'success';
        }
        else {
            echo 'error';
        }
    }
 
}
