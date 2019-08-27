<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Listdetail_Controller extends CI_Controller {

    protected $debugflag = 1;                   //测试标识 0 为测试， 1 为运行
    protected $searchflag = 1;                  //searcharea creat, 搜索区生成方式, 1为根据定义生成, 0为自己生成
    protected $rightflag = 1;                   // rightflag 是否进行按钮权限校验, 1 为校验,0 为不校验
    protected $sessioninfo = array();
    protected $url_model;
    protected $url_method;
    protected $url_module;
    protected $sysconfig;
    protected $gloablconfig;
    protected $menudefine;
    protected $griddefine;
    protected $listurl;
    protected $detailurl;
    protected $idmode = 'ID';   // 表关键字模式  ID 为自增量类型， UUID 为guid模式
    protected $deletemode = "UPDATE"; // 删除方式 DEL 为直接删除 ,UPDATE 为软删除 
    protected $deletefield = "IS_DISABLED";

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('PRC');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('ousuclass');
        $this->config->load('cfg-system', true);
        $this->sysconfig = $this->config->item('cfg-system');
        $this->globalconfig = $this->sysconfig['seo'];
        $this->sysconfig = $this->sysconfig['acl'];
        // 加载session对象
        $this->sessioninfo = $this->session->userdata('sessioninfo');
        // 加载helper
        $this->load->helper('bootstrap/html');
        $this->load->helper('bootstrap/table');
        $this->load->helper('bootstrap/tools');
        // 定义模块和动作
        $this->url_module = $this->uri->segment(1, $this->router->default_controller);
        $this->url_model = $this->uri->segment(2, 'index');
        $this->url_method = $this->uri->segment(3, 'index');
        // 加载 界面定义
        $this->load->model('interface/' . $this->url_module . '_' . $this->url_model, 'interface');
        $this->menudefine = $this->interface->getMenuDefine();
        $this->griddefine = $this->interface->getGridDefine();
        $this->listurl = $this->url_module . '/' . $this->url_model . '_datalist.php';
        $this->detailurl = $this->url_module . '/' . $this->url_model . '_datadetail.php';
        // 按钮权限控制
        //print_r($this->sessioninfo);exit;
        if ($this->rightflag == 1) {
            $rights = json_decode($this->sessioninfo['rights'], true);
            foreach ($rights as $item) {
                if (($item['modulename'] === $this->url_module) && ($item['controllername'] === $this->url_model)) {
                    $list = explode(',', $item['btnrights']);
                    foreach ($this->menudefine['dispbutton'] as $key => $val) {
                        if (!in_array($val['id'], $list)) {
                            unset($this->menudefine['dispbutton'][$key]);
                        }
                    }
                }
            }
        }
        // 加载数据库定义
        $file = APPPATH . 'models/' . $this->url_module . '/db_' . $this->url_model . ".php";
        $classname = 'db_' . $this->url_model;
        if (file_exists($file)) {
            $this->load->model($this->url_module . '/' . $classname, 'mydb', TRUE);
        } else {
            $this->load->model('basedao_model', 'mydb', TRUE);
        }
        $this->mydb->setTable($this->griddefine['table']);
        $this->mydb->setKeyId($this->griddefine['keyid']);
        $this->load->model('dball', 'dball', TRUE);
    }

    /*
     * AJAX 返回 跟踪信息,直接返回了
     */

    protected function _JSONRESULT($message = '错误信息', $ok = false) {
        $result = array();
        $result["success"] = $ok;
        $result["msg"] = print_r($message, true);
        $result["obj"] = "";
        echo json_encode($result);
        exit;
    }

    /*
     * 定义基本的SQL查询语句
     */

    protected function _getListSql() {
        $sql = "SELECT * FROM " . $this->mydb->getTable();
        if ($this->deletemode === 'UPDATE')
            $sql .= " WHERE " . $this->deletefield . " = 0";
        log_message('debug', '_getListSql()->return : ' . print_r($sql, true));
        return $sql;
    }

    /*
     *  获得前期保存的查询条件
     */

    final protected function _getSearch($sql) {
        log_message('debug', '_getSearch()->params(sql) : ' . print_r($sql, true));
        $arrSearch = array();
        $menudefine = $this->menudefine;
        foreach ($menudefine["searchdefine"]["items"] as $arr) {
            if (($arr["type"] == "text") && (!($this->input->post($arr["name"]) === False))) {
                if ((substr($arr["name"], 0, 2) != "S_") && (substr($arr["name"], 0, 2) != "E_")) {
                    $arrSearch["%" . $arr["name"]] = "%" . $this->input->post($arr["name"]);
                } else {
                    if (substr($arr["name"], 0, 2) == "S_" && $this->input->post($arr["name"])) {
                        $tmp = substr($arr["name"], 2);
                        if ($_POST["S_" . $tmp])
                            $arrSearch[">" . $tmp] = ">" . $_POST["S_" . $tmp];
                    }
                    if (substr($arr["name"], 0, 2) == "E_" && $this->input->post($arr["name"])) {
                        $tmp = substr($arr["name"], 2);
                        if ($_POST["E_" . $tmp])
                            $arrSearch["<" . $tmp] = "<" . $_POST["E_" . $tmp];
                    }
                }
            }
            if (($arr["type"] == "select") && (!($this->input->post($arr["name"]) === "")))
                $arrSearch["=" . $arr["name"]] = "=" . $this->input->post($arr["name"]);
            if ($arr["type"] == "date") {
                if ((substr($arr["name"], 0, 1) != "S") && (substr($arr["name"], 0, 1) != "E")) {
                    $arrSearch["=" . $arr["name"]] = "=" . $this->input->post($arr["name"]);
                } else {
                    if (substr($arr["name"], 0, 1) == "S" && $this->input->post($arr["name"])) {
                        $tmp = substr($arr["name"], 2);
                        if ($_POST["S_" . $tmp])
                            $arrSearch[">" . $tmp] = ">" . $_POST["S_" . $tmp];
                    }
                    if (substr($arr["name"], 0, 1) == "E" && $this->input->post($arr["name"])) {
                        $tmp = substr($arr["name"], 2);
                        if ($_POST["E_" . $tmp])
                            $arrSearch["<" . $tmp] = "<" . $_POST["E_" . $tmp];
                    }
                }
            }
        }

        //检查是否存在Filter条件
        $sessionKey = 'Filter_' . $this->url_module . '_' . $this->url_model;
        $session = $this->session->userdata($sessionKey);
        if (!empty($session)) {
            $arrFilter = $this->session->userdata($sessionKey);
            foreach ($arrFilter as $key => $value)
                $arrSearch[$key] = $value;
        }
        //缓存搜索条件，用于分页
        $sessionKey = 'Search_' . $this->url_module . '_' . $this->url_model;
        $this->session->set_userdata($sessionKey, $arrSearch);
        log_message('debug', '_getSearch()->return : ' . print_r($arrSearch, true));
        return $this->_addWhere($sql, $arrSearch);
    }

    /*
     *  从session中提取查询条件
     */

    protected function _getSessionSearch() {
        $sessionKey = 'Search_' . $this->url_module . '_' . $this->url_model;
        $arrSearch = $this->session->userdata($sessionKey);
        if ($arrSearch == FALSE) {
            $arrTemp = "";
        } else {
            $arrTemp = array();
            foreach ($arrSearch as $key => $arr) {
                if (!(substr($arr, 1) === FALSE)) {
                    $S_or_E = substr($key, 0, 1);
                    if ($S_or_E === '>') {
                        $searchTerms = 'S_' . substr($key, 1);
                    } elseif ($S_or_E === '<') {
                        $searchTerms = 'E_' . substr($key, 1);
                    } else {
                        $searchTerms = substr($key, 1);
                    }
                    $arrTemp[$searchTerms] = substr($arr, 1);
                }
            }
        }
        log_message('debug', '_getSessionSearch()->return : ' . print_r($arrTemp, true));
        return json_encode($arrTemp);
    }

    /*
     *  提供列表页,明细页的下拉框数据准备
     */

    protected function _getGlobalData($data) {
        log_message('debug', '_getGlobalData()->params : ' . print_r($data, true));
        $data['url_module'] = $this->url_module;
        $data['url_method'] = $this->url_method;
        $data['url_model'] = $this->url_model;
        $data['menuinfo'] = $this->menudefine;
        $data['datagridinfo'] = $this->griddefine;
        $data['globalconfig'] = $this->globalconfig;
        $aoColumnDefs = array();
        $arr = array();
        $i = 1;
        foreach ($data['datagridinfo']['listfield'] as $item) {
            if (isset($item['order'])) {
                $val = array();
                $val['asSorting'] = $item['order'];
                $val['aTargets'] = array($i);
                array_push($aoColumnDefs, $val);
            }
            if (isset($item['type']) && ($item['type'] === 'hide')) {
                $val = array();
                $val['bVisible'] = false;
                $val['bSearchable'] = false;
                $val['aTargets'] = array($i);
                array_push($aoColumnDefs, $val);
            }
            $i++;
        }
        $data['aoColumnDefs'] = json_encode($aoColumnDefs);
        log_message('debug', '_getGlobalData()->return : ' . print_r($data, true));
        return $data;
    }

    /*
     * 在添加,修改提交前,增加公共修改字段
     */

    protected function _setAddModifyData($datainfo) {
        log_message('debug', '_setAddModifyData()->params : ' . print_r($datainfo, true));
        if ($this->url_method == 'create') {
            $datainfo["createdatetime"] = date('Y-m-d G:i:s');
            $datainfo["createuser"] = $this->sessioninfo['userId'];
            unset($datainfo['modifydatetime']);
            unset($datainfo['modifyuser']);
        } else {
            $datainfo["modifydatetime"] = date('Y-m-d G:i:s');
            $datainfo["modifyuser"] = $this->sessioninfo['userId'];
            unset($datainfo['createdatetime']);
            unset($datainfo['createuser']);
        }
        log_message('debug', '_setAddModifyData()->return : ' . print_r($datainfo, true));
        return $datainfo;
    }

    /*
     * 调整错误返回信息
     */

    protected function _ReturnMsg($data) {
        if (!$data['success'])
            if ($data['msg'] == "")
                $data['msg'] = '提交失败!';
        return $data;
    }

    /*
     * 在页面输出前,最后调整数据,
     * 根据$this->method来判断是哪个方法调用
     */

    protected function _beforeMethod($type, $data) {
        log_message('debug', '_beforeMethod()->params : ' . print_r($data, true));
        return $data;
    }

    /*
     * 在数据操作(create,update,delete)后,切换页面前调用
     * type = create,update,delete
     */

    protected function _afterDBAct($type, $result) {
        log_message('debug', '_afterDBAct()->type : ' . print_r($type, true));
        if (!isset($result['obj'])) {
            return $result;
        }
        if (!isset($result['obj'][$this->griddefine['keyid']])) {
            return $result;
        }
        if (!isset($this->saverecord)){
            return $result;
        }
        $this->newrecord = $this->mydb->get($result['obj'][$this->griddefine['keyid']]);
        if (!$this->newrecord['success']) {
            return $result;
        }
        $str = "记录编号【" . $result['obj'][$this->griddefine['keyid']] . "】,";
        if ($type == 'create') {
            $str .= "新增记录,";
            foreach ($this->newrecord['obj'] as $key => $item) {
                $str .= "字段【" . $key . "】=【" . $item . "】,";
            }
        }
        if ($type == 'edit') {
            $str .= "编辑记录,";
            foreach ($this->newrecord['obj'] as $key => $item) {
                if ($key == 'modifydatetime') {
                    continue;
                }
                if ($key == 'modifyuser') {
                    continue;
                }
                if ($key == 'createdatetime') {
                    continue;
                }
                if ($key == 'createuser') {
                    continue;
                }
                if ($this->saverecord['obj'][$key] <> $item) {
                    $str .= "字段【" . $key . "】从【" . $this->saverecord['obj'][$key] . "】修改为【" . $item . "】,";
                }
            }
        }
        if ($type == 'delete') {
            $str .= "删除记录,";
        }
        $insert = array();
        $insert['name'] = '后台编辑';
        $insert['table'] = $this->griddefine['table'];
        $insert['desc'] = $str;
        $insert['logTime'] = Date('Y-m-d H:i:s');
        $insert['operator'] = $this->sessioninfo['fullName'];
        $this->mydb->setTable('nh_log');
        $this->mydb->addData($insert);
        log_message('debug', '_afterDBAct()->params : ' . print_r($result, true));
        $this->mydb->setTable($this->griddefine['table']);
        return $result;
    }

    /*
     * 在数据操作(create,update,delete)前,切换页面前调用
     * type = create,update,delete
     */

    protected function _beforeDBAct($type, $result) {
        log_message('debug', '_beforeDBAct()->type : ' . print_r($type, true));
        if (isset($result[$this->griddefine['keyid']])) {
            $this->saverecord = $this->mydb->get($result[$this->griddefine['keyid']]);
        }
        log_message('debug', '_beforeDBAct()->params : ' . print_r($result, true));
        return $result;
    }

    /*
     * 添加查询条件
     */

    public function _addWhere($sql, $params, $filerflag = 0) {
        log_message('debug', '_addWhere()->params :sql = ' . print_r($sql, true));
        log_message('debug', '_addWhere()->params :params = ' . print_r($params, true));
        if ((sizeof($params) == 0) || ($params == False))
            return $sql;
        $where = "SELECT * FROM ($sql) t WHERE 1=1 ";
        foreach ($params as $key => $value) {
            if ((substr($value, 0, 1) == "%") && (substr($value, 1) != "" ))
                $where .= " and  " . substr($key, 1) . " like '%%" . substr($value, 1) . "%%' ";
            if ((substr($value, 0, 1) == "=") && (substr($value, 1) != "" ))
                $where .= " and  " . substr($key, 1) . " = '" . substr($value, 1) . "' ";
            if ((substr($value, 0, 1) == ">") && (substr($value, 1) != "" ))
                $where .= " and " . substr($key, 1) . " >= '" . substr($value, 1) . "' ";
            if ((substr($value, 0, 1) == "<") && (substr($value, 1) != "" ))
                $where .= " and " . substr($key, 1) . " <= '" . substr($value, 1) . "' ";
            if ((substr($value, 0, 1) == "!") && (substr($value, 1) != "" ))
                $where .= " and " . substr($key, 1) . " != '" . substr($value, 1) . "' ";
            if ((substr($value, 0, 1) == "~") && (substr($value, 1) != "" )) {
                $list = explode('|', substr($value, 1));
                $tmp = "";
                foreach ($list as $arr)
                    $tmp .= ";'" . $arr . "'";
                $tmp = substr($tmp, 1);
                $where .= " and " . substr($key, 1) . " not in (" . substr($value, 1) . ") ";
            }
        }
        $sql = $where;
        //print_r($sql);exit();
        log_message('debug', '_addWhere()->return : ' . print_r($sql, true));
        return $sql;
    }

    /*
     * 从POST数据中,根据数据库表字段返回数据对象
     */

    protected function _getRequestModel() {
        $sql = 'SHOW FULL FIELDS FROM ' . $this->mydb->getTable();
        $fieldlist = $this->mydb->find($sql);
        $datainfo = array();
        foreach ($fieldlist['obj'] as $arr)
            if (!($this->input->post($arr["Field"]) === null))
                $datainfo[$arr['Field']] = $this->input->post($arr["Field"]);
        log_message('debug', '_getRequestModel()->return : ' . print_r($datainfo, true));
        return $datainfo;
    }

    /*
     *  首页显示 
     */

    public function index() {
        $data = array();
        $data = $this->_getGlobalData($data);
        $sql = $this->_getListSql();
        $this->session->unset_userdata('Filter_' . $this->url_module . '_' . $this->url_model);
        $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
        $arr = $this->session->userdata($sessionKey);
        $data["iDisplayStart"] = 0;
        $data['searchdata'] = "";
        if (null != $arr && !$arr["isClear"]) {
            $data["iDisplayStart"] = $arr["page"];
            $data['searchdata'] = self::_getSessionSearch();
        } else {
            $this->session->unset_userdata('Search_' . $this->url_module . '_' . $this->url_model);
        }
        $this->session->unset_userdata('Page_' . $this->url_module . '_' . $this->url_model);
        $sql = "SELECT * FROM ($sql) t LIMIT 0,0";
        $query = $this->mydb->find($sql);
        $data['datalist'] = $query['obj'];
        if ($this->searchflag == 1) {
            $searchdefine = $data['menuinfo'];
            $data['searchArea'] = bs_searcharea('search', $searchdefine['searchdefine']);
        }
        if ($this->debugflag == 1)
            $this->load->view($this->listurl, $this->_beforeMethod('index', $data));
        else
            echo print_r($this->_beforeMethod('index', $data));
    }

    /*
     *  数据全部列表页
     */

    public function datalist() {
        $data = array();
        $data = $this->_getGlobalData($data);
        $sql = $this->_getSearch($this->_getListSql());
        $query = $this->mydb->find($sql);
        $data['datalist'] = $query['obj'];
        $data['searchdata'] = "";
        if ($this->debugflag == 1)
            $this->load->view('common/datalist.php', $this->_beforeMethod('datalist', $data));
        else
            echo print_r($this->_beforeMethod('datalist', $data));
    }

    /*
     *  选择 
     */

    public function choice() {
        $data = array();
        $data = $this->_getGlobalData($data);
        $sql = $this->_getListSql();
        $sql = "SELECT * FROM ($sql) t LIMIT 0,0";
        $query = $this->mydb->find($sql);
        $data['datalist'] = $query['obj'];
        $data['searchdata'] = "";
        if ($this->searchflag == 1) {
            $searchdefine = $data['menuinfo'];
            $data['searchArea'] = bs_searcharea('search', $searchdefine['searchdefine']);
        }
        if ($this->debugflag == 1)
            $this->load->view('common/choice.php', $this->_beforeMethod('choice', $data));
        else
            echo print_r($this->_beforeMethod('choice', $data));
    }

    /*
     * 查询页
     */

    public function search() {
        $data = array();
        $data = $this->_getGlobalData($data);
        $sql = $this->_getListSql();
        $sql = $this->_getSearch($sql);
        $query = $this->mydb->find($sql);
        $data['searchdata'] = self::_getSessionSearch();
        $data['datalist'] = "";
        if ($this->searchflag == 1) {
            $searchdefine = $data['menuinfo'];
            $data['searchArea'] = bs_searcharea('search', $searchdefine['searchdefine']);
        }
        $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
        $arr = $this->session->userdata($sessionKey);
        $data["iDisplayStart"] = 0;
        if (null != $arr && !$arr["isClear"]) {
            $data["iDisplayStart"] = $arr["page"];
        }
        if ($this->debugflag == 1)
            $this->load->view($this->listurl, $this->_beforeMethod('search', $data));
        else
            echo print_r($this->_beforeMethod('search', $data));
    }

    /*
     * Datatable 异步数据输出
     */

    public function page() {
        $sessionKey = 'Search_' . $this->url_module . '_' . $this->url_model;
        $arrSearch = $this->session->userdata($sessionKey);
        $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
        $arr = $this->session->userdata($sessionKey);
        if (isset($_GET['iDisplayStart'])) {
            $arr = array();
            $arr["isClear"] = true;
            $arr["page"] = $_GET['iDisplayStart'];
            $this->session->set_userdata($sessionKey, $arr);
        }
        $sql = $this->_getListSql();
        log_message('debug', 'core->page()->sql : ' . $sql);
        log_message('debug', 'core->page()->arrSearch : ' . print_r($arrSearch, true));
        $sql = $this->_addWhere($sql, $arrSearch);
        log_message('debug', 'core->page()->sql : ' . $sql);
        $griddefine = $this->griddefine;
        $aColumns = array($griddefine['keyid']);
        foreach ($griddefine['listfield'] as $arrTmp)
            array_push($aColumns, '`' . $arrTmp["fieldname"] . '`');
        $data = $this->mydb->pagingServer($aColumns, $sql);
        echo json_encode($this->_beforeMethod('page', $data['obj']));
    }

    /*
     * 添加数据
     */

    public function create() {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
            $arr = $this->session->userdata($sessionKey);
            $arr["isClear"] = false;
            $this->session->set_userdata($sessionKey, $arr);
            $data['editType'] = 'add';
            $data = $this->_getGlobalData($data);
            if ($this->debugflag == 1)
                $this->load->view($this->detailurl, $this->_beforeMethod('create', $data));
            else
                echo print_r($data);
        }
        else if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $datainfo = $this->_getRequestModel();
            if ($this->idmode == "UUID") {
                unset($datainfo['ID']);
                $this->load->helper('guid_helper');
                $datainfo[$this->mydb->getKeyId()] = guid();
            } else {
                unset($datainfo['uuid']);
                unset($datainfo['ID']);
            };
            if ($this->deletemode == "UPDATE")
                $datainfo[$this->deletefield] = 0;
            $datainfo = $this->_setAddModifyData($datainfo);
            $result = $this->mydb->addData($this->_beforeDBAct('create', $this->_beforeMethod('create', $datainfo)));
            $result = $this->_afterDBAct('create', $result);
            echo json_encode($this->_ReturnMsg($result));
        } else
            redirect($this->url_module . '/' . $this->url_model);
    }

    public function edit($keyid = "") {
        if ($keyid === "") {
            redirect($this->url_module . '/' . $this->url_model);
            exit();
        };
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
            $arr = $this->session->userdata($sessionKey);
            $arr["isClear"] = false;
            $this->session->set_userdata($sessionKey, $arr);
            $data['editType'] = 'modify';
            $data['keyId'] = $keyid;
            $query = $this->mydb->get($keyid);
            $data['datalist'] = $query['obj'];
            $data = $this->_getGlobalData($data);
            if ($this->debugflag == 1)
                $this->load->view($this->detailurl, $this->_beforeMethod('edit', $data));
            else
                echo print_r($this->_beforeMethod('edit', $data));
        }
        else if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $datainfo = $this->_getRequestModel();
            $datagridinfo = $this->griddefine;
            $datainfo[$datagridinfo['keyid']] = $keyid;
            $datainfo = $this->_setAddModifyData($datainfo);
            if ($this->deletemode === 'UPDATE') {
                unset($datainfo[$this->deletefield]);
            }
            $result = $this->mydb->saveData($this->_beforeDBAct('edit', $this->_beforeMethod('edit', $datainfo)));
            $result = $this->_afterDBAct('edit', $result);
            echo json_encode($this->_ReturnMsg($result));
        } else
            redirect($this->url_module . '/' . $this->url_model);
    }

    public function delete($keyid = "") {
        if ($keyid === "") {
            redirect($this->url_module . '/' . $this->url_model);
            exit();
        };
        if ($this->deletemode == "DEL")
            $result = $this->mydb->deleteData($this->_beforeDBAct('delete', $this->_beforeMethod('delete', $keyid)));
        else {
            $datainfo = array();
            $datainfo[$this->griddefine['keyid']] = $keyid;
            $datainfo[$this->deletefield] = 1;
            $datainfo = $this->_setAddModifyData($datainfo);
            $result = $this->mydb->saveData($this->_beforeDBAct('delete', $this->_beforeMethod('delete', $datainfo)));
        }
        $result = $this->_afterDBAct('delete', $result);
        echo json_encode($this->_ReturnMsg($result));
    }

    public function view($keyid = "") {
        if ($keyid === "") {
            redirect($this->url_module . '/' . $this->url_model);
            exit();
        };
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            $sessionKey = 'Page_' . $this->url_module . '_' . $this->url_model;
            $arr = $this->session->userdata($sessionKey);
            $arr["isClear"] = false;
            $this->session->set_userdata($sessionKey, $arr);
            $data['editType'] = 'detail';
            $data['keyId'] = $keyid;
            $query = $this->mydb->get($keyid);
            $data['datalist'] = $query['obj'];
            $data = $this->_getGlobalData($data);
            if ($this->debugflag == 1)
                $this->load->view($this->detailurl, $this->_beforeMethod('view', $data));
            else
                echo print_r($this->_beforeMethod('view', $data));
        } else
            redirect($this->url_module . '/' . $this->url_model);
    }

    // 上传文件保存并更新到字段中
    protected function _uploadfile($data, $path = "/upload/pic/") {
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (0 != $file["error"]) {
                    continue;
                }
                $targetFolder = FCPATH . $path;
                if (!is_dir($targetFolder))
                    mkdir($targetFolder);
                $strarr = explode('.', $file['name']);
                $filename = str_replace(" ", "", $this->url_module . "_" . $this->url_model . "_" . $key . "_" . time() . '.' . $strarr[1]);
                $showname = $path . $filename;
                $futurename = rtrim($targetFolder, '/') . '/' . $filename;
                if (is_uploaded_file($file['tmp_name']) && move_uploaded_file($file['tmp_name'], $futurename)) {
                    $data[$key] = $showname;
                }
            }
        }
        return $data;
    }

    /**
     * 弹出层 界面显示
     */
    public function getsearchdata() {
        $sessionKey = "Dialog_" . $this->url_module . '_' . $this->url_model;
        $arr = array("Dialog_", "Search_", "Filter_");
        foreach ($arr as $a) {
            $this->session->unset_userdata($a . $this->url_module . '_' . $this->url_model);
        }
        $data = $session = array();
        $data = $this->_getGlobalData($data);
        $ref = &$session[$sessionKey];
        $ref['needFilter'] = (isset($_POST['needFilter']{1})) ? trim($_POST['needFilter']) : "";
        $ref['getsName'] = (isset($_POST['getsName']{1})) ? trim($_POST['getsName']) : "";
        $ref['needField'] = (isset($_POST['needField']{1})) ? trim($_POST['needField']) : "";
        $ref['sql'] = (isset($_POST['sql']{1})) ? trim($_POST['sql']) : "";
        $this->session->set_userdata($session);
        unset($ref);
        $data['title'] = (isset($_POST['title']{1})) ? trim($_POST['title']) : "";
        $data['fromid'] = (isset($_POST['fromid']{1})) ? trim($_POST['fromid']) : "";
        $data['searchdata'] = "";
        if ($this->searchflag == 1) {
            $searchdefine = $data['menuinfo'];
            $data['searchArea'] = bs_searchmodal('modelsearch', $searchdefine['searchdefine']);
        }
        echo $this->load->view('common/modal.php', $this->_beforeMethod('getsearchdata', $data));
    }

    /*
     * Datatable 异步数据输出
     */

    public function modelpage() {
        $sessionDialog = "Dialog_" . $this->url_module . '_' . $this->url_model;
        $arrDialog = $this->session->userdata($sessionDialog);

        if (!empty($arrDialog['sql'])) {
            $sql = $arrDialog['sql'];
        } else {
            $sql = $this->_getListSql();
        }
        $nf = (!empty($arrDialog['needFilter'])) ? $arrDialog['needFilter'] : '';
        if (!empty($arrDialog['search'])) {
            $sql = self::_addWhere($sql, $arrDialog['search']);
        }

        /* else{
          $nf='where 1=1 '.$nf;
          } */

        $sql .=$nf;
        $griddefine = $this->griddefine;
        $aColumns = array($griddefine['keyid']);
        foreach ($griddefine['listfield'] as $arrTmp)
            array_push($aColumns, $arrTmp["fieldname"]);
        $data = $this->mydb->pagingServer($aColumns, $sql);
        echo json_encode($this->_beforeMethod('modelpage', $data['obj']));
    }

    /**
     * 弹层中查询条件整合 
     * * */
    public function modelsearch() {
        $arrSearch = $arrsession = array();
        $menudefine = $this->menudefine;
        $sessionKey = "Dialog_" . $this->url_module . '_' . $this->url_model;
        $session = $this->session->userdata($sessionKey);
        foreach ($menudefine["searchdefine"]["items"] as $arr) {
            if (($arr["type"] == "text") && (!($this->input->post($arr["name"]) === False)))
                $arrSearch["%" . $arr["name"]] = "%" . $this->input->post($arr["name"]);
            if (($arr["type"] == "select") && (!($this->input->post($arr["name"]) === "")))
                $arrSearch["=" . $arr["name"]] = "=" . $this->input->post($arr["name"]);
            if ($arr["type"] == "date") {

                if ((substr($arr["name"], 0, 1) != "S") && (substr($arr["name"], 0, 1) != "E")) {
                    $arrSearch["=" . $arr["name"]] = "=" . $this->input->post($arr["name"]);
                } else {
                    if (substr($arr["name"], 0, 1) == "S" && $this->input->post($arr["name"])) {
                        $tmp = substr($arr["name"], 2);
                        if ($_POST["S_" . $tmp])
                            $arrSearch[">" . $tmp] = ">" . $_POST["S_" . $tmp];

                        if ($_POST["E_" . $tmp])
                            $arrSearch["<" . $tmp] = "<" . $_POST["E_" . $tmp];
                    }
                }
            }
        }

        if (!empty($session['needFilter'])) {
            $arrsession = explode("-", $session['needFilter']);
            foreach ($arrsession as $val) {
                $val = explode("&", $val);
                $arrSearch[$val['0']] = $val['1'];
            }
        }

        $session["search"] = $arrSearch;
        $this->session->set_userdata($sessionKey, $session);
    }

    /*
     * 数据读取 显示在DETAIL页面上
     */

    public function readdata() {
        $keyid = $_POST['serkeyid'];
        $sessionKey = "Dialog_" . $this->url_module . '_' . $this->url_model;
        $session = $this->session->userdata($sessionKey);
        if (!isset($session['getsName']{1}) || !isset($session['needField']{1})) {
            $this->session->unset_userdata($sessionKey);
            echo 1;
            exit;
        }
        if (!empty($session['sql'])) {
            $sql = "select temp.* from (" . $session['sql'] . ") as temp where temp.id = '" . $keyid . "' ";
            $query = $this->db->query($sql);
            $query = $query->result_array();
            $data['datalist'] = $query[0];
        } else {
            $query = $this->mydb->get($keyid);
            $data['datalist'] = $query['obj'];
        }
        $needField = explode(",", $session['needField']);
        $getsName = explode(",", $session['getsName']);
        $key1 = &$data['datalist'];
        $key2 = &$data['json'];
        foreach ($needField as $key => $nf) {
            $key2[$key][$getsName[$key]] = $key1[$nf];
        }
        $this->session->unset_userdata($sessionKey);
        unset($key1, $key2, $data['datalist']);
        echo json_encode($data['json']);
    }

    /**
     * Excel导出
     * */
    //test Excel
    public function excelExport($keyid = '') {
        try {
            $sessionKey = 'Search_' . $this->url_module . '_' . $this->url_model;
            $arrSearch = $this->session->userdata($sessionKey);
            if (empty($keyid)) {
                $sql = $this->_getListSql();
            } else {
                $sql = " select * from ( {$this->_getListSql()} ) as tempSql where {$this->griddefine['keyid']} = '{$keyid}' ";
            }
            $sql = $this->_addWhere($sql, $arrSearch);
            $griddefine = $this->griddefine;
            $aColumns = array($griddefine['keyid']);
            foreach ($griddefine['listfield'] as $arrTmp)
                array_push($aColumns, $arrTmp["fieldname"]);
            $data = $this->mydb->pagingServer($aColumns, $sql);
            $tempdatainfo = $data['obj']['aaData'];
            $listfield = $this->griddefine['listfield'];
            $listfield = array_values($listfield);
            $datainfo = array();
            for ($i = 0; $i < count($tempdatainfo); $i ++) {
                foreach ($tempdatainfo[$i] as $key => $val) {
                    for ($j = 0; $j < count($listfield); $j ++) {
                        if ($listfield[$j]['fieldname'] == $key) {
                            $datainfo[$i][$listfield[$j]['dispname']] = $val;
                        }
                    }
                }
            }
            $query = $this->db->get_where('sys_conf', array('modulename' => $this->url_module, 'controllername' => $this->url_model));
            $result = $query->row_array();
            $title = $result['name'];
            $Frow = 1; //记录打印开始行,一表示第一行
            $Fcol = 0; //记录打印开始列,零表示第一列
            require_once (APPPATH . 'libraries/PHPExcel1.7.6/PHPExcel.php');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator($title)
                    ->setLastModifiedBy($title)
                    ->setTitle($title)
                    ->setSubject("Office XLSX Document")
                    ->setDescription("document for Office XLSX, generated using PHP classes.")
                    ->setKeywords("office 2007 openxml php")
                    ->setCategory($title);
            $objPHPExcel->setActiveSheetIndex(0);
            $objActSheet = $objPHPExcel->getActiveSheet();
            $arrX = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
                , 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT');
            //表格样式
            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );
            //居中 加粗样式
            $headStyle = array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            );
            $objActSheet->setCellValue($arrX[$Fcol] . $Frow, $title);
            $objActSheet->mergeCells($arrX[$Fcol] . $Frow . ':' . $arrX[$Fcol + count($datainfo[0])] . $Frow);
            $objActSheet->getStyle($arrX[$Fcol] . $Frow)->getFont()->setName('Candara');
            $objActSheet->getStyle($arrX[$Fcol] . $Frow)->getFont()->setSize(20);
            $objActSheet->getStyle($arrX[$Fcol] . $Frow)->applyFromArray($headStyle);
            $countdf = count($datainfo);
            //打印表格头字段
            for ($c = 0; $c < $countdf; $c++) {
                $i = 0;
                foreach ($datainfo[$c] as $key => $value) {
                    if ($c == 0) {
                        //只在第一次打印标题
                        $objActSheet->setCellValue($arrX[$Fcol + $i] . ($Frow + 1), $key);
                        $objActSheet->getStyle($arrX[$Fcol + $i] . ($Frow + 1))->applyFromArray($borderStyle);
                    }
                    $objActSheet->setCellValue($arrX[$Fcol + $i] . ($Frow + 2 + $c), $datainfo[$c][$key]);
                    $i += 1;
                }
            }
            // Rename sheet
            $objActSheet->setTitle($title);
            $objPHPExcel->setActiveSheetIndex(0);
            // Save Excel file
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $data['fileName'] = 'output' . date('Ymdhis') . '.xls';
            $path = dirname(dirname(dirname(__FILE__))) . '/excel/';
            $data['urlFile'] = base_url() . '/excel/' . $data['fileName'];
            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
            $objWriter->save($path . $data['fileName']);
            echo json_encode($data);
        } catch (Exception $e) {
            log_message('error', 'ERROR MESSAGE:' . print_r($e, true));
        }
    }

    //endExcelExport

    /**
     * wordexport
     * */
    public function wordExport($keyid) {

        if (substr(strtolower(php_uname('s')), 0, strlen('windows')) == 'windows') {
            $basePath = dirname(dirname(dirname(__FILE__))) . "\\word";
            $comCopy = $basePath . "\\" . $this->url_module . "_" . $this->url_model;
        } else {
            $basePath = dirname(dirname(dirname(__FILE__))) . "/word";
            $comCopy = $basePath . "/" . $this->url_module . "_" . $this->url_model;
        }
        if (substr(strtolower(php_uname('s')), 0, strlen('linux')) == 'linux') {
            $comCopy = "\\cp -f " . $comCopy . "/basefile.xml " . $comCopy . "/word/document.xml";
        } else if (substr(strtolower(php_uname('s')), 0, strlen('windows')) == 'windows') {
            $comCopy = "copy " . $comCopy . "\\basefile.xml " . $comCopy . "\\word\\document.xml /Y";
        }
        exec($comCopy);
        $str = file_get_contents(dirname(dirname(dirname(__FILE__))) . "/word/" . $this->url_module . '_' . $this->url_model . "/word/document.xml");
        $controller = $this->url_model;
        $wordFieldData = $controller->getWordFieldData($keyid);
        $tempData = array();
        foreach ($wordFieldData as $key => $val) {
            if (is_array($wordFieldData[$key])) {
                $tempArrKey = array_keys($wordFieldData[$key][0]);
                for ($j = 0; $j < count($tempArrKey); $j ++) {
                    for ($i = 0; $i < count($wordFieldData[$key]); $i ++) {
                        foreach ($wordFieldData[$key][$i] as $key1 => $val1) {
                            if ($key1 == $tempArrKey[$j]) {
                                if (!empty($val1)) {
                                    $tempData[$tempArrKey[$j]] = (isset($tempData[$tempArrKey[$j]]) ? $tempData[$tempArrKey[$j]] : ' ') . "(" . ($i + 1) . ")." . $val1 . "&#x0D;";
                                } else {
                                    $tempData[$tempArrKey[$j]] = ' ';
                                }
                            }
                        }
                    }
                }
                foreach ($tempData as $key2 => $val2) {
                    $str = str_replace($key2, $val2, $str);
                    file_put_contents(dirname(dirname(dirname(__FILE__))) . "/word/" . $this->url_module . '_' . $this->url_model . "/word/document.xml", $str);
                }
            } else {
                $str = str_replace($key, $val, $str);
                file_put_contents(dirname(dirname(dirname(__FILE__))) . "/word/" . $this->url_module . '_' . $this->url_model . "/word/document.xml", $str);
            }
        }
        if (substr(strtolower(php_uname('s')), 0, strlen('windows')) == 'windows') {
            $path1 = $basePath . "/zip.exe u " . $basePath . "/" . $this->url_module . "_" . $this->url_model . "/" . $this->url_module . "_" . $this->url_model . ".docx ";
        } else {
            $path1 = "cd " . $basePath . ";" . $basePath . "/7za a " . $basePath . "/" . $this->url_module . "_" . $this->url_model . "/" . $this->url_module . "_" . $this->url_model . ".docx ";
        }
        $path2 = $basePath . "/" . $this->url_module . '_' . $this->url_model . "/word";
        $command = $path1 . $path2;
        exec($command);
        $data['fileName'] = $this->url_module . "_" . $this->url_model . ".docx ";
        $data['urlFile'] = base_url() . '/word/' . $this->url_module . "_" . $this->url_model . '/' . $data['fileName'];
        echo json_encode($data);
    }

    //endwordexport
}
