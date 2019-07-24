<?php
/**
 * BaseModel.php
 *
 * @author : iqgmy
 * @date : 2017.5.28
 * @version : v1.0.0.0
 */

namespace app;

use think\Model;
use think\Db;

class BaseModel extends Model
{

    protected $error = 0;
    protected $table;
	protected $createTime = 'add_time';
    protected $updateTime = 'update_time';
    protected $userInfo;//前端会员信息
    protected $Model;
    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        global $userInfo;
        parent::initialize();
        $this->userInfo = $userInfo;
    }
    /**
     * 获取空模型
     */
    public function getEModel($tables)
    {
        $rs = Db::query('show columns FROM `' . config('database.prefix') . $tables . "`");
        $obj = [];
        if ($rs) {
            foreach ($rs as $key => $v) {
                $obj[$v['Field']] = $v['Default'];
                if ($v['Key'] == 'PRI')
                    $obj[$v['Field']] = 0;
            }
        }
        return $obj;
    }

    /**
     * 数据库开启事务
     */
    public function startTrans()
    {
        Db::startTrans();
    }

    /**
     * 数据库事务提交
     */
    public function commit()
    {
        Db::commit();
    }

    /**
     * 数据库事务回滚
     */
    public function rollback()
    {
        Db::rollback();
    }
	/**
     * 返回表名
     */
	 public function table()
    {
        return $this->table;
    }
	
	
	
	/**
     * 判断字估是否存在
     */
	public function isSetField($field){
		$fields = Db::getTableFields($this->getTable());
		return in_array($field,$fields);
	}
	/**
     * 获取表字段，并附空值
     */
	public function getField(){
		$fields = Db::getTableFields($this->getTable());
		$arr = array();
		foreach ($fields as $field){
			$arr[$field] = '';
		}
		return $arr;
	}
    /**
     * 列表查询
     *
     * @param unknown $page_index            
     * @param string $condition 
     * @param string $where            
     * @param string $field  
      * @param string $order_by
	 * @param string $sort_by   
     */
    public function getPageList($page, $condition = '', $field = '*',$page_size = 10,$sqlOrder='')
    {		
        if (empty($sqlOrder)){
            $sqlOrder = $this->pk.' DESC';
        }
		$this->whereAnd = $this->whereOr = array();
		if (empty($condition['and']) == false){
			$this->whereAnd = $condition['and'];
			unset($condition['and']);			
		}
		if (empty($condition['or']) == false){
			$this->whereOr = $condition['or'];			
			unset($condition['or']);		
		}
		$viewObj = $this->where($condition);
		
		if (empty($this->whereAnd) == false){
			foreach ($this->whereAnd as $where){
				$viewObj->where($where);			
			}	
		}	
		if (empty($this->whereOr) == false){			
			 $viewObj->whereOr(function ($query) {				 		 
					foreach ($this->whereOr as $where){
						 $query->where($where);			
					}
			 });	
		}
		
		
        if ($page_size == 0) {
			$list = array();
            $sql = $viewObj->field($field)->order($sqlOrder)->select(false);
            $count = Db::query($viewObj->count());
			$count = $count[0]['tp_count'];
			if ($count > 0){
				 $list = Db::query($sql);
			}
            $page_count = 1;
        } else {
			$start_row = $page_size * ($page - 1);
            $sql = $viewObj->field($field)->limit($start_row . "," . $page_size)->order($sqlOrder)->select(false);
			$count = Db::query($viewObj->count());
			$count = $count[0]['tp_count'];
            $list = Db::query($sql);
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int) ($count / $page_size) + 1;
            }
        }
        return array(
            'list' => $list,
			'page' => $page,
            'total_count' => $count,
            'page_count' => $page_count,
			'page_size' => $page_size
        );
    }
   
    /**
     * 获取关联查询列表
     *
     * @param unknown $viewObj
     * @param unknown $page_index            
     * @param unknown $page_size            
     * @param unknown $condition            
     * @param unknown $order            
     * @return multitype:number unknown
     */
    public function getJointList( $page = 0, &$viewObj,  $page_size = 10)
    {		
		$start_row = $page_size * ($page - 1);
		$sql = $viewObj->limit($start_row . "," . $page_size)->select(false);
		$count = Db::query($viewObj->count());
		$count = $count[0]['tp_count'];
        if ($page_size == 0) {
			$list = array();
			if ($count > 0){
            	$list = $viewObj->select()->toArray();
			}
            $page_count = 1;
        } else {          
            $list = Db::query($sql);
			 if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int) ($count / $page_size) + 1;
            }
        }
       return array(
            'list' => $list,
			'page' => $page,
            'total_count' => $count,
            'page_count' => $page_count,
			'page_size' => $page_size
        );
    }

    /**
     * 获取关联查询数量
     *
     * @param unknown $viewObj
     *            视图对象
     * @param unknown $condition
     *            下旬条件
     * @return unknown
     */
    public function viewCount($viewObj, $condition = '')
    {
        $count = $viewObj->where($condition)->count();
        return $count;
    }

    /**
     * 设置关联查询返回数据格式
     *
     * @param unknown $list
     *            查询数据列表
     * @param unknown $count
     *            查询数据数量
     * @param unknown $page_size
     *            每页显示条数
     * @return multitype:unknown number
     */
    public function setReturnList($list, $count, $page_size)
    {
        if($page_size == 0)
        {
            $page_count = 1;
        }else{
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int) ($count / $page_size) + 1;
            }
        }
        return array(
            'data' => $list,
            'total_count' => $count,
            'page_count' => $page_count
        );
    }

    /**
     * 获取单条记录的基本信息
     *
     * @param unknown $condition            
     * @param string $field            
     */
    public function getInfo($condition = '', $field = '*',$order = '')
    {
		if (is_object($condition)){
			$info = $condition->field($field)->find();
			$info = $info->data;
		}else{
			$info = Db::table($this->table)->where($condition)->order($order)->field($field)->find()->toArray();			
		}
         return $info;
    }
	/**
     * 获取单字段值
     *
     * @param unknown $condition            
     * @param string $field            
     */
    public function getVal($condition = '', $field = '')
    {
		if (empty($field)) return $field;
        return Db::table($this->table)->where($condition)->value($field);
    }
    /**
     * 查询数据的数量
     * @param unknown $condition
     * @return unknown
     */
    public function getCount($condition)
    {
        $count = Db::table($this->table)->where($condition)
        ->count();
        return $count;
    }
    /**
     * 查询条件数量
     * @param unknown $condition
     * @param unknown $field
     * @return number|unknown
     */
    public function getSum($condition, $field)
    {
        $sum = Db::table($this->table)->where($condition)
        ->sum($field);
        if(empty($sum))
        {
            return 0;
        }else
        return $sum;
    }
    /**
     * 查询数据最大值
     * @param unknown $condition
     * @param unknown $field
     * @return number|unknown
     */
    public function getMax($condition, $field)
    {
        $max = Db::table($this->table)->where($condition)
        ->max($field);
        if(empty($max))
        {
            return 0;
        }else
            return $max;
    }
    /**
     * 查询数据最小值
     * @param unknown $condition
     * @param unknown $field
     * @return number|unknown
     */
    public function getMin($condition, $field)
    {
        $min = Db::table($this->table)->where($condition)
        ->min($field);
        if(empty($min))
        {
            return 0;
        }else
            return $min;
    }
    /**
     * 查询数据均值
     * @param unknown $condition
     * @param unknown $field
     */
    public function getAvg($condition, $field)
    {
        $avg = Db::table($this->table)->where($condition)
        ->avg($field);
        if(empty($avg))
        {
            return 0;
        }else
            return $avg;
    }
    /**
     * 查询第一条数据
     * @param unknown $condition
     */
    public function getFirstData($condition, $order)
    {
        $data = Db::table($this->table)->where($condition)->order($order)
        ->limit(1)->select();
        if(!empty($data))
        {
            return $data[0];
        }else
            return '';
    }
    /**
     * 修改表单个字段值
     * @param unknown $pk_id
     * @param unknown $field_name
     * @param unknown $field_value
     */
    public function ModifyTableField($pk_name, $pk_id, $field_name, $field_value)
    {
        $data = array(
            $field_name => $field_value
        );
        $res = $this->save($data,[$pk_name => $pk_id]);
        return $res;
    }

    /**
     * 获取列表
     *
     * @param array|callable $option 条件 或者 闭包
     * @param int $page 页码
     * @param string $orderBy 排序
     * @param int $pageSize 页大小
     * @return array|\PDOStatement|string|\think\Collection
     */
    public static function getListInStatic($option = [], $page = 1, $orderBy = '', $pageSize = 50) {
        $query = (new static)->db(false);
        $query
            ->order($orderBy)
            ->page($page, $pageSize);

        if (is_callable($option)) {
            call_user_func($option, $query);

        } else {
            $query->where($option);
        }

        return $query->select();
    }

    /**
     * 获取计数
     *
     * @param array $option
     * @param int $pageSize
     * @return array
     */
    public static function getCountInStatic($option = [], $pageSize = 50) {
        $query = (new static)->db(false);

        if (is_callable($option)) {
            call_user_func($option, $query);

        } else {
            $query->where($option);
        }

        $count = $query->count();

        $pageCount = ceil($count / $pageSize);

        return [
            'count'      => $count,
            'page_count' => $pageCount,
        ];
    }
}
