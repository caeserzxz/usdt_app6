<?php

namespace app\shop\model;
use app\BaseModel;
use app\mainadmin\model\RegionModel;
use think\facade\Cache;
use kuaidi\Kdapieorder;
use app\shop\model\OrderGoodsModel;
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
        $data = Cache::get($mkey);
        if ($data) return $data;
        $data['DEFSP'] = ['shipping_name'=>'默认快递','status'=>1];
        /*$rows = self::select()->toArray();
        foreach ($rows as $row){
            $data[$row['shipping_code']] = $row;
        }*/
        Cache::set($mkey,$data,600);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 快递鸟发货
    /*------------------------------------------------------ */
    public function kdnShipping(&$shipping,&$orderInfo = []){
        $OrderGoodsModel = new OrderGoodsModel();
        static $kdapiorder;
        if (empty($shipping)) return "请选择快递公司";
        $kdn_appid = settings('kdn_appid');
        $kdn_apikey = settings('kdn_apikey');
        $kdn_apiurl = settings('kdn_apiurl');
        $kdn_name = settings('kdn_name');
        $kdn_phone = settings('kdn_phone');
        $kdn_province = settings('kdn_province');
        $kdn_city = settings('kdn_city');
        $kdn_area = settings('kdn_area');
        $kdn_address = settings('kdn_address');
        $kdn_postcode = settings('kdn_postcode');
        if ($kdn_appid == "") return "请先配置电商ID（快递鸟）";
        if ($kdn_apikey == "") return "请先配置电商加密私钥（快递鸟）";
        if ($kdn_apiurl == "") return "请先配置接口地址（快递鸟）";
        if ($kdn_name == "") return "请先配置寄件人名称（快递鸟）";
        if ($kdn_phone == "") return "请先配置联系电话（快递鸟）";
        if ($kdn_province == "") return "请先配置地区（快递鸟）";
        if ($kdn_city == "") return "请先配置地区（快递鸟）";
        if ($kdn_area == "") return "请先配置地区（快递鸟）";
        if ($kdn_address == "") return "请先配置详情地址（快递鸟）";
        if (!isset($kdapiorder)) {
            $kd_config = [];
            $kd_config['kd_id'] = $kdn_appid; //电商ID
            $kd_config['ke_appkey'] = $kdn_apikey; //电商加密私钥
            $kd_config['ke_requrl'] = $kdn_apiurl;
            $kdapiorder = new Kdapieorder($kd_config);
        }
        //$customer_name = $this->field('customer_name','customer_pwd')->where(['shipping_id' => $kdn_shipping_id])->find();
        //构造电子面单提交信息
        $regionfrist = new RegionModel();
        $district = $regionfrist->info($orderInfo['district']);

        $eorder = [];
        $eorder["ShipperCode"] = $shipping['kdn_code']; //发件地邮编
        $eorder["OrderCode"] = $orderInfo['order_sn'];
        $eorder["CustomerName"] = $shipping['customer_name'];
        $eorder["CustomerPwd"] = $shipping['customer_pwd'];
        $eorder["PayType"] = 1;
        $eorder["ExpType"] = 1;
        $eorder["IsReturnPrintTemplate"] = 1;
        //月结号或秘钥串
        $shipping['month_code']?$eorder["MonthCode"] = $shipping['month_code']:false;
        //所属网点
        $shipping['send_site']?$eorder["SendSite"] = $shipping['send_site']:false;
        //模板ID
        $shipping['template_size']?$eorder["TemplateSize"] = $shipping['template_size']:false;

        //寄件人
        $sender = [];
        $sender["Name"] = $kdn_name;
        $sender["Mobile"] = $kdn_phone;
        $sender["ProvinceName"] = $kdn_province;
        $sender["CityName"] = $kdn_city;
        $sender["ExpAreaName"] = $kdn_area;
        $sender["Address"] = $kdn_address;
        $sender["PostCode"] = $kdn_postcode;

        //收件人
        list($ProvinceName, $CityName, $ExpAreaName) = $merger_name = explode(',', $orderInfo['merger_name']);
        $receiver = [];
        $receiver["Name"] = $orderInfo['consignee'];
        $receiver["Mobile"] = $orderInfo['mobile'];
        $receiver["ProvinceName"] = $ProvinceName;
        $receiver["CityName"] = $CityName;
        $receiver["ExpAreaName"] = $ExpAreaName;
        $receiver["Address"] = $orderInfo['address'];
        $receiver["PostCode"] = $district['zip_code'];

        $orderGoods = $OrderGoodsModel->where(['order_id'=>$orderInfo['order_id']])->select()->toArray();
        $commodity = [];
        //快递鸟不支持的符号过滤
        $unsetWord = ["'","#","&","+","%","\\","<",">"];
        foreach($orderGoods as $key => $val){
            $commodityOne["GoodsName"] = str_replace($unsetWord,' ', $val['goods_name']);  //商品名称【必填写】
            $commodityOne["GoodsCode"] = $val['goods_sn']; //商品编码【可填写】goods_sn
            $commodityOne["Goodsquantity"] = $val['goods_number']; //商品数量【可填写】
            $commodityOne["GoodsPrice"] =  $val['shop_price']; //商品价格【可填写】
            $commodityOne["GoodsWeight"] = $val['goods_weight']; //商品重量kg【可填写】 goods_weight
            $commodityOne["GoodsDesc"] = ""; //商品描述【可填写】
            $commodityOne["GoodsVol"] = ""; //商品体积m3【可填写】
            $commodity[] = $commodityOne;
        }
        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;
        //调用电子面单
        $returndata = $kdapiorder->submitEOrder($eorder);
        $returndata = json_decode($returndata, true);
        //$returndata = json_decode('{ "EBusinessID": "1350174","Success": true,"Order": {"OrderCode": "1552293275", "ShipperCode": "SF", "LogisticCode": "444071155508", "OriginCode": "755", "DestinatioCode": "755", "KDNOrderCode": "KDN1903111600000041"},"Reason": "成功","ResultCode": "100"}', true);
        if ($returndata['Success'] == false) {
            //$reason = $returndata['Reason']; //错误信息 //{ "EBusinessID": "1350174","Success": false,"Reason": "远程服务器返回错误: (404) 未找到。","ResultCode": "105"}
            return $returndata['Reason'];
        } else {
            $kdorder = $returndata['Order'];
            $ordercode = $kdorder['OrderCode'];//订单号
            $shippercode = $kdorder['ShipperCode'];//物流编号
            $logisticcode = $kdorder['LogisticCode'];//物流订单号
            $origincode = $kdorder['OriginCode'];//物流状态码
            $destinatiocode = $kdorder['DestinatioCode'];
            $kdnordercode = $kdorder['KDNOrderCode'];
            $PrintTemplate = $returndata['PrintTemplate'];  //返回电子面单模板
        }

        return [$shipping,$logisticcode,$PrintTemplate];
    }
}
