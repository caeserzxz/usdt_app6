<?php

namespace app\integral\model;

use app\BaseModel;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;

//*------------------------------------------------------ */
//-- 积分商品子表
/*------------------------------------------------------ */

class IntegralGoodsListModel extends BaseModel
{
    protected $table = 'integral_goods_list';
    public  $pk = 'gid';

    /*------------------------------------------------------ */
    //-- 订单商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalGoodsStore(&$goodsList = array(), $type = 'addOrder')
    {
        $GoodsSkuModel = new GoodsSkuModel();
        $GoodsModel =  new GoodsModel();
        $time = time();
        foreach ($goodsList as $grow) {

            if ($type == 'cancel') {
                $iupData['sale_num'] = ['DEC', $grow['goods_number']];
            } else {
                $iupData['sale_num'] = ['INC', $grow['goods_number']];
            }
            $iupData['update_time'] = $time;
            $iwhere['goods_id'] = $grow['goods_id'];
            $iwhere['sku_id'] = $grow['sku_id'];
            $res = $this->where($iwhere)->update($iupData);
            if ($res < 1) return false;

            if ($grow['sku_id'] > 0) {//多规格商品执行
                if ($type == 'cancel') {
                    $sub_data['goods_number'] = ['INC', $grow['goods_number']];
                    $data['sale_num'] = ['DEC', $grow['goods_number']];
                } else {
                    $sub_data['goods_number'] = ['DEC', $grow['goods_number']];
                    $data['sale_num'] = ['INC', $grow['goods_number']];
                }
                $sub_map['goods_id'] = $grow['goods_id'];
                $sub_map['sku_id'] = $grow['sku_id'];
                $res = $GoodsSkuModel->where($sub_map)->update($sub_data);
                if ($res < 1) return false;
                $res = $GoodsModel->where('goods_id', $grow['goods_id'])->update($data);
                if ($res < 1) return false;
            } else {
                if ($type == 'cancel') {
                    $data['goods_number'] = ['INC', $grow['goods_number']];
                    $data['sale_num'] = ['DEC', $grow['goods_number']];
                } else {
                    $data['goods_number'] = ['DEC', $grow['goods_number']];
                    $data['sale_num'] = ['INC', $grow['goods_number']];
                }
                $res = $GoodsModel->where('goods_id', $grow['goods_id'])->update($data);
                if ($res < 1) return false;
            }
            $GoodsModel->cleanMemcache($grow['goods_id']);
        }
        return true;
    }

}
