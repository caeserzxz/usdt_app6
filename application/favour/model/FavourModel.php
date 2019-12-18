<?php

namespace app\favour\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 限时优惠
/*------------------------------------------------------ */

class FavourModel extends BaseModel
{
    protected $table = 'favour';
    public $pk = 'fa_id';
    protected $mkey = 'favour_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($sg_id = 0)
    {
        Cache::rm($this->mkey . 'info_' . $sg_id);
    }
    /*------------------------------------------------------ */
    //-- 获取活动信息
    //-- $fg_id int 活动id
    /*------------------------------------------------------ */
    public function info($fa_id)
    {
        $favour = Cache::get($this->mkey . 'info_' . $fa_id);
        if (empty($favour)) {
            $favour = $this->where('fa_id', $fa_id)->find();
            if (empty($favour) == true) return array();
            $favour = $favour->toArray();
            Cache::set($this->mkey . 'info_' . $fa_id, $favour, 600);
        }
        return $favour;
    }
    /*------------------------------------------------------ */
    //-- 获取档期列表
    /*------------------------------------------------------ */
    public function getCycleList()
    {
        $favour_time_cycle = settings('favour_time_cycle');
        $favour_start_time = settings('favour_start_time');
        if (empty($favour_time_cycle) || empty($favour_start_time)&&$favour_start_time!==0) return false;//没有相关配置

        $time = time();
        $toTime = date("G", $time);//当前时间点

        $hourList = [];
        for ($i = $favour_start_time; $i <= 24; $i++) {
            $hourList[] = $i;
        }
        $cycleArr = array_chunk($hourList, $favour_time_cycle);
        $cycleList = [];
        $end = 0;
        foreach ($cycleArr as $val) {
            if ($end >= 24) continue;
            $start = current($val);
            $cycle['name'] = $start . ':00';
            $end = current($val) + $favour_time_cycle;
            $end = $end > 24 ? 24 : $end;
            if (count($val) == 1) {
                $cycle['value'] = $start . ':00';
            } else {
                $end = current($val) + $favour_time_cycle;
                $end = $end > 24 ? 24 : $end;
                $cycle['value'] = $start . ':00-' . $end . ':00';
            }
            $cycle['start'] = $start;
            $cycle['start_time'] = strtotime($start . ':00:00');
            $cycle['end'] = $end;
            $cycle['end_time'] = strtotime($end . ':00:00');

            if ($toTime >= $cycle['start'] && $toTime < $cycle['end']) {
                $cycle['status'] = 1;
                $cycle['_status'] = '抢购中';
                $cycle['diff_time'] = $cycle['end_time'] - $time;
            } elseif ($toTime < $cycle['start']) {
                $cycle['status'] = 0;
                $cycle['_status'] = '即将开抢';
                $cycle['diff_time'] = $cycle['start_time'] - $time;
            } elseif ($toTime >= $cycle['end']) {
                $cycle['status'] = 2;
                $cycle['_status'] = '已结束';
                $cycle['diff_time'] = 0;
            }
            $cycleList[] = $cycle;
        }
        return $cycleList;
    }

    /*------------------------------------------------------ */
    //-- 获取获取两个日期之间的所有日期
    /*------------------------------------------------------ */
    public function splitDates($start_date, $end_date)
    {
        $dt_start = strtotime($start_date);
        $dt_end = strtotime($end_date);
        $dateList = [];
        if ($dt_start == $dt_end) {
            $dateList[] = date('Y-m-d', $dt_start);
        } else {
            while ($dt_start <= $dt_end) {
                $dateList[] = date('Y-m-d', $dt_start);
                $dt_start = strtotime('+1 day', $dt_start);
            }
        }
        return $dateList;
    }
}
