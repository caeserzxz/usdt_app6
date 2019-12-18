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

    /*------------------------------------------------------ */
    //-- 获取购物车数据
    /*------------------------------------------------------ */
    public function getCartList()
    {
        $fg_id = input('fg_id', '0', 'intval');
        $number = input('number', 0, 'intval');
        $sku_val = input('sku_val', 0, 'trim');
        $fgInfo = (new FightGroupModel)->info($fg_id);
        if (empty($fgInfo)) return $this->error('拼团不存在.');
        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        if ($goods['is_spec'] == 1) {//多规格处理
            if (empty($sku_val)) {
                return $this->error('传参错误-1.');
            }
            if (empty($goods['sub_goods'][$sku_val])) {
                return $this->error('传参错误-2.');
            }
            $return['buyGoods'] = $goods['sub_goods'][$sku_val];
        } else {
            $return['buyGoods'] = $goods;
        }

        $return['buyGoods']['goods_name'] = $goods['goods_name'];
        $return['totalGoodsPrice'] = sprintf("%.2f", $return['buyGoods']['sale_price'] * $number);
        $return['orderTotal'] = $return['totalGoodsPrice'];
        if ($goods['is_spec'] == 1) {//多规格
            $skuImgs = (new GoodsModel)->getImgsList($fgInfo['goods_id'], true, true);//获取sku图片
            $return['goods_img'] = empty($skuImgs[$sku_val]) ? $goods['goods_thumb'] : $skuImgs[$sku_val];
        } else {
            $return['goods_img'] = $goods['goods_thumb'];
        }

        $goods['goods_id'] = $fgInfo['fg_id'];

        $goods['goods_id'] = $fgInfo['fg_id'];
        $return['goodsList'][$goods['goods_id'] . '_' . $goods['sku_val']] = $goods;
        return $return;
    }

}
