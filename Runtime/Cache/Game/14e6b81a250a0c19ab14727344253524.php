<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title>普通商品</title>
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
<script src="/Tpl/Game/js/base.js"></script>
<style>
body {
	background: #efeff4;
	max-width: 800px;
}

.toubu {
	width: 100%;
	height: 85px;
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
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	height: 20px;
	line-height: 20px;
}

.bor {
	border-bottom: #d5d7dc solid 1px;
}

.bor-r {
	border-right: #d5d7dc solid 1px;
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
    margin-left: 10px;
}

.money {
	font-size: 120%;
}

.type {
	color: #783500
}

.jiage {
	height: 45px;
	line-height: 45px;
	width: 50px;
	margin: 0 auto;
	text-align: center;
	display: block;
}

.arrows_d {
	background: url(/Tpl/Game/image/arrows_d.png) right center no-repeat;
	background-size: 8px;
}

.arrows_u {
	background: url(/Tpl/Game/image/arrows_u.png) right center no-repeat;
	background-size: 8px;
}

#cate {
	width: 80%;
	position: fixed;
	left: 0px;
	top: 0px;
	z-index: 9999999;
	text-align: center;
}

li {
	padding:10px;
	text-align: left;
	cursor:pointer;
}

.firstMenu {
	background: #eee
}

.firstMenu ul li {
	border-bottom: #d5d7dc solid 1px;
	height: 40px;
	line-height: 40px;
	color: #999999;
}

.right_transparent {
	position: fixed;
	right: 0;
	top: 0;
	width: 20%;
	background: rgba(0, 0, 0, .3)
}

.cate, all {
	display: block;
	cursor: pointer;
}

.second {
	display: none;
}

.jiage, .loadMore {
	cursor: pointer
}

.second ul{cursor:pointer;}
.second ul li {
	cursor: pointer;
}

.firstMenu ul li {
	cursor: pointer
}

#hideCate, #allGoods {
	cursor: pointer
}

.all {
	display: block;
}
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
		<div class="_title_top">普通商品</div>
		<div class="_right_top mess"
			onclick="location.href='/Game/Mess/mess/r/shop_goodsList'"></div>
	</div>
	<form id="form1" action="<?php echo U('Shop/goodsList');?>" method="get">
		<div class="lh45" style="height: 44px;"></div>
		<div class="toubu">
			<div class="shop ub">
				<div class="shopImg">
					<img src="/<?php echo ((isset($shopInfo["shopImg"]) && ($shopInfo["shopImg"] !== ""))?($shopInfo["shopImg"]):'/Tpl/Game/image/shopImg.png'); ?>" />
				</div>
				<div class=" ub-f1" style="margin-left: 5px;">
					<div class="shopName"><?php echo ($shopInfo["shopName"]); ?></div>
					<div class="fw">经营范围：<?php echo ($fanwei); ?></div>
					<div class="xl">销量：<?php echo ((isset($sales) && ($sales !== ""))?($sales):0); ?></div>
				</div>
			</div>
		</div>
		<div class="lh45 back-ff ub txt-c bor">
			<div class="ub-f1 bor-r">
				<span class="all">最新发布</span>
			</div>
			<div class="ub-f1 bor-r">
				<span class="jiage arrows_d <?php if($_GET['orderby'] == 'ASC' ): ?>arrows_u<?php endif; ?>
					">价格
				</span>
			</div>
			<div class="ub-f1">
				<span class="cate">商品分类</span>
			</div>
		</div>
		<div class="append">
			<?php if(is_array($goodsInfo)): $i = 0; $__LIST__ = $goodsInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sc): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
				<?php if($sc['scopeId'] == '2'): ?><a
					href="<?php echo U('Validatadc/yanzhen',array('id'=>$sc['goodsId'],'goodsType'=>0));?>">
					<?php else: ?> <a href="<?php echo U('Goods/index',array('id'=>$sc['goodsId']));?>"><?php endif; ?>
				<div class="shop ub ">
					<div class="shopImg">
						<img src="/<?php echo ((isset($sc["goodsThums"]) && ($sc["goodsThums"] !== ""))?($sc["goodsThums"]):'/Tpl/Game/image/shopImg.png'); ?>" />
					</div>
					<div class=" ub-f1" style="margin-left: 5px;">
						<div class="shopName">
							<span class="type">【<?php echo ($sc["type"]); ?>】</span><?php echo ($sc["goodsName"]); ?>
						</div>
						<div class="fw col-9"><?php echo ($sc["goodsSpec"]); ?></div>
						<?php if($sc['isMiao'] == 1): ?><div class="mc">秒充</div><?php endif; ?>
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
		</div>
		<div class="ub lh45 "></div>
		<input type="hidden" id="orderby" name="orderby"
			value="<?php echo ((isset($_GET['orderby']) && ($_GET['orderby'] !== ""))?($_GET['orderby']):'ASC'); ?>" /> <input type="hidden"
			id="scondCate" name="scondCate"
			value="<?php echo ((isset($_GET['scondCate']) && ($_GET['scondCate'] !== ""))?($_GET['scondCate']):'0'); ?>" /> <input type="hidden"
			id="firstCate" name="firstCate"
			value="<?php echo ((isset($_GET['firstCate']) && ($_GET['firstCate'] !== ""))?($_GET['firstCate']):'0'); ?>" /> <input type="hidden"
			id="id" name="id" value="<?php echo ((isset($_GET['id']) && ($_GET['id'] !== ""))?($_GET['id']):'0'); ?>" /> <input
			type="hidden" id="page" name="page" value="0" />
		<div id="cate" style="display: none;">
			<div class=" back-ff bor lh45 text-c">
				<div class="ub text-c">
					<div class="ub-f1 text-c" id="allGoods">全部商品</div>
					<div id="hideCate"
						style="width: 40px; background: #fff url(/Tpl/Game/image/left_arrows.png) center no-repeat; background-size: 9px;"></div>
				</div>
			</div>
			<div class="ub  back-ff">
				<div class="ub-f1  pos-r">
					<div class="firstMenu pos-a">
						<ul>
							<?php if(is_array($firstCate)): $i = 0; $__LIST__ = $firstCate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li data-cid="<?php echo ($key); ?>"<?php if($_GET['firstCate'] == $key): ?>style="color:#e50f12;background:#fff;"<?php endif; ?>
								><?php echo ($vo); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
					</div>
				</div>
				<div class="ub-f1 back-ff secondDiv pos-r" style="overflow: auto;">
					<div class=" pos-a">
						<?php if(is_array($secondCate)): $i = 0; $__LIST__ = $secondCate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sc): $mod = ($i % 2 );++$i;?><ul class="second" id="<?php echo ($key); ?>"
							<?php if($_GET['firstCate'] == $key): ?>style="display:block;"<?php endif; ?>
							>
							<?php if(is_array($sc)): $i = 0; $__LIST__ = $sc;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li data-cid="<?php echo ($vo["id"]); ?>"<?php if($_GET['scondCate'] == $vo['id']): ?>style="color:#e50f12"<?php endif; ?>><?php echo ($vo["PY"]); ?>
								<?php echo ($vo["gameName"]); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
                            
						</ul><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				</div>
			</div>
			<div class="right_transparent"></div>
		</div>
	</form>
	<div class=" lh45 txt-c back-ff loadMore"
		style="position: fixed; bottom: 0; left: 0; width: 100%; display: none;; cursor: pointer;margin: 0 auto;max-width: 800px;left: 0px;right: 0px">加载更多</div>
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
var page=1;
$(function(){
	var h=$(document).height();
	$('#cate').css('min-height',h+'px');
	$('.secondDiv').css('min-height',$(window).height()-50+'px');
	$('.right_transparent').css('min-height',h+'px');
})

$('body').on('click','#allGoods',function(){
	$('#firstCate').val(0);
	$('#scondCate').val(0);
	$('#form1').submit();
})

$('body').on('click','.jiage',function(){
	if($(this).hasClass('arrows_d')&&!$(this).hasClass('arrows_u')){
		$('.jiage').addClass('arrows_u');
		$('#orderby').val('ASC');
	}else if($(this).hasClass('arrows_u')){
		$('.jiage').removeClass('arrows_u');
		$('#orderby').val('DESC');
	}
	$('#form1').submit();
})

$('body').on('click','.cate',function(){
	$('#cate').show();
})
$('body').on('click','#hideCate',function(){
	$('#cate').hide();
})
$('body').on('click','.right_transparent',function(){
	$('#cate').hide();
})

$('body').on('click','.firstMenu ul li',function(){
	var index=$(this).index();
	var cid=$(this).attr('data-cid');
	$('#firstCate').val(cid);
	$(this).css('background','#fff');
	$(this).siblings().css('background','#eeeeee');
	$('.second').hide();
	$('.second').eq(index).show();
	$(this).css('color','#e50f12');
	$(this).siblings().css('color','#999999');
})
$('body').on('click','.second  li',function(){
		var cid=$(this).attr('data-cid');
		$('#scondCate').val(cid);
		$(this).css('color','#e50f12');
		$(this).siblings().css('color','#333333');
		$('#form1').submit();
})

$('body').on('click','.all',function(){
	$('#orderby').val('NEW');
	$('#form1').submit();
})

$(window).scroll(function(){
	// 当滚动到最底部以上100像素时， 加载新内容
	if ($(document).height() - $(this).scrollTop() - $(this).height()<100) {
		$('.loadMore').fadeIn();
	}else{
		$('.loadMore').fadeOut();
	}
});

$('body').on('click','.loadMore',function(){
	var order=$('#orderby').val();
	var scondCate=$('#scondCate').val();
	var firstCate=$('#firstCate').val();
	var id=$('#id').val();
	var pg=page;
	$.ajax({
		type:'POST',
		dataType:'json',
		data:{order:order,scondCate:scondCate,firstCate:firstCate,id:id,page:pg},
		url:"<?php echo U('Shop/goodsList');?>",
		success:function(data){
			if(isDefine(data)){
					page++;
					var html='';
					for(var i=0;i<data.length;i++){
						html+='<div class="back-ff bor">';
						if(data[i].scopeId==2){
							html+='	<a href="/Game/Validatadc/yanzhen/id/'+data[i].goodsId+'/goodsType/0">';
						}else{
							html+='	<a href="/Game/Goods/index/id/'+data[i].goodsId+'">';
						}
						html+='<div class="shop ub ">';
						html+='<div class="shopImg"><img src="/'+data[i].goodsThums+'"/></div>';
						html+='<div class=" ub-f1" style="margin-left:5px;">';
						html+='<div class="shopName"><span class="type">【'+data[i].type+'】</span>'+data[i].goodsName+'</div>';
						var goodsSpec='';
						if(isDefine(data[i].goodsSpec)){
							goodsSpec=data[i].goodsSpec;
						}
						html+='<div class="fw col-9">'+goodsSpec+'</div>';
						
						if(data[i]['isMiao']==1){
							html+='	<div class="mc">秒充</div>';
						}
						html+='	</div>	<div style="text-align:center;">';
						html+='<div class="col-9 lh20"><s>￥'+data[i].shopPrice+'</s></div>';
						html+='<div class="col-red lh20 money">￥'+data[i].attrPrice+'</div>';
						html+='</div></div></a></div>';
					}
				$('.append').append(html);
			}else{
				$('.loadMore').text('没有更多了');
				setTimeout(function(){
					$('.loadMore').text('加载更多');
				},2000);
			}
		}
	})
})

</script>
</html>