<?php
/*------------------------------------------------------ */
//-- app下载页
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\publics\controller;
use app\ClientbaseController;

class Download  extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function app(){

		return $this->fetch('app');
	}


	
}?>