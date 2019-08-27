<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Writecity extends CI_Controller {

    public function __construct()
    {
            parent::__construct();
            $this->load->database();
    }
    public function writejson()
    {
         $query = $this->db->get('nh_provincecityjson_copy');
         $json = $query->result_array();
         $this->load->library('Recursivejson');
         $this->recursivejson->initialize(array('data'=>$json));
         $json2 = $this->recursivejson->recursiveout();
         $str = "var cityjson = ".$json2;
         $this->load->helper('file');
         if(!write_file('./html/resources/cityjson.js', $str)){
            echo 'error';
         }
         else{
            echo 'success';
         }
    }
}
