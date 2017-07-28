<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta http-equiv="Cache-control" content="no-cache"/>
<meta http-equiv="Cache" content="no-cache"/>
    <link href="/Public/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="/Tpl/Admin/css/page.css" rel="stylesheet">
    <script src="/Public/js/jquery.min.js"></script>
<title>平台订单列表</title>
    <style>
        .order_table table{
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="order_table">
                <form method="post" action="">
                    <table>
                        <tr><td>订单：</td><td><input type="text" class="form-control" name="orderNo" style="width:250px"></td><td><button class="btn btn-primary">搜索</button></td></tr>
                    </table>
                </form>
            </div>
            <table class="list_tab">
                <tr><th>商品图片</th><th>订单号</th><th>商品名</th><th>付款金额</th><th>分成金额</th><th>下单时间</th><th>订单状态</th><th>操作</th></tr>
                <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr><td><img src="/<?php echo ($vo["picture"]); ?>" width="50px"></td><td><?php echo ($vo["orderNo"]); ?></td><td><?php echo ($vo["goodsName"]); ?></td><td><?php echo ($vo["needPay"]); ?></td><td><?=number_format($vo['gain_price'],2)?></td><td><?php echo ($vo["createTime"]); ?></td><td>
                        <?php if($vo["orderStatus"] == -2 ): ?>未支付
                        <?php elseif($vo["orderStatus"] == 1): ?>未发货
                        <?php elseif($vo["orderStatus"] == 2): ?>已发货
                        <?php elseif($vo["orderStatus"] == 3): ?>交易完成
                        <?php elseif($vo["orderStatus"] == -3): ?>退款中
                        <?php elseif($vo["orderStatus"] == -4): ?>买家取消订单
                        <?php elseif($vo["orderStatus"] == -5): ?>退款成功
                        <?php elseif($vo["orderStatus"] == -6): ?>商家拒绝退款
                        <?php elseif($vo["orderStatus"] == -7): ?>商家取消订单
                        <?php elseif($vo["orderStatus"] == -8): ?>平台拒绝退款
                        <?php elseif($vo["orderStatus"] == -9): ?>订单失效<?php endif; ?>
                    </td>
                        <td><button class="btn btn-primary" onclick="location.href='/index.php/Admin/Voucher/orderDetail/orderId/<?php echo ($vo["orderId"]); ?>';">查看</button> </td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </table>
            <div class="page_nav">
                <?php echo ($page); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>