<?php
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
function encode($dir, $new_dir){
    $dir = rtrim($dir, '/');
    $new_dir = rtrim($new_dir, '/');
    $handle = opendir($dir);
    if (!$handle){
        return false;
    }
    makeDir($new_dir);
    while (($file = readdir($handle))) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $path = $dir . '/' . $file;
        if (is_dir($path)){
            encode($path, $new_dir.'/'.$file);
            continue;
        }

        if (strstr($file,'.php') == true){
            beast_encode_file($path, $new_dir . '/' . $file);
            echo 'rm -f '.$path."\n";
        }

    }
    closedir($handle);
}

encode('./files','./encode_files');
$dirname = dirname(dirname(__FILE__));
$_dirname = dirname($dirname);

echo 'mv -f '.$dirname.'/encode_files/* '.$_dirname."\n";
echo 'mv -f '.$dirname.'/files/* '.$_dirname;