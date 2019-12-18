<?php

namespace app\favour\model;

use app\BaseModel;

//*------------------------------------------------------ */
//-- 限时优惠--商品信息
/*------------------------------------------------------ */

class FavourGoodsInfoModel extends BaseModel
{
    protected $table = 'favour_goods_info';

    /*------------------------------------------------------ */
    //-- 更新
    /*------------------------------------------------------ */
    public function upInfo($data, $where)
    {
        $data['update_time'] = time();
        $res = $this->where($where)->update($data);
        if ($res < 1) return false;
        return true;
    }
}
