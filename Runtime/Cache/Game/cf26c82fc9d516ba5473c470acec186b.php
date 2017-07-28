<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta property="qc:admins" content="3541104430061172011637577155" />
<title><?php if($_GET['r'] == 'index' ): ?>手游狗-手游充值平台_首充号_手游代充-手游交易平台_手游狗手游交易平台 <?php elseif($_GET['r'] == 'order' ): ?> 订单 <?php elseif($_GET['r'] == 'my' ): ?> 个人中心 <?php else: ?> 手游狗-手游充值平台_首充号_手游代充-手游交易平台_手游狗手游交易平台<?php endif; ?></title>
<meta name="viewport"
      content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="keywords" content="手游交易、手游交易平台、手机游戏交易、手机游戏交易平台、手机游戏充值、手机游戏充值中心、手游账号交易、手游金币交易、手游狗交易平台、游戏代练、手游代练、手游代练平台">
<meta name="description" content="手游狗交易平台是中国手机游戏交易服务第一门户，是手机网游玩家购买出售买卖首充号、苹果代充、买卖游戏账号、游戏金币，进行游戏币交易、满级号交易、手机游戏代练、手游郊游、游戏装备道具交易的首选交易平台，手游玩家也可在此领取游戏礼包、买充值卡、手游退游等首选交易服务平台，手游狗_jiaoyi_最安全高效的手游交易平台！">

<meta name="viewport"
	content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">

	<link rel="stylesheet" type="text/css" href="/Tpl/Game/css/swiper.min.css">
<link rel="stylesheet" type="text/css" href="/Tpl/Game/css/base.css">
<link rel="stylesheet" type="text/css" href="/Tpl/Game/css/index.css">
<script src="/Tpl/Game/js/jquery2.1.1.min.js"></script>
<script src="/Tpl/Game/js/swiper.min.js"></script>
<script src="/Tpl/Game/js/layer.min.js"></script>
<script src="/Tpl/Game/js/base.js"></script>
<script src="/Tpl/Game/js/index.js"></script>
<style>
.footerNav{cursor:pointer;}
.titleNav{display:none;}
.nav_tuijia_img{text-align:left;}
.nav_tuijia_img{min-height:100px; clear:both;}
.nav_tuijia_img a{display:inline-block;width:25%;text-align:center;}
.orderMenu,.del{cursor:pointer;}
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
/* 二次开发  start */
.logo {
	position: relative;
	left: 10px;
	top: -5px;
}
body{
	max-width: 800px;
}
.header {
	max-width: 800px;
}
.footer{
	max-width: 800px;
	left: 0;
	right: 0;
	margin: 0 auto;
}
.ps_or_login{
	margin-right:10px;
}
.ps_or_login img{
	cursor: pointer;
	margin-top: 13px;
	width: 22px;
}
.myMoney .money {
    width: 100px;
    height: 80px;
    margin: 0 auto;
    text-align: center;
    background: url(/Tpl/Game/image/money_bg.png) center no-repeat;
    background-size: 100% 100%;
    padding-top: 3px;
}
/*二次开发 end */
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
	<?php $r=$_GET['r']; $index=false; $order=false; $my=false; if($r=='index' or empty($r)){ $index=true; }else if($r=='order'){ $order=true; }else if($r=='my'){ $my=true; } $o=$_GET['o']; $one=false; $two=false; $three=false; $four=false; $five=false; if(!isset($o)){ $one=true; }else if($o=='-2'){ $two=true; }else if($o=='1'){ $three=true; }else if($o=='2'){ $four=true; }else if($o=='3'||$o=='4'||$o=='-4'||$o=='-5'||$o=='-6'||$o=='-3'){ $five=true; } ?>
	<div class="header  col-6">
		<div class="ub">
			<div class="logo <?php if($order or $my): ?>nobg<?php endif; ?>"  <?php if($_GET['r'] == 'index' or empty($_GET['r'])): ?>style="display:block; background-size:90%;width:70px;"<?php endif; ?>></div>
			<div class="ub-f1 search">
				<div class="titleNav index_title"  <?php if($_GET['r'] == 'index' or empty($_GET['r'])): ?>style="display:block;"<?php endif; ?>>
					<form id="indexForm"
						action="<?php echo U('Goods/goodsSearch');?>" method="get">
						<input type="text" name="key" id="search"
							value="<?php echo ($_GET['search']); ?>" placeholder="游戏搜索" />
					</form>
				</div>
				<div class="titleNav" <?php if($_GET['r'] == 'order' ): ?>style="display:block;"<?php endif; ?>>订单</div>
				<div class="titleNav" <?php if($_GET['r'] == 'my' ): ?>style="display:block;"<?php endif; ?>>个人中心</div>
			</div>
			<!--<div class="mess" onclick="location.href='/Game/Mess/mess/r/Index_index'"></div>-->
			<?php if(empty($_SESSION['oto_mall']['oto_userId'])): ?><div class="ps_or_login" onclick="location.href='/Game/Login/login'">登录</div>
				<?php else: ?>
				<?php if($_GET['r'] == 'order'||$_GET['r'] == 'my' ): ?><div class="mess" onclick="location.href='/Game/Mess/mess/r/Index_index'"><?php if($notReadCount != 0): ?><span class="badge"><?php echo ($notReadCount); ?></span><?php endif; ?></div>
					<?php else: ?>
					<div class="ps_or_login" onclick="location.href='/Game/Index/index/r/my'"><img src="/Tpl/Game/image/user1.png"></div><?php endif; endif; ?>
		</div>
	</div>
	<input type="hidden" id="noticePage" value="1"/>
	<input type="hidden" id="notReadCount" value="<?php echo ($notReadCount); ?>">
	<div style="width: 100%; height: 50px;"></div>
	<div id="page1" class="hideDiv" <?php if($_GET['r'] == 'index' or empty($_GET['r'])): ?>style="display:block;"<?php endif; ?>>
	<div class="banner">
		<div class="swiper-container one_swiper visible-xs-block">
			<div class="swiper-wrapper" id="imgList">
				<?php if(is_array($scrollImg)): $i = 0; $__LIST__ = $scrollImg;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$img): $mod = ($i % 2 );++$i;?><div class="swiper-slide">
						<a href="<?php echo ($img["adURL"]); ?>"><img src="/<?php echo ((isset($img["adFile"]) && ($img["adFile"] !== ""))?($img["adFile"]):'/Tpl/Game/image/banner.png'); ?>" /></a>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>	
			</div>
			<div class="swiper-pagination one-pagination"></div>
		</div>
	</div>
	<div class="ub nav">
		<div class="pos-r ub-f1 ub">
			<div class="pos-a">
				<a href="<?php echo U('Goods/goodsType',array('type'=>'sc'));?>">
				<div class="nav_img"><img src="/Tpl/Game/image/one.png"/></div>
				<div class="nav_title">首充号</div>
				</a>
			</div>
		</div>
		<div class="pos-r ub-f1 ub">
		<div class="pos-a">
			<a href="<?php echo U('Goods/goodsType',array('type'=>'dc'));?>">
				<div class="nav_img"><img src="/Tpl/Game/image/two.png"/></div>
				<div class="nav_title">首充号代充</div>
				</a>
				</div>
		</div>
		<div class="pos-r ub-f1 ub">
		<div class="pos-a">
			<a  href="/index.php/game/goodsRank">
			<!--<a href="javascript:layer.msg('暂未开放 敬请期待')">-->
				<div class="nav_img"><img src="/Tpl/Game/image/member_bg.png"/></div>
				<div class="nav_title">会员</div>
				</a>
				</div>
		</div>

	</div>
	
	<div class="tuijia bor-b">
		<div class="tuijia_title ub">
			<div class="ub-f1">热门游戏推荐</div>
		</div>
			<div class="ub" onclick="window.location.href='/Game/Goods/goodsList/o/1/gameId/<?php echo ($indexGoods[0]['id']); ?>'">
			<div class="tuijian_img " style="margin-left:9px;">	<a href="<?php echo U('Goods/goodsList',array('gameId'=>$indexGoods[0]['id'],'o'=>1));?>"><img src="/<?php echo ((isset($indexGoods[0]['gameIco']) && ($indexGoods[0]['gameIco'] !== ""))?($indexGoods[0]['gameIco']):'/Tpl/Game/image/shopIMg.png'); ?>"/></a></div>
			<div class="ub-f1  tuijin_first">
				<div class="tuijin_first_title"><?php echo ($indexGoods[0]['gameName']); ?></div>
				<div class="tuijin_first_label">
						<?php if($shouType): ?><a>首充号</a><?php endif; ?>
						<?php if($daiType): ?><a>首充号代充</a><?php endif; ?>
				</div>
			</div>
			<div class="right_arrows"> </div>
		</div>
		<div class=" nav_tuijia_img">
		<?php if(is_array($indexGoods)): $i = 0; $__LIST__ = array_slice($indexGoods,1,19,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo U('Goods/goodsList',array('gameId'=>$vo['id'],'o'=>1));?>">
							<div class="nav_img tuijian_img"><img src="/<?php echo ((isset($vo["gameIco"]) && ($vo["gameIco"] !== ""))?($vo["gameIco"]):'/Tpl/Game/image/shopIMg.png'); ?>"/></div>
							<div class="nav_title"><?php echo ($vo["gameName"]); ?></div>
						</a><?php endforeach; endif; else: echo "" ;endif; ?>

	</div>
	<a href="<?php echo U('Goods/goodsType',array('type'=>'sc'));?>">
	<div class=" lh45 back-ff  txt-c  " >更多热门游戏</div></a>
	</div>
		<div class="tuijia bor-b">
		<div class="tuijia_title">热门商铺推荐</div>
		<form action="<?php echo U('shop/searchShop');?>" method="get" id="shopSearch">
		<div class="ub searchShop">
			<div class="ub-f1"><input type="text" name="key" id="searchShop" value="<?php echo ($_GET['searchShop']); ?>" placeholder="搜索店铺"/></div>
			<div class="shopSearchBtn" style="cursor:pointer">搜索</div>
		</div>
			</form>
		<div class="nav_tuijia_img no-border" style="border:none;">
			<?php if(is_array($goodsShop)): $i = 0; $__LIST__ = array_slice($goodsShop,0,null,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$gs): $mod = ($i % 2 );++$i;?><a href="<?php echo U('Shop/personShop',array('id'=>$gs['shopId']));?>">
						<div class="nav_img tuijian_img"><img src="/<?php echo ((isset($gs["shopImg"]) && ($gs["shopImg"] !== ""))?($gs["shopImg"]):'/Tpl/Game/image/shopIMg.png'); ?>"/></div>
						<div class="nav_title"><?php echo ($gs["shopName"]); ?></div>
					</a><?php endforeach; endif; else: echo "" ;endif; ?>
	</div>
		<a href="<?php echo U('Shop/searchShop',array('key'=>''));?>">
	<div class=" lh45 back-ff  txt-c  " >更多热门店铺</div></a>
	</div>
	<!-- 公告 -->
	<div class="ub lh45 back-ff bor-b ti10 mar-t10 notice">公告</div>
	<?php if(is_array($notice)): $i = 0; $__LIST__ = $notice;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo U('Index/noticeDetail',array('id'=>$vo['id']));?>">
	<div class="ub lh45 back-ff bor-b ti10  "><span class="notice_color">【公告】</span><?php echo ($vo["title"]); ?> </div></a><?php endforeach; endif; else: echo "" ;endif; ?>
	<?php if(count($notice) >= 5): ?><div class=" lh45 back-ff bor-b  txt-c  loadMoreNotice" >加载更多</div><?php endif; ?>
	<div class="txt-c  mar-t10 col-9" style="padding:10px 20px; line-height:20px;">
	<?php echo ($bottom); ?>
	</div>
	
	</div>
	
	
	<!-- ------------------订单----------------------- -->
	
	<div id="page2" class="hideDiv" <?php if($_GET['r'] == 'order'): ?>style="display:block;"<?php endif; ?>>
		<div class="ub orderNav">
			<div class="ub-f1 pos-r br-r  orderMenu <?php if($one): ?>orderNavActive<?php endif; ?> ">
				<div class="pos-a txt-c">全部</div>
			</div>
			<div class="ub-f1 pos-r br-r orderMenu <?php if($two): ?>orderNavActive<?php endif; ?>">
				<div class="pos-a txt-c">待支付</div>
			</div>
			<div class="ub-f1 pos-r br-r orderMenu <?php if($three): ?>orderNavActive<?php endif; ?>">
				<div class="pos-a txt-c">待发货</div>
			</div>
			<div class="ub-f1 pos-r br-r orderMenu <?php if($four): ?>orderNavActive<?php endif; ?>">
				<div class="pos-a txt-c">已发货</div>
			</div>
			<div class="ub-f1 pos-r orderMenu <?php if($five): ?>orderNavActive<?php endif; ?>">
				<div class="pos-a txt-c">已完成</div>
			</div>
		</div>
	
		<div class="orderList"  <?php if($one): ?>style="display:block"<?php endif; ?>>
			<?php if(is_array($orders)): $i = 0; $__LIST__ = $orders;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="ub orderTime del_<?php echo ($vo["orderId"]); ?>">
						<div class="ub-f1 txt-l col-9">	<a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>'5'));?>"">订单状态:
							<?php if($vo['orderStatus'] == -2): ?>待支付 <?php echo ($vo["createTime"]); ?>
							<?php elseif($vo['orderStatus'] == 1): ?>
								待发货  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -4): ?>
								买家取消订单  <?php echo (date("Y-m-d H:i:s",$vo["cancelTime"])); ?>
							<?php elseif($vo['orderStatus'] == -3): ?>
								退款中  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -5): ?>
								退款成功  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -6): ?>
								商家拒绝退款  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == 2): ?>
							已发货 <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == 3): ?>
							已完成  <?php echo ($vo["signTime"]); ?>
								<?php elseif($vo['orderStatus'] == -7): ?>
								商家取消订单  <?php echo (date("Y-m-d H:i:s",$vo["cancelTime"])); ?>
								<?php elseif($vo['orderStatus'] == -8 ): ?>
								平台拒绝退款 <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
								<?php elseif($vo['orderStatus'] == -9 ): ?>
								订单失效 <?php echo ($vo["createTime"]); endif; ?>
						</a>
						</div>
						<!--<?php if($vo['orderStatus'] == 4): ?><div class="del " data-id="<?php echo ($vo["orderId"]); ?>" ></div><?php endif; ?>-->
                        <!--peng -->
                        <?php if(in_array($vo['orderStatus'],[2,3,-4,-5,-7,-8,-9])): ?>
                        <div class="del " data-id="<?php echo ($vo["orderId"]); ?>" ></div>
                        <?php endif; ?>
					</div>
				    <a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId']));?>" class="del_<?php echo ($vo["orderId"]); ?>">
					<div class="ub orderImgList del_<?php echo ($vo["orderId"]); ?>">
						<div class="orderImg"><img src="/<?php echo ($vo["goodsThums"]); ?>"/></div>
						<div class="ub-f1 pos-r orderContent">
							<div class="pos-a">
									<div class="order_title"><?=$vo['shopId']?$vo['type'].' -':''; echo ($vo["goodsName"]); ?></div>
							<div class="order_type"><?php echo ($vo["gameName"]); if($vo['vName']): ?>/<?php echo ($vo["vName"]); endif; ?></div>
							<div class="order_money">总计：<span class="zhongji">￥<?php echo ($vo["needPay"]); ?></span></div>
							</div>
						</div>
					</div>
					</a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
		<div class="orderList" <?php if($two): ?>style="display:block"<?php endif; ?>>
				<?php if(is_array($waitPay)): $i = 0; $__LIST__ = $waitPay;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>$vo['orderStatus']));?>"">
				<div class="ub orderTime">
					<div class="ub-f1 txt-l col-9">订单状态:待支付  <?php echo ($vo["createTime"]); ?></div>
				</div>
				<div class="ub orderImgList">
					<div class="orderImg"><img src="/<?php echo ($vo["goodsThums"]); ?>"/></div>
					<div class="ub-f1 pos-r orderContent">
						<div class="pos-a">
							<div class="order_title"><?=$vo['shopId']?$vo['type'].' -':''; echo ($vo["goodsName"]); ?></div>
							<div class="order_type"><?php echo ($vo["gameName"]); if($vo['vName']): ?>/<?php echo ($vo["vName"]); endif; ?></div>
							<div class="order_money">总计：<span class="zhongji">￥<?php echo ($vo["needPay"]); ?></span></div>
						</div>
					</div>
				</div>
				</a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
		<div class="orderList" <?php if($three): ?>style="display:block"<?php endif; ?>>
				<?php if(is_array($waitDeliver)): $i = 0; $__LIST__ = $waitDeliver;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>$vo['orderStatus']));?>"">
				<div class="ub orderTime">
					<div class="ub-f1 txt-l col-9">订单状态:待发货  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?></div>
				</div>
				<div class="ub orderImgList">
					<div class="orderImg"><img src="/<?php echo ($vo["goodsThums"]); ?>"/></div>
					<div class="ub-f1 pos-r orderContent">
						<div class="pos-a">
							<div class="order_title"><?=$vo['shopId']?$vo['type'].' -':''; echo ($vo["goodsName"]); ?></div>
							<div class="order_type"><?php echo ($vo["gameName"]); if($vo['vName']): ?>/<?php echo ($vo["vName"]); endif; ?></div>
							<div class="order_money">总计：<span class="zhongji">￥<?php echo ($vo["needPay"]); ?></span></div>
						</div>
					</div>
				</div>
				</a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
		<div class="orderList" <?php if($four): ?>style="display:block"<?php endif; ?>>
				<?php if(is_array($fahuo)): $i = 0; $__LIST__ = $fahuo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="ub orderTime del_<?php echo ($vo["orderId"]); ?>">
					<div class="ub-f1 txt-l col-9"><?php if($vo['orderStatus'] == 2): ?>订单状态: 已发货  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); else: ?>
					订单状态: 退款中 <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); endif; ?></div>
                    <!--peng -->
                    <?php if(in_array($vo['orderStatus'],[2,3,-4,-5,-7,-8,-9])): ?>
                    <div class="del " data-id="<?php echo ($vo["orderId"]); ?>" ></div>
                    <?php endif; ?>
				</div>
				<a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>$vo['orderStatus']));?>" class="del_<?php echo ($vo["orderId"]); ?>">
				
				<div class="ub orderImgList">
					<div class="orderImg"><img src="/<?php echo ($vo["goodsThums"]); ?>"/></div>
					<div class="ub-f1 pos-r orderContent">
						<div class="pos-a">
							<div class="order_title"><?=$vo['shopId']?$vo['type'].' -':''; echo ($vo["goodsName"]); ?></div>
							<div class="order_type"><?php echo ($vo["gameName"]); if($vo['vName']): ?>/<?php echo ($vo["vName"]); endif; ?></div>
							<div class="order_money">总计：<span class="zhongji">￥<?php echo ($vo["needPay"]); ?></span></div>
						</div>
					</div>
				</div>
				</a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
		<div class="orderList" <?php if($five): ?>style="display:block"<?php endif; ?>>
				<?php if(is_array($orderFinsh)): $i = 0; $__LIST__ = $orderFinsh;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="ub orderTime del_<?php echo ($vo["orderId"]); ?>" >
						<div class="ub-f1 txt-l col-9"><a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>$vo['orderStatus']));?>"">订单状态:
	<?php if($vo['orderStatus'] == -2): ?>待支付 <?php echo ($vo["createTime"]); ?>
							<?php elseif($vo['orderStatus'] == 1): ?>
								待发货  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -4): ?>
								买家取消订单  <?php echo (date("Y-m-d H:i:s",$vo["cancelTime"])); ?>
							<?php elseif($vo['orderStatus'] == -3): ?>
								退款中  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -5): ?>
								退款成功  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == -6): ?>
								商家拒绝退款  <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == 2): ?>
							已发货 <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); ?>
							<?php elseif($vo['orderStatus'] == 3): ?>
							已完成  <?php echo ($vo["signTime"]); ?>
							<?php elseif($vo['orderStatus'] == -7): ?>
								商家取消订单 <?php echo (date("Y-m-d H:i:s",$vo["cancelTime"])); ?>
							<?php elseif($vo['orderStatus'] == -8): ?>
								平台拒绝退款 <?php echo (date("Y-m-d H:i:s",$vo["paytime"])); endif; ?></a></div>
					<div class="del" data-id="<?php echo ($vo["orderId"]); ?>"></div>
				</div>
				<a href="<?php echo U('Orders/orderDetail',array('id'=>$vo['orderId'],'o'=>$vo['orderStatus']));?>" class="del_<?php echo ($vo["orderId"]); ?>">
				<div class="ub orderImgList" >
					<div class="orderImg"><img src="/<?php echo ($vo["goodsThums"]); ?>"/></div>
					<div class="ub-f1 pos-r orderContent">
						<div class="pos-a">
							<div class="order_title"><?=$vo['shopId']?$vo['type'].' -':''; echo ($vo["goodsName"]); ?></div>
							<div class="order_type"><?php echo ($vo["gameName"]); if($vo['vName']): ?>/<?php echo ($vo["vName"]); endif; ?></div>
							<div class="order_money">总计：<span class="zhongji">￥<?php echo ($vo["needPay"]); ?></span></div>
						</div>
					</div>
				</div>
				</a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
	</div>
	
	<!-- --------------------订单--------------------- -->
	
	<!-- 个人中心 -->
	<div id="page3" class="hideDiv" <?php if($_GET['r'] == 'my'): ?>style="display:block;"<?php endif; ?>>
		<div class="avator">
			<a href="<?php echo U('Login/userInfo');?>"><div class="avator_img"><img src="<?php echo ((isset($userInfo["userPhoto"]) && ($userInfo["userPhoto"] !== ""))?($userInfo["userPhoto"]):'/Tpl/Game/image/default_avator.png'); ?>"/></div></a>
			<div class="avator_phone"><a href="<?php echo U('Login/userInfo');?>"><?php echo ($userInfo["userPhone"]); ?></a></div>
		</div>
		
		<div class="myMoney">
			<div class="money" onclick="location.href='<?php echo U('Ucenter/wallet');?>'">
				<p><span class="money_num"><?php echo ((isset($userInfo["userMoney"]) && ($userInfo["userMoney"] !== ""))?($userInfo["userMoney"]):0); ?></span>元</p>
				<p class="money_text">我的余额</p>
			</div>
		</div>
		
		<div class="myList">
	
			<div class="ub myListText" onclick='location.href="<?php echo U('Agent/index');?>"'>
				<div class="ub-f1">我要赚钱</div>
				<div class="right_arrows arrowsMy"></div>
			</div>
			<div class="ub myListText" onclick='location.href="<?php echo U('Service/customer');?>"'>
				<div class="ub-f1">客服中心</div>
				<div class="right_arrows arrowsMy"></div>
			</div>
			<div class="ub myListText" onclick='location.href="<?php echo U('Login/userInfo');?>"'>
				<div class="ub-f1">我的账户</div>
				<div class="right_arrows arrowsMy"></div>
			</div>
			<div class="ub myListText" onclick='location.href="/Game/Mess/mess/r/Index_index"'>
				<div class="ub-f1">我的消息</div>
				<div class="right_arrows arrowsMy"></div>
			</div>
            <div class="ub myListText" onclick='location.href="<?php echo U('goodsRank/myVoucher');?>"'>
				<div class="ub-f1">我的代金券</div>
				<div class="right_arrows arrowsMy"></div>
			</div>
			<div class="ub myListText" onclick='logOut()'>
					<div class="ub-f1">退出当前账户</div>
			<!--<div class="right_arrows arrowsMy"></div>-->
			</div>
		</div>
		
	</div>
	<!-- 个人中心 -->

    <!--
    @author peng
    @date 2016-12-18
    @descreption 换图标
    -->
	<!--<div class="footer ub">
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
	</div>-->
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
	/**
	 * 魏永就
	 * 16-12-15
	 * 退出当前用户
	 */
	function logOut(){
		layer.confirm('确认退出登录吗?', {icon: 3, title:'退出登录'}, function(index){
			//do something
			$.ajax({
				type : "POST",
				url : "<?php echo U('Login/logOut');?>",
				data : {},
				dataType : "json",
				success : function(data) {
					if(data.status==0){
						layer.msg('成功退出');
						setTimeout(function(){
							location.href="<?php echo U('Index/index');?>";
						},1000)
					}
				}
			});
			layer.close(index);
		});
	}

/**
 * @author peng
 * @copyright 2016
 * @remark 登录判断
 */
 
    //var is_login=<?=$_SESSION['oto_mall']['oto_userId']?1:0 ?>;
	$(function() {
		var swiper = new Swiper('.one_swiper', {
			autoplay : 3000,
			paginationtouchendable : false,
			pagination : '.one-pagination',
			observer : true,//修改swiper自己或子元素时，自动初始化swiper
			observeParents : true,//修改swiper的父元素时，自动初始化swiper
			autoplayDisableOnInteraction : false,
		});
	})
	
	$('body').on('click','.shopSearchBtn',function(){
		$('#shopSearch').submit();
	})


</script>
</html>