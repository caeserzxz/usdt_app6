<?php
//*------------------------------------------------------ */
//-- 后台独立公共function
/*------------------------------------------------------ */
/*------------------------------------------------------ */
//-- 获取管理员信息
/*------------------------------------------------------ */ 
function adminInfo($user_id,$returnName=true){
	static $adminList;
	if ($user_id < 1) return $returnName == true ? '--' : [];
	if (!isset($adminList)){
		 $Model = model('app\mainadmin\model\AdminUserModel');	
		 $adminList = $Model->getRows();
	}
	if (empty($adminList[$user_id])) return $returnName == true ? '--' : [];
	$info = $adminList[$user_id];
	if ($returnName == true) return $info['user_name'];
	return $info;
}
/*------------------------------------------------------ */
//-- 获取管理员角色
/*------------------------------------------------------ */ 
function adminRole($role_id,$returnName=true){
	static $roleList;
	if ($role_id < 1) return $returnName == true ? '--' : [];
	if (!isset($roleList)){
		 $Model = model('app\mainadmin\model\AdminRoleModel');	
		 $roleList = $Model->getRows();
	}
	if (empty($roleList[$role_id])) return $returnName == true ? '--' : [];
	$info = $roleList[$role_id];
	if ($returnName == true) return $info['role_name'];
	return $info;
}
/*------------------------------------------------------ */
//--  * 格式化字节大小
//--  * @param  number $size      字节数
//--  * @param  string $delimiter 数字和单位分隔符
//--  * @return string   
/*------------------------------------------------------ */  
function formatBytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/*------------------------------------------------------ */
//-- swf上传跨域，session无法传参时调用
/*------------------------------------------------------ */ 
function editerUploadCkv($val = ''){
	$val = time();
	return md5($val);
}
/*------------------------------------------------------ */
//-- 后台判断是否加载历史查询
/*------------------------------------------------------ */ 
function getSearch(){
	return false;
}
/*------------------------------------------------------ */
//-- 获得所有模块
/*------------------------------------------------------ */ 
function readModules($directory = '.'){
	
    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();
	$i = 0;
    while (false !== ($file = @readdir($dir))){
        if (preg_match("/^.*?\.php$/", $file)){
            include_once($directory. '/' .$file);
			$modules[$i]["function"] = str_replace('.php','',$file);
			$i++;
        }
    }
    @closedir($dir);
    unset($set_modules);
    foreach ($modules as $key => $value)    {
        ksort($modules[$key]);
    }
    ksort($modules);
    return $modules;
}
/*------------------------------------------------------ */
//-- 创建目录
/*------------------------------------------------------ */   
function makeDir($folder){
    $reval = false;
    if (!file_exists($folder)){
        /* 如果目录不存在则尝试创建该目录 */
        @umask(0);
        /* 将目录路径拆分成数组 */
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
        /* 如果第一个字符为/则当作物理路径处理 */
        $base = ($atmp[0][0] == '/') ? '/' : '';
        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] as $val){
            if ('' != $val){
                $base .= $val;
                if ('..' == $val || '.' == $val){
                    /* 如果目录为.或者..则直接补/继续下一个循环 */
                    $base .= '/';
                    continue;
                }
            }else{continue;}
            $base .= '/';
            if (!file_exists($base)){
                /* 尝试创建目录，如果创建失败则继续循环 */
                if (@mkdir(rtrim($base, '/'), 0777)){
                    @chmod($base, 0777);
                    $reval = true;
                }
            }
        }
    }else{
        /* 路径已经存在。返回该路径是不是一个目录 */
        $reval = is_dir($folder);
    }
    clearstatcache();
    return $reval;
}