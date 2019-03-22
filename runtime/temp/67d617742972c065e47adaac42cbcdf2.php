<?php /*a:1:{s:76:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\order\shipping.html";i:1552460091;}*/ ?>
<!DOCTYPE html>
<form class="form-horizontal form-validate form-modal" method="post" action="<?php echo url('shipping'); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">发货信息</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning m-b">
                    <p>订单编号：<span class="m-l-xs"><?php echo htmlentities($orderInfo['order_sn']); ?></span></p>
                    <p>下单时间：<span class="m-l-xs"><?php echo htmlentities(dateTpl($orderInfo['add_time'])); ?></span></p>
                    <p>收货信息：<span class="m-l-xs"><?php echo htmlentities($orderInfo['consignee']); ?>,<?php echo htmlentities($orderInfo['mobile']); ?>,<?php echo htmlentities($orderInfo['merger_name']); ?> <?php echo htmlentities($orderInfo['address']); ?></span></p>
                </div>
                <div class="bs-example bs-example-tabs">
                    <ul id="myTab" class="nav nav-tabs">
                        <li class="active"><a href="#logistics" onclick="$('.kd_type').val(1);" data-toggle="tab">物流信息</a></li>
                        <li><a href="#nlogistics" onclick="$('.kd_type').val(2);" data-toggle="tab">无需物流</a></li>
                        <li><a href="#nloprinteorder" onclick="$('.kd_type').val(3);" data-toggle="tab">快递鸟打单</a></li>
                    </ul>
                    <input type="hidden" name="kd_type" class="kd_type" value="1"/>
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane m-t fade in active" id="logistics"  style="overflow:hidden;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">快递公司</label>
                                <div class="col-sm-6 must">
                                    <select class="input-xlarge" name="shipping_id">
                                        <option value="">请选择快递</option>
                                        <?php echo $shippingOpt; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">快递单号</label>
                                <div class="col-sm-6 must">
                                    <input type="text" class="input-xlarge" placeholder="请输入快递单号" name="invoice_no" value="<?php echo htmlentities($orderInfo['invoice_no']); ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane m-t fade" id="nlogistics"><p>如果该物品无需物流运送(如虚拟产品)</p></div>
                        <div class="tab-pane m-t fade" id="nloprinteorder">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">快递公司</label>
                                <div class="col-sm-6 must">
                                    <select class="input-xlarge" name="kdn_shipping_id">
                                        <option value="">请选择快递</option>
                                        <?php echo $kdnpingopt; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">货物名称</label>
                                <div class="col-sm-6 must">
                                    <input type="text" class="input-xlarge" placeholder="请输入货物名称"  name="kdeorder_goods_name" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" value="<?php echo htmlentities($orderInfo['order_id']); ?>" name="id" />
                <button type="submit" class="btn btn-primary" data-loading-text="保存中..." disabled>保存</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
        </div>
    </div>
</form>

