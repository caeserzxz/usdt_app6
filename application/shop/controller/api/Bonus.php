<?php

namespace app\shop\controller\api;

use app\ApiController;
use app\shop\controller\sys_admin\GoodsModel;
use app\shop\model\BonusModel;
use app\shop\model\BonusListModel;

/*------------------------------------------------------ */
//-- 优惠相关API
/*------------------------------------------------------ */

class Bonus extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new BonusModel();
    }
    /*------------------------------------------------------ */
    //-- 获取用户优惠券列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $return['data'] = $this->Model->getListByUser();
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取优惠券列表
    /*------------------------------------------------------ */
    public function getFreeList()
    {
        $return['data'] = $this->Model->getList();
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取可领取的优惠劵
    /*------------------------------------------------------ */
    public function getBonusListReceivable()
    {
        $user_id = $this->userInfo['user_id'];
        $goods_type = input('goods_type', 0, 'intval');
        if ($goods_type == 1) {
            $goods_id = input('goods_id', 0, 'intval');
            $goodsInfo = (new \app\shop\model\GoodsModel)->info($goods_id);
        }
        $bonusList = $this->Model->getListReceivable($user_id, $goods_type, $goodsInfo);//获取可领取优惠券
        $return['list'] = $bonusList;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 获取结算页可用优惠券
    /*------------------------------------------------------ */
    public function getBonusListToCheckout()
    {
        $userInfo = $this->userInfo;
        $goods_type = input('goods_type', 0, 'intval');
        if ($goods_type == 1) {//普通商品
            $is_sel = input('is_sel', 0, 'intval');
            $recids = input('recids', '', 'trim');
            //获取购物信息
            $CartModel = new \app\shop\model\CartModel();
            $cartInfo = $CartModel->getCartList($is_sel, false, $recids);
        } elseif ($goods_type == 2) {//拼团

        }
        $bonusList = $this->Model->getListAvailable($userInfo['user_id'], $cartInfo);//获取可用优惠券
        $return['data'] = $bonusList;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 首页注册优惠券弹出提示
    /*------------------------------------------------------ */
    public function getUntipBonus()
    {
        $userInfo = $this->userInfo;
        $result = $this->Model->getUntipBonusList($userInfo['user_id'], $userInfo['bonus_tip']);
        if (empty($result)) {
            $return['code'] = 0;
            $return['msg'] = '暂无提示';
            return $this->ajaxReturn($return);
        }
        $return['code'] = 1;
        $return['msg'] = '';
        $return['data'] = $result;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 标记优惠券未已提示状态
    /*------------------------------------------------------ */
    public function updateUntipBonus()
    {
        $bonus_ids = input('bonus_ids', '', 'trim');
        if (empty($bonus_ids)) {
            $return['msg'] = '参数错误';
            $return['code'] = 0;
            return $this->ajaxReturn($return);
        }
        $BonusListModel = new BonusListModel();
        $where[] = ['bonus_id', 'in', $bonus_ids];
        $BonusListModel->where($where)->update(['is_tip' => 1]);
    }
    /*------------------------------------------------------ */
    //-- 领取优惠卷
    /*------------------------------------------------------ */
    public function receiveFree()
    {
        $this->checkLogin();//验证登陆
        $id = input('id', 0, 'intval');
        $user_id = $this->userInfo['user_id'];
        $result = $this->Model->receiveFreeBonus($id, $user_id);
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 获取优惠券可用的商品
    /*------------------------------------------------------ */
    public function getGoodsList()
    {
        $time = time();
        $type_id = input('type_id', 0, 'intval');//优惠券ID
        $keyword = input('keyword', 0, 'trim');//关键词
        if ($type_id < 1) return $this->error('参数错误.');
        $goodsModel = new \app\shop\model\GoodsModel();
        $bonus = $this->Model->info($type_id);

        switch ($bonus['use_type']) {
            case 1:
                $ClassList = $goodsModel->getClassList();
                $use_by = explode(',',$bonus['use_by']);
                $cidInfo=[];//分类信息
                foreach ($use_by as $cid){
                    $cidInfo[] = $ClassList[$cid]['children'];
                }
                $cidInfo= join(',',$cidInfo);
                $where[] = ['cid', 'in', $cidInfo];
                break;
            case 2:
                if ($bonus['goods_type'] == 1) {//普通商品
                    $where[] = ['goods_id', 'in', $bonus['use_by']];
                } elseif ($bonus['goods_type'] == 2) {//拼团商品
                    $where[] = ['fg_id', 'in', $bonus['use_by']];
                } elseif ($bonus['goods_type'] == 3) {//秒杀商品
                    $where[] = ['sg_id', 'in', $bonus['use_by']];
                }
                break;
            default:
                break;
        }
        if (empty($keyword) == false) {
            $where[] = ['goods_name', 'like', '%' . $keyword . '%'];
        }
        if ($bonus['goods_type'] == 1) {//普通商品
            $where[] = ['is_delete', '=', 0];
            $where[] = ['is_alone_sale', '=', 1];
            $where[] = ['isputaway', '>', 0];
            $where[] = ['is_promote', '=', 0];
            $data = $this->getPageList($goodsModel, $where, 'goods_id', 10);
        } elseif ($bonus['goods_type'] == 2) {//拼团商品
            $FightGroupModel = new \app\fightgroup\model\FightGroupModel();
            $where[] = ['start_date', '<', $time];
            $where[] = ['end_date', '>', $time];
            $viewObj = $FightGroupModel->alias('fg')->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left');
            $viewObj->where($where)->field('fg.*,g.cid')->order('fg_id DESC');
            $data = $this->getPageList($FightGroupModel, $viewObj, '*', 10);
        } elseif ($bonus['goods_type'] == 3) {//秒杀商品
            $SecondModel = new \app\second\model\SecondModel();
            $viewObj = $SecondModel->alias('sg')->join("shop_goods g", 'sg.goods_id=g.goods_id', 'left');
            $viewObj->where(join(' AND ', $where))->field('sg.*,g.goods_name,g.goods_sn,g.is_spec')->order('sg_id DESC');
            $data = $this->getPageList($SecondModel, $viewObj, '*', 10);
        }

        foreach ($data['list'] as $key => $goods) {
            $goods = $goodsModel->info($goods['goods_id']);
            $_goods['goods_id'] = $goods['goods_id'];
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['short_name'] = $goods['short_name'];
            $_goods['is_spec'] = $goods['is_spec'];
            $_goods['exp_price'] = $goods['exp_price'];
            $_goods['now_price'] = $goods['sale_price'];
            $_goods['market_price'] = $goods['market_price'];
            $_goods['sale_count'] = $goods['sale_count'];
            $_goods['collect_count'] = $goods['collect_count'];
            $_goods['goods_thumb'] = $goods['goods_thumb'];
            $_goods['is_promote'] = $goods['is_promote'];
            $return['list'][] = $_goods;
        }
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }


}
