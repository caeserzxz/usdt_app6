<?php
namespace app\shop\controller\sys_admin;

use app\AdminController;

use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\OrderLogModel;
use app\shop\model\ShippingModel;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendModel;
use think\Lang;
use Kuaidi\Kdapieorder;

/**
 * 订单相关
 * Class Index
 * @package app\store\controller
 */
class Order extends AdminController
{

    private $shipping_model = null;
    private $setting_model = null;
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new OrderModel();
        $this->shipping_model = new ShippingModel();
        $this->setting_model = new SettingsModel();
        $this->store_id = 0;//当前默认为总后台，门店值默认为0
    }

    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_cancel = false)
    {
        $where[] = ['store_id', '=', $this->store_id];
        $time_type = input('time_type', '', 'trim');
        $export = input('export', 0, 'intval');
        if (empty($time_type) == false) {
            $search['start_time'] = input('start_time', '', 'trim');
            $search['end_time'] = input('end_time', '', 'trim');
            $start_time = $search['start_time'] ? strtotime($search['start_time']) : strtotime("-1 months");
            $end_time = $search['end_time'] ? strtotime($search['end_time']) : time();
            $where[] = [$time_type, 'between', array($start_time, $end_time)];
        } else {
            $search['state'] = input('state', 0, 'intval');
            $reportrange = input('reportrange', '', 'trim');
            if (empty($reportrange) == false) {
                $dtime = explode('-', $reportrange);
                $where[] = ['add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1]) + 86399]];
            } else {
                $where[] = ['add_time', 'between', [strtotime("-1 months"), time()]];
            }
            if ($is_cancel == true) {
                $where[] = ['order_status', '=', config('config.OS_CANCELED')];
            }

        }

        $search['order_sn'] = input('order_sn', '', 'trim');
        if ($search['order_sn']) $where[] = ['order_sn', '=', $search['order_sn']];

        $search['user_id'] = input('user_id', 0, 'intval');
        $search['user_mobile'] = input('user_mobile', '', 'trim');
        if ($search['user_id'] > 0) $where[] = ['user_id', '=', $search['user_id']];

        $search['consignee'] = input('consignee', '', 'trim');
        if ($search['consignee']) $where[] = ['consignee', 'like', '%' . $search['consignee'] . '%'];
        $search['address'] = input('address', '', 'trim');
        if ($search['address']) $where[] = ['address', 'like', '%' . $search['address'] . '%'];

        $search['tel'] = input('tel', '', 'trim');
        if ($search['tel']) {
            $where[] = ['tel', '=', $search['tel']];
        }
        $search['mobile'] = input('mobile', '', 'trim');
        if ($search['mobile']) {
            $where[] = ['mobile', '=', $search['mobile']];
        }
        //省市区
        $search['province'] = input('province', 0, 'intval');
        $search['city'] = input('city', 0, 'intval');
        $search['district'] = input('district', 0, 'intval');
        if ($search['district'] > 0) {
            $where[] = ['district', '=', $search['district']];
        } elseif ($search['city'] > 0) {
            $where[] = ['city', '=', $search['city']];
        } elseif ($search['province'] > 0) {
            $where[] = ['province', '=', $search['province']];
        }//省市区end
        $search['shipping_id'] = input('shipping_id', 0, 'intval');
        if ($search['shipping_id']) $where[] = ['shipping_id', '=', $search['shipping_id']];
        $search['pay_id'] = input('pay_id', 0, 'intval');
        if ($search['pay_id']) $where[] = ['pay_id', '=', $search['pay_id']];

        $search['order_status'] = input('order_status', 0, 'intval');
        if ($search['order_status'] > 0) $where[] = ['order_status', '=', $search['order_status']];
        $search['pay_status'] = input('pay_status', 0, 'intval');
        if ($search['pay_status'] > 0) $where[] = ['pay_status', '=', $search['pay_status']];
        $search['shipping_status'] = input('shipping_status', 0, 'intval');
        if ($search['shipping_status'] > 0) $where[] = ['shipping_status', '=', $search['shipping_status']];


        $search['keyword'] = input('keyword', '', 'trim');
        if (!empty($search['keyword'])) {
            $search['searchBy'] = input('searchBy', '', 'trim');
            //综合状态
            switch ($search['searchBy']) {
                case 'consignee':
                    $where[] = ['consignee', 'like', $search['keyword'] . '%'];
                    break;
                case 'goods_sn':
                    $where['and'][] = "FIND_IN_SET('" . $search['keyword'] . "', buy_goods_sn)";
                    break;
                case 'mobile':
                    $where[] = ['mobile', 'like', $search['keyword'] . '%'];
                    break;
                case 'order_sn':
                    $where[] = ['order_sn', 'like', $search['keyword'] . '%'];
                    break;
                default:
                    break;
            }
        }
        $config = config('config.');
        //综合状态
        switch ($search['state']) {
            /**,待确认**/
            case "1" :
                $where[] = ['order_status', '=', $config['OS_UNCONFIRMED']];
                $where[] = ['is_pay', '=', 0];
                break;
            /**,待支付**/
            case "2" :
                $where[] = ['order_status', 'in', [$config['OS_CONFIRMED'], $config['OS_UNCONFIRMED']]];
                $where[] = ['pay_status', '=', $config['PS_UNPAYED']];
                $where[] = ['is_pay', 'in', [1, 2]];
                break;
            /**,待发货**/
            case "3" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_UNSHIPPED']];
                break;
            /**已发货**/
            case "4" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_SHIPPED']];
                break;
            /**已完成**/
            case "5" :
                $where[] = ['order_status', '=', $config['OS_CONFIRMED']];
                $where[] = ['shipping_status', '=', $config['SS_SIGN']];
                break;
            /**已退货**/
            case "6" :
                $where[] = ['order_status', '=', $config['OS_RETURNED']];
                break;
            /**待退款**/
            case "7" :
                $where[] = ['order_status', 'in', [$config['OS_CANCELED'], $config['OS_RETURNED']]];
                $where[] = ['pay_status', '=', $config['PS_PAYED']];
                break;
            /**已退款**/
            case "8" :
                $where[] = ['pay_status', '=', $config['PS_RUNPAYED']];
                break;
            default:
                break;
        }
        $export = input('export', 0, 'intval');
        if ($export > 0) {
            return $this->exportOrder($where);
        }
        $data = $this->getPageList($this->Model, $where, 'order_id');
        $this->assign("is_cancel", $is_cancel);
        foreach ($data['list'] as $key => $row) {
            $data['list'][$key] = $this->Model->info($row['order_id']);
        }
        $this->assign("orderLang", lang('order'));
        $this->assign("data", $data);
        $this->assign("search", $search);
        if ($runData == false) {
            $data['content'] = $this->fetch('list');
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单详细页
    /*------------------------------------------------------ */
    public function info()
    {
        $order_id = input('order_id', 0, 'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);
        $orderLog = (new OrderLogModel)->where('order_id', $order_id)->order('log_id DESC')->select()->toArray();
        $this->assign("orderLog", $orderLog);
        $this->assign("orderLang", lang('order'));
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        $this->assign("operating", $operating);
        $this->assign('orderInfo', $orderInfo);
        $dividend_log = (new DividendModel)->where('order_id', $order_id)->order('level ASC')->select()->toArray();
        $this->assign('dividend_log', $dividend_log);
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 发货管理
    /*------------------------------------------------------ */
    public function shipping()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $ShippingModel = new ShippingModel();
        $shipping = $ShippingModel->getRows();
        if ($this->request->isPost()) {
            $config = config('config.');
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['shipping'] !== true) return $this->error('订单当前状态不能操作改价.');
            $data['order_id'] = $order_id;
            $kd_type = input('post.kd_type', '1', 'intval');
            $kdn_shipping_id = input('post.kdn_shipping_id', '', 'intval');
            $kdeorder_goods_name = input('post.kdeorder_goods_name', '', 'trim');
            if ($kd_type == 3) {
                $shippinginfo = $this->shipping_model->getVal(['shipping_id' => $kdn_shipping_id], 'kdn_code');
                if ($kdn_shipping_id == "") return $this->error("请选择快递公司");
                if ($shippinginfo == "") return $this->error("请选择快递公司");
                if ($kdeorder_goods_name == "") return $this->error("请输入货物名称");
                $kdn_appid = $this->setting_model->getVal(['name' => 'kdn_appid'], 'data');
                $kdn_apikey = $this->setting_model->getVal(['name' => 'kdn_apikey'], 'data');
                $kdn_apiurl = $this->setting_model->getVal(['name' => 'kdn_apiurl'], 'data');
                $kdn_name = $this->setting_model->getVal(['name' => 'kdn_name'], 'data');
                $kdn_phone = $this->setting_model->getVal(['name' => 'kdn_phone'], 'data');
                $kdn_province = $this->setting_model->getVal(['name' => 'kdn_province'], 'data');
                $kdn_city = $this->setting_model->getVal(['name' => 'kdn_city'], 'data');
                $kdn_area = $this->setting_model->getVal(['name' => 'kdn_area'], 'data');
                $kdn_address = $this->setting_model->getVal(['name' => 'kdn_address'], 'data');
                if ($kdn_appid == "") return $this->error("请先配置电商ID（快递鸟）");
                if ($kdn_apikey == "") return $this->error("请先配置电商加密私钥（快递鸟）");
                if ($kdn_apiurl == "") return $this->error("请先配置接口地址（快递鸟）");
                if ($kdn_name == "") return $this->error("请先配置寄件人名称（快递鸟）");
                if ($kdn_phone == "") return $this->error("请先配置联系电话（快递鸟）");
                if ($kdn_province == "") return $this->error("请先配置地区（快递鸟）");
                if ($kdn_city == "") return $this->error("请先配置地区（快递鸟）");
                if ($kdn_area == "") return $this->error("请先配置地区（快递鸟）");
                if ($kdn_address == "") return $this->error("请先配置详情地址（快递鸟）");
                $kd_config = [];
                $kd_config['kd_id'] = $kdn_appid; //电商ID
                $kd_config['ke_appkey'] = $kdn_apikey; //电商加密私钥
                $kd_config['ke_requrl'] = $kdn_apiurl;
                $kdapiorder = new Kdapieorder($kd_config);

                //构造电子面单提交信息
                $eorder = [];
                $eorder["ShipperCode"] = $shippinginfo; //发件地邮编
                $eorder["OrderCode"] = $orderInfo['order_sn'];
                $eorder["PayType"] = 1;
                $eorder["ExpType"] = 1;

                //寄件人
                $sender = [];
                $sender["Name"] = $kdn_name;
                $sender["Mobile"] = $kdn_phone;
                $sender["ProvinceName"] = $kdn_province;
                $sender["CityName"] = $kdn_city;
                $sender["ExpAreaName"] = $kdn_area;
                $sender["Address"] = $kdn_address;

                //收件人
                list($ProvinceName, $CityName, $ExpAreaName) = $merger_name = explode(',', $orderInfo['merger_name']);
                $receiver = [];
                $receiver["Name"] = $orderInfo['consignee'];
                $receiver["Mobile"] = $orderInfo['mobile'];
                $receiver["ProvinceName"] = $ProvinceName;
                $receiver["CityName"] = $CityName;
                $receiver["ExpAreaName"] = $ExpAreaName;
                $receiver["Address"] = $orderInfo['address'];

                $commodityOne = [];
                $commodityOne["GoodsName"] = $kdeorder_goods_name; //商品名称【必填写】
                $commodityOne["GoodsCode"] = ""; //商品编码【可填写】
                $commodityOne["Goodsquantity"] = ""; //商品数量【可填写】
                $commodityOne["GoodsPrice"] = ""; //商品价格【可填写】
                $commodityOne["GoodsWeight"] = ""; //商品重量kg【可填写】
                $commodityOne["GoodsDesc"] = ""; //商品描述【可填写】
                $commodityOne["GoodsVol"] = ""; //商品体积m3【可填写】
                $commodity = [];
                $commodity[] = $commodityOne;
                $eorder["Sender"] = $sender;
                $eorder["Receiver"] = $receiver;
                $eorder["Commodity"] = $commodity;
                //调用电子面单

                $returndata = $kdapiorder->submitEOrder($eorder);
                $returndata = json_decode($returndata, true);
                //$returndata = json_decode('{ "EBusinessID": "1350174","Success": true,"Order": {"OrderCode": "1552293275", "ShipperCode": "SF", "LogisticCode": "444071155508", "OriginCode": "755", "DestinatioCode": "755", "KDNOrderCode": "KDN1903111600000041"},"Reason": "成功","ResultCode": "100"}', true);
                if ($returndata['Success'] == false) {
                    //$reason = $returndata['Reason']; //错误信息 //{ "EBusinessID": "1350174","Success": false,"Reason": "远程服务器返回错误: (404) 未找到。","ResultCode": "105"}
                    return $this->error($returndata['Reason']);
                } else {
                    $kdorder = $returndata['Order'];
                    $ordercode = $kdorder['OrderCode'];//订单号
                    $shippercode = $kdorder['ShipperCode'];//物流编号
                    $logisticcode = $kdorder['LogisticCode'];//物流订单号
                    $origincode = $kdorder['OriginCode'];//物流状态码
                    $destinatiocode = $kdorder['DestinatioCode'];
                    $kdnordercode = $kdorder['KDNOrderCode'];
                }
                $data['shipping_id'] = $kdn_shipping_id;
                $data['invoice_no'] = $logisticcode;
            } else {
                $shipping_id = input('post.shipping_id', 0, 'intval');
                $invoice_no = input('post.invoice_no', '', 'trim');
                if ($shipping_id == "") return $this->error("请选择快递公司");
                if ($kdeorder_goods_name == "") return $this->error("请输入快递单号");
                $data['shipping_id'] = $shipping_id;
                $data['shipping_name'] = $shipping[$data['shipping_id']]['shipping_name'];
                $data['invoice_no'] = $invoice_no;
            }
            $data['shipping_status'] = $config['SS_SHIPPED'];
            $data['shipping_time'] = time();
            $res = $this->Model->upInfo($data);
            if ($res < 1) return $this->error();
            $orderInfo['shipping_status'] = $data['shipping_status'];
            $this->Model->_log($orderInfo, '操作发货');
            return $this->success('操作发货成功！', url('info', array('order_id' => $order_id)));
        }
        $this->assign('shippingOpt', arrToSel($shipping, $orderInfo['shipping_id']));
        $this->assign('kdnpingopt', arrToSel($shipping, $orderInfo['shipping_id'], false, 0, 1));
        $this->assign('orderInfo', $orderInfo);
        return response($this->fetch('shipping'));
    }
    /*------------------------------------------------------ */
    //-- 改价
    /*------------------------------------------------------ */
    public function changePrice()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $config = config('config.');
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['changePrice'] !== true) return $this->error('订单当前状态不能操作改价.');
            $data['shipping_fee'] = input('fee', 0, 'intval');
            $order_amount = input('amount', 0, 'floatval');
            $data['order_amount'] = $order_amount + $data['shipping_fee'];
            $data['diy_discount'] = $orderInfo['goods_amount'] - $order_amount - $orderInfo['discount'] - $orderInfo['integral_money'] - $orderInfo['buy_again_discount'] - $orderInfo['use_bonus'];
            $data['is_dividend'] = 0;
            $data['money_paid'] = 0;
            $data['order_id'] = $order_id;
            $res = $this->Model->upInfo($data, 'changePrice');
            if ($res < 1) return $this->error();
            $this->Model->_log($orderInfo, '修改价格为：' . $order_amount);
            return $this->success('修改价格成功！', url('info', array('order_id' => $order_id)));
        }
        $this->assign('orderInfo', $orderInfo);
        return response($this->fetch('change_price'));
    }

    /*------------------------------------------------------ */
    //-- 线下支付收款确认
    /*------------------------------------------------------ */
    public function cfmCodPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        if ($this->request->isPost()) {
            $config = config('config.');
            if ($orderInfo['is_pay'] != 2) return $this->error('非线下支付订单，不允许操作！');
            $operating = $this->Model->operating($orderInfo);//订单操作权限
            if ($operating['cfmCodPay'] !== true) return $this->error('订单当前状态不能操作线下打款确认.');
            $data['order_status'] = $config['OS_CONFIRMED'];
            $data['pay_status'] = $config['PS_PAYED'];
            if ($orderInfo['confirm_time'] < 1) {
                $data['confirm_time'] = time();
            }
            $data['pay_time'] = time();
            $data['money_paid'] = $orderInfo['order_amount'];
            $data['cfmpay_user'] = AUID;
            $data['order_amount'] = 0;
            $data['order_id'] = $order_id;
            $res = $this->Model->upInfo($data);
            if ($res < 1) return $this->error();
            $orderInfo['order_status'] = $data['order_status'];
            $orderInfo['pay_status'] = $data['pay_status'];
            // 根据购买金额，判断是否升级分销身份
            // $res = D('DividendRole')->evalUpLevel($row);
            $this->Model->_log($orderInfo, '线下支付收款确认：' . input('pay_no', '', 'trim'));
            return $this->success('线下支付收款确认成功！', url('info', array('order_id' => $order_id)));
        }
        $this->assign('orderInfo', $orderInfo);
        return response($this->fetch('cfm_cod_pay'));
    }
    /*------------------------------------------------------ */
    //-- 取消订单
    /*------------------------------------------------------ */
    public function cancel()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['store_id'] > 0) return $this->error('此订单只有门店有操作权限.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['isCancel'] !== true) return $this->error('订单当前状态不能操作取消.');
        $data['order_id'] = $order_id;
        $data['order_status'] = $config['OS_CANCELED'];
        $data['cancel_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['order_status'] = $data['order_status'];
        $this->Model->_log($orderInfo, '取消订单');
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 未发货
    /*------------------------------------------------------ */
    public function unshipping()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['unshipping'] !== true) return $this->error('订单当前状态不能操作未发货.');
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_UNSHIPPED'];
        $data['shipping_time'] = 0;
        $data['invoice_no'] = '';
        $data['shipping_name'] = '';
        $data['shipping_id'] = 0;
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['shipping_status'] = $config['SS_UNSHIPPED'];
        $this->Model->_log($orderInfo, '设为未发货,原发货信息：' . $orderInfo['shipping_name'] . '，单号：' . $data['invoice_no']);
        return $this->success('设为未发货成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 设置为已签收
    /*------------------------------------------------------ */
    public function sign()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        $operating = $this->Model->operating($orderInfo);//订单操作权限
        if ($operating['sign'] !== true) return $this->error('订单当前状态不能操作未发货.');
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_SIGN'];
        $data['sign_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $this->Model->_log($orderInfo, '设为签收');
        return $this->success('设为签收成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 设置为未签收
    /*------------------------------------------------------ */
    public function unsign()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['shipping_status'] != $config['SS_SIGN']) return $this->error('订单不是签收状态，无法设为未签收！');
        //判断是否已分成 已分成订单不能设为未签收
        $data['order_id'] = $order_id;
        $data['shipping_status'] = $config['SS_SHIPPED'];
        $data['sign_time'] = 0;
        $res = $this->Model->upInfo($data, 'unsign');
        if ($res < 1) return $this->error();
        $orderInfo['shipping_status'] = $data['shipping_status'];
        $this->Model->_log($orderInfo, '设为未签收');
        return $this->success('设为未签收成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 设置为退货
    /*------------------------------------------------------ */
    public function returned()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['shipping_status'] != $config['SS_SHIPPED'] && $orderInfo['shipping_status'] != $config['SS_SIGN']) return $this->error('订单不是发货/签收状态，无法设为退货！');

        $data['order_id'] = $order_id;
        $data['order_status'] = $config['OS_RETURNED'];
        $data['returned_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['order_status'] = $data['order_status'];
        $this->Model->_log($orderInfo, '设为退货');
        return $this->success('设为退货成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 设置为未付款
    /*------------------------------------------------------ */
    public function setUnPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['pay_status'] != $config['PS_PAYED']) return $this->error('订单不是付款状态，无法设为未付款！');
        $data['order_id'] = $order_id;
        $data['pay_status'] = $config['PS_UNPAYED'];
        $data['pay_time'] = 0;
        $data['money_paid'] = 0;
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['pay_status'] = $data['pay_status'];
        $this->Model->_log($orderInfo, '设为未付款');
        return $this->success('设为未付款成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 设置为退款
    /*------------------------------------------------------ */
    public function returnPay()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['pay_status'] != $config['PS_PAYED']) return $this->error('订单不是付款状态，无法设为退款！');
        $data['order_id'] = $order_id;
        $data['pay_status'] = $config['PS_RUNPAYED'];
        $data['tuikuan_money'] = $orderInfo['money_paid'];
        $data['tuikuan_time'] = time();
        $res = $this->Model->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['pay_status'] = $data['pay_status'];
        $this->Model->_log($orderInfo, '设为退款');
        return $this->success('设为退款成功！', url('info', array('order_id' => $order_id)));
    }
    /*------------------------------------------------------ */
    //-- 订单确认
    /*------------------------------------------------------ */
    public function confirmed()
    {
        $order_id = input('id', 0, 'intval');
        $orderInfo = $this->Model->info($order_id);
        $config = config('config.');
        if ($orderInfo['order_status'] == $config['OS_CONFIRMED']) return $this->error('订单已经是已确认，无需修改！');
        $data['order_id'] = $order_id;
        $data['order_status'] = $config['OS_CONFIRMED'];
        $data['pay_status'] = $config['PS_UNPAYED'];
        $data['confirm_time'] = time();
        $data['cancel_time'] = 0;
        $data['money_paid'] = 0;
        $data['tuikuan_money'] = 0;
        $data['tuikuan_time'] = 0;
        $res = $this->_mod->upInfo($data);
        if ($res < 1) return $this->error();
        $orderInfo['order_status'] = $data['order_status'];
        $orderInfo['pay_status'] = $data['pay_status'];
        $this->Model->_log($orderInfo, '设为已确认');
        return $this->success();
    }
    /*------------------------------------------------------ */
    //-- 查询主页
    /*------------------------------------------------------ */
    public function search()
    {
        $ShippingModel = new ShippingModel();
        $this->assign("shippingList", $ShippingModel->getRows());
        $PaymentModel = new \app\mainadmin\model\PaymentModel();
        $this->assign("paymentList", $PaymentModel->getRows());
        $this->assign("orderLang", lang('order'));
        return $this->fetch('search');
    }

    /*------------------------------------------------------ */
    //-- 回收站
    /*------------------------------------------------------ */
    public function trash()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->trashList(true, true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 商品回收站查询
    /*------------------------------------------------------ */
    public function trashList($runData = false)
    {
        return $this->getList($runData, 1);
    }

    /*------------------------------------------------------ */
    //-- 导出订单
    /*------------------------------------------------------ */
    public function exportOrder($where)
    {

        $count = $this->Model->where($where)->count('order_id');

        if ($count < 1) return $this->error('没有找到可导出的订单资料！');
        $filename = '订单资料_' . date("YmdHis") . '.xls';
        $export_arr['订单编号'] = 'order_sn';
        $export_arr['会员ID'] = 'user_id';
        $export_arr['订单状态'] = 'order_status';
        $export_arr['物流状态'] = 'shipping_status';
        $export_arr['支付状态'] = 'pay_status';
        $export_arr['收货人'] = 'consignee';
        $export_arr['省'] = 'province';
        $export_arr['城市'] = 'city';
        $export_arr['区域'] = 'district';
        $export_arr['省市区'] = 'merger_name';
        $export_arr['地址'] = 'address';
        $export_arr['手机号码'] = 'mobile';
        $export_arr['送货时间'] = 'best_time';
        $export_arr['买家留言'] = 'buyer_message';
        $export_arr['快递名称'] = 'shipping_name';
        $export_arr['发货单号'] = 'invoice_no';
        $export_arr['支付名称'] = 'pay_name';
        $export_arr['商品总金额'] = 'goods_amount';
        $export_arr['运费'] = 'shipping_fee';
        $export_arr['折扣金额'] = 'discount';
        $export_arr['额外折扣'] = 'diy_discount';
        $export_arr['分成总金额'] = 'dividend_amount';
        $export_arr['订单金额'] = 'order_amount';
        $export_arr['添加时间'] = 'add_time';
        $export_arr['确定时间'] = 'confirm_time';
        $export_arr['取消时间'] = 'cancel_time';
        $export_arr['支付时间'] = 'pay_time';
        $export_arr['发货时间'] = 'shipping_time';
        $export_arr['签收时间'] = 'sign_time';
        $export_arr['退货时间'] = 'returned_time';
        $export_arr['商品明细'] = 'goods_list';

        $export_field = $export_arr;
        $page = 0;
        $page_size = 500;
        $page_count = 100;
        $title = join("\t", array_keys($export_arr)) . "\t";
        unset($export_field['商品明细']);
        $field = join(",", $export_field);

        $OrderGoodsModel = new OrderGoodsModel();
        $orderLang = lang('order');
        $os = $orderLang['os'];
        $ss = $orderLang['ss'];
        $ps = $orderLang['ps'];
        $data = '';
        do {
            $rows = $this->Model->field('order_id,' . $field)->where($where)->limit($page * $page_size, $page_size)->select();

            if (empty($rows)) break;
            foreach ($rows as $row) {
                $merger_name = explode(',', $row['merger_name']);
                foreach ($export_arr as $val) {
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]) . "\t";
                    } elseif ($val == 'mobile' || $val == 'tel') {
                        $data .= $row[$val] . "\t";
                    } elseif ($val == 'order_status') {
                        $data .= strip_tags($os[$row['order_status']]) . "\t";
                    } elseif ($val == 'shipping_status') {
                        $data .= strip_tags($ss[$row['shipping_status']]) . "\t";
                    } elseif ($val == 'pay_status') {
                        $data .= strip_tags($ps[$row['pay_status']]) . "\t";
                    } elseif ($val == 'province') {
                        $data .= $merger_name[0] . "\t";
                    } elseif ($val == 'city') {
                        $data .= $merger_name[1] . "\t";
                    } elseif ($val == 'district') {
                        $data .= $merger_name[2] . "\t";
                    } elseif ($val == 'goods_list') {
                        $grows = $OrderGoodsModel->field('goods_name,sku_name,goods_sn,goods_number')->where(['order_id' => $row['order_id']])->select();
                        foreach ($grows as $grow) {
                            $data .= $grow['goods_name'] . '_' . $grow['sku_name'] . '(' . $grow['goods_sn'] . ') * ' . $grow['goods_number'] . ' || ';
                        }
                        $data .= "\t";
                    } else {
                        $data .= str_replace(array("\r\n", "\n", "\r"), '', strip_tags($row[$val])) . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }

}
