<?php
namespace app\shop\controller\api;
use think\Db;
use app\ApiController;
use app\shop\model\GoodsModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\GoodsCommentModel;
use app\shop\model\GoodsCommentImagesModel;
/*------------------------------------------------------ */
//-- 评论相关API
/*------------------------------------------------------ */
class Comment extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	/*public function initialize(){
        parent::initialize();
    }*/
	/*------------------------------------------------------ */
	//-- 获取会员评论列表
	/*------------------------------------------------------ */
 	public function getList(){
		 $this->checkLogin();//验证登陆
		$this->Model = new OrderGoodsModel();
        $where[] = ['user_id','=',$this->userInfo['user_id']];
		$type = input('type','wait','trim');
     
        switch ($type){
            case 'wait'://待评论
               $where[] = ['is_evaluate','=',1];
                break;
            default://已评论
				$where[] = ['is_evaluate','=',2];
                break;
        }
        $data = $this->getPageList($this->Model, $where,'rec_id,pic,goods_id,goods_name,sku_name,shop_price,sale_price,is_evaluate',5);
        foreach ($data['list'] as $key=>$row){
            $row['exp_price'] = explode('.',$row['shop_price']);
            $return['list'][] = $row;
        }
        $return['page_count'] = $data['page_count'];
		$return['total_count'] = $data['total_count'];
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}
	
	/*------------------------------------------------------ */
	//-- 获取会员评论相关数据
	/*------------------------------------------------------ */
 	public function getInfo(){
		$this->checkLogin();//验证登陆
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$this->Model = new OrderGoodsModel();		 
		$row = $this->Model->find($rec_id);
		if ($row['user_id'] != $this->userInfo['user_id']){
			return $this->error('无权获取数据.');
		}
		$row['exp_price'] = explode('.',$row['sale_price']);
		if ($row['is_evaluate'] == 2){//如果已评论返回评论内容
			$GoodsCommentModel = new GoodsCommentModel();
			$where[] = ['order_id','=',$row['order_id']];
			$where[] = ['goods_id','=',$row['goods_id']];		
			$row['comment'] = $GoodsCommentModel->where($where)->find();
			if (empty($row['comment']) == false){
				$row['comment'] = $row['comment']->toArray();
				$row['_time'] = date('Y-m-d',$row['comment']['create_time']);
				$GoodsCommentImagesModel = new GoodsCommentImagesModel();
				$row['imgs'] = $GoodsCommentImagesModel->where('comment_id',$row['comment']['id'])->select()->toArray();
			}
		}
		$return['data'] = $row;
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}
	/*------------------------------------------------------ */
	//-- 提交评论
	/*------------------------------------------------------ */
 	public function post(){
		$this->checkLogin();//验证登陆
		$shop_goods_comment = settings('shop_goods_comment');
		if ($shop_goods_comment < 1){
			return $this->error('暂不开放评论.');
		}		
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$OrderGoodsModel = new OrderGoodsModel();		 
		$ogoods = $OrderGoodsModel->find($rec_id);
		if ($ogoods['user_id'] != $this->userInfo['user_id']){
			return $this->error('无权评论此商品.');
		}
		if ($ogoods['is_evaluate'] != 1){
			return $this->error('此商品不能评论.');
		}
		$inArr['content'] = input('content','','trim');
		if (empty($inArr['content'])){
			return $this->error('请填写评论.');
		}
		if (strlen($inArr['content']) < 15){
			return $this->error('评论长度不能小于15.');
		}
		Db::startTrans();//启动事务
		//更新订单商品为已评论
		$OrderGoodsModel = new OrderGoodsModel();
		$res = $OrderGoodsModel->where('rec_id',$rec_id)->update(['is_evaluate'=>2]);
		if ($res < 1){
			Db::rollback();// 回滚事务
            return $this->error('未知原因，写入失败-1.');
		}
		$imgfile = input('imgfile');
		
		//写入评论
		$inArr['rec_id'] = $rec_id;
		$inArr['type'] = 'goods';
		$inArr['goods_id'] = $ogoods['goods_id'];
		if (empty($imgfile) == false){
			$inArr['is_imgs'] = 1;
		}
		$inArr['sku_val'] = $ogoods['sku_val'];
		$inArr['sku_name'] = $ogoods['sku_name'];
		$inArr['by_name'] = $ogoods['goods_name'];
		$inArr['order_id'] = $ogoods['order_id'];
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['user_name'] = $this->userInfo['nick_name'];
		$inArr['headimgurl'] = $this->userInfo['headimgurl'];
		$inArr['create_time'] = time();
		$GoodsCommentModel = new GoodsCommentModel();
		$res = $GoodsCommentModel->save($inArr);
		if ($res < 1){
			Db::rollback();// 回滚事务
            return $this->error('未知原因，写入失败-2.');
		}
		$comment_id = $GoodsCommentModel->id;
		//处理图片		
		if (empty($imgfile) == false){
			$GoodsCommentImagesModel = new GoodsCommentImagesModel();
			$file_path = config('config._upload_').'comment/'.date('Ymd').'/';
			makeDir($file_path);
			foreach ($imgfile as $file){
				$file_name = $file_path.random_str(12).'.jpg';
				file_put_contents($file_name,base64_decode(str_replace('data:image/jpeg;base64,','',$file)));
				$imgInArr['comment_id'] = $comment_id;
				$imgInArr['image'] = trim($file_name,'.');
				$imgInArr['thumbnail'] = trim($file_name,'.');
				$res = $GoodsCommentImagesModel->save($imgInArr);
				if ($res < 1){
					@unlink($file_name);
					Db::rollback();// 回滚事务
					return $this->error('未知原因，写入失败-3.');
				}				
			}
		}
		 Db::commit();// 提交事务
		$this->success('评论成功.',url("shop/comment/index"));
	}
	/*------------------------------------------------------ */
	//-- 评论列表，商品调用
	/*------------------------------------------------------ */
 	public function getListByGoods(){
		$goods_id = input('goods_id',0,'intval');
		$limit = input('limit',5,'intval');		
		$goods = (new GoodsModel)->info($goods_id);		
		$GoodsCommentModel = new GoodsCommentModel();
		$GoodsCommentImagesModel = new GoodsCommentImagesModel();
		
		$where['and'][] = "goods_id = '".$goods_id."' AND type = 'goods' AND status = 2";	
		$where['or'][] = "cat_id = '".$goods['cid']."' AND type = 'goods_category' AND create_time > '".$goods['add_time']."'";
	
		$this->sqlOrder = 'goods_id DESC,id DESC';
		$data = $this->getPageList($GoodsCommentModel, $where,'id,user_name,headimgurl,content,sku_val,sku_name,create_time',$limit);	
		
		foreach ($data['list'] as $key=>$row){
			$row['imgs'] = $GoodsCommentImagesModel->where('comment_id',$row['id'])->select()->toArray();
			$row['_time'] = date('Y-m-d',$row['create_time']);
			$data['list'][$key] = $row;
		}
		$return['data'] = $data;
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}
   
}
