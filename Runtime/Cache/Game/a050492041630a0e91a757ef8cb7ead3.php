<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title>登录</title>
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
<script src="/Tpl/Game/js/layer.min.js"></script>
<style>
body {
	background: #fff;
	max-width: 800px;
}

.trsp {
	background-color: transparent;
	width: 90%;
	height: 100%;
	text-indent: 20px;
	margin: 0 auto;
	border: none;
	height: 35px;
	line-height: 35px;
	border-bottom: #d5d7dc solid 1px;
	background: url(/Tpl/Game/image/ico_r.png) 5px center no-repeat;
	background-size: 12px;
}

.password_ico {
	background: url(/Tpl/Game/image/ico_s.png) 5px center no-repeat;
	background-size: 12px;
}

.login_btn {
	display: block;
	width: 80%;
	height: 40px;
	line-height: 40px;
	margin: 0 auto;
	background: #00b4ff;
	border-radius: 4px;
	text-align: center;
	color: #fff;
}

.yi {
	background: url(/Tpl/Game/image/yi.png) center repeat-x;
}

.findpwd a {
	color: #00b4ff;
	font-size: 12px;
}

.qqlogin {
	height: 30px;
	padding-top: 40px;
	background: url(/Tpl/Game/image/qq_login.png) center 10px no-repeat;
	background-size: 25px;
}

.wxlogin {
	height: 30px;
	padding-top: 40px;
	background: url(/Tpl/Game/image/wx_login.png) center 10px no-repeat;
	background-size: 25px;
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
	<div class="_top">
		<div class="_left_top" onclick="location.href='<?php echo U('Index/index');?>' "></div>
		<div class="_title_top">登录</div>
		<div class="_right_top no-bg">
			<a href="<?php echo U('Register/register');?>">注册</a>
		</div>
	</div>
    <form id="loginForm">
    	<div class="ub lh45 mr-t45">
    		<div class="ub-f1 txt-c">
    			<input class="trsp no-bg" type="text" name="userName" id="userName"
    				value="" placeholder="手机号码" />
    		</div>
    	</div>
    	<div class="ub lh45 ">
    		<div class="ub-f1 txt-c">
    			<input class="trsp password_ico" type="password" id="passWord"
    				name="passWord" value="" placeholder="请输入密码" />
    		</div>
    	</div>
    	<div class="ub lh25 col-9 txt-r "></div>
    
    	<!--<div class="ub lh35">
    		<div class="ub-f1" onclick="login()">
    			<span class="login_btn"> 登录 </span>
    		</div>
    	</div>-->
        <div class="ub lh35">
    		<div class="ub-f1" onclick="login()">
    			<button class="login_btn" style="border: none;"> 登录 </button>
    		</div>
    	</div>
    </form>
	<div class="ub lh25 col-9 txt-r "></div>
	<div class="ub lh45 col-9 txt-c ">
		<div class="ub-f1 txt-c mr-r10 findpwd" style="color:">
			<a href="<?php echo U('Login/resetLoginPwd');?>">忘记密码？</a>
		</div>
	</div>

	<div class="col-9 ub"
		style="width: 60%; margin: 0 auto; text-align: center;">
		<div class="ub-f1 yi"></div>
		<div style="font-size: 12px; margin: 0 10px;">第三方登录</div>
		<div class="ub-f1 yi"></div>
	</div>

	<div class="col-9 ub"
		style="width: 60%; margin: 0 auto; text-align: center; margin-top: 10px;">
		<div style="cursor:pointer;" class="qqlogin ub-f1" onclick="location.href='<?php echo U('Game/Login/qqLogin');?>'"><a href="<?php echo U('Login/qqLogin');?>">QQ帐号</a></div>
		<div style="cursor:pointer;" class="wxlogin ub-f1" onclick="location.href='<?php echo U('Game/Login/wxLogin');?>'"><a href="<?php echo U('Login/wxLogin');?>">微信帐号</a></div>
	</div>
	<?php if(stristr($_SERVER['HTTP_REFERER'],'login')||empty($_SERVER['HTTP_REFERER'])||stristr($_SERVER['HTTP_REFERER'],'register')){ $_ref=_getCookie('ref')?:U('Index/index',array('r'=>'my')); }else{ $_ref=$_SERVER['HTTP_REFERER']; _setcookie('ref', $_ref, 3600 * 24); } ?>
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
 * @author peng	
 * @date 2016-12
 * @descreption 改成表单形式让uc可以提示保存密码
 */
$(function(){
	$('#loginForm').submit(function(){
	   return false;
	})
})
    function login(){
        var url="<?php echo U('Login/loginHandle');?>";
        var phone=$.trim($('#userName').val());
        var pwd=$.trim($('#passWord').val());
        if(!phone||!pwd){
        	layer.msg('请输入用户名或者密码');
        	return;
        }
        $.ajax({
            type: "POST",
            url: url,
            data: {
                phone:phone,
                pwd:pwd
            },
            dataType: "json",
            success: function(data){
                try{
                    if(data.status==0){
                        layer.msg('登录成功！');
                        setTimeout(function(){
                        	var ref="<?php echo ($_ref); ?>";
                        	window.location.href=ref;
                        },2000)
                    }else if(data.status==-1){
                    	   layer.msg('用户名或密码错误！');
                    }else if(data.status==-2){
                    	   layer.msg('用户不存在');
                    }
                }catch(err){

                }
            }
        });
        

    }



</script>
</html>