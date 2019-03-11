<?php /*a:1:{s:81:"D:\phpStudy\WWW\moduleshop\application\weixin\view\sys_admin\reply_text\info.html";i:1549953096;}*/ ?>
<form class="form-horizontal form-validate form-modal" method="post" action="<?php echo url('info'); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $row['id']>0 ? '编辑文本素材' : '添加文本素材'; ?></h4>
            </div>
            <div class="modal-body">
            			<div class="form-group">
                                <label class="control-label">关键词</label>
                                <div class="col-sm-7">
                                     <input name="keyword" id="keyword" data-rule-required="true" data-rule-maxlength="100" value="<?php echo htmlentities($row['keyword']); ?>" class="input-xlarge" title="关键词" type="text"><span class="maroon">*</span>
                                </div>
                         </div>
                      	 <div class="form-group">
                                <label class="control-label">是否开启</label>
                                <div class="col-sm-7">
                                    <label>
                                          <input class="checkbox-slider colored-blue" name="status" type="checkbox" value="1" <?php echo htmlentities(tplckval($row['status'],1,'checked')); ?>>
                                          <span class="text"></span>
                                   </label>
                                </div>
                         </div>
                    	<div class="form-group">
                    		<label class="control-label">自动回复内容</label>
                            <div class="col-sm-7">
                               <textarea name="reply_text" style=" width:100%; height:80px;" data-rule-required="true" data-msg-required="请填写自动回复内容"  class="input-text"><?php echo htmlentities($row['data']); ?></textarea><span class="maroon">*</span>
                            
                            </div>
                            
                        </div>
            </div>
            <div class="modal-footer">
                <input  type="hidden" name="id" value="<?php echo htmlentities(intval($row['id'])); ?>"/>
                <button type="submit" class="btn btn-primary" data-loading-text="保存中..." disabled>保存</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
        </div>
    </div>
</form>