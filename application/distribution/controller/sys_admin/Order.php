<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\RoleOrderModel;
use app\distribution\model\DividendRoleModel;
use app\distribution\model\DividendModel;
/*------------------------------------------------------ */
//-- 身份订单
/*------------------------------------------------------ */
class Order extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RoleOrderModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $this->assign("start_date", $dtime[0]);
            $this->assign("end_date", $dtime[1]);
        } else {
            $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
            $this->assign("end_date", date('Y/m/d'));
        }
		$this->getList(true);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_delete=0) {
		$this->sqlOrder = 'order_id DESC';
        $search['pay_status'] = input('pay_status', -1, 'intval');
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $where[] = ['add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1]) + 86399]];
        } else {
            $where[] = ['add_time', 'between', [strtotime("-1 months"), time()]];
        }
        if ($search['pay_status'] >= 0){
            $where[] = ['pay_status', '=',$search['pay_status']];
        }

        $data = $this->getPageList($this->Model,$where);
		$this->assign("data", $data);
        $this->assign("search", $search);
		if ($runData == false){
			$data['content'] = $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单详细页
    /*------------------------------------------------------ */
    public function info()
    {
        $order_id = input('order_id', 0, 'intval');
        $orderInfo = $this->Model->find($order_id)->toArray();

        $orderInfo['buy_role_name'] =  (new DividendRoleModel)->info($orderInfo['last_role_id'],true);
        $this->assign('orderInfo', $orderInfo);
        $logWhere[] = ['order_type','=','role_order'];
        $logWhere[] = ['order_id','=',$order_id];
        $dividend_log = (new DividendModel)->where($logWhere)->order('award_id,level ASC')->select()->toArray();
        $this->assign('dividend_log', $dividend_log);
        $this->assign("orderLang", lang('order'));
        return $this->fetch('info');
    }

}
