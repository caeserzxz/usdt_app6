<?php
/*------------------------------------------------------ */
//-- 我的团队
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\ClientbaseController;
use app\ddkc\model\DdInformationModel;



class Information extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		$setting = settings();
 		$banner = unserialize($setting['informa_banner']);

        $this->assign('not_top_nav', true);
        $this->assign('banner', $banner);

		return $this->fetch('index');
	}
	
   	/*------------------------------------------------------ */
	//-- 详情
	/*------------------------------------------------------ */
	public function info(){
		$id = input('id');
 		if (!$id)  $this->redirect(url('index'));

        $informationModel = new DdInformationModel();
		
		$data = $informationModel->where('id',$id)->find();	
		if (!$data) $this->redirect(url('index'));

		if ($data['is_show'] != 1) $this->redirect(url('index'));

        $this->assign('title', '快讯详情');
        $this->assign('data', $data);
		return $this->fetch();
	}
}?>