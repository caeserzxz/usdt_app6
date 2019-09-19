<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\SettingsModel;
/**
 * 设置
 * Class Index
 * @package app\store\controller
 */
class Setting extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();		
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		
		$this->assign("setting", $this->Model->getRows());
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.setting');
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }
    /*------------------------------------------------------ */
    //-- 上传app
    /*------------------------------------------------------ */
    public function uploadapp(){
        if($_FILES['file']['size'] > 100000000){
            $result['code'] = 1;
            $result['msg'] = '最大支持 100M MB上传.';
            return $this->ajaxReturn($result);
        }
        if (strstr($_FILES["file"]['type'],'application') == false) {
            $result['code'] = 1;
            $result['msg'] = '未能识别文件格式，请核实.';
            return $this->ajaxReturn($result);
        }
        $file_type = end(explode('.',$_FILES['file']['name']));
        if (in_array($file_type,['apk']) == false){
            $result['code'] = 1;
            $result['msg'] = '格式不对，只支持 (apk格式)，请核实.';
            return $this->ajaxReturn($result);
        }
        $dir = config('config._upload_').'download/';
        makeDir($dir);
        $file_name = random_str(16).'.'.$file_type;
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $result['code'] = 0;
        $result['filename'] = trim($dir.$file_name,'.');
        return $this->ajaxReturn($result);
    }
}
