<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    
    // 功能菜单
    'prefix'          => 'shop_',
    'DATA_BACKUP_PATH' => DATA_PATH.'sqldata/', //数据库备份根路径
    'DATA_BACKUP_PART_SIZE' => 20971520, //数据库备份卷大小
    'DATA_BACKUP_COMPRESS' => 0, //数据库备份文件是否启用压缩
    'DATA_BACKUP_COMPRESS_LEVEL' => 9, //数据库备份文件压缩级别
	
];
