<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
    <title><?php echo ($gameName); ?></title>
    <meta name="viewport"
      content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="keywords" content="手游交易、手游交易平台、手机游戏交易、手机游戏交易平台、手机游戏充值、手机游戏充值中心、手游账号交易、手游金币交易、手游狗交易平台、游戏代练、手游代练、手游代练平台">
<meta name="description" content="手游狗交易平台是中国手机游戏交易服务第一门户，是手机网游玩家购买出售买卖首充号、苹果代充、买卖游戏账号、游戏金币，进行游戏币交易、满级号交易、手机游戏代练、手游郊游、游戏装备道具交易的首选交易平台，手游玩家也可在此领取游戏礼包、买充值卡、手游退游等首选交易服务平台，手游狗_jiaoyi_最安全高效的手游交易平台！">

    <link rel="stylesheet" href="/Tpl/Game/css/supermarket.css"/>
    <link rel="stylesheet" href="/Tpl/Game/css/orderDetail.css"/>
    <link rel="stylesheet" href="/Tpl/Game/css/base.css"/>
    <link rel="stylesheet" href="/Tpl/Game/css/index.css"/>
    <script src="/Tpl/Game/js/jquery2.1.1.min.js"></script>
    <script src="/Tpl/Game/js/base.js"></script>
    <style>
        body {
            background: #efeff4;
            /* 二次开发 添入*/
            max-width: 800px;
        }

        .toubu {
            width: 100%;
            height: 140px;
            background: url(/Tpl/Game/image/my_bg.png) center no-repeat;
            background-size: 100% 100%;
        }

        .shop {
            width: 95%;
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
            font-size: 10px;
            text-align: center;
            height: 18px;
            line-height: 18px;
            margin: 0 10px;
        }

        .moneys {
            font-size: 120%;
        }

        .type {
            color: #783500
        }

        .list {
            display: none;
        }

        .orderMenu {
            cursor: pointer;
        }
        /* 二次开发添加*/
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
    <div class="_title_top"><?php echo ($gameName); ?></div>
    <div class="_right_top mess"
         onclick="location.href='/Game/Mess/mess/r/Goods_goodsList'"></div>
</div>
<div class="lh45"></div>
<div class="ub orderNav" style="background: #ffffff;">
    <div class="ub-f1 pos-r br-r  orderMenu <?php if( $_GET[ 'o'] == 1): ?>orderNavActive<?php endif; ?>
    ">
    <div class="pos-a txt-c">首充号</div>
</div>
<div class="ub-f1 pos-r br-r orderMenu  <?php if( $_GET[ 'o'] == 2): ?>orderNavActive<?php endif; ?>
">
<div class="pos-a txt-c">首充号代充</div>
</div>
<!--<div class="ub-f1 pos-r br-r orderMenu  <?php if( $_GET[ 'o'] == 3): ?>orderNavActive<?php endif; ?>
">
<div class="pos-a txt-c">会员商品</div>
</div>-->
<!-- <div class="ub-f1 pos-r orderMenu  <?php if($_GET['o'] == 4): ?>orderNavActive<?php endif; ?>">
        <div class="pos-a txt-c">自主充值</div>
    </div> -->
</div>
<div class="list"
<?php if($_GET['o'] == 1): ?>style="display:block;"<?php endif; ?>
>
<?php if(is_array($shouChongInfo)): $i = 0; $__LIST__ = $shouChongInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
        <a href="<?php echo U('Goods/index',array('id'=>$vo['goodsId']));?>">
            <div class="shop ub ">
                <div class=" ub-f1 pos-r" style="margin-left: 5px; width: 60%;">
                    <div class="pos-a">
                        <div class="shopName">
                            <span class="type">【首充号】</span><?php echo ($vo["goodsName"]); ?>
                        </div>
                        <div class="fw col-9"><?php echo ($vo["goodsSpec"]); ?></div>
                    </div>
                </div>
                <?php if($vo['isMiao'] == 1): ?><div class="mc" style="width: 30px;">秒充</div><?php endif; ?>
                <div style="text-align: center;" class="pos-r">
                    <div class="col-9 lh20">
                        <s>￥<?php echo ($vo["shopPrice"]); ?></s>
                    </div>
                    <div class="col-red lh20 moneys">￥<?php echo ($vo["attrPrice"]); ?></div>
                </div>
            </div>
        </a>
    </div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<div class="list"
<?php if($_GET['o'] == 2): ?>style="display:block;"<?php endif; ?>
>
<?php if(is_array($daiChongInfo)): $i = 0; $__LIST__ = $daiChongInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
        <a href="<?php echo U('Validatadc/yanzhen',array('id'=>$vo['goodsId'],'goodsType'=>0));?>">
            <div class="shop ub ">
                <div class=" ub-f1 pos-r" style="margin-left: 5px; width: 60%;">
                    <div class="pos-a">
                        <div class="shopName">
                            <span class="type">【首充号代充】</span><?php echo ($vo["goodsName"]); ?>
                        </div>
                        <div class="fw col-9"><?php echo ($vo["goodsSpec"]); ?></div>
                    </div>
                </div>
                <?php if($vo['isMiao'] == 1): ?><div class="mc" style="width: 30px;">秒充</div><?php endif; ?>
                <div style="text-align: center;" class="pos-r">
                    <div class="col-9 lh20">
                        <s>￥<?php echo ($vo["shopPrice"]); ?></s>
                    </div>
                    <div class="col-red lh20 moneys">￥<?php echo ($vo["attrPrice"]); ?></div>
                </div>
            </div>
        </a>
    </div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<div class="list"
<?php if($_GET['o'] == 3): ?>style="display:block;"<?php endif; ?>
>
<?php if(is_array($fenxiaoChongInfo)): $i = 0; $__LIST__ = $fenxiaoChongInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="back-ff bor">
        <?php if($vo['scopeId'] == 1): ?><a
                href="<?php echo U('Goods/index',array('id'=>$vo['goodsId']));?>">
            <?php else: ?>
            <a href="<?php echo U('Validatadc/yanzhen',array('id'=>$vo['goodsId'],'goodsType'=>1));?>"><?php endif; ?>
        <div class="shop ub ">
            <div class=" ub-f1 pos-r" style="margin-left: 5px; width: 60%;">
                <div class="pos-a">
                    <div class="shopName">
							<span class="type">【<?php if($vo['scopeId'] == 1): ?>会员首充<?php else: ?>会员首代<?php endif; ?>】
							</span><?php echo ($vo["goodsName"]); ?>
                    </div>
                    <div class="fw col-9"><?php echo ($vo["goodsSpec"]); ?></div>
                </div>
            </div>
            <?php if($vo['isMiao'] == 1): ?><div class="mc" style="width: 30px;">秒充</div><?php endif; ?>
            <div style="text-align: center;" class="pos-r">
                <div class="col-9 lh20">
                    <s>￥<?php echo ($vo["shopPrice"]); ?></s>
                </div>
                <div class="col-red lh20 moneys">￥<?php echo ($vo["attrPrice"]); ?></div>
            </div>
        </div>
        </a>
    </div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>


<div class="ub lh45"></div>
<div class=" lh45 txt-c back-ff loadMore"
     style="position: fixed; cursor: pointer; bottom: 0; left: 0; display: none;right: 0px;margin: 0 auto;max-width: 800px;">加载更多
</div>
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
    var page = 1;
    var type = "<?php echo ($_GET['o']); ?>";
    var menuIndex = 0;
    $(function () {
        //$('.orderMenu').eq(0).addClass('orderNavActive');
        //$('.list').eq(0).show();
    })

    //全部订单，待发货，待付款，已完成切换
    $('body').on('click', '.orderMenu', function () {
        var index = $(this).index();
        menuIndex = index;
        type = $(this).text();
        $('.orderMenu').removeClass('orderNavActive');
        $(this).addClass('orderNavActive');
        $('.list').hide();
        $('.list').eq(index).show();
        $('.loadMore').show();
    })

    $(window).scroll(
            function () {
                // 当滚动到最底部以上100像素时， 加载新内容
                if ($(document).height() - $(this).scrollTop()
                        - $(this).height() < 100) {
                    $('.loadMore').fadeIn();
                } else {
                    $('.loadMore').fadeOut();
                }
            });

    $('body').on(
            'click',
            '.loadMore',
            function () {
                var name = "<?php echo ($_GET['gameId']); ?>";
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        page: page,
                        type: type,
                        gameId: name
                    },
                    url: "<?php echo U('Goods/goodsList');?>",
                    success: function (data) {
                        if (isDefine(data)) {
                            page++;
                            var html = '';
                            for (var i = 0; i < data.length; i++) {
                                html += '  <div class="back-ff bor">';
                                var url = '/Game/Goods/index/id/' + data[i].goodsId;
                                if(data[i].goodsType==0){
                                    var type = '首充号';
                                    if (data[i]['scopeId'] == 2) {
                                        var type = '首充号代充';
                                        if (menuIndex == 1) {
                                            var url = '/Game/Validatadc/yanzhen/id/' + data[i].goodsId + '/goodsType/0';
                                        } else {
                                            var url = '/Game/Validatadc/yanzhen/id/' + data[i].goodsId + '/goodsType/1';
                                        }
                                    }
                                }else{

                                    var type = '会员首充';
                                    if (data[i]['scopeId'] == 2) {
                                        var type = '会员首代';
                                        if (menuIndex == 1) {
                                            var url = '/Game/Validatadc/yanzhen/id/' + data[i].goodsId + '/goodsType/0';
                                        } else {
                                            var url = '/Game/Validatadc/yanzhen/id/' + data[i].goodsId + '/goodsType/1';
                                        }
                                    }

                                }

                                html += '	<a href="' + url + '">';
                                html += '	<div class="shop ub ">';
                                html += '<div class=" ub-f1 pos-r" style="margin-left:5px;width:60%;">';
                                html += '<div class="pos-a">';

                                html += '<div class="shopName"><span class="type">【' + type + '】</span>' + data[i].goodsName + '</div>';
                                var goodsSpec = '';
                                if (isDefine(data[i].goodsSpec)) {
                                    goodsSpec = data[i].goodsSpec;
                                }
                                html += '<div class="fw col-9">' + goodsSpec + '</div>	</div>	</div>';
                                if (data[i]['isMiao'] == 1) {
                                    html += '   <div class="mc" style="width:30px;">秒充</div>';
                                }
                                html += '	<div style="text-align:center;" class="pos-r">';
                                html += '<div class="col-9 lh20"><s>￥' + data[i].shopPrice + '</s></div>';
                                html += '<div class="col-red lh20 moneys">￥' + data[i].attrPrice + '</div>';
                                html += '</div></div></a></div>';
                                html += '';
                            }
                            $('.list').eq(menuIndex).append(html);
                        } else {
                            $('.loadMore').text('没有更多了');
                            setTimeout(function () {
                                $('.loadMore').text('加载更多');
                            }, 2000);
                        }
                    }
                })
            })
</script>
</html>