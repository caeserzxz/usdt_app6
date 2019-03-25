<?php /*a:1:{s:75:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\shipping\info.html";i:1553477539;}*/ ?>
<form class="form-horizontal form-validate form-modal" method="post" action="<?php echo url('info'); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">编辑快递</h4>
            </div>
            <div class="modal-body">
                		<div class="form-group">
                            <label class="col-sm-2 control-label">快递名称</label>
                            <div class="col-sm-6 must">
                                <input type="text" class="input-xlarge" data-rule-maxlength="20" data-rule-required="true" name="shipping_name" value="<?php echo htmlentities($row['shipping_name']); ?>" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">快递代码</label>
                            <div class="col-sm-6 must">
                                <input type="text" class="input-xlarge" data-rule-maxlength="20" data-rule-required="true" name="shipping_code" value="<?php echo htmlentities($row['shipping_code']); ?>" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">电子面单客户号（快递鸟）</label>
                            <div class="col-sm-6"><input type="text" class="input-xlarge" name="customer_name" value="<?php echo htmlentities($row['customer_name']); ?>" ></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">电子面单密码（快递鸟）</label>
                            <div class="col-sm-6"><input type="text" class="input-xlarge" name="customer_pwd" value="<?php echo htmlentities($row['customer_pwd']); ?>" ></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">是否启用</label>
                            <div class="col-sm-6">
                             <label class="radio-inline">
                                  <input name="status" value="1" <?php echo htmlentities(tplckval($row['status'],'=1','checked',true)); ?> type="radio">是
                              </label>
                              <label class="radio-inline">
                                  <input name="status" value="0" <?php echo htmlentities(tplckval($row['status'],'=0','checked')); ?> type="radio">否
                              </label>
                              </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">前台开放</label>
                            <div class="col-sm-3">
                             <label class="radio-inline">
                                  <input name="is_front" value="1" <?php echo htmlentities(tplckval($row['is_front'],'=1','checked',true)); ?> type="radio">是
                              </label>
                              <label class="radio-inline">
                                  <input name="is_front" value="0" <?php echo htmlentities(tplckval($row['is_front'],'=0','checked')); ?> type="radio">否
                              </label>
                              </div>
                              <p class="help-inline">是否开放前台用户下单选择</p>
                        </div> 
                        <div class="form-group">
                            <label class="col-sm-2 control-label">货到付款</label>
                            <div class="col-sm-6">
                                  <label class="radio-inline">
                                      <input name="support_cod" value="1" <?php echo htmlentities(tplckval($row['support_cod'],'=1','checked')); ?> type="radio">
                                      是
                                  </label>
                                  <label class="radio-inline">
                                      <input name="support_cod" value="0" type="radio" <?php echo htmlentities(tplckval($row['support_cod'],'=0','checked',true)); ?> >
                                      否
                                  </label>
                              </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">排序</label>
                            <div class="col-sm-2 ">
                                <input type="text" class="input-max"  data-rule-required="true" name="sort_order" value="<?php echo htmlentities(intval($row['sort_order'])); ?>" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">快递描述</label>
                            <div class="col-sm-6 ">
                              <textarea name="shipping_desc" class="input-xlarge" ><?php echo htmlentities($row['shipping_desc']); ?></textarea>
                            </div><input name="shipping_id" type="hidden" value="<?php echo htmlentities(intval($row['shipping_id'])); ?>" />
                        </div> 
            </div>
            <div class="modal-footer">
                <input  type="hidden" name="id" value="<?php echo htmlentities(intval($row['shipping_id'])); ?>"/>
                <button type="submit" class="btn btn-primary" data-loading-text="保存中..." disabled>保存</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
        </div>
    </div>
</form>