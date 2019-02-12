<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-21
 * Time: 9:42
 */

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\common\model\ShopGoodsComment;
use app\mainadmin\model\AdminUserModel;
use app\member\model\UsersModel;
use app\shop\model\GoodsModel;
use PDO;
use think\Db;
use think\db\Query;
use think\facade\Config;

class GoodsComment extends AdminController {

    /**
     * @var ShopGoodsComment
     */
    public $Model;

    // 删除状态列表
    protected $modeList = [
        [
            'id' => 1,
            'name' => '未删除的',
        ],
        [
            'id' => 2,
            'name' => '已删除的',
        ],
    ];

    protected function initialize() {
        parent::initialize();

        // 不自动提交
        Config::set('database.params', [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET autocommit = 0',
        ]);

        $this->Model = new ShopGoodsComment();
    }

    /**
     * @return string
     */
    public function index() {
        $options = $this->getListOptions();
        $this->assign('search', $options);

        $statusList = [];
        foreach (ShopGoodsComment::$statusList as $statusId => $item) {
            $statusList[] = [
                'id'   => $statusId,
                'name' => $item,
            ];
        }
        $this->assign('statusListHtml', arrToSel($statusList, $options['status']));

        $this->assign('modeListHtml', arrToSel($this->modeList, $options['mode']));

        $listInfo = $this->getPagedList($options);
        $this->assign('data', $listInfo);

        return $this->fetch(__FUNCTION__);
    }

    public function getList() {
        $options = $this->getListOptions();

        $listInfo = $this->getPagedList($options);
        $this->assign('data', $listInfo);
        $listInfo['content'] = $this->fetch('list');

        $this->success('', '', $listInfo);
    }

    /**
     * 获取分页列表
     *
     * @param array $options
     * @return array $listInfo
     */
    protected function getPagedList(array $options = []) {

        $where = [];

        if ($options['keyword']) {
            $where[] = ['c.content', 'like', "%{$options['keyword']}%"];
        }

        if ($options['status'] && in_array($options['status'], array_keys(ShopGoodsComment::$statusList))) {
            $where[] = ['c.status', '=', $options['status']];
        }

        $queryHandler = function(Query $query) use ($options, $where) {

            /** @var ShopGoodsComment $model */
            switch ($options['mode']) {
                case 2:
                    // 显示已删除的
                    $model = $query->getModel();
                    $query->useSoftDelete($model->getDeleteTimeField(), $model->getWithTrashedExp());
                    break;
                case 3:
                    // 显示全部
                    $query->removeOption('soft_delete');
                    break;
            }

            $query->alias('c')
                ->leftJoin('shop_goods g', 'g.goods_id = c.goods_id')
                ->leftJoin('users u', 'c.user_id <> 0 and u.user_id = c.user_id')
                ->where($where)
                ->field([
                    'c.*',
                    'g.goods_name',
                    'g.is_spec',
                    'g.goods_sn',
                    'u.user_name' => 'true_user_name',
                ]);
        };

        $countInfo = ShopGoodsComment::getCountInStatic($queryHandler, $options['page_size']);

        $list = ShopGoodsComment::getListInStatic($queryHandler, $options['page'], 'create_time desc', $options['page_size']);

        foreach ($list as $item) {
            if ($item['user_id']) {
                $item['user_name'] = $item['true_user_name'];
            }
            if ($item['is_spec']) {
                $item['goods_sn'] = '多规格';
            }

            $item['deleted'] = $item->trashed();

            $item['status_text'] = ShopGoodsComment::$statusList[$item['status']];
            switch ($item['status']) {
                case ShopGoodsComment::PASSED:
                    $item['status_class'] = 'text-success';
                    $item['status_icon_class'] = 'fa fa-check';
                    break;
                case ShopGoodsComment::DENIED:
                    $item['status_class'] = 'text-danger';
                    $item['status_icon_class'] = 'fa fa-times';
                    break;
            }
        }

        return [
            'list'        => $list,
            'page'        => $options['page'],
            'total_count' => $countInfo['count'],
            'page_count'  => $countInfo['page_count'],
            'page_size'   => $options['page_size'],
        ];
    }

    /**
     * 获取列表参数
     *
     * @return array
     */
    protected function getListOptions() {
        $options = parent::getListOptions();

        $options['status'] = (int) $this->request->param('status', ShopGoodsComment::UNREVIEWED);
        $options['mode'] = (int) $this->request->param('mode', 1);

        return $options;
    }

    /**
     * @param array $item
     * @return array $item
     * @throws \think\exception\DbException
     */
    protected function asInfo($item) {
        $item['admin_id'] and $admin = AdminUserModel::get($item['admin_id']);
        $this->assign('admin', $admin ? $admin->toArray() : null);

        $item['user_id'] and $user = UsersModel::get($item['user_id']);
        $this->assign('user', $user ? $user->toArray() : null);

        if (!$user) {
            if ($item['headimgurl']) {
                $this->assign('images', [$item['headimgurl']]);
            }
        }

        $goodsId = $item['goods_id'];
        $goodsList = (new GoodsModel)->getList([], [
            'goods_id',
            'goods_name',
        ]);
        $goodsList = array_map(function($item) {
            return [
                'id'   => $item['goods_id'],
                'name' => $item['goods_name'],
            ];
        }, $goodsList);
        $this->assign('goodsListHtml', arrToSel($goodsList, $goodsId));

        return $item;
    }

    protected function beforeAdd($item) {
        $this->checkData($item);

        Db::startTrans();

        if (is_array($item['headimgurl'])) {
            $item['headimgurl'] = $item['headimgurl']['path'][0];
        }

        $item['status'] = ShopGoodsComment::PASSED;
        $item['admin_id'] = $this->admin['info']['user_id'];
        $item['review_admin_id'] = $this->admin['info']['user_id'];
        $item['create_time'] = time();

        return $item;
    }

    protected function afterAdd($item) {
        Db::commit();

        $this->_log($item['id'], "添加商品评论: {$item['id']}");
    }

    protected function beforeEdit($item) {
        $this->checkData($item);

        Db::startTrans();

        if (is_array($item['headimgurl'])) {
            $item['headimgurl'] = $item['headimgurl']['path'][0];
        }

        return $item;
    }

    protected function afterEdit($item) {
        Db::commit();

        ShopGoodsComment::clearCache($item['id']);
        $this->_log($item['id'], "修改商品评论: {$item['id']}");
    }

    protected function checkData($item) {
        if (!$item['user_name']) {
            $this->error('请输入用户名');
        }
        if (!$item['goods_id']) {
            $this->error('请选择商品');
        }
        if (!$item['content']) {
            $this->error('请输入商品评论内容');
        }
    }

    /**
     * 审核商品评论
     *
     * @throws \think\exception\DbException
     */
    public function review() {
        $id = (int) $this->request->param('id');
        $id and $item = ShopGoodsComment::withTrashed()->where([
            'id' => $id,
        ])->find();
        if (!$item) {
            $this->error();
        }

        $reviewResult = (string) $this->request->param('result');
        switch ($reviewResult) {
            case 'passed':
                $status = ShopGoodsComment::PASSED;
                break;
            case 'denied':
                $status = ShopGoodsComment::DENIED;
                break;
            default:
                $this->error();
                break;
        }

        Db::startTrans();
        $item['status'] = $status;

        $item->save();

        Db::commit();

        ShopGoodsComment::clearCache($item['id']);
        $this->_log($item['id'], "审核商品评论: {$item['id']}");

        $this->success('操作成功');
    }

    /**
     * @throws \think\exception\DbException
     */
    public function delete() {
        $id = (int) $this->request->param('id');

        if ($id) {
            /** @var ShopGoodsComment $item */
            $item = ShopGoodsComment::withTrashed()->where([
                'id' => $id,
            ])->find();
        }
        if (!$item) {
            $this->error();
        }

        Db::startTrans();

        $result = $item->delete();
        if (!$result) {
            $this->error();
        }

        Db::commit();

        ShopGoodsComment::clearCache($id);
        $this->_log($id, "删除商品评论: {$id}");

        $this->success('操作成功');
    }

    /**
     * @throws \think\exception\DbException
     */
    public function revert() {
        $id = (int) $this->request->param('id');

        if ($id) {
            /** @var ShopGoodsComment $item */
            $item = ShopGoodsComment::withTrashed()->where([
                'id' => $id,
            ])->find();
        }
        if (!$item) {
            $this->error();
        }

        Db::startTrans();

        $result = $item->restore();
        if (!$result) {
            $this->error();
        }

        Db::commit();

        ShopGoodsComment::clearCache($id);
        $this->_log($id, "还原商品评论: {$id}");

        $this->success('操作成功');
    }
}