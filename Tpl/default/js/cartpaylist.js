


//修改购物车中的商品数量
function changeCatGoodsnum(flag,shopId,goodsId,priceAttrId,isBook,isGroup,isSeckill){
	isBook = 0;
	var num = parseInt($("#buy-num_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).val(),10);
	if(num<0){
		num = Math.abs(num);
		$("#buy-num_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).val(num);
	}
	
	if(flag==1){
		if(num>1)num = num-1;		
	}else if(flag==2){
		num = num+1;
	}
	if(num<1){
		num = 1;
		$("#buy-num_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).val(1);
	}
	
	if($("#chk_goods_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).is(":checked")){
		checkCartPay(shopId,goodsId,num,1,isBook,priceAttrId,isGroup,isSeckill);
	}else{
		checkCartPay(shopId,goodsId,num,0,isBook,priceAttrId,isGroup,isSeckill);
	}
	
}


function checkCartPay(shopId,goodsId,num,ischk,isBook,goodsAttrId,isGroup,isSeckill){
	jQuery.post( Think.U('Home/Goods/getGoodsStock') ,{goodsId:goodsId,isBook:isBook,goodsAttrId:goodsAttrId,isGroup:isGroup,isSeckill:isSeckill},function(data) {
		var json = WST.toJson(data);
		if(json.goodsStock==0){
			$("#stock_"+json.goodsId).html("<span style='color:red;'>无货</span>");
		}
		num = parseInt(num,10);
		if(json.goodsStock>=num){
			num = num>200?199:num;	
			$("#stock_"+json.goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).html("有货");
			$("#selgoods_"+json.goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).css({"border":"0"});
		}else{
			num = json.goodsStock;	console.log(num);
			$("#stock_"+json.goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).html("<span style='color:red;'>仅剩最后"+json.goodsStock+"份</span>");
			$("#selgoods_"+json.goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).css({"border":"0"});
		}
		jQuery.post( Think.U('Home/Cart/changeCartGoodsNum') ,{goodsId:goodsId,num:num,ischk:ischk,goodsAttrId:goodsAttrId,isGroup:isGroup,isSeckill:isSeckill},function(rsp) {
			if(rsp){
				console.log(rsp);
				if(rsp=='2'){
                    $("#stock_"+json.goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).html("<span style='color:red;'>已达限购数量</span>");
                    return false;
                }//正常情况下是1 当秒杀商品的数量已经达到最大限量值，则返回2  表示不可再增加数量,并给予提示
				var totalMoney = 0;	
				$("#buy-num_"+goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).val(num);
				$("#buy-num_"+goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).css({"border":""});
				var price = parseFloat($("#price_"+goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).val(),10);
				$("#prc_"+goodsId+"_"+goodsAttrId+"_"+isSeckill+"_"+isGroup).html((num*price).toFixed(1));
				//店铺下的商品
				var shopTotalMoney = 0;
				$("input[name='chk_goods_"+shopId+"']").each(function(){
					if($(this).is(":checked")){
						var goodsAttrId = $(this).attr('dataId');
						var gid = $(this).val();
						var gnum = $("#buy-num_"+gid).val();
						var gprice = parseFloat($("#price_"+gid).val(),10);
						shopTotalMoney += gnum*gprice;
					}
				});
				$("#shop_totalMoney_"+shopId).html(shopTotalMoney.toFixed(1));
				//所有商品
				$(".cgoodsId").each(function(){
					var goodsAttrId = $(this).attr('dataId');
					var gid = $(this).val();
					if($("#chk_goods_"+gid).is(":checked")){
						var price = parseFloat($("#price_"+gid).val(),10);
						var cnt = parseInt($("#buy-num_"+gid).val(),10);
						
						totalMoney += price*cnt;
					}
				});			
				
				$("#wst_cart_totalmoney").html(totalMoney.toFixed(1));
			}
		});
	});
}

function toContinue(){
	location.href= domainURL ;
}

//去结算
function goToPay(){
	var flag = true;
	var cflag = true;
	$("input[id^='buy-num_']").each(function(){
		if($(this).val()<1){
			$(this).css({"border":"2px solid red"});
			layer.tips("购买数量不能小于1","#"+$(this).attr("id"));
			if(cflag){
				cflag = false;
			}
				
		}
	});
	if(!cflag){
		return false;
	}
	jQuery.post( Think.U('Home/Cart/checkCartGoodsStock') ,{},function(data) {
		var goodsInfo = WST.toJson(data);		
		for(var i=0;i<goodsInfo.length;i++){
			var goods = goodsInfo[i];
			if(goods.cnt<1){
				cflag = false;
				$("#buy-num_"+goods.goodsId).css({"border":"2px solid red"});
				layer.tips("购买数量不能小于1","#buy-num_"+goods.goodsId);
			}
			if(goods.stockStatus<1){
				flag = false;
				$("#selgoods_"+goods.goodsId).css({"border":"2px solid red"});				
				if(goods.goodsStock>0){
					$("#stock_"+goods.goodsId).html("<span style='color:red;'>仅剩最后"+goods.goodsStock+"份</span>");
				}else{
					$("#stock_"+goods.goodsId).html("<span style='color:red;'>无货</span>");
				}				
			}else{
				$("#stock_"+goods.goodsId).html("有货");
			}
		}
		if(!cflag){
			return false;
		}
		if(flag){
			location.href = Think.U('Home/Orders/checkOrderInfo','rnd='+new Date().getTime());
		}else{
			$("#showwarnmsg").show();
		}
	});
	
	
}

//删除购物车中的商品
function delCatGoods(shopId,goodsId,priceAttrId,isGroup,isSeckill){
	layer.confirm('您确定要从购物车中删除该商品吗？',{icon: 3, title:'系统提示',offset: '150px'}, function(tips){
		var ll = layer.load('数据处理中，请稍候...');
		var num = parseInt($("#buy-num_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).val(),10);
		var totalMoney = parseFloat($("#wst_cart_totalmoney").html(),10);
		var shop_totalMoney = parseFloat($("#shop_totalMoney_"+shopId).html(),10);
		var price = parseFloat($("#price_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).val(),10);
		var ischk = $("#chk_goods_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).prop('checked');
		jQuery.post(Think.U('Home/Cart/delCartGoods') ,{goodsId:goodsId,goodsAttrId:priceAttrId,isGroup:isGroup,isSeckill:isSeckill},function(data) {
			layer.close(ll);
	    	layer.close(tips);
			var vd = WST.toJson(data);console.log(vd);
			$("#selgoods_"+goodsId+"_"+priceAttrId+"_"+isSeckill+"_"+isGroup).remove();
			if($("input[name='chk_goods_"+shopId+"']").length==0){
				$("#wst_cart_shop_"+shopId).remove();
			}
			if(ischk){
			    $("#shop_totalMoney_"+shopId).html(parseFloat((shop_totalMoney-price*num),10).toFixed(2));
			    $("#wst_cart_totalmoney").html(parseFloat((totalMoney-price*num),10).toFixed(2));
			}
			if($("div[id^='wst_cart_shop_']").length==0){
				$("#wst_cartlist_pbox").html("<div style='height:80px;line-height:80px;font-size:20px;text-align:center;'>您的购物车空空如也，赶快开始购物吧！</div><br/>");
				$('.wst-btn-checkout').hide();
			}
		});	
	});
}

jQuery(function(){
	jQuery(".goodsStockFlag").each(function(){		
		if($(this).val()==-1){
			$("#showwarnmsg").show();
			return;
		}
	});

	$("#chk_all").click(function(){
		if($(this).prop("checked")){
			$("input[id^='chk_shop_']").each(function(){
				$(this).prop("checked",true);
				var shopId = $(this).val();
				$("input[name='chk_goods_"+shopId+"']").each(function(){
					$(this).prop("checked",true);
					var shopId = $(this).attr("parent");
					var goodsId = $(this).val();
					var num = $("#buy-num_"+goodsId).val();
					var isBook = $(this).attr("isBook");
					checkCartPay(shopId,goodsId,num,1,isBook);
					
				});
			});
		}else{
			$("input[id^='chk_shop_']").each(function(){
				$(this).prop("checked",false);
				var shopId = $(this).val();
				$("input[name='chk_goods_"+shopId+"']").each(function(){
					$(this).prop("checked",false);
					
					var shopId = $(this).attr("parent");
					var goodsId = $(this).val();
					var num = $("#buy-num_"+goodsId).val();
					var isBook = $(this).attr("isBook");
					checkCartPay(shopId,goodsId,num,0,isBook);
					
				});
			});
		}
	});
	
	
	$("input[id^='chk_shop_']").click(function(){
		var shopId = $(this).val();
		if($(this).prop("checked")){
			$("input[name='chk_goods_"+shopId+"']").each(function(){
				$(this).prop("checked",true)
				
				var shopId = $(this).attr("parent");
				var goodsId = $(this).val();
				var num = $("#buy-num_"+goodsId).val();
				var isBook = $(this).attr("isBook");
				checkCartPay(shopId,goodsId,num,1,isBook);
				
			});
		}else{
			$("input[name='chk_goods_"+shopId+"']").each(function(){
				$(this).prop("checked",false);
				var shopId = $(this).attr("parent");
				var goodsId = $(this).val();
				var num = $("#buy-num_"+goodsId).val();
				var isBook = $(this).attr("isBook");
				checkCartPay(shopId,goodsId,num,0,isBook);
			});
		}
	});
	
	$("input[id^='chk_goods_']").click(function(){
		
		var shopId = $(this).attr("parent");
		var goodsId = $(this).val();
		var num = $("#buy-num_"+goodsId).val();
		var isBook = $(this).attr("isBook");
		if($(this).is(":checked")){
			checkCartPay(shopId,goodsId,num,1,isBook);
		}else{
			checkCartPay(shopId,goodsId,num,0,isBook);
		}
		
	});
	
});
