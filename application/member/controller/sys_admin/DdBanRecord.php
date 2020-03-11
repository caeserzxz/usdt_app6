<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\member\model\UsersModel;
use app\member\model\DdBanRecordModel;

use think\Db;
use think\facade\Cache;
/**
 * 封禁记录
 * Class Index
 * @package app\store\controller
 */
class DdBanRecord extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new DdBanRecordModel();
        $this->is_ban = 1;
    }

    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('rode_id',input('rode_id', 0, 'intval'));
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->getList(true);

        //首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }
        $this->assign("roleOpt", arrToSel($this->roleList, $this->search['roleId']));
        $this->assign("levelOpt", arrToSel($this->levelList, $this->search['levelId']));
        return $this->fetch('sys_admin/dd_ban_record/index');
    }

    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_ban = -1)
    {
        $this->search['roleId'] = input('rode_id', -1);
        $this->search['levelId'] = input('level_id', 0, 'intval');
        $this->search['keyword'] = input("keyword");
        $this->search['time_type'] = input("time_type");

        $where = [];
        $time_type = input('time_type', '', 'trim');
        if (empty($time_type) == false) {
            $reportrange = input('reportrange');
            if (empty($reportrange) == false) {
                $dtime = explode('-', $reportrange);
                $where[] = ' u.ban_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
            }
        }

        if ($this->search['roleId'] == -1){
//            $where[] = ' u.role_id > 0 ';
        }elseif ($this->search['roleId'] >= 0) {
            $where[] = ' u.ban_status = ' . $this->search['roleId'] * 1;
        }

        if (empty($this->search['keyword']) == false) {
            if (is_numeric($this->search['keyword'])) {
                $where[] = "  u.user_id = '" . ($this->search['keyword']) . "' or uc.mobile like '" . $this->search['keyword'] . "%'";
            } else {
                $where[] = " ( uc.user_name like '" . $this->search['keyword'] . "%' or uc.nick_name like '" . $this->search['keyword'] . "%' )";
            }
        }
        $this->assign("search", $this->search);
        $sort_by = input("sort_by", 'DESC', 'trim');
        $order_by = 'u.user_id';
        $viewObj = $this->Model->alias('u')->join("users uc", 'u.user_id=uc.user_id', 'left')->where(join(' AND ', $where))->field('u.*,uc.nick_name,uc.mobile')->order($order_by . ' ' . $sort_by);

        $data = $this->getPageList($this->Model, $viewObj);
        $data['order_by'] = $order_by;
        $data['sort_by'] = $sort_by;
        $this->assign("data", $data);
        $this->assign("search",$this->search);
        if ($runData == false) {
            $data['content'] = $this->fetch('sys_admin/dd_ban_record/list')->getContent();
            return $this->success('', '', $data);
        }
        return true;
    }

}
