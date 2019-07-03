<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;


//*------------------------------------------------------ */
//-- 微信消息模板
/*------------------------------------------------------ */
class WeiXinMsgTplModel extends BaseModel
{
	protected $table = 'weixin_msg_tpl';
	public  $pk = 'tpl_id';
	protected static $mkey = 'weixin_msg_tpl';
    public  $msgTplReplace = [];
    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){

        parent::initialize();
        $this->msgTplReplace = [
            'order'=>[
                'name'=>'订单通知',
                'list'=>[
                    'order_id'=> '[订单ID]',
                    'order_sn'=> '[订单编号]',
                    'consignee'=> '[收货人]',
                    'order_amount'=> '[订单金额]',
                    'add_time'=> '[下单时间]',
                    'shipping_name'=> '[快递公司]',
                    'invoice_no'=> '[快递编号]',
                    'now_time'=>'[当前时间]'
                ]
            ],
            'superior'=>[
                'name'=>'捆绑通知',
                'list'=>[
                    'level'=>'[绑定级别]',
                    'user_id'=>'[注册会员ID]',
                    'nickname'=>'[注册会员昵称]',
                    'sex'=>'[注册会员性别]',
                    'region'=>'[注册会员区域]',
                    'now_time'=>'[当前时间]'
                ]
            ],
            'dividend'=>[
                'name'=>'分佣通知',
                'list'=>[
                    'award_name'=> '[奖项名称]',
                    'level_award_name'=> '[奖励名称]',
                    'level'=> '[分佣级别]',
                    'dividend_amount'=> '[佣金金额]',
                    'role_name'=> '[分佣身份]',
                    'add_time'=> '[产生时间]',
                    'order_sn'=> '[订单编号]',
                    'order_amount'=> '[订单金额]',
                    'buy_nick_name'=> '[下单会员昵称]',
                    'buy_user_id'=> '[下单会员ID]',
                    'send_nick_name'=> '[分佣会员昵称]',
                    'order_operating'=>'[订单操作]',
                    'now_time'=>'[当前时间]'
                ]
            ],
            'withdraw'=>[
                'name'=>'提现通知',
                'list'=>[
                    'amount'=> '[提现金额]',
                    'balance_money'=>'[提现后余额]',
                    'refuse_time'=> '[拒绝时间]',
                    'admin_note'=> '[客服备注]',
                    'add_time'=> '[申请时间]',
                    'user_id'=> '[会员ID]',
                    'nick_name'=> '[会员昵称]',
                    'now_time'=>'[当前时间]'
                ]
            ],
            'after_sale'=>[
                'name'=>'售后通知',
                'list'=>[
                    'add_time'=> '[产生时间]',
                    'as_sn'=> '[售后编号]',
                    'now_time'=>'[当前时间]',
                    'remark'=>'[拒绝说明]',
                    'return_money'=>'[退款金额]'
                ]
            ]

        ];
    }
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm(self::$mkey);
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public static function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = self::select()->toArray();
		foreach ($rows as $row){
			$data[$row['tpl_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
    /*------------------------------------------------------ */
    //-- 消息模板发送
    /*------------------------------------------------------ */
    public function send(&$data = array())
    {
        if (empty($data['openid']) || empty($data['send_scene'])) return false;
        $msgTemp['touser'] = $data['openid'];
        $tplData = $this->where('send_scene',$data['send_scene'])->find();
        if (empty($tplData)) return false;
        if ($tplData['status'] == 0) return false;
        $msgTemp['template_id'] = $tplData['tpl_code'];
        $msgTemp['topcolor'] = $tplData['topcolor'];
        if (empty($data['url']) == false){
            $msgTemp['url'] = $data['url'];
        }elseif (empty($tplData['url']) == false){
            $msgTemp['url'] = config('config.host_path').$tplData['url'];
        }
        list($first,$tpl_keys,$remark) = $this->replaceTpl($data,$tplData);

        $msgTemp['data']['first']['value'] = $first;
        $tpl_keys = json_decode($tpl_keys,true);
        foreach ($tpl_keys as $key=>$tplkey){
            $msgTemp['data']['keyword'.$key]['value'] = $tplkey;
        }
        $msgTemp['data']['remark']['value'] = empty($remark)?'如有问题，请联系客服.':$remark;

        $msgTemp = urldecode(json_encode($msgTemp));
        $res = (new WeiXinModel)->weiXinCurl('https://api.weixin.qq.com/cgi-bin/message/template/send?',$msgTemp);
        if ($res['errmsg'] != 'ok') return '操作失败，返回结果：'.$res['errcode'].'-'.$res['errmsg'];
        return true;
    }
    /*------------------------------------------------------ */
    //-- 消息模板内容替换
    /*------------------------------------------------------ */
    public function replaceTpl(&$data,&$tplData)
    {
        $msgTplReplace = $this->msgTplReplace[$tplData['type']];
        $keywrod = $replaceKey = [];
        foreach ($msgTplReplace['list'] as $key=>$val){
            if ($key == 'now_time') {
                $replaceKey[] = date('Y-m-d H:i',time());
            }elseif (strstr($key, '_time')) {
                $replaceKey[] = date('Y-m-d H:i', $data[$key]);
            } elseif(empty($data[$key]) == false){
                $replaceKey[] = $data[$key];
            }else{
                continue;
            }
            $keywrod[] = $val;
        }
        $tplStr = str_replace($keywrod,$replaceKey, $tplData['first'].'|_*_|'.$tplData['tpl_keys'].'|_*_|'.$tplData['remark']);
        return explode('|_*_|',$tplStr);
    }
}
