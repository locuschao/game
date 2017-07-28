<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title><?php echo ($shopInfo["shopName"]); ?></title>
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
	max-width: 800px;
}

.toubu {
	width: 100%;
	min-height: 140px;
	background: url(/Tpl/Game/image/my_bg.png) center no-repeat;
	background-size: 100% 100%;
}

.shop {
	width: 90%;
	margin: 0 auto;
	padding: 10px 0;
}

.shopImg img {
	width: 60px;
	height: 60px;
	border-radius: 4px;
}

.shopName {
	height: 20px;
	line-height: 20px;
	font-size: 120%;
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
}

.fw {
	font-size: 90%;
	/* text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden; 
	height: 20px;*/
	line-height: 20px;
}

.bor {
	border-bottom: #d5d7dc solid 1px;
}

.bor-r {
	border-right: #d5d7dc solid 1px;
}

.all, .cate {
	height: 45px;
	line-height: 45px;
	width: 70px;
	margin: 0 auto;
	text-align: center;
	padding-left: 20px;
	background: url(/Tpl/Game/image/all_goods.png) left center no-repeat;
	background-size: 17px;
	display: block;
}

.cate {
	background: url(/Tpl/Game/image/goodsCate.png) left center no-repeat;
	background-size: 17px;
}

.mc {
	background: #e50f12;
	color: #fff;
	width: 30px;
	border-radius: 2px;
	font-size: 12px;
	text-align: center;
	height: 18px;
	line-height: 18px;
    margin-left:10px;
}

.money {
	font-size: 120%;
}

.type {
	color: #783500
}
._top{
	max-width: 800px;
}
.badge {
	position: relative;
	left: 33px;
	top: -13px;
	display: inline-block;
	min-width: 10px;
	padding: 3px 7px;
	font-size: 12px;
	font-weight: 700;
	line-height: 1;
	color: #fff;
	text-align: center;
	white-space: nowrap;
	vertical-align: baseline;
	background-color: red;
	border-radius: 10px;
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
		<div class="_title_top"><?php echo ($shopInfo["shopName"]); ?></div>
		<div class="_right_top mess"
			onclick="location.href='/Game/Mess/mess/r/shop_personShop'"><?php if($notReadCount != 0): ?><span class="badge"><?php echo ($notReadCount); ?></span><?php endif; ?></div>
	</div>
	<div class="lh45" style="height: 44px;"></div>
	<div class="toubu">
		<div class="shop ub">
			<div class="shopImg">
				<img src="/<?php echo ((isset($shopInfo["shopImg"]) && ($shopInfo["shopImg"] !== ""))?($shopInfo["shopImg"]):'/Tpl/Game/image/shopImg.png'); ?>" />
			</div>
			<div class=" ub-f1" style="margin-left: 5px;">
				<div class="shopName"><?php echo ($shopInfo["shopName"]); ?></div>
				<div class="fw">经营范围：<?php echo ($fanwei); ?></div>
				<div class="xl">销量：
                <?php if(I('id',0,'intval')==18):?>
                <?php echo ($sales+30000); ?>
                <?php else: ?>
                <?php echo ((isset($sales) && ($sales !== ""))?($sales):0); ?>
                <?php endif;?></div>
			</div>
		</div>
		<div class="bor" style="width: 90%; margin: 0 auto;"></div>
		<div class="lh35 txt-l ti10" style="padding-top: 10px;padding-bottom:10px;">
			<p style="line-height: 20px;">营业时间：<?php echo ($shopInfo["serviceStartTime"]); ?> -
				<?php echo ($shopInfo["serviceEndTime"]); ?></p>
			<p style="line-height: 20px;">开店时间：<?php echo ($shopInfo["createTime"]); ?></p>
		</div>
	</div>
	<!--<div class="lh45 back-ff ub txt-c bor">-->
	<div class="">
		<div class="ub-f1 bor-r">
			<a href="<?php echo U('Shop/goodsList',array('id'=>$shopInfo['shopId']));?>"><span
				class="all" style="background: #3385ff;
    border-radius: 4px;
    color: #fff;
    display: block;
    height: 30px;
    line-height: 30px;
    margin: 7px 0 0 5px;
    padding: 0 10px;
    text-align: left;">按类别查看</span></a>
		</div>
		<div class="ub-f1" style="display:none">
			<a href="<?php echo U('Shop/memberGoodsList',array('id'=>$shopInfo['shopId']));?>">
			
				<span
				class="cate">会员商品</span></a>
		</div>
	</div>
	<div class="ub lh45 back-ff bor ti10" style="margin-top: 10px">首充号</div>
	<?php if(is_array($shouChong)): $i = 0; $__LIST__ = $shouChong;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sc): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
		<a href="<?php echo U('Goods/index',array('id'=>$sc['goodsId']));?>">
			<div class="shop ub ">
				<div class="shopImg">
					<img src="/<?php echo ((isset($sc["goodsThums"]) && ($sc["goodsThums"] !== ""))?($sc["goodsThums"]):'/Tpl/Game/image/shopImg.png'); ?>" />
				</div>
				<div class=" ub-f1" style="margin-left: 5px;">
					<div class="shopName">
						<span class="type">【首充号】</span><?php echo ($sc["goodsName"]); ?>
					</div>
					<div class="fw col-9"><?php echo ((isset($sc["goodsSpec"]) && ($sc["goodsSpec"] !== ""))?($sc["goodsSpec"]):'&nbsp;'); ?></div>
					<?php if($sc['isMiao']): ?><div class="mc">秒充</div><?php endif; ?>
				</div>
				<div style="text-align: center;">
					<div class="col-9 lh20">
						<s>￥<?php echo ($sc["shopPrice"]); ?></s>
					</div>
					<div class="col-red lh20 money">￥<?php echo ($sc["attrPrice"]); ?></div>
				</div>
			</div>
		</a>
	</div><?php endforeach; endif; else: echo "" ;endif; ?>

	<div class="ub lh45 back-ff bor ti10" style="margin-top: 10px">首充号代充</div>

	<?php if(is_array($daiChong)): $i = 0; $__LIST__ = $daiChong;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sc): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
		<a href="<?php echo U('Validatadc/yanzhen',array('id'=>$sc['goodsId'],'goodsType'=>0));?>">
			<div class="shop ub ">
				<div class="shopImg">
					<img src="/<?php echo ((isset($sc["goodsThums"]) && ($sc["goodsThums"] !== ""))?($sc["goodsThums"]):'/Tpl/Game/image/shopImg.png'); ?>" />
				</div>
				<div class=" ub-f1" style="margin-left: 5px;">
					<div class="shopName">
						<span class="type">【首充号代充】</span><?php echo ($sc["goodsName"]); ?>
					</div>
					<div class="fw col-9"><?php echo ((isset($sc["goodsSpec"]) && ($sc["goodsSpec"] !== ""))?($sc["goodsSpec"]):'&nbsp;'); ?></div>
					<?php if($sc['isMiao']): ?><div class="mc">秒充</div><?php endif; ?>
				</div>
				<div style="text-align: center;">
					<div class="col-9 lh20">
						<s>￥<?php echo ($sc["shopPrice"]); ?></s>
					</div>
					<div class="col-red lh20 money">￥<?php echo ($sc["attrPrice"]); ?></div>
				</div>
			</div>
		</a>
	</div><?php endforeach; endif; else: echo "" ;endif; ?>
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

</script>
</html>