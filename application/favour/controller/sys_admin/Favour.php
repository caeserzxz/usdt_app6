<?php

namespace app\favour\controller\sys_admin;

use think\Db;
use app\AdminController;
use  app\favour\model\FavourModel;
use  app\favour\model\FavourGoodsModel;

/**
 * 限时优惠相关
 * Class Index
 * @package app\store\controller
 */
class Favour extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize($isretrun = true)
    {
        parent::initialize();
        $this->Model = new FavourModel();
    }
    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $this->assign("start_date", $dtime[0]);
            $this->assign("end_date", $dtime[1]);
        } else {
            $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
            $this->assign("end_date", date('Y/m/d'));
        }
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $nowDate =date("Y-m-d");
        $where = [];
        $where[]=['favour_type','>',0];
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where[] = ['title', 'like',"%".$search['keyword']."%"];
        }
        $search['goods_name'] = input('goods_name', '', 'trim');
        if (empty($search['goods_name']) == false) {
            $whereGoods[]= ['g.goods_name', 'like',"%".$search['goods_name']."%"];
            $fa_ids =(new FavourGoodsModel)->alias('fg')->join('shop_goods g','fg.goods_id=g.goods_id')->where($whereGoods)->column('fg.fa_id');
            $fa_ids[]=0;
            $where[] = ['fa_id', 'in',$fa_ids];
        }
        $search['status'] = input('status', '-1', 'intval');
        switch ($search['status']){
            case 1:
                $where[]=['status','=',1];
                $where[]=['start_date','<=',$nowDate];
                $where[]=['end_date','>=',$nowDate];
                break;
            case 2:
                $where[]=['status','=',0];
                break;
            case 3:
                $where[]=['start_date','>',$nowDate];
                break;
            case 4:
                $where[]=['end_date','<',$nowDate];
                break;
        }
        $this->data = $this->getPageList($this->Model, $where);
        foreach ($this->data['list'] as $key=>$row){
            if($row['status']==0){
                $this->data['list'][$key]['status']=2;
            }elseif ($row['start_date']>$nowDate){
                $this->data['list'][$key]['status']=3;
            }elseif ($row['end_date']<$nowDate){
                $this->data['list'][$key]['status']=4;
            }else{
                $this->data['list'][$key]['status']=1;
            }
            $whereGoods=[];
            $whereGoods[]=['fa_id','=',$row['fa_id']];
            $goods_num = (new FavourGoodsModel)->where($whereGoods)->count('fg_id');
            $this->data['list'][$key]['goods_num']=$goods_num;
        }

        //显示状态
        $statusList = array(
            '1'=>'进行中',
            '2'=>'已关闭',
            '3'=>'未开始',
            '4'=>'已结束',
        );
        $this->assign("statusList", $statusList);

        $this->assign("FavourType", $this->getDict('FavourType'));//限时优惠活动方案

        $this->assign("data", $this->data);
        if ($runData == false){
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('','',$this->data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function setGoods()
    {
        $favour_type = input('favour_type',1,'intval');
        $this->assign('favour_type',$favour_type);
        return $this->fetch('set_goods');
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        if (empty($data['fa_id'])){
            $admin_id = $this->admin['info']['user_id'];
            $where['admin_id']=$admin_id;
            $where['favour_type']=0;
            $hasFavourAdmin = $this->Model->where($where)->find();
            if(empty($hasFavourAdmin['favour_type'])){
                $data['add_time']=time();
                $data['admin_id']=$this->admin['info']['user_id'];
                $data['start_date']=date("Y-m-d");
                $data['end_date']=date("Y-m-d",strtotime('+1 month',time()));
                $res = $this->Model->create($data);
                $data = $res;
            }else{
                $data=$hasFavourAdmin->toArray();
            }
        }
        $cycleList = $this->Model->getCycleList();
        $this->assign('cycleList',$cycleList);
        $favourType = $this->getDict('FavourType');
        $this->assign("favourType",$favourType);//限时优惠活动方案
        $this->assign("favourTypeOpt",arrToSel($favourType, $data['favour_type']));//限时优惠活动方案

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        $row = $this->checkData($row);
        Db::startTrans();//启动事务
        $row['add_time'] = $row['update_time'] = time();
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        $inData['sg_id'] = $row['sg_id'];
        $inData['update_time'] = time();
        Db::commit();// 提交事务
        return $this->success('添加成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        $row = $this->checkData($row);
        Db::startTrans();//启动事务
        $row['update_time'] = time();
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        $inData['update_time'] = time();
        Db::commit();// 提交事务
        $this->Model->cleanMemcache($row['fa_id']);
        return $this->success('修改成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 验证相关数据
    /*------------------------------------------------------ */
    public function checkData($row)
    {
        if (empty($row['start_date']) || empty($row['end_date'])) return $this->error('操作失败:请设置活动时间.');
        if ($row['start_date'] > $row['end_date']) return $this->error('操作失败::开始时间必须大于等于结束时间.');
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 获取秒杀列表-供选择使用
    /*------------------------------------------------------ */
    public function selectSecond()
    {
        $this->getselectSecondList(true);
        return $this->fetch('select_second');
    }
    /*------------------------------------------------------ */
    //-- 获取秒杀列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getselectSecondList($runData = false)
    {
        $SecondModel = new SecondModel();
        $where = [];
        $status = input('status', 0, 'intval');
        $time = time();
        switch ($status) {
            case 1:
                $where[] = ['fg.start_date', '>', $time];
                break;
            case 2:
                $where[] = ['fg.start_date', '<', $time];
                $where[] = ['fg.end_date', '>', $time];
                break;
            case 3:
                $where[] = ['fg.end_date', '<', $time];
                break;
            default:
                break;
        }
        $search['goodsArr'] = input('goodsArr', 0, 'trim');
        if (empty($search['goodsArr']) == false) {
            $where[] = ['sg.sg_id', 'not in', $search['goodsArr']];
        }

        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where[] = ['g.goods_name|g.goods_sn', 'like',"%".$search['keyword']."%"];
        }

        $viewObj = $SecondModel->alias('sg')->join("shop_goods g", 'sg.goods_id=g.goods_id', 'left');
        $viewObj->where($where)->field('sg.*,g.goods_name,g.goods_sn,g.is_spec')->order('sg_id DESC');
        $this->data = $this->getPageList($SecondModel, $viewObj);
        $this->assign("data", $this->data);
        $this->assign("time", $time);
        if ($runData == false) {
            $this->data['content'] = $this->fetch('second_list');
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }
}