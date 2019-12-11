<?php
namespace app\publics\controller\api;
use app\ApiController;
use app\shop\model\SlideModel;
use app\shop\model\GoodsModel;
use app\shop\model\NavMenuModel;

/*------------------------------------------------------ */
//-- 公共调用
/*------------------------------------------------------ */
class Index extends ApiController
{
	/*------------------------------------------------------ */
	//-- 获取全站设置
	/*------------------------------------------------------ */
 	public function setting(){
		$key_str = input('key_str', '');
		$data['code'] = 1;
		$data['data'] = settings($key_str);
		return $this->ajaxReturn($data);
	}

   	public function uploaderimages(){
		if ($_FILES['file']){
			$result = $this->_upload($_FILES['file'],'image/');
			if ($result['error']) {
				$this->error($result['info']);
			}
			$savepath = trim($result['info'][0]['savepath'],'.');
			$file_url = $savepath.$result['info'][0]['savename'];
			$data['code'] = 1;
			$data['msg'] = "上传成功";
			$data['savename'] = $result['info'][0]['savename'];
			$data['src'] = $file_url;
			$data['src_thumb'] = $result['info'][0]['savename'];
			return $this->ajaxReturn($data);
		}
	}

    public function get_bank(){
        $config = config('config.');
        $result['bank'] = $config['bank'];
        $result['other_bank'] = $config['other_bank'];
        $all_bank = array_merge($config['bank'],$config['other_bank']);
        $temp_key = array_column($all_bank,'code');  //键值
        $arr = array_combine($temp_key,$all_bank) ;
        $result['code_bank'] = $arr;
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }
    
    public function  get_index_data(){
        $result['is_diy'] = settings('xcx_index_tpl');
        if ($result['is_diy'] == 1){//自定义修装
            $ShopPageTheme = new \app\shop\model\ShopPageTheme();
            $result['diypage'] = $ShopPageTheme->getToWxApp();
        }else{
            $GoodsModel = new GoodsModel();
            $result = [];
            $result['slideList'] = SlideModel::getRows();//获取幻灯片
            $result['navMenuList'] = NavMenuModel::getRows();//获取导航菜单
            $result['classGoods'] = $GoodsModel->getIndexClass();//获取商品橱窗商品
            $result['promoteList'] = $GoodsModel->getIndexPromoteList();//促销商品
            $result['bestGoods'] = $GoodsModel->getIndexBestGoods();//猜你喜欢
        }
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }
}
