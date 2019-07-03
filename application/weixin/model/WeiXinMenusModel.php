<?php

namespace app\weixin\model;

use app\BaseModel;

//*------------------------------------------------------ */
//-- 文章
/*------------------------------------------------------ */
class WeiXinMenusModel extends BaseModel
{
	protected $table = 'weixin_menus';
	public  $pk = 'id';
	/*------------------------------------------------------ */
	//-- 保存本地微信菜单
	//-- @param 
	//-- 	map 			tuid、plc_id
	//-- 	add_menu		新增的菜单
	//-- 	save_menu		修改的菜单
	//-- 
	//-- @return array
	/*------------------------------------------------------ */
	public function saveMenu($add_menu,$save_menu){
		
		// 判断启用的主菜单与子菜单是否超过微信规定的数量
		$parent_menu = $this->_is_save_menu($add_menu,$save_menu);
		if($parent_menu['res'] != 0) return array('res'=>1,'error'=>$parent_menu['error']);
		
		if($add_menu['sort']){
			$sort_count = count($add_menu['sort']);
			for($i = 1; $i <= $sort_count; $i++){			
				$uparr[$i-1]['pid'] = $add_menu['parent_id'][$i];
				$uparr[$i-1]['sort'] = $add_menu['sort'][$i];
				$uparr[$i-1]['name'] = $add_menu['name'][$i];
				$uparr[$i-1]['keyword'] = $add_menu['keyword'][$i];
				$uparr[$i-1]['keyword_value'] = $add_menu['keyword_value'][$i];
				if($add_menu['is_show'][$i]){
					$uparr[$i-1]['is_show'] = $add_menu['is_show'][$i];
				}else{
					$uparr[$i-1]['is_show'] = 0;
				}
				$uparr[$i-1]['add_time'] = time();
				$uparr[$i-1]['type'] = $add_menu['type'][$i];
			}
			$res = $this->saveAll($uparr,false);
		}
		
		foreach($save_menu as $s_key => $up_menu){
			$where['id'] = $s_key;
			$up_arr['pid'] = $up_menu['parent_id'];
			$up_arr['sort'] = $up_menu['sort'];
			$up_arr['name'] = $up_menu['name'];
			$up_arr['keyword'] = $up_menu['keyword'];
			$up_arr['keyword_value'] = $up_menu['keyword_value'];
			if($up_menu['is_show']){
				$up_arr['is_show'] = $up_menu['is_show'];
			}else{
				$up_arr['is_show'] = 0;
			}
			$up_arr['update_time'] = time();
			$up_arr['type'] = $up_menu['type'];
			$res = $this->where($where)->update($up_arr);
		}		
		if ($res < 1)  return array('res'=>1,'error'=>'保存失败！');
		// 更新清除用户memcache
		//_mymamcache(md5($map['tuid'].'wxmenu'),'del');
		return array('res'=>0,'error'=>'保存成功！');
	}
	/*------------------------------------------------------ */
	//-- 判断后台创建的微信菜单数量是否在微信指定内（主菜单3个，每个主菜单的子菜单5个）
	//-- @param 
	//-- 	user.tuid 		所属主帐号
	//-- 	user.plc_id		所属公众号
	//-- 	add_menus		新增菜单
	//-- 	update_menus	修改的菜单
	//-- 	
	//-- @return array
	/*------------------------------------------------------ */
	public function _is_save_menu($update_menus,$add_menus){		
		$menu_p_nums =0; $menu_un_arr = array();
		foreach ($update_menus['name'] as $key=>$val){
			if (empty($update_menus['keyword'][$key]) && $update_menus['parent_id'][$key] > 0 && in_array($update_menus['type'][$key],array('view','click'))){
				if (empty($update_menus['type'][$key])) return array('res'=>1,'error'=>$val.' - 请选择响应动作类型！');
				if ($update_menus['type'][$key] == 'click') return array('res'=>1,'error'=>$val.' - 请选择对应关键字！');
				if ($update_menus['keyword_value'][$key] == 'http://') return array('res'=>1,'error'=>$val.' - 请填写完整的网址！');
				if (!strstr($update_menus['keyword_value'][$key],'/'))return array('res'=>1,'error'=>$val.' - 请选择对应的文章！');
			}
			if($update_menus['is_show'][$key] == 1){
				$update_menus['parent_id'][$key] == 0 ? $menu_p_nums++ : $menu_un_arr[$update_menus['parent_id'][$key]]++;
			}
		}
	
		foreach($add_menus as $key => $v){
			if (empty($v['keyword']) && $v['parent_id'] > 0 && in_array($v['type'],array('view','click')))
			{
				if (empty($v['type'])) return array('res'=>1,'error'=>$v['name'].' - 请选择响应动作类型！');
				if ($v['type'] == 'click') return array('res'=>1,'error'=>$v['name'].' - 请选择对应关键字！');
				if ($v['keyword_value'] == 'http://') return array('res'=>1,'error'=>$v['name'].' - 请填写完整的网址！');
				if (!strstr($v['keyword_value'],'/')) return array('res'=>1,'error'=>$v['name'].' - 请选择对应的文章！');
			}			
			if($v['is_show'] == 1){
				$v['parent_id'] == 0 ? $menu_p_nums++ : $menu_un_arr[$v['parent_id']]++;
			}
			if ($v['parent_id'] == 0 ){
				$have_menus[$key] = $v['name'];
			}
		}
		
		if($menu_p_nums > 3) return array('res'=>1,'error'=>'主菜单最多启用3个，请在启用列勾选您要开启的主菜单！');
		foreach ($menu_un_arr as $key=>$val){
			if ($val > 5) return array('res'=>1,'error'=>'【'.$have_menus[$key].'】下的子菜单启用项超过5个，微信公众号只允许开启5个，请调整您的子菜单选项！');
		}
	
		return array('res'=>0);
	}
    
}
