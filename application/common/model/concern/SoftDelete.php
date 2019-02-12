<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-24
 * Time: 15:36
 */

namespace app\common\model\concern;

use think\db\Query;

/**
 * 开放部分私有方法
 *
 * @package app\common\model\concern
 */
trait SoftDelete {

    use \think\model\concern\SoftDelete;

    /**
     * 是否包含软删除数据
     *
     * @access public
     * @param  bool $withTrashed 是否包含软删除数据
     * @return $this
     */
    public function withTrashedData($withTrashed) {
        $this->withTrashed = $withTrashed;

        return $this;
    }

    /**
     * 获取软删除数据的查询条件
     *
     * @access public
     * @return array
     */
    public function getWithTrashedExp() {
        return is_null($this->defaultSoftDelete) ?
            ['notnull', ''] : ['<>', $this->defaultSoftDelete];
    }

    /**
     * 获取软删除字段
     *
     * @access public
     * @param  bool $read 是否查询操作 写操作的时候会自动去掉表别名
     * @return string|false
     */
    public function getDeleteTimeField($read = false) {
        $field = property_exists($this, 'deleteTime') && isset($this->deleteTime) ? $this->deleteTime : 'delete_time';

        if (false === $field) {
            return false;
        }

        if (!strpos($field, '.')) {
            $field = '__TABLE__.' . $field;
        }

        if (!$read && strpos($field, '.')) {
            $array = explode('.', $field);
            $field = array_pop($array);
        }

        return $field;
    }

    /**
     * 查询的时候默认排除软删除数据
     *
     * @access public
     * @param  Query $query
     * @return void
     */
    public function withNoTrashed($query) {
        $field = $this->getDeleteTimeField(true);

        if ($field) {
            $query->useSoftDelete($field, $this->defaultSoftDelete);
        }
    }
}