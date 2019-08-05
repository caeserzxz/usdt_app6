<?php

namespace app\member\controller\api;

use app\ApiController;

use app\member\model\UserAddressModel;


/*------------------------------------------------------ */
//-- 会员相关API
/*------------------------------------------------------ */

class Address extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UserAddressModel();
    }
    /*------------------------------------------------------ */
    //-- 获取地址列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $address_id = input('address_id', 0, 'intval');
        $list = $this->Model->getRows(0,$address_id);
        foreach ($list as $row) {
            $return['list'][] = $row;
        }
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 设置默认地址
    /*------------------------------------------------------ */
    public function editDefault($address_id = 0)
    {
        $address_id = empty($address_id) ? input('address_id', 0, 'intval') : $address_id;
        if ($address_id < 1) return $this->error('传参失败，请重试.');
        $where[] = ['user_id', '=', $this->userInfo['user_id']];
        $where[] = ['address_id', '=', $address_id];
        $res = $this->Model->where($where)->update(['is_default' => 1]);
        if ($res > 0) {
            unset($where);
            $where[] = ['user_id', '=', $this->userInfo['user_id']];
            $where[] = ['address_id', '<>', $address_id];
            $this->Model->where($where)->update(['is_default' => 0]);
            $this->Model->cleanMemcache();
        }
        $return['code'] = 1;

        //传递修改直接返回
        if($address_id) return $return;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 添加地址
    /*------------------------------------------------------ */
    public function add()
    {
        $inarr = $this->checkData();
        $inarr['user_id'] = $this->userInfo['user_id'];
        unset($inarr['address_id']);
        $res = $this->Model->save($inarr);
        if ($res < 1) return $this->error('添加失败，请重试..');
        $this->Model->cleanMemcache();
        return $this->getList();
    }
    /*------------------------------------------------------ */
    //-- 修改地址
    /*------------------------------------------------------ */
    public function edit()
    {
        $uparr = $this->checkData();
        $where['address_id'] = $uparr['address_id'];
        $where['user_id'] = $this->userInfo['user_id'];
        unset($uparr['address_id']);

        $is_default = input('is_default', '0', 'intval');
        $uparr['is_default'] = $is_default;

        //验证数据是否出现变化
        $dbarr = $this->Model->field(join(',',array_keys($uparr)))->where($where)->find()->toArray();
        $this->checkUpData($dbarr,$uparr,true);
        unset($uparr['is_default']);

        $res = $this->Model->where($where)->update($uparr);

        //判断是否设置默认地址
        if($is_default){
           $default = $this->editDefault($where['address_id']);
        }

        //修改错误
        if ($res < 1 && empty($default['code'])) return $this->error('修改失败，请重试..');
        $this->Model->cleanMemcache();
        return true;
    }



    /*------------------------------------------------------ */
    //-- 验证修改/添加数据
    /*------------------------------------------------------ */
    private function checkData()
    {
        $data['consignee'] = input('consignee', '', 'trim');
        $data['address'] = input('address', '', 'trim');
        $data['address_id'] = input('address_id', 0, 'intval');
        $data['mobile'] = input('mobile', '', 'trim');
        if (empty($data['consignee'])) return $this->error('请输入收货人.');
        if (empty($data['address'])) return $this->error('请输入详细地址.');
        if (empty($data['mobile'])) return $this->error('请输入手机号码.');
        if (checkMobile($data['mobile']) == false) return $this->error('手机号码格式不正确.');
        $regionIds = input('regionIds', '', 'trim');
        if (empty($regionIds)) return $this->error('请选择所在地区.');
        $regionIds = explode(',', $regionIds);
        $data['province'] = $regionIds[0];
        $data['city'] = $regionIds[1];
        $data['district'] = $regionIds[2];
        if ($data['district'] < 1) return $this->error('请选择所在地区.');

        $regionInfo = (new \app\mainadmin\model\RegionModel)->info($data['district']);
        $data['merger_name'] = $regionInfo['merger_name'];
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 删除地址
    /*------------------------------------------------------ */
    public function delete()
    {
        $where['user_id'] = $this->userInfo['user_id'];
        $count = $this->Model->where($where)->count();
        if ($count ==1)return $this->error('必须保留一个收货地址...');
        $where['address_id'] = input('address_id', 0, 'intval');
        $res = $this->Model->where($where)->delete();
        if ($res < 1) return $this->error('删除失败，请重试..');
        $this->Model->cleanMemcache();
        return $this->getList();
    }
}
