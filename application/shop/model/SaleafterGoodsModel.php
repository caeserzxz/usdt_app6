<?php
namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 幻灯片
/*------------------------------------------------------ */

class SaleafterGoodsModel extends BaseModel
{
    protected $table = 'shop_saleafter_goods';
    public $pk = 'saleafter_goods_id';
    protected static $mkey = 'shop_saleafter_goods_list';

    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        Cache::rm(self::$mkey);
    }

    /**
     * 提交申请售后资料
     * @param $data
     * @return string|void
     */
    public function addSaleafterGoods($data)
    {
        try {
            $OrderModel = new OrderModel();
            $orderinfo = $OrderModel->info($data['order_id']);
            if ($orderinfo['sign_time'] == 0) throw new \Exception("未收货不能申请售后");
            $shop_after_sale_limit = getmainsettings('shop_after_sale_limit');
            if ($shop_after_sale_limit == 0) throw new \Exception("不支持申请售后");
            $sign_time = $orderinfo['sign_time'] + 86400 * $shop_after_sale_limit;
            if ($sign_time < time()) throw new \Exception("不支持申请售后");
            $OrderGoodsModel = new OrderGoodsModel();
            $ordergoodsinfo = $OrderGoodsModel->ordergoodsfind(['rec_id' => $data['rec_id']]);
            if ($ordergoodsinfo['has_asleafter'] == 1) throw new \Exception("您已经申请过售后，不能重复申请");
            $return = $this->insertGetId($data);
            if ($return) {
                $OrderGoodsModel->updateOrderGoods(['rec_id' => $data['rec_id']], ['has_asleafter' => 1]);
                return $data;
            } else {
                throw new \Exception("网络繁忙，请稍后重试");
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 查询当前用户资料存入缓存中
     * @param $user_id
     * @return array|mixed|\PDOStatement|string|\think\Collection\
     */
    public function saleafterlist($user_id)
    {
        $redis_name = 'saleafter_goods_list_' . $user_id;
        //Cache::rm($redis_name);
        $saleaftergoods = Cache::get($redis_name);
        if (empty($saleaftergoods)) {
            $saleaftergoods = $this->where(['user_id' => $user_id])->select();
            foreach ($saleaftergoods as $key => $val) {
                $_t = $val;
                if ($val['status'] == 0) {
                    $val['status_name'] = "申请中";
                } elseif ($val['status'] == 1) {
                    $val['status_name'] = "已审核";
                } elseif ($val['status'] == 2) {
                    $val['status_name'] = "已退款";
                } elseif ($val['status'] == 3) {
                    $val['status_name'] = "已拒绝";
                } else{
                    $val['status_name'] = "";
                }
                $_t['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
                $saleaftergoods[$key] = $_t;
            }
            Cache::set($redis_name, $saleaftergoods, 600);
        }
        return page_array($saleaftergoods);
    }

    public function info($where){
        $data = $this->where($where)->find();
        return $data;
    }



}
