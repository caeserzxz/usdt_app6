<?php /*a:1:{s:79:"D:\phpStudy\WWW\mainshop\application\distribution\view\sys_admin\role\list.html";i:1552460091;}*/ ?>
<table class="table table-striped  m-b-none">
<thead>
<tr>
  <th>身份名称</th>
  <th>升级方法</th>
  <th>级别</th>
  <th>更新时间</th>
  <th width="100">操作</th>
</tr>
</thead>
<tbody>

<?php if(is_array($data['list']) || $data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
<tr>
    <td><?php echo htmlentities($vo['role_name']); ?></td>
    <td><?php echo htmlentities($vo['uplevel_fun_name']); ?></td>
    <td><?php echo htmlentities($vo['level']); ?></td>
    <td><?php echo htmlentities(dateTpl($vo['update_time'])); ?></td>
    <td>  <a href="<?php echo url('info',array('role_id'=>$vo['role_id'])); ?>"   class="m-xs" title="编辑">
            <i class="fa fa-edit text-muted"></i>
        </a>
        <a href="<?php echo url('delete',array('role_id'=>$vo['role_id'])); ?>" data-toggle="ajaxRemove" data-msg="确定删除 <?php echo htmlentities($vo['level_name']); ?>" class="m-xs" title="删除">
            <i class="fa fa-trash-o text-muted"></i>
        </a></td>
</tr>
<?php endforeach; endif; else: echo "" ;endif; ?> 
</tbody>
</table>
<?php if(empty($data['list']) || (($data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator ) && $data['list']->isEmpty())): ?>
<table width="100%" >
 	<tr><td height="300" colspan="8" align="center" valign="middle" >没有相关数据！</td></tr>
</table>
<?php endif; ?>  