<?php
namespace app\member\model;
use app\BaseModel;
use think\Db;
//*------------------------------------------------------ */
//-- 会员上级汇总表
/*------------------------------------------------------ */
class UsersBindSuperiorModel extends BaseModel
{
	protected $table = 'users_bind_superior';
	public  $pk = 'user_id';

    /**
     * 处理会员上级汇总
     * @param $user_id 操作会员ID
     * @param $pid 上级ID
     */
	public function treat($user_id = 0,$pid = 0){
        $data = $this->where('user_id',$user_id)->find();

        if ($pid > 0){
            $superior = $this->where('user_id',$pid)->value('superior');
            if (empty($superior) == true) {
                $superior = $user_id.','.$pid;
            }else {
                $superior = $user_id.','.$superior;
            }
        }else{
            $superior = $user_id;
        }
        if (empty($data)){//不存在数据时执行
            $inData['user_id'] = $user_id;
            $inData['pid'] = $pid;
            $inData['superior'] = $superior;
            $res = $this->save($inData);
            if ($res < 1){
                return false;
            }
            return true;
        }
        $upData['pid'] = $pid;
        $upData['superior'] = $superior;
        $res = $this->where('user_id',$user_id)->update($upData);
        if ($res < 1){
            return false;
        }

        $where[] = ['','exp',Db::raw("match(superior) against('{$user_id}')")];
        $allCount = $this->where($where)->count('user_id');

        if ($allCount < 1) return true;//没有下级不执行

        $upDataAll['superior'] = Db::raw("REPLACE(superior,'{$data['superior']}','{$superior}')");
        $res = $this->where($where)->update($upDataAll);

        if ($allCount == $res){
            return true;
        }
        return false;
    }
}
