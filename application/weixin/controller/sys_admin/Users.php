<?php
namespace app\weixin\controller\sys_admin;
use app\AdminController;
use app\weixin\model\WeiXinUsersModel;
/*------------------------------------------------------ */
//-- 微信会员
/*------------------------------------------------------ */
class Users extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new WeiXinUsersModel();		
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {		    
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['wx_subscribe_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['wx_subscribe_time','between',[strtotime("-1 months"),time()]];
		}	
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false){
			unset($where);
			$where[] = ['wx_nickname','like','%'.$search['keyword'].'%'];	
		}
		
        $data = $this->getPageList($this->Model, $where);			
		$this->assign("data", $data);
		$this->assign("search", $search);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	
}
