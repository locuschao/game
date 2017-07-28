function toEditAddress(addressId){
	$("#consignee1").hide();
	$("#consignee2").show();
	changeAddress(addressId);
}
function changeAddress(addressId){
	//var addressId = $('input:radio[name="seladdress"]:checked').val();	
	//$("#seladdress_"+addressId).attr("checked",true);
	//alert($("#seladdress_"+addressId).attr("checked"));
	$("#consigneeId").val(addressId);
	if(addressId>=1){
		loadAddress(addressId);
	}else{
		$("#consignee_add_userName").val("");
		$("#consignee_add_address").val("");
		$("#consignee_add_userPhone").val("");
		$("#consignee_add_userTel").val("");
		$("#consignee_add_zipCode").val("");
		
		var html = new Array();
		$("#consignee_add_countyId").val(0);
		
		var html = new Array();
		$("#consignee_add_CommunityId").empty();
		html.push("<option value='0'>请选择</option>");
		$("#consignee_add_CommunityId").html(html.join(""));
	}
}

function editAddress(addressId){
	$("#seladdress_"+addressId).click();
}

function loadCommunitys(obj){
	var districtId = obj.value;
	if(districtId<1){
		var html = new Array();
		$("#consignee_add_CommunityId").empty();
		html.push("<option value='0'>请选择</option>");
		$("#consignee_add_CommunityId").html(html.join(""));
		return;
	}
	
	jQuery.post(Think.U('Home/Communitys/getByDistrict') ,{areaId3:districtId},function(rsp){
		var json = WST.toJson(rsp);
		var html = new Array();
		$("#consignee_add_CommunityId").empty();
		html.push("<option value='0'>请选择</option>");
		if(json.list && json.list.length>0){
			for(var i=0;i<json.list.length;i++){	    	
				html.push("<option value='"+json.list[i].communityId+"'>"+json.list[i].communityName+"</option>");    	
			}
		}
		$("#consignee_add_CommunityId").html(html.join(""));
	});
}

function loadAddress(addressId){
	$("#address_form").show();	
	jQuery.post(Think.U('Home/UserAddress/getUserAddress') ,{addressId:addressId},function(rsp) {
		var rs = WST.toJson(rsp);
		if(rs.status>0){
			var addressInfo = rs.address;
			$("#consignee_add_cityName").val(addressInfo.areaName);
			$("#consignee_add_userName").val(addressInfo.userName);
			$("#consignee_add_address").val(addressInfo.address);
			$("#consignee_add_userPhone").val(addressInfo.userPhone?addressInfo.userPhone:"");
			$("#consignee_add_userTel").val(addressInfo.userTel);
			$("#consignee_add_countyId").val(addressInfo.areaId1);
			
			var countys = addressInfo.area3List;
			var areaList = new Array();
			areaList.push("<option value='0'>请选择</option>");
			for(var i=0;i<countys.length;i++){
				var county = countys[i];				
				if(county.areaId == addressInfo.areaId3){
					areaList.push("<option value="+county.areaId+" selected='selected'>"+county.areaName+"</option>");
				}else{
					areaList.push("<option value="+county.areaId+" >"+county.areaName+"</option>");
				}
			}
			$("#consignee_add_countyId").html(areaList.join(""));
			
			var communitys = addressInfo.communitysList;
			var areaList = new Array();
			areaList.push("<option value='0'>请选择</option>");
			for(var i=0;i<communitys.length;i++){
				var community = communitys[i];				
				if(community.communityId == addressInfo.communityId){
					areaList.push("<option value="+community.communityId+" selected='selected'>"+community.communityName+"</option>");
				}else{
					areaList.push("<option value="+community.communityId+" >"+community.communityName+"</option>");
				}
			}
			$("#consignee_add_CommunityId").html(areaList.join(""));
		}
	});
}
function saveAddress(){
	var seladdress = $('input:radio[name="seladdress"]:checked').val();
	var addressId = $("#consigneeId").val();
	var userName = $("#consignee_add_userName").val();
	var cityId = $("#consignee_add_cityId").val();
	var countyId = $("#consignee_add_countyId").val();
	var communityId = $("#consignee_add_CommunityId").val();
	var address = $("#consignee_add_address").val();
	var userPhone = $("#consignee_add_userPhone").val();
	var userTel = $("#consignee_add_userTel").val();

	var params = {};
	params.id = addressId;
	params.userName = jQuery.trim(userName);
	params.areaId2 = cityId;
	params.areaId3 = countyId;
	params.communityId = communityId;
	params.address = jQuery.trim(address);
	params.userPhone = jQuery.trim(userPhone);
	params.userTel = jQuery.trim(userTel);
	
	if(addressId<1 && $("#seladdress_0").attr("checked")==false){
		WST.msg("请选择收货地址", {icon: 5});
		return ;
	}
	if(params.userName==""){
		WST.msg("请输入收货人", {icon: 5});
		return ;		
	}
	if(!WST.checkMinLength(params.userName,2)){
		WST.msg("收货人姓名长度必须大于1个汉字", {icon: 5});
		return ;	
	}
	if(params.areaId2<1){
		WST.msg("请选择市", {icon: 5});
		return ;		
	}
	if(params.areaId3<1){
		WST.msg("请选择区县", {icon: 5});
		return ;		
	}
	if(params.communityId<1){
		WST.msg("请选择社区", {icon: 5});
		return ;		
	}
	if(params.address==""){
		WST.msg("请输入详细地址", {icon: 5});
		return ;		
	}
	if(userPhone=="" && userTel==""){
		WST.msg("请输入手机号码或固定电话", {icon: 5});
		return ;		
	}
	if(userPhone!="" && !WST.isPhone(params.userPhone)){
		WST.msg("手机号码格式错误", {icon: 5});
		return ;		
	}
	if(userTel!="" && !WST.isTel(params.userTel)){
		WST.msg("固定电话格式错误", {icon: 5});
		return ;		
	}	

	jQuery.post(Think.U('Home/UserAddress/edit') ,params,function(data,textStatus){	
		var json = WST.toJson(data);
		
		if(json.status>0){
			$("#consignee1").show();
			$("#consignee2").hide();
			var addressInfo = new Array();			
			addressInfo.push('<div>');
			addressInfo.push('<span style="font-weight: bold;">'+userName+'&nbsp;&nbsp;&nbsp;&nbsp;</span>');
			if(userPhone!=""){
				addressInfo.push(userPhone);
			}else{
				addressInfo.push(userTel);
			}				
			addressInfo.push('</div>');
			addressInfo.push('<div>');
			addressInfo.push($("#consignee_add_cityName").val());
			addressInfo.push($("#consignee_add_countyId").find("option:selected").text());
			addressInfo.push($("#consignee_add_CommunityId").find("option:selected").text());
			addressInfo.push(address+"&nbsp;&nbsp;&nbsp;&nbsp;");			
			addressInfo.push('</div>');		
			$("#showaddinfo").html(addressInfo.join(""));

			if(addressId==0){
				$("#consigneeId").val(json.status);
				var addressInfo = new Array();
				addressInfo.push('<div id="caddress_'+json.status+'">');										
				addressInfo.push('<label>');
				addressInfo.push('<input id="seladdress_'+json.status+'" onclick="changeAddress('+json.status+');" name="seladdress" type="radio" checked="checked" value="'+json.status+'"/>');
				addressInfo.push('<span style="font-weight: bold;"  id="radusername_'+json.status+'">'+userName+'&nbsp;&nbsp;&nbsp;&nbsp;</span>');
				addressInfo.push('<span id="radaddress_'+json.status+'">');
				addressInfo.push("&nbsp;&nbsp;&nbsp;&nbsp;"+$("#consignee_add_cityName").val());
				addressInfo.push($("#consignee_add_countyId").find("option:selected").text());
				addressInfo.push($("#consignee_add_CommunityId").find("option:selected").text());
				addressInfo.push(address);	
				addressInfo.push("</span>");	
				if(userPhone!=""){
					addressInfo.push(userPhone);
				}else{
					addressInfo.push(userTel);
				}		
				
				addressInfo.push('<span class="optionspan" style="padding-left:50px;color: #999999">[<a onclick="javascript:editAddress('+json.status+');">修改</a>]</span>');
				addressInfo.push('<span class="optionspan" style="padding-left:10px;color: #999999">[<a onclick="javascript:delAddress('+json.status+');">删除</a>]</span>');
				addressInfo.push('</label>');
				addressInfo.push('</div>');
				$(addressInfo.join("")).insertAfter("#flagdiv");
				
			}else{
				$("#radusername_"+addressId).html(params.userName);
				var addressInfo = new Array();
				addressInfo.push("&nbsp;&nbsp;&nbsp;&nbsp;"+$("#consignee_add_cityName").val());
				addressInfo.push($("#consignee_add_countyId").find("option:selected").text());
				addressInfo.push($("#consignee_add_CommunityId").find("option:selected").text());
				addressInfo.push(params.address);	
				$("#radaddress_"+addressId).html(addressInfo.join(""));
			}

		}else{
			WST.msg("收货人信息添加失败", {icon: 5});
		}
	});	
}
function addHour(hour){
    var d=new Date();
    d.setHours(d.getHours()+hour);
    var m=d.getMonth()+1;
    var year = d.getFullYear();
    var month = (m>=10?m:'0'+m);
    
    var day = (d.getDate()>=10)?d.getDate():"0"+d.getDate();
    var h = (d.getHours()>=10)?d.getHours():"0"+d.getHours();
    var min = (d.getMinutes()>=10)?d.getMinutes():"0"+d.getMinutes();
    return (year+'-'+month+'-'+day+" "+h+":"+min+":00");
  }

function delAddress(addressId){
	layer.confirm('您确定删除该地址吗？',{icon: 3, title:'系统提示'}, function(tips){
		var ll = layer.msg('数据处理中，请稍候...', {icon: 16,shade: [0.5, '#B3B3B3']});
		jQuery.post(Think.U('Home/UserAddress/del') ,{id:addressId},function(rsp) {
			layer.close(ll);
	    	layer.close(tips);
			if(rsp){
				$("#caddress_"+addressId).remove();
				$("#consigneeId").val(0);
				$("#seladdress_0").click();
			}else{
				WST.msg("删除失败", {icon: 5});
			}    
		});
	});
	
}
//提交商品订单
function submitOrder(){
	var flag = true;
	$(".tst").each(function(){
		if($(this).val()==-1){			
			flag = false;
		}
	});
	if(!flag){
		WST.msg("抱歉，您的订单金额未达到该店铺的配送订单起步价，不能提交订单。", {icon: 5});
		return;
	}
	var ll = layer.msg('正在保存数据，请稍候...', {icon: 16,shade: [0.5, '#B3B3B3']});
	jQuery.post(Think.U('Home/Goods/checkGoodsStock') ,{},function(data) {
		var goodsInfo = WST.toJson(data);
		layer.close(ll);
		var flag = true;
		for(var i=0;i<goodsInfo.length;i++){
			var goods = goodsInfo[i];
			if(goods.isGroup==1){
				if(goods.goodsStock<=0){
					flag = false;
				}
			}else{
				if(goods.isSale<1 || goods.goodsStock<=0)
				flag = false;							
			}
		}
		if(flag){
			var consigneeId = $("#consigneeId").val();	
			if(!$("#consignee2").is(":hidden")){
				WST.msg("请先保存收货人信息",{icon: 5});
				return;
			}	
			var invoiceClient = $.trim($("#invoiceClient").val());	
			var rdate = $("#requestdate").val();
			var rtime = $("#requesttime").val();
			var requireTime = rdate+" "+rtime+":00";
			var payway = $('input:radio[name="payway"]:checked').val();
			var needreceipt = $('input:radio[name="needreceipt"]:checked').val();
			var isself = $('input:radio[name="isself"]:checked').val();
			var remarks = $.trim($("#remarks").val());
			var d1 = requireTime;	
			d1 = d1.replace(/-/g,"/");
			var date1 = new Date(d1);
			var d2 = addHour(1);	
			d2 = d2.replace(/-/g,"/");
			var date2 = new Date(d2);
			
			if(consigneeId<1){
				WST.msg("请填写收货人地址", {icon: 5});
				return ;
			}
			if(needreceipt==1 && invoiceClient==""){
				WST.msg("请输入抬头", {icon: 5});
				return ;		
			}
			if(date1<date2){
				WST.msg("亲，期望送达时间必须设定为下单时间1小时后哦！", {icon: 5});
				return ;
			}
			if(!subCheckArea()){
				WST.msg("您选的商品不在配送区域内！", {icon: 5});
				return ;
			}
			
			
			var orderunique = new Date().getTime();
			location.href= Think.U('Home/Orders/submitOrder','consigneeId='+consigneeId+"&payway="+payway+"&isself="+isself+"&invoiceClient="+invoiceClient+"&needreceipt="+needreceipt+"&remarks="+remarks+"&requireTime="+requireTime+"&orderunique="+orderunique);
		}else{
			if(goods.isSale<1){
				WST.msg('商品'+goods.goodsName+'已下架，请返回重新选购!', {icon: 5});
			}else if(goods.goodsStock<=0){
				if(goods.isGroup == 1){
					WST.msg('团购商品'+goods.goodsName+'活动已结束，请返回重新选购!', {icon: 5});
				}else{
					WST.msg('商品'+goods.goodsName+'库存不足，请返回重新选购!', {icon: 5});
				}
			}
		}
	});
}

//提交拍卖商品订单
function submitAuctionOrder(){
    var flag = true;

    var ll = layer.msg('正在保存数据，请稍候...', {icon: 16,shade: [0.5, '#B3B3B3']});

    layer.close(ll);
    var flag = true;
    if(flag){
        var consigneeId = $("#consigneeId").val();
        if(!$("#consignee2").is(":hidden")){
            WST.msg("请先保存收货人信息",{icon: 5});
            return;
        }
        var orderId=$('#orderId').val();
        var totalMoney=$('#totalMoney').val();
        var deliverMoney=$('#deliveryMoney').val();
        var needPay=$('#needPay').val();

        var invoiceClient = $.trim($("#invoiceClient").val());
        var rdate = $("#requestdate").val();
        var rtime = $("#requesttime").val();
        var requireTime = rdate+" "+rtime+":00";
        var payway = $('input:radio[name="payway"]:checked').val();
        var needreceipt = $('input:radio[name="needreceipt"]:checked').val();
        var isself = $('input:radio[name="isself"]:checked').val();
        var remarks = $.trim($("#remarks").val());
        var d1 = requireTime;
        d1 = d1.replace(/-/g,"/");
        var date1 = new Date(d1);
        var d2 = addHour(1);
        d2 = d2.replace(/-/g,"/");
        var date2 = new Date(d2);

        if(consigneeId<1){
            WST.msg("请填写收货人地址", {icon: 5});
            return ;
        }
        if(needreceipt==1 && invoiceClient==""){
            WST.msg("请输入抬头", {icon: 5});
            return ;
        }
        if(date1<date2){
            WST.msg("亲，期望送达时间必须设定为下单时间1小时后哦！", {icon: 5});
            return ;
        }
        if(!subCheckArea()){
            WST.msg("您选的商品不在配送区域内！", {icon: 5});
            return ;
        }


        var orderunique = new Date().getTime();
        location.href= Think.U('Home/Orders/submitAuctionOrder','consigneeId='+consigneeId+"&payway="+payway+"&isself="+isself+"&invoiceClient="+invoiceClient+"&needreceipt="+needreceipt+"&remarks="+remarks+"&requireTime="+requireTime+"&orderunique="+orderunique+'&orderId='+orderId+'&totalMoney='+totalMoney+'&deliverMoney='+deliverMoney+'&needPay='+needPay);
    }

}
//提交积分商品订单
function submitIntegralOrder(goodsId,gcount){
	var flag = true;
	$(".tst").each(function(){
		if($(this).val()==-1){			
			flag = false;
		}
	});
	var ll = layer.msg('正在保存数据，请稍候...', {icon: 16,shade: [0.5, '#B3B3B3']});
	jQuery.post(Think.U('Home/Integral/checkGoodsStock') ,{goodsId:goodsId},function(data) {
		var goodsInfo = WST.toJson(data);
		layer.close(ll);
		var flag = true;
		for(var i=0;i<goodsInfo.length;i++){
			var goods = goodsInfo[i];			
			if(goods.isSale<1 || goods.goodsStock<=0){
				flag = false;							
			}
		}
		if(flag){
			var consigneeId = $("#consigneeId").val();	
			if(!$("#consignee2").is(":hidden")){
				WST.msg("请先保存收货人信息",{icon: 5});
				return;
			}	
			var invoiceClient = $.trim($("#invoiceClient").val());	
			var rdate = $("#requestdate").val();
			var rtime = $("#requesttime").val();
			var requireTime = rdate+" "+rtime+":00";
			var payway = $('input:radio[name="payway"]:checked').val();
			if(payway==4){
				//判断商品积分和用户积分
				var shopPirce = parseInt($('#totalMoney_span').text());
				var userScore = parseInt($('#userScore').text());
				if(shopPirce>userScore){
					WST.msg("您的积分不足", {icon: 5});
					return ;
				}
			}
			var needreceipt = $('input:radio[name="needreceipt"]:checked').val();
			var isself = $('input:radio[name="isself"]:checked').val();
			var remarks = $.trim($("#remarks").val());
			var d1 = requireTime;	
			d1 = d1.replace(/-/g,"/");
			var date1 = new Date(d1);
			var d2 = addHour(1);	
			d2 = d2.replace(/-/g,"/");
			var date2 = new Date(d2);
			if(consigneeId<1){
				WST.msg("请填写收货人地址", {icon: 5});
				return ;
			}
			var orderunique = new Date().getTime();
			location.href= Think.U('Home/IntegralOrders/submitOrder','consigneeId='+consigneeId+"&payway="+payway+"&isself="+isself+"&invoiceClient="+invoiceClient+"&needreceipt="+needreceipt+"&remarks="+remarks+"&requireTime="+requireTime+"&orderunique="+orderunique+"&goodsId="+goodsId+"&gcount="+gcount);
		}else{
			if(goods.isSale<1){
				WST.msg('商品'+goods.goodsName+'已下架，请返回重新选购!', {icon: 5});
			}else if(goods.goodsStock<=0){
				WST.msg('商品'+goods.goodsName+'库存不足，请返回重新选购!', {icon: 5});
			}
		}
		
	});
}

function getOrderInfo(orderId){
	window.location = Think.U('Home/orders/getOrderInfo','orderId='+orderId);
}

function getPayUrl(){
	var params = {};
	params.orderIds = $.trim($("#orderIds").val());
	params.payCode = $.trim($("#payCode").val());
	params.needPay = $.trim($("#needPay").val());
	if(params.payCode==""){
		WST.msg('请先选择支付方式', {icon: 5});
		return;
	}
	jQuery.post(Think.U('Home/Payments/get'+params.payCode+"URL") ,params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="Weixin"){
				location.href = json.url;
			}else{
				window.open(json.url);
			}
		}else if(json.status==-2){
			var rlist = json.rlist;
			var garr = new Array();
			for(var i=0;i<rlist.length;i++){
				garr.push(rlist[i].goodsName+rlist[i].goodsAttrName);
				rlist[i].goodsAttrName
			}
			WST.msg('订单中商品【'+garr.join("，")+'】库存不足，不能进行支付。', {icon: 5});
			
		}else{
			WST.msg('您的订单已支付!', {icon: 5});
			setTimeout(function(){				
				window.location = Think.U('Home/orders/queryDeliveryByPage');
			},1500);
		}
	});
}
/*余额支付*/
function yuePay(){
	var params = {};
	params.orderIds = $.trim($("#orderIds").val());
	params.payCode = $.trim($("#payCode").val());
	params.needPay = $.trim($("#needPay").val());
	var password = $("#password").val();
	if(password.length !=6){
		WST.msg('请输入6位密码！', {icon: 5});
		return;
	}
	$.post(Think.U('Home/Payments/payPwd'),{password:password},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
				jQuery.post(Think.U('Home/Payments/yuePay') ,params,function(data) {
				var json = WST.toJson(data);console.log(json);
				if(json.status==2){
					window.location =Think.U('Home/Payments/paySuccess');
				}else if(json.status==-2){
					var rlist = json.rlist;
					var garr = new Array();
					for(var i=0;i<rlist.length;i++){
						garr.push(rlist[i].goodsName+rlist[i].goodsAttrName);
						rlist[i].goodsAttrName
					}
					WST.msg('订单中商品【'+garr.join("，")+'】库存不足，不能进行支付。', {icon: 5});return;
				}else if(json.status==-3){
					WST.msg('您的余额不足!', {icon: 5});
					return;
				}else{
					WST.msg('您的订单已支付!', {icon: 5});return;
					setTimeout(function(){				
						window.location = Think.U('Home/orders/queryDeliveryByPage');
					},1500);
				}
			});
		}else{
			WST.msg('支付密码错误!', {icon: 5});
			location.reload();return;
		}
	})
}

$(function() {
	$('input:radio[name="needreceipt"]').click(function(){
		if($(this).val()==1){
			$("#invoiceClientdiv").show();
		}else{
			$("#invoiceClientdiv").hide();
		}		
	});
	
	$("#wst-order-details").click(function(){
		$("#wst-orders-box"). toggle(100);
	});
	
	
	$(".wst-payCode").click(function(){
		$(".wst-payCode-curr").removeClass().addClass("wst-payCode");
		$(this).removeClass().addClass("wst-payCode-curr");
		$("#payCode").val($(this).attr("data"));
	});
	
	
	$('input:radio[name="isself"]').click(function(){
		if($(this).val()==0){//送货上门
			$("#totalMoney_span").html($("#totalMoney").val());
			//$("#pay_hd").attr("disabled",false);
			$("[id^=tst_]").val("-1");
			$("[id^=showwarnmsg_]").show();
			$("[id^=deliveryMoney_span_]").each(function(){
				var dvids = $(this).attr("id").split("deliveryMoney_span_");
				$(this).html($("#deliveryMoney_"+dvids[1]).val());
			});
		}else{//自提
			$("#totalMoney_span").html($("#gtotalMoney").val());
			//$("#pay_ali").attr("checked",true);
			//$("#pay_hd").attr("disabled",true);
			$("[id^=tst_]").val("1");
			$("[id^=showwarnmsg_]").hide();
			$("[id^=deliveryMoney_span_]").each(function(){
				var dvids = $(this).attr("id").split("deliveryMoney_span_");
				$(this).html("¥0");
			});
		}
	});
	
});

//判断和计算优惠券
function yhsettlement(val,shopId){
	 
       $.post("/Home/Orders/runYouhui.html",
        {
         val:val,
         shopId:shopId
        },
        function(data,textStatus){
          var json = WST.toJson(data);
          if(json.status=='1'){
           	$('#ytotalMoney_'+shopId).html('¥'+json.ytotalMoney+'&nbsp;&nbsp;');
           	$('#yhprice_'+shopId).css('display','block');
           	$('#totalMoney_span').text(json.zonge);
           	$('#youhuimsg').text(json.msg);
           	$('#youhuimsg').css('display','block');
          }
          if (json.status=='3') {
          	$('#yhprice_'+shopId).css('display','none');
           	$('#totalMoney_span').text(json.zonge);
           	$('#youhuimsg').css('display','none');
           	
           	$('#youhuimsg').html('');

          }
        });
    }


