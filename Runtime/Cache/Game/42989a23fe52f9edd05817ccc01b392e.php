<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title>游戏列表</title>
<meta name="viewport"
      content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="keywords" content="手游交易、手游交易平台、手机游戏交易、手机游戏交易平台、手机游戏充值、手机游戏充值中心、手游账号交易、手游金币交易、手游狗交易平台、游戏代练、手游代练、手游代练平台">
<meta name="description" content="手游狗交易平台是中国手机游戏交易服务第一门户，是手机网游玩家购买出售买卖首充号、苹果代充、买卖游戏账号、游戏金币，进行游戏币交易、满级号交易、手机游戏代练、手游郊游、游戏装备道具交易的首选交易平台，手游玩家也可在此领取游戏礼包、买充值卡、手游退游等首选交易服务平台，手游狗_jiaoyi_最安全高效的手游交易平台！">

<link rel="stylesheet" href="/Tpl/Game/css/supermarket.css" />
<link rel="stylesheet" href="/Tpl/Game/css/orderDetail.css" />
<link rel="stylesheet" href="/Tpl/Game/css/base.css" />
<link rel="stylesheet" href="/Tpl/Game/css/goods-type.css" />
<style>
.back-ff.padd.nav.nav{
    text-align:left;
    height:auto;
}
</style>
<script src="/Tpl/Game/js/jquery2.1.1.min.js"></script>
<script src="/Tpl/Game/js/base.js"></script>

<!--
@author peng
@date 2016
@remark 
 -->
 <script src="/Tpl/Game/js/goods/goods-type.js"></script>
<style>

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
		<div class="_left_top" onclick='location.href="/Game/Index/index"'></div>
		<div class="_title_top">
			<?php $_scopeId=1; switch($_GET['type']){ case 'sc'; echo '首充号';$_scopeId=1;break; case 'dc'; echo '首充号代充';$_scopeId=2;break; } ?>
		</div>
		<div class="_right_top mess"
			onclick="location.href='/Game/Mess/mess/r/Goods_goodsType'"></div>
	</div>
	<div class="lh45"></div>
	<form action="<?php echo U('goodsType');?>" method="get" id="shopSearch">
		<div class="lh35  back-ff ub " style="padding: 10px 0px;">
			<div class="ub-f1 margin_left10">
				<input type="text" name="key" value="<?php echo ($_GET['key']); ?>"
					placeholder="请输入游戏名" />
			</div>
			<div class="margin_right10 search">搜索</div>
		</div>
		<input type="hidden" name="letter" id="letter"
			value="<?php echo ($_GET['letter']); ?>"> <input type="hidden" name="type"
			id="type" value="<?php echo ($_GET['type']); ?>">
	</form>
	<div class="lh45  back-ff">
		<div class="bor"
			style="width: 90%; margin: 0 auto; margin-top: 10px; height: 44px;">选择游戏</div>
	</div>
	<div class="back-ff padd nav">
		<a data-letter="Hot">热</a> <a data-letter="A">A</a> <a data-letter="B">B</a>
		<a data-letter="C">C</a> <a data-letter="D">D</a> <a data-letter="E">E</a>
		<a data-letter="F">F</a> <a data-letter="G">G</a> <a data-letter="H">H</a>
		<a data-letter="I">I</a> <a data-letter="J">J</a> <a data-letter="K">K</a>
		<a data-letter="L">L</a> <a data-letter="M">M</a> <a data-letter="N">N</a>
		<a data-letter="O">O</a> <a data-letter="P">P</a> <a data-letter="Q">Q</a>
		<a data-letter="R">R</a> <a data-letter="S">S</a> <a data-letter="T">T</a>
		<a data-letter="U">U</a> <a data-letter="V">V</a> <a data-letter="W">W</a>
		<a data-letter="X">X</a> <a data-letter="Y">Y</a> <a data-letter="Z">Z</a>
	</div>
	<!-- <div style="margin-top: 10px; text-align: left;" class="back-ff goodsList">
		<?php if(is_array($goodsInfo)): $i = 0; $__LIST__ = $goodsInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($_GET['type'] == 'sc'): ?><a href="<?php echo U('goodsList',array('gameId'=>$vo['id'],'o'=>$_scopeId));?>"	class="goods"> <?php else: ?><a href="<?php echo U('Validatadc/yanzhen',array('gameId'=>$vo['id'],'o'=>$_scopeId,'goodsType'=>'0'));?>" class="goods"><?php endif; ?>
		<div class="gameImg"><img src="/<?php echo ($vo["gameIco"]); ?>" /></div>
		<div class="gameName"><?php echo ($vo["gameName"]); ?></div></a><?php endforeach; endif; else: echo "" ;endif; ?>
	</div> -->
    <div style="margin-top: 10px; text-align: left;" class="back-ff goodsList">
	
	</div>
	
	<div class="ub lh45"></div>
	<div class=" lh45 txt-c back-ff loadMore"
		style="position: fixed; bottom: 0; left: 0; width: 100%; display: none; max-width: 800px;right:0px;margin: 0 auto">加载更多</div>
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
var letter="<?php echo ($_GET['letter']); ?>";
$(function(){
	if(isDefine(letter)){
		var a=$('.nav a')
		$.each(a,function(){
			if($(this).attr('data-letter')==letter){
				$(this).addClass('active');
			}
		})
	}
})
/**
 * @author peng
 * @copyright 2016
 * @remark 注释
 */
//$('body').on('click','.search',function(){
//	$('#shopSearch').submit();
//})
//$('body').on('click','.nav a',function(){
//    
//	var letter=$(this).attr('data-letter');
//	$('#letter').val(letter);
//	$('#shopSearch').submit();
//})

$(window).scroll(function(){
	// 当滚动到最底部以上100像素时， 加载新内容
	if ($(document).height() - $(this).scrollTop() - $(this).height()<100) {
		$('.loadMore').fadeIn();
	}else{
		$('.loadMore').fadeOut();
	}
});

$('body').on('click','.loadMore',function(){
	var scopeId=<?php echo ($_scopeId); ?>;
	var key="<?php echo ($_GET['key']); ?>";
	var letter="<?php echo ($_GET['letter']); ?>";
	var type="<?php echo ($_GET['type']); ?>"
	$.ajax({
		type:'POST',
		dataType:'json',
		data:{
			page:page,letter:letter,type:type,key:key
		},
		url:"<?php echo U('Goods/goodsType');?>",
		success:function(data){
			if(isDefine(data)){
					page++;
					var html='';
					for(var i=0;i<data.length;i++){
						html+='	<a href="/Game/Goods/goodsList/gameId/'+data[i].id+'/o/'+scopeId+'" class="goods">';
						html+='<div class="gameImg"><img src="/'+data[i].gameIco+'"/></div>';
						html+='<div class="gameName">'+data[i].gameName+'</div></a>';
					}
				$('.goodsList').append(html);
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