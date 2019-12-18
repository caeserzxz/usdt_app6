<?php

namespace app\shop\controller\sys_admin;

use app\AdminController;

use app\second\model\SecondModel;
use app\shop\model\BonusModel;
use app\shop\model\BonusListModel;
use app\member\model\AccountModel;
use app\member\model\UsersLevelModel;
use app\shop\model\GoodsModel;
use app\fightgroup\model\FightGroupModel;
use PHPExcel_IOFactory;

/**
 * 优惠券相关
 * Class Index
 * @package app\store\controller
 */
class Bonus extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new BonusModel();
        $this->store_id = 0;//当前默认为总后台，门店值默认为0

    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index()
    {
        $this->assign("send_start_date", date('Y/m/d', strtotime("-1 years")));
        $this->assign("send_end_date", date('Y/m/d', strtotime('+1 years')));
        $this->assign("use_start_date", date('Y/m/d', strtotime("-1 years")));
        $this->assign("use_end_date", date('Y/m/d', strtotime('+1 years')));
        $this->getList(true);
        return $this->fetch('index');
    }

    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_delete = 0)
    {
        $where[] = ['store_id', '=', $this->store_id];
        $search['status'] = input('status', 0, 'intval');
        $search['send_type'] = input('send_type', -1, 'intval');
        $search['use_type'] = input('use_type', -1, 'intval');
        $time = time();
        switch ($search['status']) {
            case 1://发放中
                $where[] = ['send_status', '=', 1];
                $where[] = ['send_start_date', '<', $time];
                $where[] = ['send_end_date', '>', $time];
                break;
            case 2://发放结束
                $where[] = ['send_end_date', '<', $time];
                break;
            case 3://禁用
                $where[] = ['use_status', '=', 2];
                break;
            case 4://未发放
                $where[] = ['send_start_date', '>', $time];
                break;
        }
        if ($search['send_type'] >= 0) {
            $where[] = ['send_type', '=', $search['send_type']];
        }
        if ($search['use_type'] >= 0) {
            $where[] = ['use_type', '=', $search['use_type']];
        }
        $send_time = input('send_time', '', 'trim');
        if (empty($send_time) == false) {
            $send_time = str_replace('_', '/', $send_time);
            $dtime = explode('-', $send_time);
            $where[] = ['send_start_date', '>=', strtotime($dtime[0])];
            $where[] = ['send_end_date', '<=', strtotime($dtime[1]) + 86399];
        } else {
            $where[] = ['send_start_date', '>=', strtotime("-1 years")];
            $where[] = ['send_end_date', '<=', strtotime("+1 years")];
        }
        $use_time = input('use_time', '', 'trim');
        if (empty($use_time) == false) {
            $use_time = str_replace('_', '/', $use_time);
            $dtime = explode('-', $use_time);
            $where[] = ['use_start_date', '>=', strtotime($dtime[0])];
            $where[] = ['use_end_date', '<=', strtotime($dtime[1]) + 86399];
        } else {
            $where[] = ['use_start_date', '>=', strtotime("-1 years")];
            $where[] = ['use_end_date', '<=', strtotime("+1 years")];
        }
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where[] = ['type_name', 'like', "%" . $search['keyword'] . "%"];
        }

        $data = $this->getPageList($this->Model, $where);
        foreach ($data['list'] as $key => $row) {
            if ($row['send_status'] == 2) {
                $data['list'][$key]['_send_status'] = 3;
            } else {
                if ($row['send_start_date'] < $time && $row['send_end_date'] > $time) {
                    $data['list'][$key]['_send_status'] = 1;
                } elseif ($row['send_start_date'] > $time) {
                    $data['list'][$key]['_send_status'] = 4;
                } elseif ($row['send_end_date'] < $time) {
                    $data['list'][$key]['_send_status'] = 2;
                }
            }
        }

        //优惠券状态
        $sendStatusList = array(
            '1' => '发放中',
            '2' => '发放结束',
            '3' => '暂定发放',
            '4' => '未开始',
        );
        $useStatusList = array(
            '1' => '正常',
            '2' => '失效',
        );
        $this->assign("sendStatusList", $sendStatusList);
        $this->assign("useStatusList", $useStatusList);

        $this->assign("data", $data);
        $this->assign("search", $search);

        $this->assign("SendType", $this->getDict('BonusSendType'));
        $this->assign("GoodsType", $this->getDict('BonusGoodsType'));//指定商品类型
        $this->assign("UseType", $this->getDict('BonusUseType'));
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 添加修改前调用
    /*------------------------------------------------------ */
    public function asInfo($row)
    {
        $this->assign("SendType", $this->getDict('BonusSendType'));//发放类型
        $this->assign("GoodsType", $this->getDict('BonusGoodsType'));//指定商品类型
        $this->assign("UseType", $this->getDict('BonusUseType'));//使用范围
        $GoodsModel = new GoodsModel();
        $ClassList = $GoodsModel->getClassList();//商品分类
        $ClassListOpt = arrToSel($ClassList);
        $this->assign("classListOpt", $ClassListOpt);//商品分类下拉选择

        if($row['use_type']==1){//指定分类可用
            $cidList=[];//已选分类
            $use_by = explode(',',$row['use_by']);
            foreach ($ClassList as $cid){
                if(in_array($cid['id'],$use_by)){
                    $cidList[]=$cid;
                }
            }
            $this->assign("cidList", $cidList);//已选分类
        }
        if (empty($row['type_id'])) {//新增默认时间
            $nowTime = time();
            $row['send_start_date'] = $nowTime;
            $row['send_end_date'] = strtotime("+1 month");
            $row['use_start_date'] = $nowTime;
            $row['use_end_date'] = strtotime("+2 month");
        }
        if ($row['use_type'] == 2) {//指定商品的优惠券
            $where[] = ['goods_id', 'in', $row['use_by']];
            $goodsList = $GoodsModel->where($where)->field("goods_id,goods_name,shop_price,is_spec,goods_sn,goods_thumb")->limit(20)->select();
            $this->assign("goodsList", $goodsList);
        }
        if(empty($row['type_id'])){
            $row['send_type']=1;
            $row['goods_type']=1;
            $row['use_type']=0;
        }
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        if (empty($row['type_id'])) {
            if (!isset($row['send_type'])) return $this->error('请选择发放类型.');
            if (!isset($row['goods_type'])) return $this->error('请选择指定商品类型.');
            if (!isset($row['use_type'])) return $this->error('请选择使用范围.');
        }
        if (empty($row['send_start_date'])) return $this->error('请选择发放起始日期.');
        if (empty($row['send_end_date'])) return $this->error('请选择发放结束日期.');
        if (empty($row['use_start_date'])) return $this->error('请选择使用起始日期.');
        if (empty($row['use_end_date'])) return $this->error('请选择使用结束日期.');
        $row['send_start_date'] = strtotime($row['send_start_date']);
        $row['send_end_date'] = strtotime($row['send_end_date']);
        if ($row['send_start_date'] >= $row['send_end_date']) return $this->error('发放结束日期必须大于发放起始日期！');
        $row['use_start_date'] = strtotime($row['use_start_date']);
        $row['use_end_date'] = strtotime($row['use_end_date']);
        if ($row['use_start_date'] >= $row['use_end_date']) return $this->error('使用结束日期必须大使用起始日期！');
        $row['add_time'] = time();
//        if ($row['type_money'] > $row['min_amount']) return $this->error('优惠券金额不能大于最小订单金额！');

        switch ($row['use_type']) {
            case 1:
                if (empty($row['cid'])) {
                    return $this->error('指定分类优惠券必须选择商品分类！');
                }
                $row['use_by'] = implode(',', $row['cid']);
                break;
            case 2:
                if (empty($row['goods_id'])) {
                    return $this->error('指定商品优惠券必须选择商品！');
                }
                $row['use_by'] = implode(',', $row['goods_id']);
                break;
            default:
                $row['use_by'] = 0;
                break;
        }
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        $this->Model->cleanMemcache($row['type_id']);
        $this->_log($row['type_id'], '添加优惠券：' . $row['type_name'] . '-' . $row['type_money']);
        return $this->success('添加成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        return $this->beforeAdd($row);
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        $this->Model->cleanMemcache($row['type_id']);
        $this->_log($row['type_id'], '修改优惠券：' . $row['type_name'] . '-' . $row['type_money']);
        return $this->success('修改成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 发送红包
    /*------------------------------------------------------ */
    public function send()
    {
        $type_id = input('type_id', 0, 'intval');
        if ($type_id < 1) return $this->error('获取传值失败.');
        $row = $this->Model->find($type_id);
        if (empty($row)) return $this->error('优惠券不存在.');
        $UsersLevelModel = new UsersLevelModel();
        $levelList = $UsersLevelModel->getRows();
        if ($this->request->isPost()) {
            $time = time();//记录为同一时间写入，也作为同一批次的标识
            if ($row['send_type'] == 3) {
                $send_num = input('send_num', 0, 'intval');
                if ($send_num < 1) return $this->error('发送数量必须大于0.');
                if ($send_num > 2000) return $this->error('单次只允许最多发送2000.');
                $num = $this->Model->makeBonusSn($type_id, $send_num, [], $time);
            } else {
                $user_level = input('user_level', -1, 'intval');
                $userIds = input('user_id');
                if (empty($userIds) == false && $user_level >= 0) {
                    return $this->error('按级别发放和指定会员，不能同时设置.');
                }
                if ($user_level >= 0) {
                    $level = $levelList[$user_level];
                    $AccountModel = new AccountModel();
                    if($user_level>0){
                        $where[] = ['total_integral', 'between', [$level['min'], $level['max']]];
                    }else{
                        $where[] = ['total_integral', 'egt', 0];
                    }
                    $userIds = $AccountModel->where($where)->column('user_id');
                }
                if (empty($userIds)) return $this->error('没有找到相关可分配的会员.');
                $num = $this->Model->makeBonusSn($type_id, 0, $userIds, $time);
            }
            if ($num < 1) return $this->error('发送红包失败，请尝试重新提交.');
            return $this->success('操作成功.', url('index'));
        }
        $this->assign("SendType", $this->getDict('BonusSendType'));
        $this->assign("row", $row);
        $this->assign("levelList", $levelList);
        return $this->fetch('send');
    }

    /*------------------------------------------------------ */
    //-- 上传excel文件分析读取数据
    /*------------------------------------------------------ */
    public function upload()
    {
        set_time_limit(0);
        $this->isAjax = 1;
        if (empty($_FILES['file'])) return $this->error('请选择上传文件');
        $filePath = $_FILES['file']['tmp_name'];

        $reader = \PHPExcel_IOFactory::createReader('Excel2007');// Reader很关键，用来读excel文件
        if (!$reader->canRead($filePath)) { // 这里是用Reader尝试去读文件，07不行用05，05不行就报错。注意，这里的return是Yii框架的方式。
            $reader = PHPExcel_IOFactory::createReader('Excel5');
            if (!$reader->canRead($filePath)) {
                return $this->_error('读取excel文件失败！');
            }
        }
        $PHPExcel = $reader->load($filePath); // Reader读出来后，加载给Excel实例

        $currentSheet = $PHPExcel->getSheet(0); // 拿到第一个sheet（工作簿？）
        $allColumn = $currentSheet->getHighestColumn(); // 最高的列，比如AU. 列从A开始
        $allRow = $currentSheet->getHighestRow(); // 最大的行，比如12980. 行从0开始
        $mobileArr = array();

        $time = time();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            $row = array();
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $cell = $currentSheet->getCell($address)->getValue();
                if ($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                $row[] = empty($cell) ? '' : $cell;
            }
            if ($currentRow <= 2) {
                continue;
            }
            if (empty(trim($row[0]))) {
                continue;
            }
            $mobileArr[] = trim($row[0]);
        }
        $UsersModel = new \app\member\model\UsersModel();
        $where[] = ['mobile', 'in', $mobileArr];
        $userList = $UsersModel->where($where)->field('user_id,mobile,nick_name')->select()->toArray();
        $return['code'] = 1;
        $return['msg'] = '成功匹配' . count($userList) . '个';
        $return['data'] = $userList;
        $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 红包列表
    /*------------------------------------------------------ */
    public function bonusList()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->bList(true);
        return $this->fetch('bonusList');
    }
    /*------------------------------------------------------ */
    //-- 列表
    /*------------------------------------------------------ */
    public function bList($runData = false)
    {
        $bonus_type_id = input('type_id', 0, 'intval');
        $this->assign("bonus", $this->Model->info($bonus_type_id));
        $BonusListModel = new BonusListModel();
        $where[] = ['type_id', '=', $bonus_type_id];
        $status = input('status', -1, 'intval');
        if ($status >= 0) {
            $where[] = ['status', '=', $status];
        }
        $keyword = input('keyword', '', 'trim');
        if (empty($keyword) == false) {
            $where['and'][] = " user_id = '" . $keyword . "' OR order_sn = '" . $keyword . "' ";
        }
        $data = $this->getPageList($BonusListModel, $where, '*');
        $this->assign("data", $data);
        $statusList = array(
            '0' => '未使用',
            '1' => '已使用',
            '2' => '已失效'
        );
        $this->assign("statusList", $statusList);
        if ($runData == false) {
            $data['content'] = $this->fetch('bList')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 设置为失效
    /*------------------------------------------------------ */
    public function bonusHandle()
    {
        $BonusListModel = new BonusListModel();
        $bonus_ids = input('ids', '', 'trim');
        $type = input('type', 0, 'trim');
        if (empty($bonus_ids)) return $this->error('参数错误.');
        if ($type == 'valid') {
            $data['status'] = 0;
            $where[] = ['status', '=', 2];
            $where[] = ['bonus_id', 'in', $bonus_ids];
            $res = $BonusListModel->where($where)->update($data);
            if ($res < 1) return $this->error();
            return $this->success('操作成功.');
        }
        if ($type == 'invalid') {
            $data['status'] = 2;
            $where[] = ['status', '=', 0];
            $where[] = ['bonus_id', 'in', $bonus_ids];
            $res = $BonusListModel->where($where)->update($data);
            if ($res < 1) return $this->error();
            return $this->success('操作成功.');
        }
    }
    /*------------------------------------------------------ */
    //-- 导出线下发放的信息
    /*------------------------------------------------------ */
    public function getExcel()
    {
        $type_id = input('type_id', 0, 'intval');
        if ($type_id < 1) return $this->error('获取传值失败！');
        $bonus = $this->Model->info($type_id);
        /* 文件名称 */

        $filename = iconv('utf-8', 'GBK//IGNORE', $bonus['type_name']);
        header("Content-type: application/vnd.ms-excel;");
        header("Content-Disposition: attachment; filename=$filename.xls");

        /* 文件标题 */
        $title = "优惠券：" . $bonus['type_name'] . "\t\n";
        $title .= "面额：" . $bonus['type_money'] . "\t\n";
        $title .= "消费金额：" . $bonus['min_amount'] . "\t\n";
        $title .= "发放时间：" . dateTpl($bonus['send_start_date']) . "--" . dateTpl($bonus['send_end_date']) . "\t\n";
        $title .= "使用时间：" . dateTpl($bonus['use_start_date']) . "--" . dateTpl($bonus['use_end_date']) . "\t\n";

        echo iconv('utf-8', 'GBK//IGNORE', $title);
        /* 红包序列号, 红包金额, 类型名称(红包名称), 使用结束日期 */
        echo iconv('utf-8', 'GBK//IGNORE', '红包序列号') . "\t";
        echo iconv('utf-8', 'GBK//IGNORE', '面额') . "\t";
        echo iconv('utf-8', 'GBK//IGNORE', '所属会员') . "\t";
        echo iconv('utf-8', 'GBK//IGNORE', '相关订单') . "\t";
        echo iconv('utf-8', 'GBK//IGNORE', '使用时间') . "\t\n";
        $BonusListModel = new BonusListModel();
        $rows = $BonusListModel->where('type_id', $type_id)->select();

        foreach ($rows as $row) {
            echo iconv('utf-8', 'GBK//IGNORE', ' ' . $row['bonus_sn']) . "\t";
            echo $bonus['type_money'] . " \t";
            echo iconv('utf-8', 'GBK//IGNORE', $row['user_id'] . '-' . userInfo($row['user_id'])) . "\t";
            echo $row['order_sn'] . "\t";
            echo iconv('utf-8', 'GBK//IGNORE', dateTpl($row['used_time'])) . "\t\n";
        }
    }

    /*------------------------------------------------------ */
    //-- 根据指定商品类型获取相关数据
    /*------------------------------------------------------ */
    public function pubSearch()
    {
        $GoodsModel = new \app\shop\model\GoodsModel();
        $goods_type = input('goods_type', 0, 'intval');
        $search['cid'] = input('cid', 0, 'intval');
        if ($goods_type == 1) {//普通商品
            if ($search['cid'] > 0) {
                $this->classList = $GoodsModel->getClassList();
                $where[] = ['cid', 'in', $this->classList[$search['cid']]['children']];
            }
            $_list = $GoodsModel->where($where)->field("goods_id,goods_name,shop_price,is_spec,goods_sn,goods_thumb")->select();
        }
        if ($goods_type == 2) {//拼团商品
            $FightGroupModel = new \app\fightgroup\model\FightGroupModel();
            if ($search['cid'] > 0) {
                $this->classList = $GoodsModel->getClassList();
                $where[] = ['g.cid', 'in', $this->classList[$search['cid']]['children']];
            }
            $_list = $FightGroupModel->alias('fg')
                ->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left')
                ->where($where)->field("fg.*,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        if ($goods_type == 3) {//秒杀商品
            $FightGroupModel = new \app\second\model\SecondModel;
            if ($search['cid'] > 0) {
                $this->classList = $GoodsModel->getClassList();
                $where[] = ['g.cid', 'in', $this->classList[$search['cid']]['children']];
            }
            $_list = $FightGroupModel->alias('sl')
                ->join("shop_goods g", 'sl.goods_id=g.goods_id', 'left')
                ->where($where)->field("sl.*,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        foreach ($_list as $key => $row) {
            $_list[$key] = $row;
        }
        $result['list'] = $_list;
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 弹窗选择商品
    /*------------------------------------------------------ */
    public function selectGoods()
    {
        $this->getSelectGoodsList(true);
        return $this->fetch('select_goods');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getSelectGoodsList($runData = false)
    {
        $GoodsModel = new GoodsModel();
        $this->assign('listType', $this->action);
        $runJson = input('runJson', 0, 'intval');
        $goodsArr = input('goodsArr', 0, 'trim');
        if ($this->store_id > 0) {
            $where[] = ['store_id', '=', $this->store_id];
        } elseif ($this->supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        } elseif ($this->is_supplyer == true) {
            $where[] = ['supplyer_id', '>', 0];
        } else {
            $where[] = ['store_id', '=', 0];
            $where[] = ['supplyer_id', '=', 0];
        }
        if (empty($this->ext_status) == false) {
            $search['status'] = explode(',', $this->ext_status);
        } else {
            $search['status'] = input('status', -1, 'intval');
        }

        if ($search['status'] == 1) {
            $where[] = ['isputaway', '=', 1];
        } elseif ($search['status'] == 2) {
            $where[] = ['isputaway', '=', 0];
        } elseif ($search['status'] != -1) {
            $where[] = ['isputaway', 'in', $search['status']];
        }

        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where['and'][] = "( goods_name like '%" . $search['keyword'] . "%')  OR ( goods_sn like '%" . $search['keyword'] . "%')";
        }

        $this->classList = $GoodsModel->getClassList();
        $search['cid'] = input('cid', 0, 'intval');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->classList[$search['cid']]['children']];
        }
        $search['brand_id'] = input('brand_id', 0, 'intval');
        if ($search['brand_id'] > 0) {
            $where[] = ['brand_id', '=', $search['brand_id']];
        }
        $search['is_promote'] = input('is_promote', -1, 'intval');
        if ($search['is_promote'] >= 0) {
            $where[] = ['is_promote', '=', $search['is_promote']];
        }
        $search['goodsArr'] = input('goodsArr', 0, 'trim');
        if (empty($search['goodsArr']) == false) {
            $where[] = ['goods_id', 'not in', $search['goodsArr']];
        }

        $this->data = $this->getPageList($GoodsModel, $where, $this->_field, $this->_pagesize);
        $this->assign("data", $this->data);
        $this->assign("search", $search);
        $this->assign("classListOpt", arrToSel($this->classList, $search['cid']));
        $BrandList = $GoodsModel->getBrandList();
        $this->assign("brandListOpt", arrToSel($BrandList));
        if ($runJson == 1) {
            return $this->success('', '', $this->data);
        } elseif ($runData == false) {
            $this->data['content'] = $this->fetch('goods_list');
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }



    /*------------------------------------------------------ */
    //-- 选择拼团
    /*------------------------------------------------------ */
    public function selectFightGroup()
    {
        $this->getselectFightGroupList(true);
        return $this->fetch('select_fight_group');
    }
    /*------------------------------------------------------ */
    //-- 获取拼团列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getselectFightGroupList($runData = false)
    {
        $FightGroupModel = new FightGroupModel();
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
            $where[] = ['fg.fg_id', 'not in', $search['goodsArr']];
        }

        $viewObj = $FightGroupModel->alias('fg')->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left');
        $viewObj->where($where)->field('fg.*,g.goods_name,g.goods_sn,g.is_spec')->order('fg_id DESC');
        $this->data = $this->getPageList($FightGroupModel, $viewObj);
        $this->assign("data", $this->data);
        $this->assign("time", $time);
        if ($runData == false) {
            $this->data['content'] = $this->fetch('fight_group_list');
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 选择拼团
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


    /*------------------------------------------------------ */
    //-- 获取已选择的商品/拼团/秒杀
    /*------------------------------------------------------ */
    public function getSelectedGoodsList()
    {
        $type_id = input('type_id', 0, 'intval');
        $bonus = $this->Model->find($type_id);
        if ($bonus['use_type'] != 2) {
            $result['code'] = 0;
            $result['list'] = [];
            return $this->ajaxReturn($result);
        }
        $GoodsModel = new \app\shop\model\GoodsModel();
        if ($bonus['goods_type'] == 1) {//普通商品
            $where[] = ['goods_id', 'in', $bonus['use_by']];
            $_list = $GoodsModel->where($where)->field("goods_id as id,goods_name,shop_price as show_price,is_spec,goods_sn,goods_thumb")->select();
        }
        if ($bonus['goods_type'] == 2) {//拼团商品
            $FightGroupModel = new \app\fightgroup\model\FightGroupModel();
            $where[] = ['fg.fg_id', 'in', $bonus['use_by']];
            $_list = $FightGroupModel->alias('fg')
                ->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left')
                ->where($where)->field("fg.fg_id as id,fg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        if ($bonus['goods_type'] == 3) {//秒杀商品
            $SecondModel = new \app\second\model\SecondModel;
            $where[] = ['sg.sg_id', 'in', $bonus['use_by']];
            $_list = $SecondModel->alias('sg')
                ->join("shop_goods g", 'sg.goods_id=g.goods_id', 'left')
                ->where($where)->field("sg.sg_id as id,sg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        $result['code'] = 1;
        $result['list'] = $_list;
        return $this->ajaxReturn($result);
    }

}
