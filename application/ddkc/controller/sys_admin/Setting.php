<?php
namespace app\ddkc\controller\sys_admin;
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
        $setting = $this->Model->getRows();
        $setting['rechargeMoneyList'] = unserialize($setting['rechargeMoneyList']);
        $setting['shareAwards'] = unserialize($setting['shareAwards']);
        $setting['traderTeams'] = unserialize($setting['traderTeams']);
        $this->assign('index_banner',unserialize($setting['index_banner']));
        $this->assign('setting',$setting);
		$this->assign('shippingFunction',  $this->getShippingFunction());
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        $set['rechargeMoneyList'] = serialize($set['rechargeMoneyList']);

        # 分享收益数据重组
        foreach ($set['shareAwards']['num'] as $key => $value) {
            $shareAward['num'] = $value;
            $shareAward['layer'] = $set['shareAwards']['layer'][$key];
            $shareAward['ratio'] = $set['shareAwards']['ratio'][$key];
            # 以num为key 便于后期匹配
            $shareAwards[$value] = $shareAward;
        }
        $set['shareAwards'] = serialize($shareAwards);

        # 社区收益数据重组
        foreach ($set['traderTeams']['num'] as $key => $value) {
            $traderTeam['num'] = $value;
            $traderTeam['layer'] = $set['traderTeams']['layer'][$key];
            $traderTeam['ratio'] = $set['traderTeams']['ratio'][$key];
            $traderTeams[] = $traderTeam;
        }
        $set['traderTeams'] = serialize($traderTeams);

        # 首页轮播图
        $index_banner = input('index_banner');
        $set['index_banner'] = '';
        if(is_array($index_banner['path'])) $set['index_banner'] = serialize($index_banner['path']);

		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
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
    //-- 行情列表
    /*------------------------------------------------------ */
    public function quotation_list() {

//        $list = $this->getList(true);
//        dump($list);die;

        $this->getList(true);
        return $this->fetch('quotation_list');
    }

    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $model = new MiningQuotationModel();
        $this->order_by = 'time';
        $this->sort_by = 'DESC';
        $data = $this->getPageList($model);
        $this->assign("data", $data);
        if ($runData == false){
            $data['content']= $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('','',$data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 上传分享海报背景图片
    /*------------------------------------------------------ */
    public function uploadImg(){
        $result = $this->_upload($_FILES['file'],'mining_index/');
        if ($result['error']) return $this->error('上传失败，请重试.');
        
        $file_url = str_replace('./','/',$result['info'][0]['savepath'].$result['info'][0]['savename']);
        $data['code'] = 1;
        $data['msg'] = "上传成功";
        $data['image'] = array('thumbnail'=>$file_url,'path'=>$file_url);
        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 删除图片
    /*------------------------------------------------------ */
    public function removeImg() {
        $file = input('post.url','','trim');
        unlink('.'.$file);
        return $this->success('删除成功.');
    }
}
