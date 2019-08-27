<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Frontpage_Controller extends CI_Controller
{
    protected $debugflag = 0;            //测试标识 1 为测试， 0 为运行
    protected $cachetime = 0;            //缓冲定义，0 为不开启， 1以上为缓冲最小时间，单位分
    protected $cachetime_max = 5;       //缓冲定义，随机最大缓冲时间，单位分
    protected $sessioninfo = array();
    protected $url_model;
    protected $url_method;
    protected $url_module;
    protected $detail_url;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('PRC');
        $this->load->helper('url');
        $this->load->library('session');
        $this->config->load('cfg-system', true);
        $this->sysconfig = $this->config->item('cfg-system');
        $this->sysconfig = $this->sysconfig['system'];
        $this->cachetime = $this->sysconfig['cachetime'];
        $this->cachetime_max = $this->sysconfig['cachetime_max'];
        // 加载session对象
        $this->sessioninfo = $this->session->userdata('sessioninfo');
        // 定义模块和动作
        $this->url_module = $this->uri->segment(1, $this->router->default_controller);
        $this->url_model = $this->uri->segment(2, 'index');
        $this->url_method = $this->uri->segment(3, 'index');
        self::_noauth();
        $this->cachepath = array('module' => $this->url_module, 'model' => $this->url_model, 'method' => $this->url_method);
        $this->modelpath = $this->url_module . '/' . $this->url_model . '_model';
        $this->detail_url = $this->url_module . '/' . $this->url_model . '_' . $this->url_method . '.php';
        if ($this->url_module === $this->router->default_controller) {
            $this->cachepath = array('module' => $this->url_module, 'model' => $this->url_model);
            $this->detail_url = $this->url_module . '.php';
            $this->modelpath = $this->url_module . '_model';
        }
        // 加载数据库定义
        if ($this->debugflag == 0) {
            $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file', 'key_prefix' => 'abchina_'));
        } else {
            $this->output->enable_profiler(TRUE);
        }
    }


    private function _noauth()
    {
        $params = $this->input->post_get(NULL, TRUE);
        if (!isset($params['data'])) {
            redirect(base_url() . 'html/login.html');
        }
        $params = json_decode($params['data'], true);
        // 微信商城，对需要登录的模块进行登录判断
        if (isset($this->sysconfig['authmodule'])){
            $module = $this->url_module.'/'.$this->url_model;
            // 不在登录需要的模块中，直接返回
            if (!in_array($module,$this->sysconfig['authmodule'])){
                return ;
            }
        }
        if (isset($params['userId'])) {
            $this->sessioninfo['isLogin'] = 1;
            $this->sessioninfo['userId'] = $params['userId'];
            $this->session->set_userdata('sessioninfo', $this->sessioninfo);
        }
        if ($this->sessioninfo['isLogin'] == 1) {
            return;
        }
        if (isset($params['callback'])) {
            $callback = $params['callback'];
        } else {
            $callback = 'test';
        }
        $data = array(
            'returnValue' => array(
                'code' => '10001',
                'des' => '没有登录！'
            ),
        );
        $data = json_encode($data);
        $data = $callback . '(' . $data . ')';
        echo $data;
        exit;
    }

    /*
     * AJAX 返回 跟踪信息,直接返回了
     */
    protected function _JSONRESULT($message = '错误信息')
    {
        $result = array();
        $result["success"] = false;
        $result["msg"] = print_r($message, true);
        $result["obj"] = "";
        echo json_encode($result);
        exit;
    }

    /*
     * 检查当前缓冲是否存在($params, 键值array)
     */
    protected function outCache($params = array('default'))
    {
        if ($this->debugflag == 1) return;
        if ($this->cachetime == 0) return;
        $params = array_merge($this->cachepath, $params);
        if (isset($params['_'])) {
            unset($params['_']);
        }
        $keys = json_encode($params);
        //$keys = preg_replace('/(\<)|(>)|(\|)|(\")|(\:)/','',$keys);
        $keys = preg_replace('/[[:punct:]\s]/', '', $keys);
        log_message('debug', 'outCache()->key : ' . print_r($keys, true));
        $data = $this->cache->get($keys);
        if (!($data === false)) {
            log_message('debug', 'outCache()->data : ' . print_r($data, true));
            $data = json_decode($data, true);
            $callback = $data['callback'];
            unset($data['callback']);
            $data = json_encode($data);
            $data = $callback . '(' . $data . ')';
            echo $data;
            exit;
        }
    }


    /*
     * 输出前保存缓冲($params 键值, $data 输出值
     */
    protected function jsonOut($params = array('default'), $data = array(), $callbackflag = false)
    {
        if (!isset($data['returnValue'])) {
            $data['returnValue'] = array('code' => '0001', 'des' => '查询成功');
        }
        if ($this->debugflag == 0)
            if ($this->cachetime > 0) {
                $params = array_merge($this->cachepath, $params);
                if (isset($params['_'])) {
                    unset($params['_']);
                }
                $keys = json_encode($params);
                //$keys = preg_replace('/(\<)|(>)|(\|)|(\")|(\:)|()/','',$keys);
                $keys = preg_replace('/[[:punct:]\s]/', '', $keys);
                log_message('debug', 'jsonOut()->key : ' . print_r($keys, true));
                log_message('debug', 'jsonOut()->data : ' . print_r($data, true));
                $this->cache->save($keys, json_encode($data), $this->cachetime * 60);
            }
        if ($callbackflag) {
            $callback = $data['callback'];
            unset($data['callback']);
            $data = json_encode($data);
            $data = $callback . '(' . $data . ')';
            echo $data;
            exit;
        }
        if ($this->debugflag == 0) {
            echo json_encode($data);
            exit;
        } else {
            $data['data'] = $data;
            $this->load->view('jsonout.php', $data);
        };
    }

    /*
     * 输出前保存缓冲($params 键值, $data 输出值
     */
    protected function normalOut($params = array('defaultview'), $data = '')
    {
        if ($this->debugflag == 0)
            if ($this->cachetime > 0) {
                $params = array_merge($this->cachepath, $params);
                if (isset($params['_'])) {
                    unset($params['_']);
                }
                $keys = json_encode($params);
                //$keys = preg_replace('/(\<)|(>)|(\|)|(\")|(\:)/','',$keys);
                $keys = preg_replace('/[[:punct:]\s]/', '', $keys);
                log_message('debug', 'normalOut()->key : ' . print_r($keys, true));
                log_message('debug', 'normalOut()->data : ' . print_r($data, true));
                $result = $this->cache->save($keys, $data, rand($this->cachetime * 60, $this->cachetime_max * 60));
                if ($result === false) {
                    echo 'cache fail';
                    exit;
                }
            };
        echo $data;
    }

    /*
     *  提供公共页面的数据准备
     */
    protected function _getGlobalData($data)
    {
        log_message('debug', '_getGlobalData()->params : ' . print_r($data, true));
        $data['url_module'] = $this->url_module;
        $data['url_method'] = $this->url_method;
        $data['url_model'] = $this->url_model;
        $data['session'] = $this->sessioninfo;
        log_message('debug', '_getGlobalData()->return : ' . print_r($data, true));
        return $data;
    }

    /*
     * 在页面输出前,最后调整数据,
     * 根据$this->method来判断是哪个方法调用
     */
    protected function _beforeMethod($method, $data)
    {
        log_message('debug', '_beforeMethod()->params : ' . print_r($data, true));
        return $data;
    }


    public function index()
    {
        $params = $this->input->post_get(NULL, TRUE);
        $this->outCache($params);
        $data = array();
        $data = $this->_getGlobalData($data);
        $view = $this->load->view($this->detail_url, $this->_beforeMethod($this->url_method, $data), true);
        $this->normalOut($params, $view);
    }

    protected function errorOut($params, $callback, $code = '1000', $des = '系统错误')
    {
        $data = array(
            'returnValue' => array(
                'code' => $code,
                'des' => $des
            ),
            'callback' => $callback,
        );
        $this->jsonOut($params, $data, true);
        exit;
    }

    // 类似curl的request对象的调用
    protected function callcontrol($url, $post)
    {
        log_message('DEBUG', "Call Controll url->" . $url . " post->" . print_r($post, true));
        include_once APPPATH . "libraries/Requests.php";
        Requests::register_autoloader();
        $response = Requests::post(site_url() . $url, array(), $post);
        log_message('DEBUG', "Call Controll response ->" . print_r($response, true));
        return $response;
    }


}
