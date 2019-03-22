<?php /*a:1:{s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\sms_tpl\info.html";i:1552460091;}*/ ?>
<form class="form-horizontal form-validate form-modal" method="post" action="<?php echo url('info'); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">编缉短信模板</h4>
            </div>
            <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">模板名称</label>
                        <div class="col-sm-6 m-t-md">
                            <?php echo htmlentities($row['sms_tpl_name']); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">短信模板ID</label>
                        <div class="col-sm-6 must">
                            <input type="text" class="input-xlarge"  name="sms_tpl_code" value="<?php echo htmlentities($row['sms_tpl_code']); ?>"  data-rule-required="true" data-msg-required="短信模板ID不能为空">
                        </div>
                    </div> 
                    <div class="form-group">
                        <label class="col-sm-2 control-label">短信内容</label>
                        <div class="col-sm-9">
                        <textarea name="tpl_content" data-rule-required="true" style="width:100%; height:120px;"><?php echo htmlentities($row['tpl_content']); ?></textarea>
                           
                        </div>
                    </div> 
            </div>
            <div class="modal-footer">
                <input  type="hidden" name="tpl_id" value="<?php echo htmlentities(intval($row['tpl_id'])); ?>"/>
                <button type="submit" class="btn btn-primary" data-loading-text="保存中..." disabled>保存</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
        </div>
    </div>
</form>
