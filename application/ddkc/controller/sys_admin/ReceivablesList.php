<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use app\ddkc\model\PaymentModel;
use app\member\model\UsersModel;
use think\Db;
/**
 * 矿机订单
 */
class ReceivablesList extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new PaymentModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
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
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $UsersModel = new UsersModel();
        $this->search['keyword'] = input("keyword");
        $this->search['key_type'] = input("key_type");
        $this->search['type'] = input("type");
        $this->search['status'] = input("status");
        $this->assign("search", $this->search);
		$this->order_by = 'id';
		$this->sort_by = 'DESC';
//        $reportrange = input('reportrange');

//        if (empty($reportrange) == false) {
//            $dtime = explode('-', $reportrange);
//            $where[] = ' add_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
//        }
        if ($this->search['type']) {
            $where[] =  ' type ='.($this->search['type']);
        }
        if ($this->search['status']) {
            $where[] =  ' status ='.($this->search['status']-1);
        }
        if ($this->search['keyword']) {
            if ($this->search['key_type'] == 1) {
                $where[] = " user_id = '" . ($this->search['keyword'])."' ";
            }elseif ($this->search['key_type'] == 2) {
                $where[] = " bank_user_name LIKE '%" . $this->search['keyword']."%' OR alipay_user_name LIKE '%" . $this->search['keyword']."%'";
            }
        }
        $viewObj = $this->Model->where(join(' AND ', $where))->order($this->order_by . ' ' . $this->sort_by);
        $data = $this->getPageList($this->Model,$viewObj);
        foreach ($data['list'] as $key => $value) {
        	$data['list'][$key]['add_date'] = date('m-d H:i',$value['add_time']);
            // 账号
            if ($value['type'] == 1) {
                $data['list'][$key]['account'] = $value['card_number'];
            }elseif ($value['type'] == 2) {
                $data['list'][$key]['account'] = $value['alipay_number'];
            }elseif ($value['type'] == 3) {
                $data['list'][$key]['account'] = $value['wx_number'];
            }
            $data['list'][$key]['mobile'] = $UsersModel->where('user_id',$value['user_id'])->value('mobile');
        }
        $type = ['其他','银行卡','支付宝','微信'];

		$this->assign("type", $type);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单信息
    /*------------------------------------------------------ */
    protected function asInfo($data) {
        $type = ['其他','银行卡','支付宝','微信'];

    	$data['type_name'] = $type[$data['type']];
    	$data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
        return $data;
    }

    public function examine(){
        $id = input('id');
        $type = input('type');
        $remarks = input('remarks');
        $pay_info = $this->Model->where('id',$id)->find();
        $save['status'] = $type;
        $save['audit_time'] = time();
        $save['remarks'] = $remarks;
        $res = $this->Model->where('id',$id)->update($save);
        if($res){
            # 升级
            roleUpgrade($pay_info['user_id']);
            return $this->ajaxReturn(['code' => 1,'msg' => '操作成功','url' => url('index')]);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '操作失败']);
        }

    }

    public function  bind_payment(){
        $UsersModel = new UsersModel();
        if ($this->request->isPost()) {
            $binding_user_id = input('binding_user_id');
            $target_user_id = input('target_user_id');

            if(empty($binding_user_id)){
                return $this->error('待绑定的用户ID不能为空.');
            }
            if(empty($target_user_id)){
                return $this->error('目标用户ID不能为空.');
            }
            $binding_user_info = $UsersModel->info($binding_user_id);
            if(empty($binding_user_info)){
                return $this->error('待绑定的用户信息不存在.');
            }
            $target_user_info = $UsersModel->info($target_user_id);
            if(empty($target_user_info)){
                return $this->error('待绑定的用户信息不存在.');
            }
            $target_where[] = ['status','=',1];
            $target_where[] = ['user_id','=',$target_user_id];
            #目标收款收信息
            $target_pay_info =  $this->Model->where($target_where)->select();
            if(empty($target_pay_info)){
                return $this->error('目标用户的没有有效的收款信息,无法绑定.');
            }
            #待绑定的用户收款信息
            $binding_pay_info =  $this->Model->where('user_id',$binding_user_id)->select();
            Db::startTrans();
            if($binding_pay_info){
                foreach ($binding_pay_info as $k=>$v){
                    #删除原有的收款信息
                    $res = $this->Model->where('id',$v['id'])->delete();
                    if($res==false){
                        Db::rollback();
                        return $this->error('删除原有收款信息失败');
                    }
                }
            }
            foreach ($target_pay_info as $k=>$v){
                #更新新的收款信息
                $data['bank_name'] = $v['bank_name'];
                $data['sub_branch'] = $v['sub_branch'];
                $data['card_number'] = $v['card_number'];
                $data['bank_user_name'] = $v['bank_user_name'];
                $data['alipay_number'] = $v['alipay_number'];
                $data['alipay_payment_code'] = $v['alipay_payment_code'];
                $data['alipay_user_name'] = $v['alipay_user_name'];
                $data['wx_number'] = $v['wx_number'];
                $data['wx_payment_code'] = $v['wx_payment_code'];
                $data['type'] = $v['type'];
                $data['user_id'] = $binding_user_id;
                $data['add_time'] = time();
                $data['status'] = 1;
                $data['audit_time'] = time();

                $res = $this->Model->insert($data);
                if($res==false){
                    Db::rollback();
                    return $this->error('更新收款信息失败');
                }
            }

            #更新会员等级
            # 升级
            roleUpgrade($binding_user_id);
            Db::commit();
            return $this->success('操作成功.',url('index'));

        }

        return $this->fetch();
    }
}
