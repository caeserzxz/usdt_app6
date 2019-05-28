<?php
namespace app\fightgroup\model;
use app\BaseModel;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
//*------------------------------------------------------ */
//-- 拼团商品列表
/*------------------------------------------------------ */
class FightGoodsModel extends BaseModel
{
	protected $table = 'fightgroup_goods';
    public  $pk = 'gid';
    /*------------------------------------------------------ */
    //-- 拼团商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalGoodsStore($fg_id,$goods_id = 0,$sku_id = 0,$number = 0,$type = 'addOrder')
    {
        if ($type == 'cancel') {
            $upData['sale_num'] = ['DEC', $number];
        } else {
            $upData['sale_num'] = ['INC', $number];
        }
        $upData['update_time'] = time();
        $where[] = ['fg_id','=',$fg_id];
        $where[] = ['goods_id','=',$goods_id];
        $where[] = ['sku_id','=',$sku_id * 1];
        $res = $this->where($where)->update($upData);
        if ($res < 1) return false;
        $upData = [];
        $GoodsModel = new GoodsModel();
        if ($sku_id > 0){
            $GoodsSkuModel = new GoodsSkuModel();
            if ($type == 'cancel') {
                $subData['goods_number'] = ['INC', $number];
                $upData['sale_num'] = ['DEC', $number];
            } else {
                $subData['goods_number'] = ['DEC', $number];
                $upData['sale_num'] = ['INC', $number];
            }
            $res = $GoodsSkuModel->where('sku_id',$sku_id)->update($subData);
            if ($res < 1) return false;
            $res = $GoodsModel->where('goods_id', $goods_id)->update($upData);
            if ($res < 1) return false;
        }else{
            if ($type == 'cancel') {
                $subData['goods_number'] = ['INC', $number];
                $upData['sale_num'] = ['DEC', $number];
            } else {
                $subData['goods_number'] = ['DEC', $number];
                $upData['sale_num'] = ['INC', $number];
            }
            $res = $GoodsModel->where('goods_id', $goods_id)->update($upData);
            if ($res < 1) return false;
        }

        $GoodsModel->cleanMemcache($goods_id);
        return true;
    }
}
