<?php /*a:1:{s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\article\list.html";i:1552460091;}*/ ?>
<table class="table table-bordered table-striped ">
<thead >
 <thead>
        <tr>
           <th class="th-sortable" data-sort-name="id">文章ID</th>
            <th class="col-sm-3 ">文章标题</th>
            <th>所属分类</th>
            <th>发布时间</th>
            <th>修改时间</th>
            <th class="col-sm-1 mn70"></th>
        </tr>
    </thead>
<tbody>

<?php if(is_array($data['list']) || $data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>

 <tr>
        	<td><?php echo htmlentities($vo['id']); ?></td>
            <td><?php echo htmlentities($vo['title']); ?></td>
            <td><span data-toggle="dropdown"><?php echo htmlentities($cg_list[$vo['cid']]['name']); ?></span></td>
            <td><?php echo htmlentities(dateTpl($vo['add_time'])); ?></td>
            <td><?php echo htmlentities(dateTpl($vo['update_time'])); ?></td>
           
            <td>
                <a href="<?php echo url('info',array('id'=>$vo['id'])); ?>" class="m-xs" title="编辑">
                    <i class="fa fa-edit text-muted"></i>
                </a>
                <a href="<?php echo url('del',array('id'=>$vo['id'])); ?>" data-toggle="ajaxRemove" data-msg="确定删除 <?php echo htmlentities($vo['title']); ?>？" class="m-xs" title="删除">
                    <i class="fa fa-trash text-muted"></i>
                </a>
            </td>
        </tr>

<?php endforeach; endif; else: echo "" ;endif; ?> 
</tbody>
</table>
<?php if(empty($data['list']) || (($data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator ) && $data['list']->isEmpty())): ?>
<table width="100%" >
 	<tr><td height="300" colspan="8" align="center" valign="middle" >没有相关数据！</td></tr>
</table>
<?php endif; ?>  