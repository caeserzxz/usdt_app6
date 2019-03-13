<?php
/*------------------------------------------------------ */
//-- 微信主体程序
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\weixin\controller;
use app\BaseController;

use app\weixin\model\WeiXinModel;
use app\weixin\model\WeiXinKeywordsModel;
use app\weixin\model\WeiXinUsersModel;
class Index  extends BaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index($isIndex = false){
		/*$arr = (new WeiXinKeywordsModel)->checkKey(1,123);		
		print_r($arr);
		exit;*/
		$WeiXinModel = new WeiXinModel();
		$this->logResult('upload/1.txt','接收');
		// 关注验证
		if ($_GET["echostr"]) die($WeiXinModel->valid($_GET["echostr"]));
		// 验证微信请求
		$WeiXinModel->getMsg();
		if ($WeiXinModel->keyword == '【在线客服】') {
			$resultStr = '<xml><ToUserName><![CDATA['.$WeiXinModel->fromUsername.']]></ToUserName><FromUserName><![CDATA['.$WeiXinModel->toUsername.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[transfer_customer_service]]></MsgType></xml>';
			echo $resultStr;
			exit;
		}
       
		// 关注/取消关注定义
		if ($WeiXinModel->keyword == 'subscribe' || $WeiXinModel->keyword == 'unsubscribe'){
			$WeiXinUsersModel = new WeiXinUsersModel();
			$wxuid = $WeiXinUsersModel->where('wx_openid',$WeiXinModel->fromUsername)->getField('wxuid');
						
			if ($wxuid < 1 && $_arr['subscribe'] == 1 ){
				$inArr['subscribe'] = $WeiXinModel->keyword == 'subscribe' ? 1 : 2;
				$inArr['wx_openid'] = $WeiXinModel->fromUsername;	
				$inArr['add_time'] = $inArr['update_time'] = time();
				$WeiXinUsersModel->save($inArr);
			}else{
				$upArr['subscribe'] = $WeiXinModel->keyword == 'subscribe' ? 1 : 2;
				$upArr['wx_subscribe_time'] = time();
				$upArr['update_time'] = time();
				$WeiXinUsersModel->where('wxuid',$wxuid)->update($upArr);
			}
			
			if ($WeiXinModel->keyword == 'unsubscribe') return false;
		}
		
		$this->logResult('upload/1.txt',$WeiXinModel->keyword);
		$arr = (new WeiXinKeywordsModel)->checkKey($WeiXinModel->keyword,$WeiXinModel->fromUsername);		
		$this->logResult('upload/1.txt',$WeiXinModel->keyword.$WeiXinModel->fromUsername.json_encode($arr));
		die($WeiXinModel->responseMsg($arr));
	}
	// 打印log
	function  logResult($file,$word) 
	{
		return false;
	    $fp = fopen($file,"a");
	    flock($fp, LOCK_EX) ;
	    fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\n\n");
	    flock($fp, LOCK_UN);
	    fclose($fp);
	}
	
}?>