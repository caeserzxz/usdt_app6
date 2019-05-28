<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\RegionModel;
use PHPExcel_IOFactory;

/**
 * 区域管理
 * Class Index
 * @package app\store\controller
 */
class Region extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RegionModel();		
    }
	
	
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$pid = input('pid') * 1;
		if ($pid == 0){
			$where = ['pid' => $pid];
		}else{
			$where = ['id' => $pid];
		}
		$region = $this->Model->field(['id','name','pid','merger_name'])->where($where)->find(); 	
		
		$this->assign("region", $region);		
		$list = $this->Model->field(['id','name','pid','level_type','status'])->where(['pid' => $region['id']])->select(); 
		$this->assign("list", $list);
        return $this->fetch();
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
		$keyarr = array();
			
		$time = time();
		 //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
		for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
			$row = array();
			//从哪列开始，A表示第一列
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$cell =$currentSheet->getCell($address)->getValue();
				if($cell instanceof PHPExcel_RichText){
					$cell  = $cell->__toString();
				}					
				$row[] = empty($cell)?'':$cell;
			}
			if ($currentRow == 1){
				$keyarr = $row;
				continue;
			}
			$inall = array();
			foreach ($keyarr as $key=>$val){
				if (in_array($val,['merger_name','merger_short_name'])){					
					$inall[$val] = 	str_replace('中国,','',$row[$key]);								
				}else{
					$inall[$val] = 	$row[$key];
				}
			}
			
			$inall['status'] = 	1;
			$inall['update_time'] = $time;
			$region = $this->Model->where(['id' => $inall['id']])->find();	
			if (empty($region) == false){
				$res = $this->Model->where('',$region['id'])->update($inall);
			}else{
				$res = $this->Model->create($inall);
                $res =$res->id;
			}
			if (!$res){			
				return $this->error('处理数据失败，请重试！');
			}
		}
		$delwhere[] = ['update_time','<',$time];
		$this->Model->where($delwhere)->update(['status'=>'0']);
		
		
		return $this->success('导入成功.');
    }

}
