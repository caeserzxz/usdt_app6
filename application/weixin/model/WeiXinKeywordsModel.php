<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
use think\Db;
//*------------------------------------------------------ */
//-- 素材库
/*------------------------------------------------------ */
class WeiXinKeywordsModel extends BaseModel
{
	protected $table = 'weixin_keywords';
	public  $pk = 'id';
	protected static $mkey =  'mem_weixin_keywords_';
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache($keyword){
		Cache::rm(self::$mkey.$keyword);
	}
    /*------------------------------------------------------ */
	//-- 验证回复关键字
	//-- 
	//-- $get			获取url参数
	//-- $qrcode
	//-- 	msg			预定义文本回复内容
	//-- $keyword		请求类型（qrscene为二维码请求）
	//-- $fromUsername	微信用户openid
	/*------------------------------------------------------ */
    public function checkKey($keyword,$fromUsername){
        $where[] = ['status','=',1];	
		// 关注回复
        if ($keyword == 'subscribe'){
            $where[] = ['subscribe','=',1];
			$where[] = ['pid','=',0];
		}else{
			$where[] = ['','exp',Db::raw("FIND_IN_SET('".$keyword."',keyword)")];
		}
		$keyword = trim($keyword);
        $_mkey = self::$mkey.'_'.$keyword;
		$row = Cache::get($_mkey);
		if ($row) return $row;
        $row = $this->where($where)->find();
		unset($map);
		// 调用默认回复
		if (empty($row) == true){
			$where = [];
			$where[] = ['default','=',1];
			$where[] = ['status','=',1];
			$where[] = ['pid','=',0];
			$row = $this->where($where)->find();
			
		}
		if ($row['type'] == 'text'){
			$arr['MsgType'] = 'text';
			$arr['Content'] = $row['data'];
		}elseif ($row['type'] == 'news'){
			$arr['MsgType'] = 'news';
        	$rows = $this->where('pid',$row['id'])->select();
			if ($rows){ 
				array_unshift($rows,$row);
			}else{
				$rows[] = $row;
			}
			
			$Articles = array();
			foreach ($rows as $key=>$row)
			{
				$Articles[$key]['Title'] = $row['title'];
				if ($key == 0) $Articles[$key]['Description'] = $row['description'];
				if (strstr($row['imgurl'],'http://') == false){
					$Articles[$key]['PicUrl'] = config('config.host_path').'/'.$row['imgurl'];
				}
				switch($row['bind_type']){
					case 'link':
                        if (strstr($row['data'],'http://') == false) {
                            $Articles[$key]['Url'] = config('config.host_path').$row['data'];
                        }else{
                            $Articles[$key]['Url'] = $row['data'];
                        }
					break;
					case 'article':
						$Articles[$key]['Url'] = _url('shop/article/info',array('id'=>$row['ext_id']),false,true);
					break;
					case 'tel':
						$Articles[$key]['Url'] = 'tel:'.$row['data'];
					break;
					default:
					break;
				}
			}
			$arr['Articles'] = $Articles;			
		}
		
		Cache::set($_mkey,$arr,30);
		return $arr;
    }
}
