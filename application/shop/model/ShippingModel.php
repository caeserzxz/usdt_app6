<?php

namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 物流管理
/*------------------------------------------------------ */
class ShippingModel extends BaseModel
{
	protected $table = 'shop_shipping';
	public  $pk = 'shipping_id';
	protected static $mkey = 'shipping_list';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm(self::$mkey.'toTpl');
        Cache::rm(self::$mkey);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public static function getRows($where_filed ='shipping_code'){
        $data = Cache::get(self::$mkey . $where_filed);
        if ($data) return $data;
        $rows = self::field('*,shipping_id as id,shipping_name as name')->where($where_filed . " != ''")->select()->toArray();
        foreach ($rows as $row) {
            $data[$row['shipping_id']] = $row;
        }
        Cache::set(self::$mkey . $where_filed, $data, 600);
        return $data;
	}
    /*------------------------------------------------------ */
    //-- 运费模板调用
    /*------------------------------------------------------ */
    public static function getToSTRows(){
        $mkey = self::$mkey.'toTpl';
        //$data = Cache::get($mkey);
        if ($data) return $data;
        $data['DEFSP'] = ['shipping_name'=>'默认快递','status'=>1];
        /*$rows = self::select()->toArray();
        foreach ($rows as $row){
            $data[$row['shipping_code']] = $row;
        }*/
        Cache::set($mkey,$data,600);
        return $data;
    }

}
