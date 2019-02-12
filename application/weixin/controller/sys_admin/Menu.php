<?php
/*------------------------------------------------------ */
//-- 微信菜单管理
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\weixin\controller\sys_admin;
use app\AdminController;

use app\weixin\model\WeiXinMenusModel;


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
		$this->assign("domain", $this->request->domain());	
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
		if($res['res'] != 0) return $this->error($res['error']);
		return $this->success('保存成功！');
	}
	/*------------------------------------------------------ */
	//-- 删除微信自定义菜单和子菜单
	//-- update by yxb
	/*------------------------------------------------------ */
    public function delete(){
	   $mapb['pid'] = $map['id'] = I('id',0,'intval');
	   if($map['id'] < 1) return $this->error('非法操作！');
	   $res = $this->Model->where($map)->delete();
       if ($res <　1) return $this->error();
	   $res = $this->Model->where($mapb)->delete();
       return $this->success('删除成功！');
    }
	/*------------------------------------------------------ */
	//-- 推送菜单到微信
	/*------------------------------------------------------ */
	function push(){		
		$map['pid'] = 0;
		$map['is_show'] = 1;
		$rows = $this->_mod->where($map)->order('sort,id ASC')->limit(3)->select();
		$bntarr = array();
		foreach ($rows as $row){			
			unset($p_row);
			$map['pid'] = $row['id'];
			$rowsb = $this->_mod->where($map)->order('sort,id ASC')->limit(5)->select();
			$p_row['name'] = urlencode($row['name']);	
			if ($rowsb){
				foreach ($rowsb as $rowb){
					$_row['type'] = $rowb['type'];
					if ($rowb['type'] == 'click'){
						$_row['key'] =  urlencode($rowb['keyword_value']);
					}elseif ($rowb['type'] == 'OnlineService'){//在线客服
						$_row['type'] = 'click';		
						$_row['key'] = urlencode('【在线客服】');
					}else{
						if ($rowb['keyword'] >= 1){							
							$_row['url'] = C('SERVER_LOCATION').U('Shop/Article/info',array('id'=>$rowb['keyword']));
						}else{
							$_row['url'] = $_row['type'] == 'view' ? $rowb['keyword_value'] : urlencode($rowb['keyword_value']);
						}
					}
					$_row['name'] = urlencode($rowb['name']);
					$p_row['sub_button'][] = $_row;
				}
			}else{	
			    $p_row['type'] = $row['type'];
				if ($row['type'] == 'click'){
					$p_row['key'] = urlencode($row['keyword_value']);
				}elseif ($row['type'] == 'OnlineService'){//在线客服
					$p_row['type'] = 'click';		
					$p_row['key'] = urlencode('【在线客服】');
				}else{
					if ($row['keyword'] >= 1){
						$p_row['url'] = C('SERVER_LOCATION').U('Shop/Article/info',array('id'=>$row['keyword']));
					}else{
						$p_row['url'] = $p_row['type'] == 'view' ? $row['keyword_value'] : urlencode($row['keyword_value']);
					}
				}
			}			
			$bntarr['button'][] = $p_row;			
		}
		
		if (empty($bntarr)) return $this->error('没有可推送的菜单定义');				
		$bntarr = urldecode(json_encode($bntarr));
		
		$wx_mod = D('WeiXin');
		$wx_mod->plcInfo = $this->plcInfo;
		$res = $wx_mod->weiXinCurl('https://api.weixin.qq.com/cgi-bin/menu/create?',$bntarr);
		if ($res['errmsg'] != 'ok') return $this->error('操作失败，返回结果：'.$res['errcode'].'-'.$res['errmsg']);
		//记录日志
		$this->_log(0,'推送'.$this->ntitle.'到微信');
		return $this->success();
	}
	/*------------------------------------------------------ */
	//-- 撤销微信菜单
	/*------------------------------------------------------ */
	function remove()
	{
		$res = D('WeiXin')->weiXinCurl('https://api.weixin.qq.com/cgi-bin/menu/delete?',$bntarr);
		if ($res['errmsg'] != 'ok') return $this->error('操作失败，返回结果：'.$res['errcode'].'-'.$res['errmsg']);
		//记录日志
		$this->_logsave(array('info'=>'撤销微信菜单'));
		$this->success();
	}
}
