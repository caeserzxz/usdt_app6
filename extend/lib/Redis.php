<?php
// +----------------------------------------------------------------------
// | redis扩展
// | Author: iqgmy
// +----------------------------------------------------------------------
namespace lib;
use think\facade\Cache;
 
class Redis{
    private  $Redis;
    private  $prefix;
    /**
     * 架构函数
     */
    public function __construct() {
        $cache = Cache::init();
        $this->Redis = $cache->handler();
        $this->prefix = config('cache.prefix');
    }

    /**
     * 将value添加到链表key的左边（头部）
     * @param $key string 队列名
     * @param $val string/array 内容
     * @return mixed
     */
    public function lpush($key,$val){
        return $this->Redis->lpush($this->prefix.$key,$val);
    }
    /**
     * 将value添加到链表key的右边（尾部）
     * @param $key string 队列名
     * @param $val string/array 内容
     * @return mixed
     */
    public function rpush($key,$val){
        return $this->Redis->rpush($this->prefix.$key,$val);
    }
    /**
     * 将链表key的左边（头部）元素删除并取出
     * @param $key string 队列名
     * @return mixed
     */
    public function lpop($key){
        return $this->Redis->lpop($this->prefix.$key);
    }
    /**
     * 将链表key的右边（头部）元素删除并取出
     * @param $key string 队列名
     * @return mixed
     */
    public function rpop($key){
        return $this->Redis->rpop($this->prefix.$key);
    }
    /**
     * 返回链表key中有多少个元素
     * @param $key string 队列名
     * @return mixed
     */
    public function count($key){
        return $this->Redis->lSize($this->prefix.$key);
    }

    /**
     * 上锁，用于并发处理
     * @param $key string 锁名
     * @param $time int 锁的有效时间（单位秒）
     * @return mixed
     */
    public function look($key,$time = 5){
        $mkey = $this->prefix.$key;
        $lock_time = $this->Redis->setnx($mkey,time()+$time);
        if ($lock_time == false){
            $lock_time = $this->Redis->get($mkey);
            if(time()>$lock_time){
                $this->Redis->del($mkey);
                $lock_time = $this->Redis->setnx($mkey,time()+$time);
                if ($lock_time == false) return false;
            }else{
                return false;
            }
        }
        return true;
    }
}
