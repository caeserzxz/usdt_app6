<?php
namespace app;
use think\Controller;
use think\Container;
use think\facade\Session;
use think\facade\Cache;
use think\exception\HttpResponseException;
use think\Response;
use think\response\Redirect;
use think\facade\Env;
error_reporting(E_ERROR | E_PARSE );
/**
 * 控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class BaseController extends Controller
{
	 /* @var string $route 当前控制器名称 */
    protected $controller = '';

    /* @var string $route 当前方法名称 */
    protected $action = '';

    /* @var string $route 当前路由uri */
    protected $routeUri = '';

    /* @var string $route 当前路由：分组名称 */
    protected $group = '';
    /* @var string $route 当前菜单组名 */
    protected $menus_group = '';
 	public  $returnJson = false;//是否统一返回json
    public  $Model;
    public  $main_transfer = true;//是否主层级调用
    // 初始化
    protected function initialize(){
        //多语言支持
        $langPre = '';
        $lang = '';
        $d_default_lang = config('config.d_default_lang');
        if (config('config.d_lang_switch_on') == true){
            $SERVER_NAME = explode('.',$_SERVER['SERVER_NAME']);
            $lang = strtolower($SERVER_NAME[0]);
            if (in_array($lang,config('config.d_lang_list'))){
                $langPre = $lang.'_';
            }elseif( empty($d_default_lang) == false && $d_default_lang != 'cn'){
                $lang = $d_default_lang;
                $langPre = $lang.'_';
            }
        }
        define('LANG_PRE',$langPre);
        define('LANG',$lang);
        //多语言end
    }

    //*------------------------------------------------------ */
	//-- 获取字典数据
	/*------------------------------------------------------ */
	public function getDict($key = ''){
		return \app\mainadmin\model\PubDictModel::getRows($key);
	}

    //*------------------------------------------------------ */
    //-- 获取前端登陆会员ID
    /*------------------------------------------------------ */
    public function getLoginInfo()
    {
        $userId = Session::get('userId') * 1;  
        if ($userId < 1) {
            $devtoken = input('devtoken', '', 'trim');//小程序登陆
            if (empty($devtoken) == false) {
				header('Content-type: text/json'); 
				//判断接口请求是否合法
                $timeStamp = input('timeStamp/s');
				$sign = input('sign/s');
				if (md5($devtoken.$timeStamp.config('config.apikey')) !== $sign) return $this->error('接口验证失败！');
				if (time() - intval($timeStamp/1000) > 60) return $this->error('请求超时.'.$timeStamp);
                $userId = Cache::get('devlogin_'.$devtoken);
            }
        }
        if ($userId > 0){
            return (new \app\member\model\UsersModel)->info($userId);
        }
        return [];
    }
	 //*------------------------------------------------------ */
	 //* 获取post数据 (数组)
     //* @param $key
     //* @return mixed
	/*------------------------------------------------------ */
    protected function postData($key)
    {
        return $this->request->post($key . '/a');
    }
	/*------------------------------------------------------ */
	//-- 解析当前路由参数 （分组名称、控制器名称、方法名）
	/*------------------------------------------------------ */
    protected function getRouteinfo()
    {
		// 模块名称
		$this->module = $this->request->module();
        // 控制器名称
        $this->controller = toUnderScore($this->request->controller());
        // 方法名称
        $this->action = $this->request->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri = $this->controller . '/' . $this->action;
    }
	//*------------------------------------------------------ */
	//-- 获取分页数据
	/*------------------------------------------------------ */
	protected function getPageList(&$model,&$where = '',$field = '*',$page_size = ''){
		if (empty($page_size)){
			$page_size = input("page_size/d",0);
            $session_page_size = Session::get('page_size') * 1;
			if ($page_size <= 1 ){
                if ($session_page_size <= 1) $session_page_size = 10;
                $page_size = $session_page_size;
            }
			elseif ($page_size != $session_page_size) Session::set('page_size',$page_size);
	    }	
		
		if (is_object($where) == false){//单表查询
            if (empty($this->sqlOrder)){
                $sort_by = input("sort_by/s");                
                if (empty($sort_by) == false){
                    $sort_by = strtoupper($sort_by);
                    if (in_array($sort_by,array('DESC','ASC')) == false){
                        $sort_by = 'DESC';
                    }
                }
                $order_by = input("order_by/s");
                //判断排序字段是否存在
                if (empty($order_by) == false){
                    if ($model->isSetField($order_by) == false){
                        $order_by = '';
                    }
                }			
				if (empty($order_by) == false){
					$this->sqlOrder = $order_by.' '.$sort_by;
				}
            }
			 if (empty($this->sqlOrder)){
				 $this->sqlOrder = '';
			 }
			return $model->getPageList(input("p/d", 1),$where,$field,$page_size,$this->sqlOrder);
		}else{//联表查询
			return $model->getJointList(input("p/d", 1),$where,$page_size);
		}

	}
	 /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];        
        } elseif ('' !== $url && 'reload' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Container::get('url')->build($url);
        }
        $code = 1;
        if (is_array($msg) == true){
            $code = $msg[1];
            $msg = $msg[0];
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
		
        $type = $this->returnJson == true ? 'json' :$this->getResponseType();
        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => $this->app['config']->get('dispatch_success_tmpl')]);

        throw new HttpResponseException($response);
    }
	//直接返回json
 	protected function ajaxReturn($result = array())
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result,JSON_UNESCAPED_UNICODE));
      
    }
    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '操作失败,请重试.', $url = null,$data = [],  $wait = 3, array $header = [])
    {
        $type = $this->returnJson == true ? 'json' :$this->getResponseType();
        if (is_null($url)) {
            $url = $this->app['request']->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app['url']->build($url);
        }
        $code = 0;
        if (is_array($msg) == true){
            $code = $msg[1];
            $msg = $msg[0];
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
	
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => $this->app['config']->get('dispatch_error_tmpl')]);

        throw new HttpResponseException($response);
    }
	/*------------------------------------------------------ */
	//-- 检查更新数据是否变化
	//-- $olddata array 旧数据
	//-- $data array 更新数据
	//-- $returnOk bool 无变化时返回错误还是直接提示成功
	/*------------------------------------------------------ */
    protected function checkUpData($olddata=array(),$data=array(),$returnOk = false) {
		if (empty($olddata) || empty($data)) return $this->error('操作失败:传值异常.');
		$is_ok = false;
        foreach ($data as $key=>$val){
			if ($val != $olddata[$key]){				
				$is_ok = true;
				break;
			}
		}
		if ($is_ok == false){
			if ($returnOk == true){
				return $this->success('操作成功.');
			}
		 	return $this->error('操作失败:数据内容没有变化，请核.！');
		 }		
		return true;
    }
	/*------------------------------------------------------ */
	//-- 记录操作日志，只提供给后台管理调用
	/*------------------------------------------------------ */
	public function _log($edit_id,$log_info,$controller = ''){
	    if (empty($controller)) {
            $controller = 'mainadmin';
        }
		$inData['edit_id'] = $edit_id;
        $inData['log_info'] = $log_info;
        $inData['module'] = request()->path();
        $inData['log_ip'] = request()->ip();
        $inData['log_time'] = time();
        $inData['user_id'] = 0;
        if (defined('AUID')){
            $inData['user_id'] =  AUID;
        }elseif (defined('SAUID')){
            $inData['user_id'] = SAUID;
        }
        $Model = str_replace('/', '\\', "/app/$controller/model/LogSysModel");
        (new $Model)->save($inData);
		return true;
	}
	/*------------------------------------------------------ */
	//-- 上传文件
	/*------------------------------------------------------ */
    protected function _upload($file, $dir = '', $thumb = array(), $save_rule='uniqid') {

        $upload = new \lib\UploadFile();

        $upload_path = config('config._upload_') . $dir ;


        if ($thumb) {
            $upload->thumb = true;
            $upload->thumbMaxWidth = $thumb['width'];
            $upload->thumbMaxHeight = $thumb['height'];
            $upload->thumbPrefix = '';
            $upload->thumbSuffix = isset($thumb['suffix']) ? $thumb['suffix'] : '_thumb';
            $upload->thumbExt = isset($thumb['ext']) ? $thumb['ext'] : '';
            $upload->thumbRemoveOrigin = isset($thumb['remove_origin']) ? true : false;
        }
        //自定义上传规则
        $upload = $this->_upload_init($upload);
        if( $save_rule!='uniqid' ){
            $upload->saveRule = $save_rule;
        }

        if ($result = $upload->uploadOne($file,$upload_path)) {
            return array('error'=>0, 'info'=>$result);
        } else {
            return array('error'=>1, 'info'=>$upload->getErrorMsg());
        }
    }
    protected function _upload_init($upload) {
        $file_type = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'img'   => array('jpg', 'jpeg'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb','mp4'),
            'file'  => array('htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
            'work'  => array('doc', 'docx', 'xls', 'xlsx', 'ppt'),
        );

        //和总配置取交集
		if (empty($ext_arr[$file_type]) == false){
        	$upload->allowExts = $ext_arr[$file_type];
		}elseif (in_array($file_type, ['image','i㎎','flash','media','file','work']) == false){
            $upload->allowExts = $ext_arr['image'];
        }
        $upload->savePath =  config('config._upload_'). $file_type . '/';
        $upload->saveRule = 'uniqid';
        $upload->autoSub = true;
        $upload->subType = 'date';
        $upload->dateFormat = 'Y/m/';
        return $upload;
    }
    /**
     * [自定义Log 日志log]
     * @param  [type] $type        [类型]
     * @param  [type] $log_content [内容]
     * @return [type]              [description]
     */
    public function diyLog($type, $log_content) {
        $max_size = 30000000;
        $log_file_path = Env::get('runtime_path') . 'diylogs/'.$type.'/';
        $log_filename = $log_file_path.date('Ymd') . ".log";
        !is_dir($log_file_path) && mkdir($log_file_path, 0755, true);
        if (file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)) {
            rename($log_filename, dirname($log_filename) . DS . date('Ym-d-His') . $keyp . ".log");
        }

        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new \DateTime (date('Y-m-d H:i:s.' . $micro, $t));
        if(is_array($log_content)){
            $log_content = json_encode($log_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($log_filename, '   ' . $d->format('Y-m-d H:i:s u') .  "\r\n" . $log_content . "\r\n------------------------ --------------------------\r\n", FILE_APPEND);
    }
}
