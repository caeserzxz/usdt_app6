<?php
namespace app\http\controller;
use think\worker\Server;
use Workerman\Lib\Timer;
use think\facade\Cache;

class Blockchain extends Server
{
    protected $host = '0.0.0.0';
    protected $port = 2346;
    protected $connection;
    public function __construct()
    {
        $this->option = [
            'count'		=> 4,
            'name'		=> 'Blockchain'];
        parent::__construct();
    }
    // 启动执行
    public function onWorkerStart($worker) {
        // 每N秒执行一次
        $time_interval = 3;
        Timer::add($time_interval, function()use($worker,$time_interval){
            $data = $this->getData();
            foreach($worker->connections as $connection) {
                $connection->send($data);
            }
        });
     }
    // 接收数据
    public function onMessage($connection, $data)
    {
        $this->connection = $connection;
        $_data = json_decode($data, true);
        if(!$_data) {
            return ;
        }
        // 根据类型执行不同的业务
        switch($_data['type']) {
            // 登录
            case 'login':
                $data = $this->getData();
                $connection->send($data);
        }

    }
    //断开连接
    public static function onClose($connection)
    {

    }
    // 请求获取区块行情数据
    public function getData(){
        $mkey = 'blockchain_quotes';
        $data = Cache::get($mkey);
        if (empty($data)){
            $data = file_get_contents('https://api.coincap.io/v2/assets');
            Cache::set($mkey, $data, 3);
        }
        return $data;
    }
}
