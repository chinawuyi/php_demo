<?php
class Comment_model extends CI_Model {

    private $userid;

    public function __construct()
    {
            parent::__construct();
            $this->load->database();
    }

    public function json_data($params)
    {
        $data = array();
        $data = self::basedata($params);
        $sql = "SELECT npc.Id, nu.userName as name,npc.content,npc.star,npc.comTime "
                . "FROM nh_product_comment npc,nh_user nu "
                . "WHERE npc.userId=nu.Id AND nu.IS_DISABLED=0 AND npc.IS_DISABLED=0 "
                . "AND npc.prodId=".$params['productId'];
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $data['list'] = $result;
        foreach($data['list'] as $key=>$item){
            $sql  = "SELECT nu.userName as name,npc.context as content "
                    . "FROM nh_product_comment_reply npc,nh_user nu "
                . "WHERE npc.userId=nu.Id AND nu.IS_DISABLED=0 AND npc.IS_DISABLED=0 "
                . "AND npc.commentId=".$item['Id']." ORDER BY npc.replytime DESC";
            $query = $this->db->query($sql);
            $data['list'][$key]['reply'] = $query->result_array();
            $data['list'][$key]['replyNum'] = sizeof($data['list'][$key]['reply']);
            $sql  = "SELECT npc.img  FROM nh_product_comment_pic npc "
                . "WHERE npc.IS_DISABLED=0 AND npc.commentId=".$item['Id'];
            $query = $this->db->query($sql);
            $data['list'][$key]['images'] = $query->result_array();
        }
        return $data;
    }
    
    public function basedata($params)
    {
        $sql = "SELECT star,count(*) as num FROM nh_product_comment WHERE prodId=".$params['productId']." AND IS_DISABLED=0 GROUP BY star";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $data = array();
        $data['totalNum'] = 0;
        $data['goodNum'] = 0;
        $data['normalNum'] = 0;
        $data['badNum'] = 0;
        foreach($result as $item){
            $data['totalNum'] += $item['num'];
            if ($item['star'] === '5') $data['goodNum'] += $item['num'];
            if ($item['star'] === '4') $data['goodNum'] += $item['num'];
            if ($item['star'] === '3') $data['normalNum'] += $item['num'];
            if ($item['star'] === '2') $data['normalNum'] += $item['num'];
            if ($item['star'] === '1') $data['badNum'] += $item['num'];
        }
        $sql = "SELECT count(*) as num FROM nh_product_comment_pic WHERE prodId=".$params['productId']." AND IS_DISABLED=0";
        $query = $this->db->query($sql);
        $result = $query->row_array();
        $data['photoNum'] = $result['num'];
        return $data;
    }
    
    public function addreply($params){
        $query = $this->db->get_where('nh_product_comment',array('Id' => $params['commentId']));
        if ( 0 === $query->num_rows() ){
            $result['code'] = 1001;
            $result['msg'] ='没有找到该评价';
            return $result;
        }
        $data = $query->result_array();
        $data = $data[0];
        $insert = array();
        $insert['prodId'] = $data['prodId'];
        $insert['userId'] = $params['userId'];
        $insert['commentId'] = $params['commentId'];
        $insert['context'] = $params['content'];
        $insert['replytime'] = Date('Y-m-d H:i:s');
        $insert['IS_DISABLED'] = 0;
        $insert['createdatetime'] = Date('Y-m-d H:i:s');
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['createuser'] =  $params['userId'];
        $insert['modifyuser'] =  $params['userId'];
        $this->db->insert('nh_product_comment_reply',$insert);
        $result['code'] = 0;
        $result['msg'] ='回复成功';
        return $result;    
        }

    public function adddata($params){
        $insert = array();
        $insert['prodId'] = $params['productId'];
        $insert['userId'] = $params['userId'];
        $insert['orderNo'] = $params['orderNo'];
        $insert['star'] = $params['star'];
        $insert['content'] = $params['content'];
        $insert['comTime'] = Date('Y-m-d H:i:s');
        $insert['IS_DISABLED'] = 0;
        $insert['createdatetime'] = Date('Y-m-d H:i:s');
        $insert['modifydatetime'] = Date('Y-m-d H:i:s');
        $insert['createuser'] =  $params['userId'];
        $insert['modifyuser'] =  $params['userId'];
        $query = $this->db->insert('nh_product_comment',$insert);
        $insertid = $this->db->insert_id();
        foreach($params['pics'] as $item){
            $insert = array();
            $insert['prodId'] = $params['productId'];
            $insert['userId'] = $params['userId'];
            $insert['commentId'] = $insertid;
            $insert['img'] = $item;
            $insert['uploadtime'] = Date('Y-m-d H:i:s');
            $insert['IS_DISABLED'] = 0;
            $insert['createdatetime'] = Date('Y-m-d H:i:s');
            $insert['modifydatetime'] = Date('Y-m-d H:i:s');
            $insert['createuser'] =  $params['userId'];
            $insert['modifyuser'] =  $params['userId'];
            $this->db->insert('nh_product_comment_pic',$insert);
        }
        $this->db->query("UPDATE nh_user SET waitevaluate=waitevaluate-1 WHERE Id=".$params['userId']);
        $this->db->query("UPDATE nh_order SET commentstatus='已评价' WHERE orderNo='".$params['orderNo']."'");
        $result['code'] = 0;
        $result['msg'] ='评价成功';
        return $result;    
        }

}
