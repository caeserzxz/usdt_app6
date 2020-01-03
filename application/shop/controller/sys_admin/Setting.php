<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\facade\Env;
use app\mainadmin\model\SettingsModel;
/*------------------------------------------------------ */
//-- 设置
/*------------------------------------------------------ */
class Setting extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();		
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		
		$this->assign("setting", $this->Model->getRows());
		$this->assign('shippingFunction',  $this->getShippingFunction());
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        $favour_time_cycle= settings('favour_time_cycle');
        $favour_start_time= settings('favour_start_time');
        $res = $this->Model->editSave($set);
        if ($res == false) return $this->error();
        //档期档期间隔 或 活动开始时间 变动，清空档期记录
        if($set['favour_time_cycle']!=$favour_time_cycle||$set['favour_start_time']!=$favour_start_time){
            (new \app\favour\model\FavourGoodsModel)->clearTimeSlot();

        }
		return $this->success('设置成功.');
    }
	/*------------------------------------------------------ */
	//-- 获取所快递有接口程序
	/*------------------------------------------------------ */
    public function getShippingFunction() {
		$rows = readModules(Env::get('extend_path').'/shipping');
		$modules = array();
		foreach ($rows as $row){
			$modules[$row['function']] = $row;
		}
		return $modules;
	}
    /*------------------------------------------------------ */
    //-- 商品分享海报合成处理
    /*------------------------------------------------------ */
    public function mergeShareImg(){
        $MergeImg = new \lib\MergeImg();
        $post = input('post.');
        $data['share_goods_bg'] = $post['share_goods_bg'];
        if (empty($data['share_goods_bg'])){
            return false;
        }
        $data['share_avatar'] = './static/share/avatar.jpg';
        $data['share_nick_name'] = '测试';
        $data['share_qrcode'] = './static/share/qrcode.png';

        $data['share_goods_name'] = '【屈臣氏】新碧双重保湿水感防晒露80克*2件 隔离防晒伤小金帽';
        $data['share_goods_price'] = '售价：￥99.00元';
        $data['share_goods_img'] = './static/share/goods.jpg';
        $data['share_goods_xy'] =  $post['share_goods_xy'];
        $data['share_goods_wh'] =  $post['share_goods_wh'];
        $data['share_goods_name_xy'] =  $post['share_goods_name_xy'];
        $data['share_goods_name_color'] =  $post['share_goods_name_color'];
        $data['share_goods_name_size'] =  $post['share_goods_name_size'];
        $data['share_goods_name_br'] =  $post['share_goods_name_br'];
        $data['share_goods_price_xy'] =  $post['share_goods_price_xy'];
        $data['share_goods_price_color'] =  $post['share_goods_price_color'];
        $data['share_goods_price_size'] =  $post['share_goods_price_size'];

        $data['share_goods_avatar_xy'] = $post['share_goods_avatar_xy'];
        $data['share_goods_avatar_width'] = $post['share_goods_avatar_width'];
        $data['share_goods_avatar_shape'] = $post['share_goods_avatar_shape'];
        $data['share_goods_nickname_xy'] = $post['share_goods_nickname_xy'];
        $data['share_goods_nickname_color'] = $post['share_goods_nickname_color'];
        $data['share_goods_nickname_size'] = $post['share_goods_nickname_size'];
        $data['share_goods_qrcode_xy'] = $post['share_goods_qrcode_xy'];
        $data['share_goods_qrcode_width'] = $post['share_goods_qrcode_width'];

        $MergeImg->shareGoodsImg($data,'./upload/share_bg/test_goods_share.jpg');
        return $this->success('请求成功.');
    }
}
