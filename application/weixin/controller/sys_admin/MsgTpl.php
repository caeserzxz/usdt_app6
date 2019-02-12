<?php
namespace app\weixin\controller\sys_admin;
use app\AdminController;
use app\weixin\model\WeiXinMsgTplModel;
/*------------------------------------------------------ */
//-- 微信消息模板
/*------------------------------------------------------ */
class MsgTpl extends AdminController
{	
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new WeiXinMsgTplModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {		
		$this->getList(true);
		$this->assign('search',$this->search);
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$where = [];
		$this->search['type'] = input('type','','trim');
		if (empty($this->search['type']) == false){
				$where[] = ['type','=',$this->search['type']];
		}
        $data = $this->getPageList($this->Model,$where);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data) {
		$data['tpl_keys'] = json_encode($data['tpl_keys'],JSON_UNESCAPED_UNICODE);
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
    public function asInfo($data){		
		if (empty($data['tpl_keys'])==false){
			$data['tpl_keys'] = json_decode($data['tpl_keys'],true);
		}else{
			$data['tpl_keys'] = array();
		}
		return $data;		
	}
	
    /*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){		
		$this->_log($data['tpl_id'],'修改微信消息模板.');
		return true;		
	}
	/*------------------------------------------------------ */
	//-- ajax快速修改
	//-- id int 修改ID
	//-- data array 修改字段
	/*------------------------------------------------------ */
	public function afterAjax($id,$data){
		if (isset($data['status'])){
			$info = '微信消息模板,快速修改状态:'.($data['status']==1?'启用':'停用');
		}else{
			return false;	
		}		
		$this->_log($id,$info);//记录日志
		return true;
	}

}
