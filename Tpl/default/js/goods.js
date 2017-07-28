function getCommunitys(obj){
	var vid = $(obj).attr("id");
	
	$("#scommunitys").find("li").removeClass("searched");
	$(obj).addClass("searched");
	var params = {};
	params.areaId = vid;
	var html = [];
	$.post(Think.U('Home/Communitys/queryByList'),params,function(data,textStatus){
	    html.push('<li class="searched">全部</li>');
		var json = WST.toJson(data);
		if(json.status=='1' && json.list.length>0){
			var opts = null;
			for(var i=0;i<json.list.length;i++){
				opts = json.list[i];
				html.push("<li id="+opts.communityId+">"+opts.communityName+"</li>")
				
			}
		}
		$('#psareas').html(html.join(''));
   });
	
}

function tohide(obj,id){
		
	if($("#"+id).height()<=28){
		$("#"+id).height('auto');
		$("#"+id).css("overflow","");
		$("#bs").val(1)
		$("#"+id+"-tp").html("&nbsp;隐藏&nbsp;");
	}else{
		$("#"+id).height(28);
		$("#"+id).css("overflow","hidden");
		$("#bs").val(0)
		$("#"+id+"-tp").html("&nbsp;显示&nbsp;");
	}
}
//商品筛选
function queryGoods(obj,mark){
	var params = {};
	params.c1Id = $("#c1Id").val();
	params.c2Id = $("#c2Id").val();
	params.c3Id = $("#c3Id").val();
	params.bs = $("#bs").val();
	params.mark = mark;
	if(mark==8){
		var sj = $("#sj").val();
		if(sj==2){
			$("#sj").val(1);
			$("#msort").val(8);
		}else{
			$("#sj").val(2);
			$("#msort").val(9);
		}		
	}else{
		$("#sj").val(0);
		$("#msort").val(mark);
	}	
	params.msort = $("#msort").val();
	params.sj = $("#sj").val();
	
	if(mark==1){
		var areaId3 = $(obj).attr("data");
		params.areaId3 = areaId3;
	}else if(mark==2){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
	}else if(mark==3){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
	}else if(mark==4){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $(obj).attr("data");
		
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
		
	}else if(mark==5){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $("#wst-price").find(".searched").attr("data");
		var shopId = $(obj).attr("data");
		communityId = communityId?communityId:'';

		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
		
	}else{
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		if(mark==12){
			var prices = $("#sprice").val()+"_"+$("#eprice").val();
		}else{
			var prices = $("#wst-price").find(".searched").attr("data");
		}
		
		var shopId = $("#wst-shop").attr("data");
		communityId = communityId?communityId:'';
		
		params.mark = mark;
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
	}
	
	var keyword = $.trim($("#keyword").val());
	if(keyword!=""){
		params.keyWords = keyword;
	}
	
	params.wstModel = "Home";
	params.wstControl = "Goods";
	params.wstAction = "getGoodsList";

	jQuery.post(Think.U('Home/Base/getURL') ,params,function(data) {
		var json = WST.toJson(data);
		window.location = json.url;
	});
	
}
//积分商品筛选
function queryIntegralGoods(obj,mark){
	var params = {};
	params.c1Id = $("#c1Id").val();
	params.c2Id = $("#c2Id").val();
	params.c3Id = $("#c3Id").val();
	params.bs = $("#bs").val();
	params.mark = mark;
	if(mark==8){
		var sj = $("#sj").val();
		if(sj==2){
			$("#sj").val(1);
			$("#msort").val(8);
		}else{
			$("#sj").val(2);
			$("#msort").val(9);
		}		
	}else{
		$("#sj").val(0);
		$("#msort").val(mark);
	}	
	params.msort = $("#msort").val();
	params.sj = $("#sj").val();
	 if(mark==4){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var catId = $("#wst-brand").find(".searched").attr("data");
		var prices = $(obj).attr("data");
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.catId = catId;
		params.prices = prices;
	}else if(mark==6){
		//积分商城礼品分类筛选
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var catId = $(obj).attr("data");
		var prices = $("#wst-price").find(".searched").attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.catId = catId;
		params.prices = prices;
	}else{
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		if(mark==12){
			var prices = $("#sprice").val()+"_"+$("#eprice").val();
		}else{
			var prices = $("#wst-price").find(".searched").attr("data");
		}
		var shopId = $("#wst-shop").attr("data");
		communityId = communityId?communityId:'';
		params.mark = mark;
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
	}
	var keyword = $.trim($("#keyword").val());
	if(keyword!=""){
		params.keyWords = keyword;
	}
	params.wstModel = "Home";
	params.wstControl = "Integral";
	params.wstAction = "index";
	jQuery.post(Think.U('Home/Base/getURL') ,params,function(data) {
		var json = WST.toJson(data);
		window.location = json.url;
	});
	
}
//团购商品筛选
function queryGroupGoods(obj,mark){
	var params = {};
	params.c1Id = $("#c1Id").val();
	params.c2Id = $("#c2Id").val();
	params.c3Id = $("#c3Id").val();
	params.bs = $("#bs").val();
	params.mark = mark;
	if(mark==8){
		var sj = $("#sj").val();
		if(sj==2){
			$("#sj").val(1);
			$("#msort").val(8);
		}else{
			$("#sj").val(2);
			$("#msort").val(9);
		}		
	}else{
		$("#sj").val(0);
		$("#msort").val(mark);
	}	
	params.msort = $("#msort").val();
	params.sj = $("#sj").val();
	
	if(mark==1){
		var areaId3 = $(obj).attr("data");
		params.areaId3 = areaId3;
	}else if(mark==2){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
	}else if(mark==3){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
	}else if(mark==4){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $(obj).attr("data");
		
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
		
	}else if(mark==5){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $("#wst-price").find(".searched").attr("data");
		var shopId = $(obj).attr("data");
		communityId = communityId?communityId:'';

		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
		
	}else{
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		if(mark==12){
			var prices = $("#sprice").val()+"_"+$("#eprice").val();
		}else{
			var prices = $("#wst-price").find(".searched").attr("data");
		}
		
		var shopId = $("#wst-shop").attr("data");
		communityId = communityId?communityId:'';
		
		params.mark = mark;
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
	}
	
	var keyword = $.trim($("#keyword").val());
	if(keyword!=""){
		params.keyWords = keyword;
	}
	
	params.wstModel = "Home";
	params.wstControl = "Group";
	params.wstAction = "index";

	jQuery.post(Think.U('Home/Base/getURL') ,params,function(data) {
		var json = WST.toJson(data);
		window.location = json.url;
	});
	
}
//秒杀商品筛选
function querySeckillGoods(obj,mark){
	var params = {};
	params.c1Id = $("#c1Id").val();
	params.c2Id = $("#c2Id").val();
	params.c3Id = $("#c3Id").val();
	params.bs = $("#bs").val();
	params.mark = mark;
	if(mark==8){
		var sj = $("#sj").val();
		if(sj==2){
			$("#sj").val(1);
			$("#msort").val(8);
		}else{
			$("#sj").val(2);
			$("#msort").val(9);
		}
	}else{
		$("#sj").val(0);
		$("#msort").val(mark);
	}
	params.msort = $("#msort").val();
	params.sj = $("#sj").val();

	if(mark==1){
		var areaId3 = $(obj).attr("data");
		params.areaId3 = areaId3;
	}else if(mark==2){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
	}else if(mark==3){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $(obj).attr("data");
		communityId = communityId?communityId:'';
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
	}else if(mark==4){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $(obj).attr("data");

		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;

	}else if(mark==5){
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		var prices = $("#wst-price").find(".searched").attr("data");
		var shopId = $(obj).attr("data");
		communityId = communityId?communityId:'';

		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;

	}else{
		var areaId3 = $("#wst-areas").find(".searched").attr("data");
		var communityId = $("#wst-communitys").find(".searched").attr("data");
		var brandId = $("#wst-brand").find(".searched").attr("data");
		if(mark==12){
			var prices = $("#sprice").val()+"_"+$("#eprice").val();
		}else{
			var prices = $("#wst-price").find(".searched").attr("data");
		}

		var shopId = $("#wst-shop").attr("data");
		communityId = communityId?communityId:'';

		params.mark = mark;
		params.areaId3 = areaId3;
		params.communityId = communityId;
		params.brandId = brandId;
		params.prices = prices;
	}

	var keyword = $.trim($("#keyword").val());
	if(keyword!=""){
		params.keyWords = keyword;
	}

	params.wstModel = "Home";
	params.wstControl = "Seckill";
	params.wstAction = "index";

	jQuery.post(Think.U('Home/Base/getURL') ,params,function(data) {
		var json = WST.toJson(data);
		window.location = json.url;
	});

}
//秒杀商品筛选
function queryAuctionGoods(obj,mark){
    var params = {};
    params.c1Id = $("#c1Id").val();
    params.c2Id = $("#c2Id").val();
    params.c3Id = $("#c3Id").val();
    params.bs = $("#bs").val();
    params.mark = mark;
    if(mark==8){
        var sj = $("#sj").val();
        if(sj==2){
            $("#sj").val(1);
            $("#msort").val(8);
        }else{
            $("#sj").val(2);
            $("#msort").val(9);
        }
    }else{
        $("#sj").val(0);
        $("#msort").val(mark);
    }
    params.msort = $("#msort").val();
    params.sj = $("#sj").val();

    if(mark==1){
        var areaId3 = $(obj).attr("data");
        params.areaId3 = areaId3;
    }else if(mark==2){
        var areaId3 = $("#wst-areas").find(".searched").attr("data");
        var communityId = $(obj).attr("data");
        communityId = communityId?communityId:'';
        params.areaId3 = areaId3;
        params.communityId = communityId;
    }else if(mark==3){
        var areaId3 = $("#wst-areas").find(".searched").attr("data");
        var communityId = $("#wst-communitys").find(".searched").attr("data");
        var brandId = $(obj).attr("data");
        communityId = communityId?communityId:'';
        params.areaId3 = areaId3;
        params.communityId = communityId;
        params.brandId = brandId;
    }else if(mark==4){
        var areaId3 = $("#wst-areas").find(".searched").attr("data");
        var communityId = $("#wst-communitys").find(".searched").attr("data");
        var brandId = $("#wst-brand").find(".searched").attr("data");
        var prices = $(obj).attr("data");

        params.areaId3 = areaId3;
        params.communityId = communityId;
        params.brandId = brandId;
        params.prices = prices;

    }else if(mark==5){
        var areaId3 = $("#wst-areas").find(".searched").attr("data");
        var communityId = $("#wst-communitys").find(".searched").attr("data");
        var brandId = $("#wst-brand").find(".searched").attr("data");
        var prices = $("#wst-price").find(".searched").attr("data");
        var shopId = $(obj).attr("data");
        communityId = communityId?communityId:'';

        params.areaId3 = areaId3;
        params.communityId = communityId;
        params.brandId = brandId;
        params.prices = prices;

    }else{
        var areaId3 = $("#wst-areas").find(".searched").attr("data");
        var communityId = $("#wst-communitys").find(".searched").attr("data");
        var brandId = $("#wst-brand").find(".searched").attr("data");
        if(mark==12){
            var prices = $("#sprice").val()+"_"+$("#eprice").val();
        }else{
            var prices = $("#wst-price").find(".searched").attr("data");
        }

        var shopId = $("#wst-shop").attr("data");
        communityId = communityId?communityId:'';

        params.mark = mark;
        params.areaId3 = areaId3;
        params.communityId = communityId;
        params.brandId = brandId;
        params.prices = prices;
    }

    var keyword = $.trim($("#keyword").val());
    if(keyword!=""){
        params.keyWords = keyword;
    }

    params.wstModel = "Home";
    params.wstControl = "Auction";
    params.wstAction = "index";

    jQuery.post(Think.U('Home/Base/getURL') ,params,function(data) {
        var json = WST.toJson(data);
        window.location = json.url;
    });

}
/**
 * 立即兑换积分商品
 */
function exchange(goodsId){
	var params = {};
	params.goodsId = goodsId;
	params.gcount = parseInt($("#buy-num").val(),10);
	params.rnd = Math.random();
	location.href=Think.U('Home/IntegralOrders/checkOrder',params);
}

/**
 * 加入购物车
 */
function addCart(goodsId,type,goodsThums){
	var attrId =new Array();
	$(".wst-goods-attrs-on").each(function(){
		attrId.push($(this).attr('dataId'));
	})
	var params = {};
	params.goodsId = goodsId;
	params.gcount = parseInt($("#buy-num").val(),10);
	params.rnd = Math.random();
	params.goodsAttrId = attrId.toString();
	params.isGroup = $('#isGroup').val();
	params.isSeckill = $('#isSeckill').val();
	$("#flyItem img").attr("src",domainURL  +"/"+ goodsThums)
	jQuery.post(Think.U('Home/Cart/addToCartAjax') ,params,function(data) {
		console.log(eval(data));
		if(type==1){
			location.href= Think.U('Home/Cart/toCart');
		}else{
			//layer.msg("添加成功!",1,1);
		}
	});
}
//修改商品购买数量
function changebuynum(flag){
	var num = parseInt($("#buy-num").val(),10);
	var num = num?num:1;
	if(flag==1){
		if(num>1)num = num-1;
	}else if(flag==2){
		num = num+1;
	}
	var maxVal = parseInt($("#buy-num").attr('maxVal'),10);
	if(maxVal<=num)num=maxVal;
	$("#buy-num").val(num);
}
//获取属性库存
function getAttrStockInfo(id){
    var goodsId = $("#goodsId").val();
    jQuery.post( Think.U('Home/Goods/getPriceAttrInfoSk') ,{goodsId:goodsId,id:id},function(data) {
        var json = WST.toJson(data);
        var price = 0;	//根据价格属性改变的价格
        var stock = 0;	//库存
        var stocks = new Array();	//每种价格属性的库存
        //遍历属性价格
        for( var p in json){
            for(var q in json[p]){
                price += parseInt(json[p][q].attrPrice);
                if(stock == 0){
                    stocks.push(parseInt(json[p][q].attrStock));
                }else{
                    if(stock >= parseInt(json[p][q].attrStock)){
                        stock = parseInt(json[p][q].attrStock);
                    }
                }
            }
        }
        //最少的价格属性
        stock = Math.min.apply(null, stocks);
        //在html上改变的价格
        $('#shopGoodsPrice_'+goodsId).html("￥"+price);
       //var buyNum = parseInt($("#buy-num").val());
        //$("#buy-num").attr('maxVal',stock);
        $("#goodsAttrStock").html(stock);
       // if(buyNum>stock){
       //     $("#buy-num").val(stock);
       // }
       // $('#shopGoodsPrice_'+goodsId).attr('dataId',id);

    });
}
//获取属性价格
function getPriceAttrInfo(id){
	var goodsId = $("#goodsId").val();
	jQuery.post( Think.U('Home/Goods/getPriceAttrInfo') ,{goodsId:goodsId,id:id},function(data) {
		var json = WST.toJson(data);
		var price = 0;	//根据价格属性改变的价格
		var stock = 0;	//库存
		var stocks = new Array();	//每种价格属性的库存
		//遍历属性价格
		for( var p in json){
			for(var q in json[p]){
				price += parseInt(json[p][q].attrPrice);		
				if(stock == 0){
					stocks.push(parseInt(json[p][q].attrStock));
				}else{
					if(stock >= parseInt(json[p][q].attrStock)){
						stock = parseInt(json[p][q].attrStock);
					}
				}		
			}
		}
			//最少的价格属性
			stock = Math.min.apply(null, stocks);
			//在html上改变的价格
			$('#shopGoodsPrice_'+goodsId).html("￥"+price);
			var buyNum = parseInt($("#buy-num").val());
			$("#buy-num").attr('maxVal',stock);
			$("#goodsStock").html(stock);
			if(buyNum>stock){
				$("#buy-num").val(stock);
			}
			if(stock>0){
				$("#haveGoodsToBuy").show();
				$("#noGoodsToBuy").hide();
				$("#goBuy").show();
			}else{
				$("#haveGoodsToBuy").hide();
				$("#noGoodsToBuy").show();
				$("#goBuy").hide();
			}
			$('#shopGoodsPrice_'+goodsId).attr('dataId',id);
		
	});
}
function checkStock(obj){
	$(obj).addClass('wst-goods-attrs-on').siblings().removeClass('wst-goods-attrs-on');
	//声明数组保存选择的价格属性id
	var attrId =new Array();
	$(".wst-goods-attrs-on").each(function(){
		attrId.push($(this).attr('dataId'));
	})
	var isGroup = $('#isGroup').val();
    var isSeckill=$('#isSeckill').val();
	if(isGroup==0){
		getPriceAttrInfo(attrId);
	}
    //如果是秒杀详情，得到商品不同属性的库存
    if(isSeckill==1){
        getAttrStockInfo(attrId);
    }
}

function getGoodsappraises(goodsId,p){
	var params = {}; 
	params.goodsId = goodsId;
	params.p = p;
	//加载商品评价
	jQuery.post(Think.U("Home/GoodsAppraises/getGoodsappraises") ,params,function(data) {
		var json = WST.toJson(data);
		if(json.root && json.root.length){
			var html = new Array();		    	
			for(var j=0;j<json.root.length;j++){
			    var appraises = json.root[j];	
			    html.push('<tr height="75" style="border:1px dotted #eeeeee;">');
				    html.push('<td width="150" style="padding-left:6px;"><div>');
				    if(appraises.userName){
				    	if(appraises.anonymity==1){
				    		html.push(appraises.userName);
				    	}else{
				    		var k  = appraises.userName.substring(1,(appraises.userName.length-1));
				    		appraises.userName = appraises.userName.replace(eval("/"+k+"/"),'***');
				    		html.push(appraises.userName+'[匿名]');
				    	}
				    	var p = appraises.createTime.indexOf(' ');
				    	appraises.createTime = appraises.createTime.substring(0,p);
				    	html.push('<br/>'+appraises.createTime);
				    }else{
				    	html.push('匿名');
				    }

				    html.push('</div></td>');
				    html.push('<td width="*"><div>'+appraises.content+'</div></td>');
				    html.push('<td width="200">');
				    html.push('<div>商品评分：');
					for(var i=0;i<appraises.goodsScore;i++){
						html.push('<img src="/Tpl/default/images/icon_score_yes.png"/>');
					}
					html.push('</div>');
					html.push('<div>商品包装满意度：');
					for(var i=0;i<appraises.packScore;i++){
						html.push("<img src='/Tpl/default/images/icon_score_yes.png'/>");
					}
					html.push('</div>');
					html.push('<div>送货速度满意度：');
					for(var i=0;i<appraises.timeScore;i++){
						html.push('<img src="/Tpl/default/images/icon_score_yes.png"/>');
					}
					html.push('</div>');
					html.push('<div>配送人员服务满意度：');
					for(var i=0;i<appraises.serviceScore;i++){
						html.push('<img src="/Tpl/default/images/icon_score_yes.png"/>');
					}
					html.push('</div>');
					html.push('</td>');
					
			    html.push('</tr>');	
			}
			$("#appraiseTab").html(html.join(""));
			if(json.totalPage>1){
				laypage({
				    cont: 'wst-page-items',
				    pages: json.totalPage,
				    curr: json.currPage,
				    skip: true,
				    skin: '#e23e3d',
				    groups: 3,
				    jump: function(e, first){
				        if(!first){
				        	getGoodsappraises(goodsId,e.curr);
				        }
				    }
				});
			}
		}else{
			$("#appraiseTab").html("<tr><td><div style='font-size:15px;text-align:center;'>没有评价信息</div></td></tr>");
		}	
	});
}

function favoriteGoods(id){
	if($('#f0_txt').attr('f')=='0'){
		jQuery.post(Think.U("Home/Favorites/favoriteGoods") ,{id:id},function(data) {
			var json = WST.toJson(data,1);
			if(json.status==1){
				$('#f0_txt').html('已关注');
				$('#f0_txt').attr('f',json.id);
			}else if(json.status==-999){
				WST.msg('关注失败，请先登录!');
			}else{
				WST.msg('关注失败!');
			}
		});
	}else{
		id = $('#f0_txt').attr('f');
		cancelFavorites(id,0);
	}
}
function favoriteShops(id){
	if($('#f1_txt').attr('f')=='0'){
		jQuery.post(Think.U("Home/Favorites/favoriteShops") ,{id:id},function(data) {
			var json = WST.toJson(data,1);
			if(json.status==1){
				$('#f1_txt').html('已关注');
				$('#f1_txt').attr('f',json.id);
			}else if(json.status==-999){
				WST.msg('关注失败，请先登录!');
			}else{
				WST.msg('关注失败!');
			}
		});
	}else{
		id = $('#f1_txt').attr('f');
		cancelFavorites(id,1);
	}
}
function cancelFavorites(id,type){
	jQuery.post(Think.U("Home/Favorites/cancelFavorite") ,{id:id,type:type},function(data) {
		var json = WST.toJson(data,1);
		if(json.status==1){
			$('#f'+type+'_txt').html('关注'+((type==1)?'店铺':'商品'));
			$('#f'+type+'_txt').attr('f',0);
		}else{
			WST.msg('取消关注失败!');
		}
	});
}


