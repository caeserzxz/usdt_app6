<?php
/*------------------------------------------------------ */
//-- 文件上传管理程序
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\mainadmin\controller\Attachment as mainAttachment;

class Attachment extends mainAttachment
{

//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {
       $this->initialize_isretrun = false;
       parent::initialize();
       $Controller = new Controller();
       $this->supplyer_id = $Controller->supplyer_id;
    }
	

}
?>