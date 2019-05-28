<?php

namespace app\integral\controller\api;

use app\ApiController;

use app\integral\model\IntegralGoodsModel;

/*------------------------------------------------------ */
//-- 商品相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new IntegralGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 获取商品列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $time = time();
        $where[] = ['ig.start_date','<',$time];
        $where[] = ['ig.end_date','>',$time];
        $where[] = ['g.is_delete', '=', 0];
        $where[] = ['g.is_alone_sale', '=', 1];
        $where[] = ['g.isputaway', '>', 0];

        $search['min_integral'] = input('min_integral') * 1;
        $search['max_integral'] = input('max_integral') * 1;
        if ($search['min_integral'] > 0) {
            $where[] = ['ig.show_integral', '>', $search['min_integral']];
        }
        if ($search['max_integral'] > 0) {
            $where[] = ['ig.show_integral', '<', $search['max_integral']];
        }

        $sqlOrder = input('order', '', 'trim');

        $sort_by = strtoupper(input('g.sort', 'DESC', 'trim'));
        if (in_array(strtoupper($sort_by), array('DESC', 'ASC')) == false) {
            $sort_by = 'DESC';
        }
        switch ($sqlOrder) {
            case 'sales':
                $this->sqlOrder = "g.virtual_sale $sort_by";
                break;
            case 'price':
                $this->sqlOrder = "ig.show_integral $sort_by";
                break;
            default:
                $this->sqlOrder = "g.virtual_sale $sort_by,g.virtual_collect $sort_by,g.is_best $sort_by";
                break;
        }

        $viewObj = $this->Model->alias('ig')->join("shop_goods g", 'ig.goods_id=g.goods_id', 'left')->where($where)->order($this->sqlOrder);

        $data = $this->getPageList($this->Model, $viewObj, 'ig.show_integral,g.goods_id,g.goods_name,g.short_name,g.is_spec,g.goods_thumb', 10);

        $return['list'] = $data['list'];
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取商品品牌列表
    /*------------------------------------------------------ */
    public function getBrandList()
    {
        $cid = input('cid', 0, 'intval');
        $return['list'] = $this->Model->getBrandList($cid);
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 添加/取消收藏商品
    /*------------------------------------------------------ */
    public function collect()
    {
        $this->checkLogin();//验证登陆
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('传参失败.');
        $GoodsCollectModel = new \app\shop\model\GoodsCollectModel();
        $where['goods_id'] = $goods_id;
        $where['user_id'] = $this->userInfo['user_id'];
        $collect = $GoodsCollectModel->where($where)->find();
        if (empty($collect) == false) {//存在,更新状态
            $upData['status'] = $collect['status'] == 1 ? 0 : 1;
            $upData['update_time'] = time();
            $res = $GoodsCollectModel->where($where)->update($upData);
        } else {
            $inData['status'] = 1;
            $inData['goods_id'] = $goods_id;
            $inData['user_id'] = $this->userInfo['user_id'];
            $inData['add_time'] = time();
            $inData['update_time'] = time();
            $res = $GoodsCollectModel->save($inData);
        }
        if ($res < 1) return $this->error('收藏商品失败.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 取消收藏商品
    /*------------------------------------------------------ */
    public function cancelCollect()
    {
        $this->checkLogin();//验证登陆
        $gids = input('gids', '', 'trim');
        if (empty($gids)) return $this->error('传参失败.');
        $GoodsCollectModel = new \app\shop\model\GoodsCollectModel();
        $where[] = ['user_id', '=', $this->userInfo['user_id']];
        $where[] = ['goods_id', 'in', explode(',', $gids)];
        $res = $GoodsCollectModel->where($where)->update(['status' => 0, 'update_time' => time()]);
        if ($res < 1) return $this->error('操作失败.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取商品收藏列表
    /*------------------------------------------------------ */
    public function getCollectlist()
    {
        $this->checkLogin();//验证登陆
        $GoodsCollectModel = new \app\shop\model\GoodsCollectModel();
        $where['user_id'] = $this->userInfo['user_id'];
        $where['status'] = 1;
        $rows = $GoodsCollectModel->where($where)->order('update_time DESC')->select();
        foreach ($rows as $row) {
            $goods = $this->Model->info($row['goods_id']);
            if ($goods['is_delete'] == 1) {
                continue;
            }
            $_goods['goods_id'] = $goods['goods_id'];
            $_goods['goods_name'] = $goods['goods_name'];
            $_goods['short_name'] = $goods['short_name'];
            $_goods['is_spec'] = $goods['is_spec'];
            $_goods['exp_price'] = $goods['exp_price'];
            $_goods['now_price'] = $goods['_price'];
            $_goods['market_price'] = $goods['market_price'];
            $_goods['sale_count'] = $goods['sale_count'];
            $_goods['collect_count'] = $goods['collect_count'];
            $_goods['goods_thumb'] = $goods['goods_thumb'];
            $_goods['is_promote'] = $goods['is_promote'];
            $return['list'][] = $_goods;
            $return['count'] += 1;
        }
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取商品详情
    /*------------------------------------------------------ */
    public function info()
    {
        $goods_id = input('goods_id', '', 'intval');
        if (empty($goods_id)) return $this->error('传参失败.');
        $return['data'] = $this->Model->info($goods_id);
        if (empty($return['data'])) return $this->error('没有找到相关商品.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
}
