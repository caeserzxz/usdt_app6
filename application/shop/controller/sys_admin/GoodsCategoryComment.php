<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-21
 * Time: 9:42
 */

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\common\model\ShopGoodsCategoryComment;
use app\mainadmin\model\AdminUserModel;
use app\shop\model\CategoryModel;
use PDO;
use think\Db;
use think\db\Query;
use think\facade\Config;

class GoodsCategoryComment extends AdminController {

    /**
     * @var ShopGoodsCategoryComment
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

        $this->Model = new ShopGoodsCategoryComment();
    }

    /**
     * @return string
     */
    public function index() {
        $options = $this->getListOptions();
        $this->assign('search', $options);

        $this->assign('modeListHtml', arrToSel($this->modeList, $options['mode']));

        $categoryId = $options['category_id'];
        $goodsCategoryList = (new CategoryModel)->getRows();
        $this->assign('goodsCategoryListHtml', arrToSel($goodsCategoryList, $categoryId));

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

        if ($options['category_id']) {
            $where[] = ['gc.id', '=', $options['category_id']];
        }

        $queryHandler = function(Query $query) use ($options, $where) {

            /** @var ShopGoodsCategoryComment $model */
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
                ->leftJoin('shop_goods_category gc', 'gc.id = c.category_id')
                ->where($where)
                ->field([
                    'c.*',
                    'gc.name' => 'category_name',
                ]);
        };

        $countInfo = ShopGoodsCategoryComment::getCountInStatic($queryHandler, $options['page_size']);

        $list = ShopGoodsCategoryComment::getListInStatic($queryHandler, $options['page'], 'create_time desc', $options['page_size']);

        foreach ($list as $item) {
            $item['deleted'] = $item->trashed();
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

        $options['mode'] = (int) $this->request->param('mode', 1);
        $options['category_id'] = (int) $this->request->param('category_id');

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

        if ($item['headimgurl']) {
            $this->assign('images', [$item['headimgurl']]);
        }

        $categoryId = $item['category_id'];
        $goodsCategoryList = (new CategoryModel)->getRows();
        $this->assign('goodsCategoryListHtml', arrToSel($goodsCategoryList, $categoryId));

        return $item;
    }

    protected function beforeAdd($item) {
        $this->checkData($item);

        Db::startTrans();

        if (is_array($item['headimgurl'])) {
            $item['headimgurl'] = $item['headimgurl']['path'][0];
        }

        $item['admin_id'] = $this->admin['info']['user_id'];
        $item['create_time'] = time();

        return $item;
    }

    protected function afterAdd($item) {
        Db::commit();

        $this->_log($item['id'], "添加商品分类评论: {$item['id']}");
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

        ShopGoodsCategoryComment::clearCache($item['id']);
        $this->_log($item['id'], "修改商品分类评论: {$item['id']}");
    }

    protected function checkData($item) {
        if (!$item['user_name']) {
            $this->error('请输入用户名');
        }
        if (!$item['category_id']) {
            $this->error('请选择商品分类');
        }
        if (!$item['content']) {
            $this->error('请输入商品分类评论内容');
        }
    }

    /**
     * @throws \think\exception\DbException
     */
    public function delete() {
        $id = (int) $this->request->param('id');
        $id and $item = ShopGoodsCategoryComment::get($id);
        if (!$item) {
            $this->error();
        }

        Db::startTrans();

        $result = $item->delete();
        if (!$result) {
            $this->error();
        }

        Db::commit();

        ShopGoodsCategoryComment::clearCache($id);
        $this->_log($id, "删除商品分类评论: {$id}");

        $this->success('操作成功');
    }

    /**
     * @throws \think\exception\DbException
     */
    public function revert() {
        $id = (int) $this->request->param('id');
        $id and $item = ShopGoodsCategoryComment::get($id);
        if (!$item) {
            $this->error();
        }

        Db::startTrans();

        $result = $item->restore();
        if (!$result) {
            $this->error();
        }

        Db::commit();

        ShopGoodsCategoryComment::clearCache($id);
        $this->_log($id, "还原商品分类评论: {$id}");

        $this->success('操作成功');
    }
}