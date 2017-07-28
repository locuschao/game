<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
<title>商品详情</title>
<meta name="viewport"
      content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="keywords" content="手游交易、手游交易平台、手机游戏交易、手机游戏交易平台、手机游戏充值、手机游戏充值中心、手游账号交易、手游金币交易、手游狗交易平台、游戏代练、手游代练、手游代练平台">
<meta name="description" content="手游狗交易平台是中国手机游戏交易服务第一门户，是手机网游玩家购买出售买卖首充号、苹果代充、买卖游戏账号、游戏金币，进行游戏币交易、满级号交易、手机游戏代练、手游郊游、游戏装备道具交易的首选交易平台，手游玩家也可在此领取游戏礼包、买充值卡、手游退游等首选交易服务平台，手游狗_jiaoyi_最安全高效的手游交易平台！">

<link rel="stylesheet" href="/Tpl/Game/css/supermarket.css" />
<link rel="stylesheet" href="/Tpl/Game/css/orderDetail.css" />
<link rel="stylesheet" href="/Tpl/Game/css/base.css" />
<link rel="stylesheet" href="/Tpl/Game/css/goods/shouchong_buy.css" />
<script src="/Tpl/Game/js/jquery2.1.1.min.js"></script>
<script src="/Tpl/Game/js/base.js"></script>
<script src="/Tpl/Game/js/layer.min.js"></script>
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
		<div class="_left_top" onclick='history.go(-1)'></div>
		<div class="_title_top">商品详情</div>
		<div class="_right_top mess"
			onclick="location.href='/Game/Mess/mess/r/Goods_index'"></div>
	</div>
	<div class="lh45" style="height: 44px;"></div>
	<div class="padd back-ff">
		<span class="col-red">【<?php echo ($goodsInfo["catName"]); ?>】</span><?php echo ($goodsInfo["goodsName"]); ?>
	</div>
	<div class="col-9 padd-b ft9 bor back-ff"><?php echo ($goodsInfo["goodsSpec"]); ?></div>
	<?php if($goodsInfo['catName'] == '首充号'): ?><div class="shouchong">首充号说明：平台为你注册且完成首次充值的帐号，只需要官网原价的3-5折即可购买。</div><?php endif; ?>
	<div class="lh45 back-ff txt-l ti10 bor">请选择游戏版本</div>
	<div class="back-ff"
		style="clear: both; background: #fff; width: 100%;">
		<?php if(empty($goodsAttr)): ?><div class="list" >
			<div class="ub list1">
				<div class="shopImg">
					<img src="/<?php echo ((isset($goodsInfo["goodsThums"]) && ($goodsInfo["goodsThums"] !== ""))?($goodsInfo["goodsThums"]):'/Tpl/Game/image/shopImg.png'); ?>" />
				</div>
				<div class="ms ub-f1">
					<div class="title"><?php echo ($goodsInfo["goodsName"]); ?></div>
					<?php $_zhe=sprintf('%.1f',($goodsInfo['shopPrice']/$goodsInfo['marketPrice'])*10); ?>
					<div class="col-9 ft9"><?php echo ($_zhe); ?>折</div>
                    <?php if($goodsInfo['isMiao']): ?><div class="mc">秒充</div><?php endif; ?>
				</div>
				<div class="select" data-attrid="0" data-money='<?php echo ($goodsInfo['shopPrice']); ?>' 
                data-price1='<?php echo ($vo['lmprice']); ?>' data-price2='<?php echo ($vo['mmprice']); ?>' data-price3='<?php echo ($vo['lowest_price']); ?>' data-zhekou="<?php echo ($_zhe); ?>"
                ></div>
			</div>
		</div>
		<?php else: ?> <?php if(is_array($goodsAttr)): $i = 0; $__LIST__ = $goodsAttr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list">
			<div class="ub list1">
				<div class="shopImg">
					<img src="/<?php echo ((isset($goodsInfo["goodsThums"]) && ($goodsInfo["goodsThums"] !== ""))?($goodsInfo["goodsThums"]):'/Tpl/Game/image/shopImg.png'); ?>" />
				</div>
                
				<div class="ms ub-f1">
					<div class="title"><?php echo ($vo["vName"]); ?></div>
					<?php $_zhe=sprintf('%.1f',($vo['attrPrice']/$goodsInfo['marketPrice'])*10); ?>
					<div class="col-9 ft9"><?php echo ($_zhe); ?>折</div>
                    <!--<div class="dc-discount">后续代充<?=round($dc_price_assoc[$vo['versionsId']]/$goodsInfo['marketPrice'],2)*10;?>折</div>-->
                    <div class="dc-discount">续充<?=$dc_price_assoc[$vo['versionsId']]*10?>折</div>
					
                    <!--<?php if($vo["vName"] == '手游狗版本' and $goodsInfo['isMiao']): ?><div class="mc" >秒充</div><?php endif; ?>-->
                   
					<?php  if(in_array($vo['versionsId'],[41,42]) && $goodsInfo['isMiao']) : ?>
                    <div class="mc" >秒充</div>
                    <?php else : ?>
                    <div class="mc" style="visibility:hidden;">秒充</div>
                    <?php endif; ?>
				</div>
				<div class="select" data-attrid="<?php echo ($vo["id"]); ?>" data-money='<?php echo ($vo['attrPrice']); ?>'  data-versionid="<?php echo ($vo['versionsId']); ?>"
                data-price1='<?php echo ($vo['lmprice']); ?>' data-price2='<?php echo ($vo['mmprice']); ?>' data-price3='<?php echo ($vo['lowest_price']); ?>' data-zhekou="<?php echo ($_zhe); ?>"
                ></div>
			</div>
		</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
	</div>
   
	<div style="width: 100%; clear: both;; height: 8px;"></div>
	<div class="ub lh20">
		<div class="ub-f1 ti10" id="priceBlock">
			<span class="col-9">价格：</span>
            <span class="da" id="attrPrice"></span>元
            
		</div>
		<div class="ub-f1 ">
			<span class="col-9">所属游戏：</span><?php echo ($gameName); ?>
		</div>
	</div>
    <div class="ub lh20 member-price-block">
            <div class="price-tip">

            </div>
            <?php if($rank!=1) : ?>           
            <a class="btn joinBtn" href="/game/goodsRank">
            <?php if($rank==0) echo '[成为会员]'; else echo '[会员升级]'; ?>
            </a>
            <?php endif;?>
    </div>
    
  
	<div class="ub lh20">
		<div class="ub-f1  ti10">
			<span class="col-9">充值金额：</span><span class="da"><?php echo ($goodsInfo["marketPrice"]); ?></span>元
		</div>
		<div class="ub-f1 ">
			<span class="col-9">商品类型：</span><?php echo ($goodsInfo["catName"]); ?>
		</div>
	</div>
	<div class="ub lh20">
		<div class="ub-f1 ti10 ">
			<span class="col-9">适用系统：</span><?php echo ($goodsInfo["applyTo"]); ?>
		</div>
		<div class="ub-f1 ti10 ">
		<a href="<?php echo ($downLoadUrl); ?>" target="_brank">
			<span class="col-9" style="padding:5px 10px; color:#619be4">游戏下载</span></a>
		</div>
	</div>
	<div class="lh35 back-ff ub">
		<div class="ti10 col-9">分享到：</div>
		<div class="ub-f1 pos-r">
			<!-- JiaThis Button BEGIN -->
			<div class="jiathis_style_m"></div>
			<script type="text/javascript"
				src="http://v3.jiathis.com/code/jiathis_m.js" charset="utf-8"></script>
			<!-- JiaThis Button END -->
		</div>
	</div>
	<div class="lh30 back-ff ub bor" style="display: none;">
		<div class="col-9 ti10 ub-f1 pos-r">
			<div class="pos-a">购买数量:</div>
		</div>
		<div class="ub-f1 pos-r">
			<div class="pos-a ub txt-c">
				<div class="ub-f1 pos-r">
					<div class="pos-a jian"></div>
				</div>
				<div class="ub-f1 pos-r">
					<form action="<?php echo U('Orders/buy');?>" method="post" id="buy">
						<div class="pos-a">
							<input onchange="check()"
								onkeyup="this.value=this.value.replace(/\D/g,'')"
								onafterpaste="this.value=this.value.replace(/\D/g,'')"
								type="text" id="num" name="num" value="1" />
						</div>
						<input type="hidden" name="goodsId" value="<?php echo ($goodsInfo["goodsId"]); ?>" />
						<input type="hidden" id="attrid" name="attrid" value="0" />
                        <!--
                         * @author peng
                         * @date 2017-01
                         * @descreption 
                        -->
                        <input autocomplete="off" type="hidden" id="vouchers" name="vouchers" value="" />
					</form>
				</div>
				<div class="ub-f1 pos-r">
					<div class="pos-a jia"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="ub  lh35 back-ff  " style="text-align: right; width: 100%;">
		<div
			style="text-align: right; width: 95%; margin-right: 10px; color: #999">
			总计：<span id="totalMoney"><?php echo ($goodsInfo["shopPrice"]); ?></span>元
		</div>
	</div>
    <!--
    @author peng
    @date 2017-01-12
    @descreption 代金券
    -->
     <!--代金券 -->
    
    <div class="voucher-block">
    </div>
    <!--代金券 -->
    
     <!--
            @author peng
            @date 2017-01-03
            @descreption 平台商品不显示
            -->
     <?php if($goodsInfo['shopId']!=0):?>
	<a href="<?php echo U('Shop/personShop',array('id'=>$goodsInfo['shopId']));?>">
		<div class="ub "
			style="width: 90%; margin: 10px auto; background: #fff; border-radius: 5px; padding: 5px;">
			<div class="shopImg">
				<img src="/<?php echo ((isset($goodsInfo["shopImg"]) && ($goodsInfo["shopImg"] !== ""))?($goodsInfo["shopImg"]):'/Tpl/Game/image/shopImg.png'); ?>" />
			</div>
           
            
			<div class="ub-f1 shopContent">
				<div class='shopName'><?php echo ($goodsInfo["shopName"]); ?></div>
				<div class="fw">经营范围：<?php echo ($fanwei); ?></div>
				<div class="sl">销量：
                <?php if($goodsInfo['shopId']==18):?>
                <?php echo ($sales+30000); ?>
                <?php else: ?>
                <?php echo ((isset($sales) && ($sales !== ""))?($sales):0); ?>
                <?php endif;?>
                </div>
			</div>
            
			<div class="right_arrows arrowsMy"></div>
		</div>
	</a>
    <?php endif; ?>
	<div class="lh45 back-ff"   style="padding: 10px;<?php if($goodsInfo['shopId']==0):?>margin-top:10px; <?php endif; ?>">
		<div class="btn" onclick="subMitForm()"><?php if($goodsInfo['shopId']==0) echo '会员购买';else echo '立即购买'?></div>
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
<input type="hidden" id="unitPrice"
	value="<?php echo ((isset($goodsInfo["shopPrice"]) && ($goodsInfo["shopPrice"] !== ""))?($goodsInfo["shopPrice"]):0); ?>" />
<input type="hidden" id="attr" value="" />

<script>
//add by peng
var rank='<?=$rank?>';
var gameid='<?php echo ($goodsInfo["gameId"]); ?>';
var marketPrice='<?php echo ($goodsInfo["marketPrice"]); ?>';
    function subMitForm() {

		var goodsId = "<?php echo ($_GET['id']); ?>";
		var attr = $('#attr').val();
		if (!isDefine(attr)) {
			layer.msg('请选择游戏版本');
			return;
		}
		$('#attrid').val(attr);
		var num = parseInt($('#num').val());
		num = 1;
		if (num > 0) {
			//检查库存及营业时间
			$.ajax({
				type : 'post',
				url : "<?php echo U('Goods/stock');?>",
				dataType : 'json',
				data : {
					attrid : attr,
					goodsId : goodsId
				},
				success : function(data) {
					/*
						if(isDefine(data.shopId))
					{
						location.href='/Game/Shop/personShop/id/'+data.shopId;
						return;
					}
					
					if(data.num<num){
						layer.msg('库存只剩'+data.num+'份');
						$('#num').val(data.num);
						return;
					} */
					if (data.status == 0) {
                        //add by peng
                        controller.submitVoucher()
                        
						$('#buy').submit();
					} else {
						layer.msg(data.msg);
						return;
					}

				}
			})

		}

	}
    
    
	$(function() {
	   //会员等级
      
		$('body')
				.on(
						'click',
						'.select',
						function() {
							var attrid = $(this).attr('data-attrid');
							var money = $(this).attr('data-money');
                            //add by peng
                            var lmprice=$(this).data('price1');
                            var mmprice=$(this).data('price2');
                            var lowest_price=$(this).data('price3');
                            model.attrid=$(this).data('versionid');
                            model.zhekou=$(this).data('zhekou');
							$('#attr').val(attrid);
							$('#unitPrice').val(money);
							$('.select')
									.css(
											{
												'background' : 'url(/Tpl/Game/image/select_f.png) top center no-repeat',
												'background-size' : '18px'
											});
							$(this)
									.css(
											{
												'background' : 'url(/Tpl/Game/image/select.png) top center no-repeat',
												'background-size' : '18px'
											})
							check(lmprice,mmprice,lowest_price);
                           
                    
                            
						})
		$('.jtico_renren').remove();
		$('.jtico_jiathis').remove();
        
        //add by peng 默认是选择第一个
        
        $('.select:first').trigger('click');
        
	})

	$('body').on('click', '.jia', function() {
		var num = parseInt($('#num').val());
		if (isNaN(num)) {
			$('#num').val(1);
			num = 1;
		}
		$('#num').val(num + 1);
		check();
	})
	$('body').on('click', '.jian', function() {
		var num = parseInt($('#num').val());
		if (isNaN(num)) {
			$('#num').val(1);
			num = 1;
		}
		var newNum = num - 1;
		if (newNum <= 0) {
			newNum = 1;
		}
		$('#num').val(newNum);
		check();
	})

	function check(lmprice,mmprice,lowest_price) {
		var num = $('#num').val();
		if (isNaN(num)) {
			$('#num').val(1);
			num = 1;
		}
		var attr = $('#attr').val();
        var price_tip = $('.price-tip');
        var unitPrice;
        var rank_arr = [
            '非',
            '钻石',
            '白金',
            '黄金'
        ];
        var member_zhekou;
        //add by peng
        if(lowest_price>0){
            price_tip.show()
            if(rank==1) {
            price_tip.html('(已是最优惠会员价)');
                unitPrice=lowest_price;
            }else if(rank==2){
                price_tip.html('(会员价最低至'+lowest_price+'元)');
                unitPrice=mmprice;
            }else if(rank==3){
                price_tip.html('(会员价最低至'+lowest_price+'元)');
                unitPrice=lmprice;
            }else if(rank==0){
                 price_tip.html('(会员价最低至'+lowest_price+'元)');
                 unitPrice = $('#unitPrice').val();
            }
            member_zhekou = (unitPrice/marketPrice)*10
            $('#priceBlock').append('<span class="member-zhekou">('+rank_arr[rank]+'VIP'+member_zhekou.toFixed(1)+'折)</span>');
        }else{
            unitPrice = $('#unitPrice').val();
            if(rank==1) $('.member-price-block').hide();
            price_tip.hide();
            $('.member-zhekou').remove();
        }
        
		if (isDefine(attr) && attr >= 0 && unitPrice > 0) {
			$('#attrPrice').text(unitPrice);
			$('#totalMoney').text(toDecimal(num * unitPrice));
		}
        /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
        controller.getVoucherList(model)
	}

	function toDecimal(x) {
		var val = Number(x)
		if (!isNaN(parseFloat(val))) {
			val = val.toFixed(2);
		}
		return val;
	}
    
</script>
<script src="/Tpl/Game/js/voucher.js?20170308"></script>
</html>