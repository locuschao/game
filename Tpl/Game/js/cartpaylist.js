//修改购物车中的商品数量
function changeCatGoodsnum(flag, shopId, goodsId, priceAttrId, isBook,isSeckill,isGround) {
	isBook = 0;
	var num = parseInt($("#buy-num_" + goodsId + "_" + priceAttrId+'_'+isSeckill+'_'+isGround).val(), 10);
	if (num < 0) {
		num = Math.abs(num);
		$("#buy-num_" + goodsId + "_" + priceAttrId+'_'+isSeckill+'_'+isGround).val(num);
	}
	if (flag == 1) {
		if (num > 1)
			num = num - 1;
	} else if (flag == 2) {
		num = num + 1;
	}
	if (num < 1) {
		num = 1;
		$("#buy-num_" + goodsId + "_" + priceAttrId+'_'+isSeckill+'_'+isGround).val(1);
	}
	if ($("#chk_goods_" + goodsId + "_" + priceAttrId+'_'+isSeckill+'_'+isGround).is(":checked")) {
		checkCartPay(shopId, goodsId, num, 1, isBook, priceAttrId,isSeckill,isGround);
	} else {
		checkCartPay(shopId, goodsId, num, 0, isBook, priceAttrId,isSeckill,isGround);
	}
}

function checkCartPay(shopId, goodsId, num, ischk, isBook, goodsAttrId, isSeckill,isGround) {
    var url = "/wx/index/getGoodsStock";
    var changeCarturl = "/wx/index/changeCartGoodsNum";
    jQuery.post(url, {
            goodsId: goodsId,
            isBook: isBook,
            goodsAttrId: goodsAttrId,
            isSeckill:isSeckill,
            isGround:isGround
        },
        function(data) {
            var json = JSON.parse(data);
            if(!isNaN(json.seckillMaxCount)&&num>json.seckillMaxCount){
            	layer.tips("限购"+json.seckillMaxCount+'份', "#" + 'buy-num_'+goodsId+'_'+goodsAttrId+'_'+isSeckill+'_'+isGround);
            	return;
            }
            if (json.goodsStock == 0) {
            	layer.tips("无货", "#" + 'buy-num_'+goodsId+'_'+goodsAttrId+'_'+isSeckill+'_'+isGround);
            }
            num = parseInt(num, 10);
            if (json.goodsStock >= num) {
                // 库存充足提醒
            } else  {
                num = json.goodsStock;
                layer.tips("库存仅剩"+num+'份', "#" + 'buy-num_'+goodsId+'_'+goodsAttrId+'_'+isSeckill+'_'+isGround);
                if(num>0){
                	  $("#" + 'stock_'+goodsId+'_'+goodsAttrId+'_'+isSeckill+'_'+isGround).val(1);
                      $("#" + 'color_'+goodsId+'_'+goodsAttrId+'_'+isSeckill+'_'+isGround).css('background','#fff');
                }
            }
            jQuery.post(changeCarturl, {
                    goodsId: goodsId,
                    num: num,
                    ischk: ischk,
                    goodsAttrId: goodsAttrId,
                    isSeckill:isSeckill,
                    isGround: isGround
                },
                function(rsp) {
                    if (rsp) {
                        var totalMoney = 0;
                        $("#buy-num_" + goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).val(num);
                        var price = parseFloat($("#price_" + goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).val(), 10);
                        $("#prc_" + goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).html((num * price).toFixed(1));
                        /* var myMoney=num * price;
                       var startMoney=$('#deliveryStarMoney_'+ goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).val();
                       var needMoney=startMoney-myMoney;
                       if(myMoney>=startMoney){
                    	   $("#color_"+ goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).css('background','#fff');
                       }else{
                    	   $("#color_"+ goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround).css('background','#4CD964');
                    	   layer.tips('还差'+needMoney+'元起送',"#buy-num_" + goodsId + "_" + goodsAttrId+'_'+isSeckill+'_'+isGround);
                       }*/
                        // 店铺下的商品
                        var shopTotalMoney = 0;
                        $("input[name='chk_goods_" + shopId + "']").each(function() {
                            if ($(this).is(":checked")) {
                                var goodsAttrId = $(this).attr('dataid');
                                var gid = $(this).val();
                                var gnum = $("#buy-num_" + gid + goodsAttrId+'_'+isSeckill+'_'+isGround).val();
                                var gprice = parseFloat($("#price_" + gid + goodsAttrId+'_'+isSeckill+'_'+isGround).val(), 10);
                                shopTotalMoney += gnum * gprice;
                            }
                        });
                        $("#shop_totalMoney_" + shopId).html(shopTotalMoney.toFixed(1));
                        // 所有商品
                        $(".cgoodsId").each(
                            function() {
                                var goodsAttrId = $(this).attr('dataId');
                                var gid = $(this).val();
                                if ($("#chk_goods_" + gid).is(":checked")) {
                                    var price = parseFloat(
                                        $("#price_" + gid).val(), 10);
                                    var cnt = parseInt(
                                        $("#buy-num_" + gid).val(), 10);
                                    totalMoney += price * cnt;
                                }
                            });

                        $("#wst_cart_totalmoney").html(totalMoney.toFixed(1));
                    }
                });
        });
}



function toContinue() {
	location.href = domainURL;
}

// 去结算->检查选中的商品的库存
function goToPay() {
	var url = "/wx/index/checkSelectGoodsStock";
	var orderurl = "/wx/Confirm/conFirmOrder";
	var flag = true;
	var cflag = true;
	var stockFlag=true;
	var star=true;
	var goodsId_num_attrId='';//商品ID_数量_属性ID
	var goodsId_attrId='';//商品ID_属性ID
	$("input[id^='buy-num_']").each(function() {
		if ($(this).val() < 1) {
			layer.tips("购买数量不能小于1", "#" + $(this).attr("id"));
			cflag = false;
		}
	});
	if (!cflag) {
		return false;
	}
	
	
	
	//判断所有商品
	$(".goodsStockFlag").each(function() {
		var id=$(this).attr('data-id');
		if($('#chk_goods_'+id).prop("checked")){
			if ($(this).val() == -1) {
				layer.msg('部分商品库存不足');
				stockFlag=flase;
				return;
			}
		}
	});
	
	//判断起送价
/*	$('.deliveryFlag').each(function(){
		var id=$(this).attr('data-id');
		var val=$(this).val();
		var money=$('#prc_'+id).text();
		if(val>money){
			star=false;
			$("#color_"+id).css('background','#4CD964');
		}
	})
	if(!star){
		layer.msg('商品未达店铺起送价');
		return;
	}*/
	//判断团购商品状态
	$(".groupFlag").each(function() {
		var id=$(this).attr('data-id');
		if($('#chk_goods_'+id).prop("checked")){
			if ($(this).val() == -1) {
				layer.msg('部分团购活动已结束');
				stockFlag=flase;
				return;
			}
		}
	});
	//判断秒杀商品状态
	$(".secKillFlag").each(function() {
		var id=$(this).attr('data-id');
		if($('#chk_goods_'+id).prop("checked")){
			if ($(this).val() == -1) {
				layer.msg('部分秒杀活动已结束');
				stockFlag=flase;
				return;
			}
		}
	});
	
	if(!stockFlag){
		return;
	}
	$('.chkall').each(function(){
		if ($(this).is(":checked")) {
			var goodsid=$(this).attr('data-gid');
			var attrid=$(this).attr('dataid');
			var isground=$(this).attr('data-isground');
			var isskill=$(this).attr('data-isSeckill');
			var goodsNum=$('#buy-num_'+goodsid+'_'+attrid+'_'+isskill+'_'+isground).val();
			goodsId_num_attrId+=goodsid+'_'+goodsNum+'_'+attrid+'_'+isskill+'_'+isground+',';
			goodsId_attrId+=goodsid+'_'+attrid+'_'+isskill+'_'+isground+',';
		}	
	})
	goodsId_num_attrId=goodsId_num_attrId.substring(0,goodsId_num_attrId.length-1);
	if(!goodsId_num_attrId){
		layer.msg('购物车还没有宝贝哦');
		//tip('购物车还没有宝贝哦',0,2,0);
		return;
	}
	goodsId_attrId=goodsId_attrId.substring(0,goodsId_attrId.length-1);
	jQuery.post(url, {goodsId_num_attrId:goodsId_num_attrId}, function(data) {
		var goodsInfo = JSON.parse(data);
		for (var i = 0; i < goodsInfo.length; i++) {
			var goods = goodsInfo[i];
			if (goods.cnt < 1) {
				cflag = false;
				layer.tips("购买数量不能小于1", "#buy-num_"+goods.goodsId+'_' + goods.attrId+'_'+goods.isSeckill+'_'+goods.isGroup);
			}
			if (goods.stockStatus < 1) {
				flag = false;
				if (goods.goodsStock > 0) {
					layer.tips("仅剩最后"+ goods.goodsStock+"份","#buy-num_"+goods.goodsId+'_' + goods.attrId+'_'+goods.isSeckill+'_'+goods.isGroup);
					$("#color_"+goods.goodsId+'_' + goods.attrId+'_'+goods.isSeckill+'_'+goods.isGroup).css('background','#00BAFF');
					layer.msg('部分产品库存不足');
				} else {
					$("#color_"+goods.goodsId+'_' + goods.attrId+'_'+goods.isSeckill+'_'+goods.isGroup).css('background','#D5D7DC');
					layer.msg('部分产品没货');
					//layer.tips("无货", "#buy-num_"+goods.goodsId+'_' + goods.attrId+'_'+goods.isSeckill+'_'+goods.isGroup);
				}
			} else {
				//$("#stock_" + goods.goodsId).html("有货");
			}
		}
		if (!cflag||!flag) {
			return false;
		}
		location.href = orderurl+'/g_a/'+goodsId_attrId;
	});
}

// 删除购物车中的商品
function delCatGoods(shopId, goodsId, priceAttrId) {
	layer
			.confirm(
					'您确定要从购物车中删除该商品吗？',
					{
						icon : 3,
						title : '系统提示',
						offset : '150px'
					},
					function(tips) {
						var ll = layer.load('数据处理中，请稍候...');
						var num = parseInt($(
								"#buy-num_" + goodsId + "_" + priceAttrId)
								.val(), 10);
						var totalMoney = parseFloat($("#wst_cart_totalmoney")
								.html(), 10);
						var shop_totalMoney = parseFloat($(
								"#shop_totalMoney_" + shopId).html(), 10);
						var price = parseFloat($(
								"#price_" + goodsId + "_" + priceAttrId).val(),
								10);
						var ischk = $(
								"#chk_goods_" + goodsId + "_" + priceAttrId)
								.prop('checked');
						jQuery
								.post(
										Think.U('Home/Cart/delCartGoods'),
										{
											goodsId : goodsId,
											goodsAttrId : priceAttrId
										},
										function(data) {
											layer.close(ll);
											layer.close(tips);
											var vd = WST.toJson(data);
											$(
													"#selgoods_" + goodsId
															+ "_" + priceAttrId)
													.remove();
											if ($("input[name='chk_goods_"
													+ shopId + "']").length == 0) {
												$("#wst_cart_shop_" + shopId)
														.remove();
											}
											if (ischk) {
												$("#shop_totalMoney_" + shopId)
														.html(
																parseFloat(
																		(shop_totalMoney - price
																				* num),
																		10)
																		.toFixed(
																				2));
												$("#wst_cart_totalmoney")
														.html(
																parseFloat(
																		(totalMoney - price
																				* num),
																		10)
																		.toFixed(
																				2));
											}
											if ($("div[id^='wst_cart_shop_']").length == 0) {
												$("#wst_cartlist_pbox")
														.html(
																"<div style='height:80px;line-height:80px;font-size:20px;text-align:center;'>您的购物车空空如也，赶快开始购物吧！</div><br/>");
												$('.wst-btn-checkout').hide();
											}
										});
					});
}

jQuery(function() {
/*	jQuery(".goodsStockFlag").each(function() {
		if ($(this).val() == -1) {
			tip('抱歉，部分商品缺货或库存不足',0,2,0);
			//$("#showwarnmsg").show();
			return;
		}
	});*/
	$("#chk_all").click(function() {
		if ($(this).prop("checked")) {
				$("input[id^='chk_goods_']").each(function() {
					$(this).prop("checked", true);
					var shopId = $(this).attr("parent");
					var goodsId = $(this).val();
					var num = $("#buy-num_" + goodsId).val();
					var isBook = $(this).attr("isBook");
					checkCartPay(shopId, goodsId, num, 1, isBook);
					productNum();
				});
		} else {
				$("input[id^='chk_goods_']").each(function() {
					$(this).prop("checked", false);
					var shopId = $(this).attr("parent");
					var goodsId = $(this).val();
					var num = $("#buy-num_" + goodsId).val();
					var isBook = $(this).attr("isBook");
					checkCartPay(shopId, goodsId, num, 0, isBook);
					productNum();
			});
		}
	});

	$("input[id^='chk_shop_']").click(function() {
		var shopId = $(this).val();
		if ($(this).prop("checked")) {
			$("input[name='chk_goods_" + shopId + "']").each(function() {
				$(this).prop("checked", true)
				var shopId = $(this).attr("parent");
				var goodsId = $(this).val();
				var num = $("#buy-num_" + goodsId).val();
				var isBook = $(this).attr("isBook");
				checkCartPay(shopId, goodsId, num, 1, isBook);
			});
		} else {
			$("input[name='chk_goods_" + shopId + "']").each(function() {
				$(this).prop("checked", false);
				var shopId = $(this).attr("parent");
				var goodsId = $(this).val();
				var num = $("#buy-num_" + goodsId).val();
				var isBook = $(this).attr("isBook");
				checkCartPay(shopId, goodsId, num, 0, isBook);
			});
		}
	});

	$("input[id^='chk_goods_']").click(function() {
		var shopId = $(this).attr("parent");
		var goodsId = $(this).val();
		var num = $("#buy-num_" + goodsId).val();
		var isBook = $(this).attr("isBook");
		if ($(this).is(":checked")) {
			checkCartPay(shopId, goodsId, num, 1, isBook);
			productNum();
		} else {
			checkCartPay(shopId, goodsId, num, 0, isBook);
			productNum();
		}
	});
	function productNum(){
		var num=0;
		$("input[id^='chk_goods_']").each(function(){
			if($(this).is(":checked")){
				num=num+1;
			}
		});
		$('#productNum').text(num);
	}

});
