<?php
class Acl 
{
        private $CI;
	private $url_model;
	private $url_method;
	private $url_param;
	private $acl;
	
	function __construct()
	{
                $this->CI =& get_instance();
		header("Content-Type:text/html;charset=utf-8");
		$this->CI->config->load('cfg-system',true);
		$this->acl = $this->CI->config->item('cfg-system'); 
		$this->acl = $this->acl['acl'];
		$this->CI->load->helper('url');
		$this->url_model = $this->CI->uri->segment(1,'home');
		$this->url_method = $this->CI->uri->segment(2,'index');
		$this->url_param = $this->CI->uri->segment(3,'index');
 		log_message('debug','acl model : uri -> '.$this->url_model.'/'.$this->url_method.'/'.$this->url_param);
	}
	
	public function filter(){
                if (!$this->acl['active']) {return;}
		if ($this->url_model == 'public') {return;}
		if (in_array($this->url_model,$this->acl['ignore'])) {return;}
 		if ($this->acl['login'] == $this->url_model.'/'.$this->url_method) {return;}   
                $this->CI->load->library('session');
		$user = $this->CI->session->userdata($this->acl['sessionname']);
 		if (empty($user[$this->acl['user_tag']])){
			redirect($this->acl['login']);
		}
		else {
                    $action = $this->url_model.'/'.$this->url_method;
                    if (in_array($action,$this->acl['fullright'])){return;}
                    $rights = $user[$this->acl['right_tag']];
                    $rights = json_decode($rights,true);
                    foreach($rights as $val){
                        if ($action === $val['modulename'].'/'.$val['controllername']){return; }
                    }
                    echo "没有权限!";
                    exit;	
		}
	}
}
