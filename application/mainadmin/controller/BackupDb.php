<?php
/*------------------------------------------------------ */
//-- 备份数据库
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\mainadmin\controller;
use app\AdminController;
use think\Backup;
use think\Db;
class BackupDb  extends AdminController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index() {
		$dbtables = Db::query('SHOW TABLE STATUS');
        $total = 0;
        foreach ($dbtables as $k => $v) {
            $dbtables[$k]['size'] = formatBytes($v['Data_length'] + $v['Index_length']);
            $total += $v['Data_length'] + $v['Index_length'];
        }
		
        $this->assign('list', $dbtables);
        $this->assign('total', formatBytes($total));
        $this->assign('tableNum', count($dbtables));
		return $this->fetch('index');
    }
	/*------------------------------------------------------ */
	//-- 执行备份
	/*------------------------------------------------------ */
	public function export($tables = null, $id = null, $start = null){
		//防止备份数据过程超时
		function_exists('set_time_limit') && set_time_limit(0);
		if($this->request->isPost() && !empty($tables) && is_array($tables)){ //初始化
			$path = config('config.DATA_BACKUP_PATH');
			if(!is_dir($path)){
				mkdir($path, 0755, true);
			}
			//读取备份配置
			$config = array(
					'path'     => realpath($path) . DIRECTORY_SEPARATOR,
					'part'     => config('config.DATA_BACKUP_PART_SIZE'),
					'compress' => config('config.DATA_BACKUP_COMPRESS'),
					'level'    => config('config.DATA_BACKUP_COMPRESS_LEVEL'),
			);
			//检查是否有正在执行的任务
			$lock = $path."backup.lock";
			if(is_file($lock)){
                $this->error('检测到有一个备份任务正在执行，请稍后再试！');
			} else {
				//创建锁文件
				file_put_contents($lock, time());
			}

			//检查备份目录是否可写
			if(!is_writeable($config['path'])){
				return $this->error('备份目录不存在或不可写，请检查后重试！');
			}
			session('backup_config', $config);

			//生成备份文件信息
			$file = array(
					'name' => date('Ymd-His', $_SERVER['REQUEST_TIME']),
					'part' => 1,
			);
			session('backup_file', $file);
			//缓存要备份的表
			session('backup_tables', $tables);
			//创建备份文件
			$Database = new Backup($file, $config);
			if(false !== $Database->create()){
				$tab = array('id' => 0, 'start' => 0);
				return $this->success('初始化成功！','',array('tables' => $tables, 'tab' => $tab));
			} else {
				return $this->error('初始化失败，备份文件创建失败！');
			}
		} elseif ($this->request->isGet() && is_numeric($id) && is_numeric($start)) { //备份数据
			$tables = session('backup_tables');
			//备份指定表
			$Database = new Backup(session('backup_file'), session('backup_config'));
			$start  = $Database->backup($tables[$id], $start);
			if(false === $start){ //出错
				return $this->_error('备份出错！');
			} elseif (0 === $start) { //下一表
				if(isset($tables[++$id])){
					$tab = array('id' => $id, 'start' => 0);
					return $this->success('备份完成！','',array('tab' => $tab));
				} else { //备份完成，清空缓存
					unlink(session('backup_config.path') . 'backup.lock');
					session('backup_tables', null);
					session('backup_file', null);
					session('backup_config', null);
					return $this->success('备份完成！');
				}
			} else {
				$tab  = array('id' => $id, 'start' => $start[0]);
				$rate = floor(100 * ($start[0] / $start[1]));
				return  $this->success("正在备份...({$rate}%)",'', array('tab' => $tab));
			}

		} else {//出错  
			return  $this->error('参数错误！');
		}
	}
	
	/*------------------------------------------------------ */
	//-- 下载
	/*------------------------------------------------------ */
	public function downLoad(){
		$path = config('config.DATA_BACKUP_PATH');
		if(!is_dir($path)){
			mkdir($path, 0755, true);
		}
		$path = realpath($path);
		$flag = \FilesystemIterator::KEY_AS_FILENAME;
		$glob = new \FilesystemIterator($path,  $flag);
		$list = array();$filenum = $total = 0;
		foreach ($glob as $name => $file) {
			if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
				$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
				$date = "{$name[0]}-{$name[1]}-{$name[2]}";
				$time = "{$name[3]}:{$name[4]}:{$name[5]}";
				$part = $name[6];
				$info = pathinfo($file);
				if(isset($list["{$date} {$time}"])){
					$info = $list["{$date} {$time}"];
					$info['part'] = max($info['part'], $part);
					$info['size'] = $info['size'] + $file->getSize();
				} else {
					$info['part'] = $part;
					$info['size'] = $file->getSize();
				}
				$info['compress'] = ($info['extension'] === 'sql') ? '-' : $info['extension'];
				$info['time']  = strtotime("{$date} {$time}");
				$filenum++;
				$total += $info['size'];
				$list["{$date} {$time}"] = $info;
			}
		}
		
		$this->assign('list', $list);
		$this->assign('filenum',$filenum);
		$this->assign('total',$total);
		return $this->fetch();
	}
	
	/**
     * 优化
     */
    public function optimize() {
    	$batchFlag = input('get.batchFlag', 0, 'intval');
    	//批量删除
    	if ($batchFlag) {
    		$table = input('key', array());
    	}else {
    		$table[] = input('tablename' , '');
    	}
    
    	if (empty($table)) {
    		$this->error('请选择要优化的表');
    	}
	
    	$strTable = implode(',', $table);
    	if (!Db::query("OPTIMIZE TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    	$this->success("优化表成功" . $strTable);
    
    }
    
    /**
     * 修复
     */
    public function repair() {
    	$batchFlag = input('get.batchFlag', 0, 'intval');
    	//批量删除
    	if ($batchFlag) {
    		$table = input('key', array());
    	}else {
    		$table[] = input('tablename' , '');
    	}
    
    	if (empty($table)) {
    		$this->error('请选择修复的表');
    	}
    	$strTable = implode(',', $table);
    	if (!Db::query("REPAIR TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    
    	$this->success("修复表成功" . $strTable);
  
    }
	/**
	 * 下载
	 * @param int $time
	 */
	public function downFile($time = 0) {
		$name  = date('Ymd-His', $time) . '-*.sql*';
		$path  = realpath(config('config.DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR . $name;
		$files = glob($path);
		if(is_array($files)){
			foreach ($files as $filePath){
				if (!file_exists($filePath)) {
					$this->error("该文件不存在，可能是被删除");
				}else{
					$filename = basename($filePath);
					header("Content-type: application/octet-stream");
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					header("Content-Length: " . filesize($filePath));
					readfile($filePath);
				}
			}
		}
	}
}
?>