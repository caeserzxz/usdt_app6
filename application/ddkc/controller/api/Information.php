<?php

namespace app\ddkc\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
use app\ddkc\model\DdInformationModel;
use app\mainadmin\model\SettingsModel;
/*------------------------------------------------------ */
//-- 矿机相关逻辑
/*------------------------------------------------------ */

class Information extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
        $this->Model = new DdInformationModel();
    }
    /*------------------------------------------------------ */
    //-- 资讯列表
    /*------------------------------------------------------ */
    public function getInformationList(){

        $this->sqlOrder = 'id DESC';
        $where[] = ['is_show' ,'eq' ,1];
        $data = $this->getPageList($this->Model,$where);
        if (count($data['list']) > 0) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['add_data'] = date('m-d H:i',$value['add_time']);
            }
        }
        return $this->ajaxReturn($data);
    }
}
