<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\SmsTplModel;
/**
 * 短信模板
 * Class Index
 * @package app\store\controller
 */
class SmsTpl extends AdminController
{	
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new SmsTplModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {		
		$this->getList(true);
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		
        $data = $this->getPageList($this->Model);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

    /*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){		
		$this->_log($data['tpl_id'],'修改短信模板.');
		return true;		
	}


}
