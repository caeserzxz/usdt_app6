<?php

namespace app\weixin\model;

use app\BaseModel;
use think\Db;
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
		$res = $this->_is_save_menu($add_menu,$save_menu);

        if($res !== true){
		    return $res;
        }
        $time = time();
        $pidList = [];
        Db::startTrans();//启动事务

		if (empty($add_menu) == false) {

            foreach ($add_menu['name'] as $key => $val){
                $inArr = [];
                $inArr['name'] = $val;
                if (empty($add_menu['parent_id'][$key]) == false) {
                    if (is_numeric($add_menu['parent_id'][$key])){
                        $inArr['pid'] = $add_menu['parent_id'][$key]*1;
                    }else{
                        $pkey = $add_menu['parent_id'][$key];
                        $inArr['pid'] = $pidList[$pkey];
                    }
                }
                $inArr['sort'] = $add_menu['sort'][$key];
                if (empty($add_menu['is_show'][$key]) == false){
                    $inArr['is_show'] = 1;
                }
                $inArr['add_time'] = $time;
                $inArr['type'] = $add_menu['type'][$key];
                if ($inArr['type'] == 'click'){
                    $inArr['keyword'] = $add_menu['keyword'][$key];
                }else{
                    $inArr['keyword_value'] = $add_menu['keyword_value'][$key];
                }
                $res = $this->create($inArr);
                if ($res < 1){
                    Db::rollback();// 回滚事务
                    return false;
                }
                if ($inArr['pid'] == 0){
                    $pidList[$key] = $res->id;
                }
            }
        }
		foreach($save_menu as $s_key => $up_menu){
			$where['id'] = $s_key;
			$upArr['pid'] = $up_menu['parent_id'];
            $upArr['sort'] = $up_menu['sort'];
            $upArr['name'] = $up_menu['name'];
            $upArr['keyword'] = $up_menu['keyword'];
            $upArr['keyword_value'] = $up_menu['keyword_value'];
			if($up_menu['is_show']){
                $upArr['is_show'] = $up_menu['is_show'];
			}else{
                $upArr['is_show'] = 0;
			}
            $upArr['update_time'] = time();
            $upArr['type'] = $up_menu['type'];
            if ($upArr['type'] == 'click'){
                $upArr['keyword'] = $up_menu['keyword'];
                $upArr['keyword_value'] = '';
            }else{
                $upArr['keyword_value'] = $up_menu['keyword_value'];
                $upArr['keyword'] = '';
            }
			$res = $this->where($where)->update($upArr);
            if ($res < 1){
                Db::rollback();// 回滚事务
                return false;
            }
        }
        Db::commit();// 提交事务
		return true;
	}
	/*------------------------------------------------------ */
	//-- 判断后台创建的微信菜单数量是否在微信指定内（主菜单3个，每个主菜单的子菜单5个）
	//-- @param 
	//-- @return array
	/*------------------------------------------------------ */
	public function _is_save_menu($add_menus,$update_menus){
		$menu_p_nums =0; $menu_un_arr = array();
		$allPids = [];
        foreach ($add_menus['name'] as $key=>$val){
            if (empty($add_menus['parent_id'][$key]) == false){
                $allPids[] = $add_menus['parent_id'][$key];
            }
        }
		foreach ($add_menus['name'] as $key=>$val){
		    if (empty($add_menus['keyword'][$key]) == true && in_array($key,$allPids) == false){
                if (empty($add_menus['type'][$key])){
                    return $val.' - 请选择响应动作类型.';
                }
                if (in_array($add_menus['type'][$key],array('view','click')) == false){
                    return $val.' - 响应动作类型错误.';
                }
                if ($add_menus['type'][$key] == 'click'){
                    return $val.' - 请选择对应关键字.';
                }
                if ($add_menus['keyword_value'][$key] == 'http://' || $update_menus['keyword_value'][$key] == 'https://'){
                    return $val.' - 请填写完整的网址.';
                }
            }

			if($add_menus['is_show'][$key] == 1){
			    if ($add_menus['parent_id'][$key] == '0'){
                    $menu_p_nums++;
                }else{
                    $menu_un_arr[$add_menus['parent_id'][$key]]++;
                }

			}
		}
        foreach($update_menus as $key => $v){
            if (empty($v['parent_id']) == false){
                $allPids[] = $v['parent_id'];
            }
        }
		foreach($update_menus as $key => $v){
		    if (empty($v['keyword']) == true && in_array($key,$allPids) == false){
                if (empty($v['type'])){
                    return $v['name'].' - 请选择响应动作类型.';
                }
                if (in_array($v['type'],array('view','click')) == false){
                    return $v['name'].' - 响应动作类型错误.';
                }
                if ($v['type'] == 'click'){
                    return $v['name'].' - 请选择对应关键字！';
                }
                if ($v['keyword_value'] == 'http://' || $v['keyword_value'] == 'https://'){
                    return $v['name'].' - 请填写完整的网址.';
                }
            }

			if($v['is_show'] == 1){
				$v['parent_id'] == 0 ? $menu_p_nums++ : $menu_un_arr[$v['parent_id']]++;
			}
			if ($v['parent_id'] == 0 ){
				$have_menus[$key] = $v['name'];
			}
		}
		
		if($menu_p_nums > 3){
		    return '主菜单最多启用3个，请在启用列勾选您要开启的主菜单.';
        }
		foreach ($menu_un_arr as $key=>$val){
			if ($val > 5){
			    return '【'.$have_menus[$key].'】下的子菜单启用项超过5个，微信公众号只允许开启5个，请调整您的子菜单选项.';
            }
		}
	
		return true;
	}
    
}
