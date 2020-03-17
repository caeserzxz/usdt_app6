<?php

namespace app\ddkc\controller\api;

use app\ApiController;
use app\member\model\UsersModel;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\ddkc\model\MiningUnsealingModel;
use app\member\model\UsersBindModel;
use app\distribution\model\DividendRoleModel;

/*------------------------------------------------------ */
//-- 会员登陆、注册、找回密码相关API
/*------------------------------------------------------ */

class Center extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
   
    /*------------------------------------------------------ */
    //-- 团队统计
    /*------------------------------------------------------ */
    public function getTeamStatistics()
    {
        $userId = $this->userInfo['user_id'];
        $roleModel = new DividendRoleModel();
        $userBindModel = new UsersBindModel();

        # 各等级直推人数统计
        $allRole = $roleModel->field('role_id,role_name')->where(1)->select();
        foreach ($allRole as $key => $value) {
            # 该等级直推人数
            $allRole[$key]['subNum'] = $this->Model
                ->where(['pid' => $userId,'role_id' => $value['role_id']])
                ->count();
        }
        # 三层内各层级人数
        $where[] = ['pid','=',$userId];
        $where[] = ['level','<=',3];
        $threeInfo = $userBindModel->where($where)->group('level')->column('count(*) cc','level');
        $hierarchyText = ['其他','一','二','三'];


        $data['roleInfo'] = $allRole;
        $data['threeInfo'] = $threeInfo;
        $data['textInfo'] = $hierarchyText;
        return $this->ajaxReturn($data);
    }
}
