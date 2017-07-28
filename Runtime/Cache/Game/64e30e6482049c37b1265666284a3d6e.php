<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title>店铺列表</title>
<meta name="viewport"
      content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="keywords" content="手游交易、手游交易平台、手机游戏交易、手机游戏交易平台、手机游戏充值、手机游戏充值中心、手游账号交易、手游金币交易、手游狗交易平台、游戏代练、手游代练、手游代练平台">
<meta name="description" content="手游狗交易平台是中国手机游戏交易服务第一门户，是手机网游玩家购买出售买卖首充号、苹果代充、买卖游戏账号、游戏金币，进行游戏币交易、满级号交易、手机游戏代练、手游郊游、游戏装备道具交易的首选交易平台，手游玩家也可在此领取游戏礼包、买充值卡、手游退游等首选交易服务平台，手游狗_jiaoyi_最安全高效的手游交易平台！">

<link rel="stylesheet" href="/Tpl/Game/css/supermarket.css" />
<link rel="stylesheet" href="/Tpl/Game/css/orderDetail.css" />
<link rel="stylesheet" href="/Tpl/Game/css/base.css" />
<script src="/Tpl/Game/js/jquery2.1.1.min.js"></script>
<style>
body {
	background: #efeff4;
	max-width:800px;
}

input[type=text] {
	height: 30px;
	line-height: 30px;
	background: transparent;
	border: #ccc solid 1px;
	border-radius: 4px;
	width: 95%;
	text-indent: 10px;
}

.search {
	background: #fff600;
	height: 35px;
	line-height: 35px;
	border-radius: 4px;
	padding: 0px 15px;
}

.list {
	height: 60px;
	padding: 10px;
	background: #fff;
	width: 85%;
	margin: 10px auto 10px auto;
	border-radius: 4px
}

.shopImg img {
	width: 60px;
	height: 60px;
	border-radius: 4px;
}

.shopContent {
	margin-left: 5px;
}

.shopName {
	font-size: 120%;
	text-overflow: ellipsis;
	white-space: nowrap;
	height: 25px;
	line-height: 25px;
	over-flow: hidden;
}

.fw {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
	height: 20px;
	line-height: 20px;
	font-size: 90%;
}
.search{cursor:pointer;}
._top{
	max-width: 800px;
}
</style>
	<style>
.footer {
    left: 0;
    margin: 0 auto;
    max-width: 800px;
    right: 0;
}
.footerNav {
    cursor: pointer;
}
div.money {
    background: none;
    height: auto;
    margin:auto;
    padding-top:0;
    text-align: center;
    width: 100px;
}
</style>
<link rel="stylesheet" type="text/css" href="/Tpl/Game/css/index.css?2017">
<script>
/**
 * @author peng
 * @date 2017-01
 * @descreption 百度统计代码
 */
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?ae8e957a4f0953a1cf9c80e125908845";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
</head>
<body>
	<div class="_top" style="z-index: 999">
		<div class="_left_top" onclick='history.go(-1)'></div>
		<div class="_title_top">店铺列表</div>
		<div class="_right_top mess"
			onclick="location.href='/Game/Mess/mess/r/Goods_searchShop'"></div>
	</div>
	<div class="lh45"></div>
	<form action="<?php echo U('searchShop');?>" method="get" id="shopSearch">
		<div class="lh35  back-ff ub " style="padding: 10px 0px;">
			<div class="ub-f1 margin_left10">
				<input type="text" name="key" value="<?php echo ($_GET['key']); ?>"
					placeholder="请输入店铺名" />
			</div>
			<div class="margin_right10 search">店铺搜索</div>
		</div>
	</form>

	<?php if(is_array($shopList)): $i = 0; $__LIST__ = $shopList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sl): $mod = ($i % 2 );++$i;?><a
		href="<?php echo U('Shop/personShop',array('id'=>$sl['shopId']));?>">
		<div class="ub list">
			<div class="shopImg">
				<img src="/<?php echo ((isset($sl["shopImg"]) && ($sl["shopImg"] !== ""))?($sl["shopImg"]):'/Tpl/Game/image/shopImg.png'); ?>" />
			</div>
			<div class="ub-f1 shopContent">
				<div class='shopName'><?php echo ($sl["shopName"]); ?></div>
				<div class="fw">经营范围：<?php echo ($sl["scope"]); ?></div>
				<div class="sl">销量：<?php echo ((isset($sl["sales"]) && ($sl["sales"] !== ""))?($sl["sales"]):0); ?></div>
			</div>
			<div class="right_arrows arrowsMy"></div>
		</div>
	</a><?php endforeach; endif; else: echo "" ;endif; ?>
	<script>
var is_login=<?=$_SESSION['oto_mall']['oto_userId']?1:0 ?>;

</script>

<script src="/Tpl/Game/js/index.js?20170303"></script>
<?php
if( (!in_array(CONTROLLER_NAME,['Login','Register','Agent','Confirm']) && !in_array(MODULE_NAME,['Home','Admin'])) || (CONTROLLER_NAME=='Register' && ACTION_NAME == 'register') ): ?>
<!--
    @author peng
    @date 2016-12-18
    @descreption 换图标
    -->
    
	<div class="footer ub">
		<div class="ub-f1 pos-r footerNav goHome">
			<div class="pos-a">
				<div class="footer_img" data-default="/Tpl/Game/image/home_normal.png" data-active="/Tpl/Game/image/home_selected.png"><?php if($index): ?><img src="/Tpl/Game/image/home_selected.png"/><?php else: ?><img src="/Tpl/Game/image/home_normal.png"/><?php endif; ?></div>
				<div class="footer_title footer_title_active">首页</div>
			</div>
		</div>
		<div class="ub-f1 pos-r footerNav">
			<div class="pos-a">
				<div class="footer_img" data-default="/Tpl/Game/image/order_normal.png" data-active="/Tpl/Game/image/order_selected.png"><?php if($order): ?><img  src="/Tpl/Game/image/order_selected.png"/><?php else: ?><img  src="/Tpl/Game/image/order_normal.png"/><?php endif; ?></div>
				<div class="footer_title ">订单</div>
			</div>
		</div>
		<div class="ub-f1 pos-r footerNav">
			<div class="pos-a">
				<div class="footer_img" data-default="/Tpl/Game/image/user_normal.png" data-active="/Tpl/Game/image/user_selected.png"><?php if($my): ?><img src="/Tpl/Game/image/user_selected.png"/><?php else: ?><img src="/Tpl/Game/image/user_normal.png"/><?php endif; ?></div>
				<div class="footer_title">个人中心</div>
			</div>
		</div>
	</div>
<?php endif;?>

    
</body>
<script>
$('body').on('click','.search',function(){
	$('#shopSearch').submit();
})

</script>
</html>