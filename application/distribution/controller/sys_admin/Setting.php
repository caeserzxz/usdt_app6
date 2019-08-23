<?php

namespace app\distribution\controller\sys_admin;

use think\Db;

use app\AdminController;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendModel;
use app\distribution\model\EvalArrivalLogModel;
use app\member\model\UsersModel;

/**
 * 分销设置
 * Class Index
 * @package app\store\controller
 */
class Setting extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new SettingsModel();
    }
    /*------------------------------------------------------ */
    //-- 主页
    /*------------------------------------------------------ */
    public function index()
    {
        $settings = settings();

        $Dividend = json_decode($settings['DividendInfo'], true);
        $Dividend['status'] = $settings['DividendSatus'];
        $Dividend['share_by_role'] = $settings['DividendShareByRole'];
        $GoodsImages = explode(',',$settings['GoodsImages']);
        $this->assign("GoodsImages", $GoodsImages);
        $this->assign('Dividend', $Dividend);
        $this->assign('share_bg', explode(',',$settings['share_bg']));
        $this->assign('shop_after_sale_limit', $settings['shop_after_sale_limit']);
        $this->assign('setting', $settings);
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 保存配置
    /*------------------------------------------------------ */
    public function save()
    {
        $Dividend = input();
        $arr = input('post.setting');

        //背景图
        $ShareBg = input('ShareBg');
        $share_bg = '';
        if(is_array($ShareBg['path'])){
            $share_bg = implode(',', $ShareBg['path']);
        }
        $arr['share_bg'] = $share_bg;

        $arr['DividendSatus'] = $Dividend['status'];
        $arr['DividendShareByRole'] = $Dividend['share_by_role'] * 1;
        unset($Dividend['setting'], $Dividend['status'], $Dividend['DividendShareByRole']);
        $arr['DividendInfo'] = json_encode($Dividend);
        $res = $this->Model->editSave($arr);
        if ($res == false) return $this->error();
        return $this->success('设置成功.');
    }
    /*------------------------------------------------------ */
    //-- 手动结算
    /*------------------------------------------------------ */
    public function evalArrival()
    {
        $inData['log_time'] = time();
        $inData['admin_id'] = AUID;
        $EvalArrivalLogModel = new EvalArrivalLogModel();
        Db::startTrans();//事务启用
        $res = $EvalArrivalLogModel->save($inData);
        if ($res < 1) {
            Db::rollback();//事务回滚
            return $this->error('执行失败-1，请重试.');
        }
        $res = (new DividendModel)->evalArrival(0, $EvalArrivalLogModel->log_id);
        if ($res == false) {
            Db::rollback();//事务回滚
            return $this->error('执行失败-2，请重试.');
        }
        Db::commit();//事务提交
        return $this->success('操作成功.');
    }

    /*------------------------------------------------------ */
    //-- 佣金相关自动执行
    /*------------------------------------------------------ */
    public function autoEval()
    {
        Db::startTrans();//事务启用
        $res = (new DividendModel)->evalArrival();
        if ($res == false) {
            Db::rollback();//事务回滚
            return $this->error('执行失败-2，请重试.');
        }
        Db::commit();//事务提交
        return $this->success('操作成功.');
    }

    /*------------------------------------------------------ */
    //-- 上传分享海报背景图片
    /*------------------------------------------------------ */
    public function uploadShareBg(){
        $result = $this->_upload($_FILES['file'],'share_bg/');
        if ($result['error']) {
            return $this->error('上传失败，请重试.');
        }
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
    /*------------------------------------------------------ */
    //-- 海报合成处理
    /*------------------------------------------------------ */
    public function mergeShareImg(){

        $MergeImg = new \lib\MergeImg();

        $post = input('post.');
        $share_bg = $post['ShareBg']['path'][0];
        $share_bg = explode(',',$share_bg);
        $data['share_bg'] = $share_bg[0];
        if (empty($data['share_bg'])){
            return false;
        }

        $data['share_avatar'] = './static/share/avatar.jpg';
        $data['share_nick_name'] = '测试';
        $data['share_qrcode'] = './static/share/qrcode.png';

        $data['share_avatar_xy'] = $post['setting']['share_avatar_xy'];
        $data['share_avatar_width'] = $post['setting']['share_avatar_width'];
        $data['share_avatar_shape'] = $post['setting']['share_avatar_shape'];
        $data['share_nick_name_xy'] = $post['setting']['share_nick_name_xy'];
        $data['share_nick_name_color'] = $post['setting']['share_nick_name_color'];
        $data['share_nick_name_size'] = $post['setting']['share_nick_name_size'];
        $data['share_qrcode_xy'] = $post['setting']['share_qrcode_xy'];
        $data['share_qrcode_width'] = $post['setting']['share_qrcode_width'];


        /*$data['share_bg'] = settings('share_bg');
        $data['share_bg'] = explode(',',$data['share_bg']);
        $data['share_bg'] = $data['share_bg'][0];

        $data['share_avatar'] = './20180926162125_vjbwi.jpg';
        $data['share_avatar_xy'] = settings('share_avatar_xy');
        $data['share_avatar_width'] = settings('share_avatar_width');
        $data['share_avatar_shape'] = settings('share_avatar_shape');
        $data['share_nick_name'] = '测试';
        $data['share_nick_name_xy'] = settings('share_nick_name_xy');
        $data['share_nick_name_color'] = settings('share_nick_name_color');
        $data['share_nick_name_size'] = settings('share_nick_name_size');
        $data['share_qrcode'] = './upload/qrcode/8/GucDzMiZwgd7GYRs.png';
        $data['share_qrcode_xy'] = settings('share_qrcode_xy');
        $data['share_qrcode_width'] = settings('share_qrcode_width');*/
        $MergeImg->shareImg($data,'./upload/share_bg/test_share.jpg');
        return $this->success('请求成功.');
    }

}
