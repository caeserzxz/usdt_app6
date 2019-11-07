<?php
namespace app\mainadmin\controller\log;
use app\AdminController;

use app\mainadmin\model\LogSysModel;
/**
 * 操作日志
 * Class Index
 * @package app\store\controller
 */
class Operate extends AdminController
{
	

   //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new LogSysModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['log_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['log_time','between',[strtotime("-1 months"),time()]];
		}
		if (0 < $user_id = input('user_id/d')){
			$where[] = ['user_id','=',$user_id];
		}
        $export = input('export', 0, 'intval');
        if ($export > 0) {
            return $this->export($where);
        }
        $data = $this->getPageList($this->Model,$where);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

    /*------------------------------------------------------ */
    //-- 导出
    /*------------------------------------------------------ */
    private function export($where)
    {

        $count = $this->Model->where($where)->count('log_id');

        if ($count < 1) return $this->error('没有找到可导出的日志资料！');
        $filename = '系统操作日志资料_' . date("YmdHis") . '.xls';
        $export_arr['LOGID'] = 'log_id';
        $export_arr['用户ID'] = 'user_id';
        $export_arr['操作用户名'] = '';
        $export_arr['操作时间'] = 'log_time';
        $export_arr['操作IP'] = 'log_ip';
        $export_arr['影响数据ID'] = 'edit_id';
        $export_arr['操作记录'] = 'log_info';

        $export_field = $export_arr;
        $page = 0;
        $page_size = 500;
        $page_count = 100;

        $title = join("\t", array_keys($export_arr)) . "\t";
        unset($export_field['操作用户名']);
        $field = join(",", $export_field);


        $data = '';
        do {
        $rows = $this->Model->field('log_id,' . $field)->where($where)->limit($page * $page_size, $page_size)->select();

        if (empty($rows))return;
        foreach ($rows as $row) {
            foreach ($export_arr as $val) {
                if (strstr($val, '_time')) {
                    $data .= dateTpl($row[$val]) . "\t";
                } elseif ($val == '') {
                    $data .= adminInfo($row['user_id']) . "\t";
                } else {
                    $data .= str_replace(array("\r\n", "\n", "\r"), '', strip_tags($row[$val])) . "\t";
                }
            }
            $data .= "\n";
        }

        $page++;
        } while ($page <= $page_count);

        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }



}
