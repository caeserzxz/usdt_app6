<?php

namespace app\member\model;

use app\BaseModel;
use think\facade\Cache;
use think\Db;

use app\weixin\model\WeiXinUsersModel;
use app\distribution\model\DividendRoleModel;

//*------------------------------------------------------ */
//-- 会员表
/*------------------------------------------------------ */

class UsersModel extends BaseModel
{
    protected $table = 'users';
    protected $mkey = 'user_info_mkey_';
    public $pk = 'user_id';

    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($user_id)
    {
        Cache::rm($this->mkey . $user_id);
        Cache::rm($this->mkey . 'account_' . $user_id);
    }
    /*------------------------------------------------------ */
    //-- 会员登陆
    /*------------------------------------------------------ */
    public function login($data = array())
    {
        $res = $this->checkPwd($data['password']);
        if ($res !== true) return langMsg('密码不正确，格式错误.','member.login.password_format_error');
        $password = f_hash($data['password']);
        $mobile = $data['mobile'] * 1;
        $userInfo = $this->where('mobile', $mobile)->find();
        if (empty($userInfo)) {
            return langMsg('用户不存在.','member.login.not_exist');
        }
        if ($userInfo['is_ban'] == 1){
            return langMsg('用户已被禁用.','member.login.is_ban');
        }

        $time = time();
        if ($userInfo['login_odd_num'] >= 10) {
            if ($userInfo['login_odd_time'] > $time - 3600) {
                $login_odd_time =  date('Y-m-d H:i:s', $userInfo['login_odd_time'] + 3600);
                return langMsg('密码错误次数过多帐号封停，解封时间：' .$login_odd_time,'member.login.login_is_lock',[$login_odd_time]);
            } else {
                $userInfo['login_odd_num'] = 7;//如果已到解封时间，给3次机会再登陆
            }
        }
        if ($userInfo['password'] != $password) {
            //记录异常登陆
            $this->where('user_id', $userInfo['user_id'])->update(['login_odd_time' => $time, 'login_odd_num' => $userInfo['login_odd_num'] + 1]);
            return langMsg('用户密码不正确.','member.login.password_error');
        }
        $upData['login_odd_num'] = 0;//登陆异常清空
        $upData['login_time'] = $time;
        $upData['login_ip'] = request()->ip();
        $upData['last_login_time'] = $userInfo['login_time'];
        $upData['last_login_ip'] = $userInfo['login_ip'];


        $this->where('user_id', $userInfo['user_id'])->update($upData);
        session('userId', $userInfo['user_id']);
        $LogLoginModel = new LogLoginModel();
        $inLog['log_ip'] = $upData['login_ip'];
        $inLog['log_time'] = $time;
        $inLog['user_id'] = $userInfo['user_id'];
        $LogLoginModel->save($inLog);
        $this->userInfo = $this->info($userInfo['user_id']);//附值全局
        $wxInfo = session('wxInfo');
        if (empty($wxInfo) == false){
            (new \app\weixin\model\WeiXinUsersModel)->where('wxuid',$wxInfo['wxuid'])->update(['user_id'=>$userInfo['user_id']]);
        }
        //判断订单模块是否存在
        if (class_exists('app\shop\model\OrderModel')) {
            //执行订单自动签收
            (new \app\shop\model\OrderModel)->autoSign($userInfo['user_id']);
            (new \app\shop\model\CartModel)->loginUpCart($userInfo['user_id']);//更新购物车
        }

        if (empty($data['source']) == false){
            if ($data['source'] == 'developers'){
                $devtoken = random_str(10).date(s);
                Cache::set('devlogin_'.$devtoken,$userInfo['user_id'],86400 * 7);
                return [$data['source'],$devtoken];
            }
        }

        return ['H5',$userInfo['user_id']];
    }

    /*------------------------------------------------------ */
    //-- 验证密码强度
    /*------------------------------------------------------ */
    private function checkPwd($pwd)
    {
        $pwd = trim($pwd);
        if (empty($pwd)) {
            return langMsg('密码不能为空.','member.checkpwd.empty_pwd');
        }
        if (strlen($pwd) < 8) {//必须大于8个字符
            return langMsg('密码必须大于八字符.','member.checkpwd.pwd_length_error');
        }
        if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符
            return langMsg('密码不能全是数字.','member.checkpwd.pwd_not_number');
        }
        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
            return langMsg('密码不能全是字母.','member.checkpwd.pwd_not_letter');
        }
        /*if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }
        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }*/
        return true;
    }
    /*------------------------------------------------------ */
    //-- 生成用户唯一标识,主要用于分享后身份识别
    /*------------------------------------------------------ */
    public function getToken()
    {
        $token = random_str(16);
        $count = $this->where('token', $token)->count('user_id');
        if ($count >= 1) return $this->getToken();
        return $token;
    }

    /**
     * 会员注册
     * @param array $inArr 写入数据
     * @param int $wxuid 微信会员ID
     * @param bool $is_admin 是否是后台添加的用户
     * @param string $obj
     * @return bool|string
     */
    public function register($inArr = array(), $wxuid = 0, $is_admin = false,&$obj = '')
    {
        $wxInfo = session('wxInfo');
        if (empty($wxInfo)){
            $wxInfo['wxuid'] = 0;
        }
        $inArr['pid'] = 0;
//        $inArr['role_id'] = 1;//一注册就是会员
        //分享注册
        if ($is_admin == false) {
            if ($wxInfo['wxuid'] > 0) {//微信访问根据微信分享来源记录，执行
                $bind_share_rule = settings('bind_share_rule');
                if ($bind_share_rule == 0){//按最先分享绑定
                    $sort = 'id ASC';
                }else{//按最后分享绑定
                    $sort = 'id DESC';
                }
                $share_user_id = (new \app\weixin\model\WeiXinInviteLogModel)->where('wxuid',$wxInfo['wxuid'])->order($sort)->value('user_id');
                if (empty($share_user_id) == false){
                    $inArr['pid'] = $share_user_id;
                }
            }else{
                $share_token = session('share_token');
                if (empty($share_token) == false){
                    $inArr['pid'] = $this->getShareUser($share_token);
                }
            }
        }//end

        if ($wxuid == 0) {
            if (empty($inArr)) {
                return '获取注册数据失败.';
            }
            if (empty($inArr['mobile'])) {
                return '请填写手机号码';
            }
            if (checkMobile($inArr['mobile']) == false) {
                return '手机号码不正确.';
            }
            $count = $this->where('mobile', $inArr['mobile'])->count('user_id');
            if ($count > 0) return '手机号码：' . $inArr['mobile'] . '，已存在.';
            if (empty($inArr['nick_name']) == false) {//昵称不为空时，判断是否已存在
                $count = $this->where('nick_name', $inArr['nick_name'])->count('user_id');
                if ($count > 0) return '昵称：' . $inArr['nick_name'] . '，已存在.';
            }
            $res = $this->checkPwd($inArr['password']);//验证密码强度
            if ($res !== true) {
                return $res;
            }
            $inArr['password'] = f_hash($inArr['password']);

            if ($is_admin == false && $inArr['pid'] == 0){//非后台新增会员
                $register_invite_code = settings('register_invite_code');
                $register_must_invite = settings('register_must_invite');
                if ($register_invite_code > 0){
                    if(empty($inArr['invite_code'])){
                        if ($register_must_invite == 1) {
                            return '需要填写邀请信息才能注册.';
                        }
                    }else{
                        $share_user_id = 0;
                        if ($register_invite_code == 1) {//会员邀请码
                            $share_user_id = $this->where('token', $inArr['invite_code'])->value('user_id');
                        }elseif ($register_invite_code == 2) {//会员ID
                            $share_user_id = $this->where('user_id', $inArr['invite_code'])->value('user_id');
                        }elseif ($register_invite_code == 3) {//会员手机号
                            $share_user_id = $this->where('mobile', $inArr['invite_code'])->value('user_id');
                        }
                        if ($share_user_id < 1){
                            return '邀请帐号不存在.';
                        }
                        $inArr['pid'] = $share_user_id;
                    }
                }
            }
        }
        unset($inArr['invite_code']);
        $time = time();
        $inArr['token'] = $this->getToken();
        $inArr['reg_time'] = $time;

        if ($wxuid == 0) {//如果微信UID为0，启用事务，不为0时，外部已启用
            Db::startTrans();
        }
        $res = $this->create($inArr);
        if ($res < 1) {
            Db::rollback();
            return '未知错误-1，请尝试重新提交.';
        }
        $user_id = $res->user_id;
        if ($user_id < 29889) {
            $this->where('user_id',$user_id)->delete();
            $inArr['user_id'] = 29889;
            $res = $this->create($inArr);
            $user_id = $res->user_id;
            if ($user_id < 1) {
                Db::rollback();
                return '未知错误-2，请尝试重新提交.';
            }
        }
        //创建会员帐户信息
        $AccountLogModel = new AccountLogModel();
        $res = $AccountLogModel->createData(['user_id' => $user_id, 'update_time' => $time]);
        if ($res < 1) {
            Db::rollback();
            return '未知错误-2，请尝试重新提交.';
        }
        //edn
        //注册赠送积分
        $register_integral = settings('register_integral') * 1;
        if ($register_integral > 0) {
            $changedata['change_desc'] = '注册赠送积分';
            $changedata['change_type'] = 7;
            $changedata['by_id'] = $user_id;
            $changedata['use_integral'] = $register_integral;
            $changedata['total_integral'] = $register_integral;
            $res = $AccountLogModel->change($changedata, $user_id, false);
            if ($res < 1) {
                Db::rollback();
                return '未知错误-3，请尝试重新提交.';
            }
        }
        //edn
        //捆绑微信会员信息
        if ($wxuid == 0) {
            $wxuid = $wxInfo['wxuid'];
        }
        if ($wxuid > 0) {
            $WeiXinUsersModel = new WeiXinUsersModel();
            $res = $WeiXinUsersModel->bindUserId($wxuid, $user_id);
            if ($res < 1) {
                Db::rollback();
                return '未知错误-4，请尝试重新提交.';
            }
        } //end
        Db::commit();
        $bind_pid_time = settings('bind_pid_time');
        if ($bind_pid_time < 1) {
            //写入九级关系链
            $this->regUserBind($user_id,$inArr['pid']);
        }
        //红包模块存在执行
        if (class_exists('app\shop\model\BonusModel')) {
            //注册送红包
            (new \app\shop\model\BonusModel)->sendByReg($user_id);
        }

        //后台添加的用户，加日志
        if($is_admin&&!empty($obj)){
            $obj->_log($user_id, '后台手动新增会员-用户id:'.$user_id, 'member');
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd($data = array(), &$obj)
    {
        if (empty($data)) {
            return '获取数据失败.';
        }
        if (empty($data['mobile'])) {
            return '请填写手机号码';
        }
        if (checkMobile($data['mobile']) == false) {
            return '手机号码不正确.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        $user = $this->where('mobile', $data['mobile'])->find();
        if (f_hash($data['password']) == $user['password']) {
            return '新密码与旧密码一致,请核实.';
        }
        $upArr['password'] = f_hash($data['password']);
        $res = $this->where('user_id', $user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
        $obj->_log($res, '用户找回密码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd($data = array(), &$obj)
    {
        if (empty($data)) {
            return '获取数据失败.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        $user = $this->where('user_id', $this->userInfo['user_id'])->find();
        $oldPwd = f_hash($data['old_password']);
        if ($oldPwd != $user['password']) {
            return '旧密码错误.';
        }
        $upArr['password'] = f_hash($data['password']);
        if ($upArr['password'] == $user['password']) {
            return '新密码与旧密码一致无须修改.';
        }
        $res = $this->where('user_id', $user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
        $obj->_log($user['user_id'], '用户修改密码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 绑定会员手机
    /*------------------------------------------------------ */
    public function bindMobile($data = array(), &$obj)
    {
        if (empty($data)) {
            return '获取数据失败.';
        }
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true) {
            return $res;
        }
        if (is_numeric($data['pay_password']) == false){
            return '请填写6位数字的支付密码.';
        }
        $count = $this->where('mobile', $data['mobile'])->count('user_id');
        if ($count > 0) {
            return $data['mobile'] . '此手机号码已绑定其它帐号.';
        }
        $upArr['mobile'] = $data['mobile'];
        $upArr['password'] = f_hash($data['password']);
        $upArr['pay_password'] = f_hash($data['pay_password']);
        $res = $this->where('user_id', $this->userInfo['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，绑定手机失败.';
        $obj->_log($this->userInfo['user_id'], '用户绑定手机号码.', 'member');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取用户信息
    //-- val 查询值
    //-- type 查询类型
    //-- isCache 是否调用缓存
    /*------------------------------------------------------ */
    public function info($val, $type = 'user_id', $isCache = true)
    {
        if (empty($val)) return false;
        if ($isCache == true) $info = Cache::get($this->mkey . $val);
        if (empty($info) == false) return $info;
        if ($type == 'token') {
            $info = $this->where('token', $val)->find();
            if (empty($info)){
                return [];
            }
            $info = $info->toArray();
        } else {
            $info = $this->where('user_id', $val)->find();
            if (empty($info)){
                return [];
            }
            $info = $info->toArray();
            $AccountModel = new AccountModel();
            $account = $AccountModel->where('user_id', $val)->find();
            if (empty($account) == true) {
                //创建会员帐户信息
                $AccountModel->createData(['user_id' => $val, 'update_time' => time()]);
                $account = $AccountModel->where('user_id', $val)->find();
            }
            $info['account'] = $account->toArray();
        }
        unset($info['password']);
        $info['shareUrl'] = config('config.host_path') . '/?share_token=' . $info['token'];//分享链接
        $info['level'] = userLevel($info['account']['total_integral'], false);//获取等级信息
        if ($info['role_id'] > 0) {
            $info['role'] = (new DividendRoleModel)->info($info['role_id']);
        }else{
            $info['role']['role_id'] = 0;
            $info['role']['role_name'] = '粉丝';
        }
        //还没有执行绑定关系执行
        if ($info['is_bind'] == 0 && $info['pid'] > 0){
            $bind_pid_time = settings('bind_pid_time');
            if ($bind_pid_time < 1){
                $this->regUserBind($info['user_id'],$info['pid'],'edit');
            }elseif($info['last_buy_time'] > 0){
                $this->regUserBind($info['user_id'],$info['pid'],'edit');
            }
        }//end
        Cache::set($this->mkey . $val, $info, 30);
        return $info;
    }
    /*------------------------------------------------------ */
    //--获取上级信息
    /*------------------------------------------------------ */
    public function getSuperior($pid)
    {
        if ($pid < 1) return [];
        $info = $this->info($pid);
        unset($info['password']);//销毁不需要的字段
        return $info;
    }
    /*------------------------------------------------------ */
    //--获取会员帐户
    /*------------------------------------------------------ */
    public function getAccount($user_id, $isCache = true)
    {
        $user_id = $user_id * 1;
        if ($user_id < 1) return array();
        $mkey = $this->mkey . 'account_' . $user_id;
        if ($isCache == true) $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $info = $this->where('u.user_id', $user_id)->alias('u')->field('u.user_id,u.mobile,ua.*')->join('users_account ua', 'u.user_id = ua.user_id', 'left')->find();
        if (empty($info) == false){
            $info = $info->toArray();
        }
        Cache::set($mkey, $info, 60);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 更新会员信息
    /*------------------------------------------------------ */
    public function upInfo($user_id, $data)
    {
        $user_id = $user_id * 1;
        $res = $this->where('user_id', $user_id)->update($data);
        $this->cleanMemcache($user_id);
        return $res;
    }
    /*------------------------------------------------------ */
    //-- 根据token获取分享者进行关联
    //-- $val int/string 用户ID/分享token
    //-- $type string 类型
    /*------------------------------------------------------ */
    public function getShareUser($val = '',$type = 'token')
    {
        if (empty($val)) return 0;
        $DividendSatus = settings('DividendSatus');
        if ($DividendSatus == 0) return 0;//不开启推荐，不执行
        if ($type == 'token'){
            $pInfo = $this->where('token', $val)->find();
        }else{
            $pInfo = $this->where('user_id', $val)->find();
        }
        if (empty($pInfo)) return 0;
        if ($pInfo['is_ban'] == 1) {//如果用户被封禁，直接归被封禁用户的上级
            if ($pInfo['pid'] < 1) return 0;
           return $this->getShareUser($pInfo['pid'],'user_id');
        }
        return $pInfo['user_id'];
    }
    /*------------------------------------------------------ */
    //-- 获取会员下级汇总
    /*------------------------------------------------------ */
    public function userShareStats($user_id = 0, $isCache = true)
    {
        $info = Cache::get($this->mkey . '_us_' . $user_id);
        if ($isCache == true && empty($info) == false) return $info;
        $user_id = $user_id * 1;
        $UsersBind = new UsersBindModel();
        $rows = $UsersBind->field("count('user_id') as num,level")->where('pid', $user_id)->group('level')->select();
        $d_level = config('config.dividend_level');
        $info['all'] = 0;
        foreach ($d_level as $key => $val) {
            $info[$key] = 0;
        }
        foreach ($rows as $row) {
            $info['all'] += $row['num'];
            $info[$row['level']] = $row['num'];
        }
        Cache::set($this->mkey . '_us_' . $user_id, $info, 30);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 操作等级关联
    // -- user_id int 会员ID
    // -- pid  int  所属上级ID
    // -- is_edit boolean 是否重新修改，不是修改发送绑定消息通知
    /*------------------------------------------------------ */
    public function regUserBind($user_id = 0, $pid = 0, $is_edit = false)
    {
        static $UsersBindModel;
        if ($user_id < 1) return true;
        if ($is_edit == false){
            $DividendSatus = settings('DividendSatus');
            if ($DividendSatus == 0) return true;//不开启推荐，不执行
            $is_bind= $this->where('user_id', $user_id)->value('is_bind');
            if ($is_bind > 0) return false;//已执行绑定不再执行
            $this->where('user_id', $user_id)->update(['is_bind'=>1]);
            //会员上级汇总处理
            $res = (new UsersBindSuperiorModel)->treat($user_id,$pid);
            if ($res == false){
                return false;
            }
            //会员上级汇总处理end
        }



        if (isset($UsersBindModel) == false){
            $UsersBindModel = new UsersBindModel();
        }

        if ($is_edit == true) {//如果重新修改会员上级，清理原来的记录
            $UsersBindModel->where('user_id',$user_id)->delete();
        }
        $dividend_level = config('config.dividend_level');
        $bind_max_level = config('config.bind_max_level');//后台记录50层的关系链config('config.dividend_level');
        $_pid = $pid;
        for ($level=1;$level<=$bind_max_level;$level++) {
            if ($_pid < 1) break;
            if ($level <= 2) {//只记录前两级发送通知
                $sendUids[$_pid] = $dividend_level[$level];
            }
            $inArr['level'] = $level;
            $inArr['user_id'] = $user_id;
            $inArr['pid'] = $_pid;
            $res = $UsersBindModel->create($inArr);
            if ($is_edit == true && $res->pid < 1) return false;
            $_pid = $this->where('user_id', $_pid)->value('pid');
        }



        if ($is_edit == false) {
            //发送模板消息
            $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
            $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
            $wxInfo = $WeiXinUsersModel->info($user_id);

            $data['user_id'] = $user_id;
            $data['nickname'] = $wxInfo['wx_nickname'];
            $data['sex'] = $wxInfo['sex'] == 1 ? '男' : '女';
            $data['region'] = $wxInfo['wx_province'] . $wxInfo['wx_city'];
            $data['send_scene'] = 'bind_user_msg';
            unset($wxInfo);
            foreach ($sendUids as $uid => $val) {
                $data['level'] = $val;
                $data['openid'] = $WeiXinUsersModel->where('user_id', $uid)->value('wx_openid');
                $WeiXinMsgTplModel->send($data);
            }
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取会员的上级关联链
    /*------------------------------------------------------ */
    public function getSuperiorList($user_id = 0)
    {
        if ($user_id < 1) return array();
        $chain = Cache::get('userSuperior_' . $user_id);
        if ($chain) return $chain;
        $dividendRole = (new DividendRoleModel)->getRows();
        $i = 1;
        $user_id = $this->where('user_id', $user_id)->value('pid');
        if ($user_id < 1) return [];
        do {
            $info = $this->where('user_id', $user_id)->field('user_id,nick_name,pid,role_id,reg_time')->find();
            $chain[$i]['level'] = $i;
            $chain[$i]['user_id'] = $info['user_id'];
            $chain[$i]['reg_time'] = dateTpl($info['reg_time']);
            $chain[$i]['nick_name'] = empty($info['nick_name']) ? '未填写' : $info['nick_name'];
            $chain[$i]['role_name'] = $info['role_id'] > 0 ? $dividendRole[$info['role_id']]['role_name'] : '无身份';
            $user_id = $info['pid'];
            $i++;
        } while ($user_id > 0);

        Cache::set('userSuperior_' . $user_id, $chain, 300);
        return $chain;
    }
    /*------------------------------------------------------ */
    //-- 签到首页
    /*------------------------------------------------------ */
    public function signIndex($user_id = 0,$type = 0)
    {
        $redis_name = "sing_".$user_id."_".date('Ymd');
        $info = Cache::get($redis_name);
        if(empty($info)){
            $info = (new UsersSignModel)->where(['user_id'=>$user_id])->field('time')->select();
            Cache::set($redis_name,$info,86400);
        }
        foreach ($info as $key => $value) {
            $data[] = $value['time'];
        }
        if ($type == 0) {
            $demo = implode("','", $data);
            $demo = "'".$demo."'";
        }else{
             $demo = $data;
        }
        return $demo;
    }
    /*------------------------------------------------------ */
    //-- 签到首页七条历史记录
    /*------------------------------------------------------ */
    public function signTime($user_id = 0,$type = 0)
    {
        $redis_name = "signTime_".$user_id."_".date('Ymd');
        $info = Cache::get($redis_name);
        if(empty($info)){
            $info = (new UsersSignModel)->where(['user_id'=>$user_id])->field('time')->limit(7)->order('time desc')->select();
            Cache::set($redis_name,$info,86400);
        }
        foreach ($info as $key => $value) {
            $data[] = date('n.d', $value['time']);;
        }
        if ($type == 0) {
            $demo = implode("','", $data);
            $demo = "'".$demo."'";
        }else{
             $demo = $data;
        }
        return $demo;
    }
    /*------------------------------------------------------ */
    //-- 签到积分
    /*------------------------------------------------------ */
    public function signIntegral()
    {
        $use_integral = settings('sign_integral');
        return $use_integral;
    }
    /*------------------------------------------------------ */
    //-- 是否签到 1签到0还没签到
    /*------------------------------------------------------ */
    public function isSign($user_id = 0)
    {
        $data[0] = strtotime(date('Y-m-d', time()) . '00:00:00');
        $data[1] = strtotime(date('Y-m-d', time()) . '23:59:59');
        $res = (new UsersSignModel)->where(['user_id'=>$user_id])->whereTime('time', 'between', [$data[0], $data[1]])->find();
        $ress = $res?1:0;
        return $ress;
    }
    /*------------------------------------------------------ */
    //-- 签到记录
    /*------------------------------------------------------ */
    public function signInfos($user_id = 0, $date, $page, $limit)
    {
        $where[] = ['user_id','=',$user_id];
        $where[] = ['time','>=',$date[0]];
        $where[] = ['time','<=',$date[1]];
        $p = ($page-1)*$limit;
        $info = (new UsersSignModel)->where($where)->order('time desc')->limit($p, $limit)->select();
        foreach ($info as $key => $value) {
            $info[$key]['timeData'] = date('Y-m-d', $value['time']);
        }
        return $info;
    }
    public function signInfo($user_id = 0)
    {
        $time1 = mktime(0,0,0,date('m'),1,date('Y'));
        $time2 = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $where[] = ['user_id','=',$user_id];
        $where[] = ['time','>=',$time1];
        $where[] = ['time','<=',$time2];

        $redis_name = "singInfo_".$user_id."_".date('Ymd');
        $info = Cache::get($redis_name);
        if(empty($info)){
            $info = (new UsersSignModel)->where($where)->order('time desc')->select();
            Cache::set($redis_name,$info,86400);
        }
        foreach ($info as $key => $value) {
            $info[$key]['timeData'] = date('Y-m-d', $value['time']);
        }
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 签到
    /*------------------------------------------------------ */
    public function signIng($user_id = 0)
    {
        $begin = strtotime(date('Y-m-d', time()) . '00:00:00');
        $end = strtotime(date('Y-m-d', time()) . '23:59:59');
        $res = (new UsersSignModel)->where(['user_id'=>$user_id])->whereTime('time', 'between', [$begin, $end])->find();
        if($res){ return false; }

        $inArr['use_integral'] = settings('sign_integral');
        $inArr['user_id'] = $user_id;
        $inArr['time'] = time();
        $redis_name1 = "sing_".$user_id."_".date('Ymd');
        $redis_name2 = "signTime_".$user_id."_".date('Ymd');
        Db::startTrans();//启动事务
        try {
            $AccountLogModel = new AccountLogModel();
            $changedata['change_desc'] = '签到赠积分';
            $changedata['change_type'] = 9;
            $changedata['by_id'] = 0;
            $changedata['use_integral'] = $inArr['use_integral'];
            $re = $AccountLogModel->change($changedata, $user_id, false);
            if ($re == true) {
                $ress = Db::name('sign')->insert($inArr);
                if($ress == true){
                    Db::commit();// 提交事务
                    Cache::rm($redis_name1);
                    Cache::rm($redis_name2);
                    return true;
                }else{
                    Db::rollback();// 回滚事务
                    return false;
                }
            }else{
                Db::rollback();// 回滚事务
                return false;
            }
        } catch (Exception $e) {
             Db::rollback();// 回滚事务
             return $this->error($e->getMessage);
        }
    }
}
