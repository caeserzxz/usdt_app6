<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-19
 * Time: 16:34
 */

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

class GoodsCommentModel extends BaseModel {
   protected $table = 'shop_goods_comment';
   public $pk = 'id';
   

    // 审核状态 status
    const UNREVIEWED = 1;
    const PASSED = 2;
    const DENIED = 3;

    public  $statusList = [
        self::UNREVIEWED => '待审核',
        self::PASSED => '显示',
        self::DENIED => '隐藏',
    ];

   
}