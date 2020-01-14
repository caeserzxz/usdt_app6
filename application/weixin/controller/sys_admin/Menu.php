<?php
/*------------------------------------------------------ */
//-- 微信菜单管理
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\weixin\controller\sys_admin;
use app\AdminController;

use app\weixin\model\WeiXinMenusModel;
use app\weixin\model\WeiXinModel;


class Menu extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new WeiXinMenusModel();		
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){

		$WeixinEventType = $this->getDict('WeixinEventType');		
		$this->assign("WeixinEventType_opt", arrToSel($WeixinEventType));
		$rows = $this->Model->field('id,pid,sort,name,keyword,keyword_value,is_show,type')->order('sort,id asc')->select();
		foreach ($rows as $key=>$row){
			$rows[$key]['event_select'] = arrToSel($WeixinEventType,$row['type']);
		}	
		$this->assign("rows", returnRows($rows));	
		return $this->fetch();
	}
    /*------------------------------------------------------ */
	//-- 保存菜单
	/*------------------------------------------------------ */
    public function save(){
		$add_menu = input('new','', 'trim');
        $save_menu = input('ps','', 'trim');
		$res = $this->Model->saveMenu($add_menu,$save_menu);
		if($res !== true) return $this->error($res);
		return $this->success('保存成功！');
	}
	/*------------------------------------------------------ */
	//-- 删除微信自定义菜单和子菜单
	//-- update by yxb
	/*------------------------------------------------------ */
    public function delete(){
	   $mapb['pid'] = $map['id'] = input('id',0,'intval');
	   if($map['id'] < 1) return $this->error('非法操作！');
	   $res = $this->Model->where($map)->delete();
       if ($res < 1) return $this->error();
	   $res = $this->Model->where($mapb)->delete();
       return $this->success('删除成功！');
    }
	/*------------------------------------------------------ */
	//-- 推送菜单到微信
	/*------------------------------------------------------ */
	function push(){		
		$where[] = ['pid','=',0];
		$where[] = ['is_show','=',1];
		$rows = $this->Model->where($where)->order('sort,id ASC')->limit(3)->select()->toArray();
		$bntarr = array();
		foreach ($rows as $row){			
			unset($p_row,$where);
			$where[] = ['pid','=',$row['id']];
			$where[] = ['is_show','=',1];
			$rowsb = $this->Model->where($where)->order('sort,id ASC')->limit(5)->select()->toArray();
			$p_row['name'] = urlencode($row['name']);	
			if (empty($rowsb) == false){
				foreach ($rowsb as $rowb){
					$_row['type'] = $rowb['type'];
					if ($rowb['type'] == 'click'){
						$_row['key'] =  urlencode($rowb['keyword_value']);					
					}else{
						if ($rowb['keyword'] >= 1){							
							$_row['url'] = _url('shop/article/info',array('id'=>$rowb['keyword']),false,true);
						}else{
						    if ($_row['type'] == 'view'){
                                if (strstr($rowb['keyword_value'],'http://') == false) {
                                    $rowb['keyword_value'] = config('config.host_path').$rowb['keyword_value'];
                                }
                                $_row['url'] = $rowb['keyword_value'];
                            }else{
                                $_row['url'] = urlencode($rowb['keyword_value']);
                            }
						}
					}
					$_row['name'] = urlencode($rowb['name']);
					$p_row['sub_button'][] = $_row;
				}
			}else{	
			    $p_row['type'] = $row['type'];
				if ($row['type'] == 'click'){
					$p_row['key'] = urlencode($row['keyword_value']);			
				}else{
					if ($row['keyword'] >= 1){
						$p_row['url'] = _url('shop/article/info',array('id'=>$row['keyword']),false,true);
					}else{
                        if ($p_row['type'] == 'view'){
                            if (strstr($row['keyword_value'],'http://') == false && strstr($row['keyword_value'],'https://') == false) {
                                $row['keyword_value'] = config('config.host_path').$row['keyword_value'];
                            }
                            $p_row['url'] = $row['keyword_value'];
                        }else{
                            $p_row['url'] = urlencode($row['keyword_value']);
                        }
					}
				}
			}			
			$bntarr['button'][] = $p_row;			
		}
		
		if (empty($bntarr)) return $this->error('没有可推送的菜单定义');				
		$bntarr = urldecode(json_encode($bntarr,JSON_UNESCAPED_UNICODE));
		$res = (new WeiXinModel)->weiXinCurl('https://api.weixin.qq.com/cgi-bin/menu/create?',$bntarr);
		if ($res['errmsg'] != 'ok') return $this->error('操作失败，返回结果：'.$res['errcode'].'-'.$res['errmsg']);
		//记录日志
		
		return $this->success('推送微信菜单成功.');
	}
	/*------------------------------------------------------ */
	//-- 撤销微信菜单
	/*------------------------------------------------------ */
	function remove()
	{
		$res = (new WeiXinModel)->weiXinCurl('https://api.weixin.qq.com/cgi-bin/menu/delete?',[]);
		if ($res['errmsg'] != 'ok') return $this->error('操作失败，返回结果：'.$res['errcode'].'-'.$res['errmsg']);
		//记录日志
		
		return $this->success();
	}
}
