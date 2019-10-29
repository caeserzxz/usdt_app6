<?php
namespace app\member\controller\sys_admin;
use app\AdminController;

use app\member\model\LogLoginModel;
/**
 * 登陆日志
 * Class Index
 * @package app\store\controller
 */
class LogLogin extends AdminController
{
   //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new LogLoginModel(); 
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
		
		$this->search['user_id'] = input('user_id/d');
	
		$this->assign("search", $this->search);
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['log_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['log_time','between',[strtotime("-1 months"),time()]];
		}
		if (0 < $this->search['user_id'] ){
			$where[] = ['user_id','=',$this->search['user_id'] ];
		}
        $export = input('export', 0, 'intval');

        if ($export > 0) {
            return $this->exportOrder($where);
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
    //-- 导出订单
    /*------------------------------------------------------ */
    public function exportOrder($where)
    {

        $count = $this->Model->where($where)->count('log_id');

        if ($count < 1) return $this->error('没有找到可导出的日志资料！');

        $filename = '会员登录日志资料_' . date("YmdHis") . '.xls';
        $export_arr['LOGID'] = 'log_id';
        $export_arr['用户ID'] = 'user_id';
        $export_arr['用户昵称'] = '';
        $export_arr['登陆时间'] = 'log_time';
        $export_arr['登陆IP'] = 'log_ip';

        $export_field = $export_arr;
        $page = 0;
        $page_size = 500;
        $page_count = 100;

        $title = join("\t", array_keys($export_arr)) . "\t";
        unset($export_field['用户昵称']);
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
                        $data .= userInfo($row['user_id']) . "\t";
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
