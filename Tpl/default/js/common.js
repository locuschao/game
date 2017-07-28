$.fn.TabPanel = function(options){
	var defaults = {    
		tab: 0      
	}; 
	var opts = $.extend(defaults, options);
	var t = this;
	$(t).find('.wst-tab-nav li').click(function(){
		$(this).addClass("on").siblings().removeClass();
		var index = $(this).index();
		$(t).find('.wst-tab-content .wst-tab-item').eq(index).show().siblings().hide();
		if(opts.callback)opts.callback(index);
	});
	$(t).find('.wst-tab-nav li').eq(opts.tab).click();
}

$(function() {
	
	//head 弹出菜单部分
    var cateMenu2 = function () {
        var cateLiNum = $(".cateMenu2 li").length;
        $(".cateMenu2 li").each(function (index, element) {
            if (index < cateLiNum - 1) {
                $(this).mouseenter(function () {
                    var ty = $(this).offset().top - 200;
                    var obj = $(this).find(".list-item");
                    var sh = document.documentElement.scrollTop || document.body.scrollTop;
                    var oy = ty + (obj.height() + 30) + 158 - sh;
                    var dest = oy - $(window).height()
                    if (oy > $(window).height()) {
                        ty = ty - dest - 10;
                    }
                    if (ty < 0) ty = 0;
                    // $(this).addClass("on");
                    obj.show();
                    $(".cateMenu2 li").find(".list-item").stop().animate({ "top": ty });
                    obj.stop().animate({ "top": ty });
                    // $(".wst-nvgbk").css("top",index*60);
                    // $(".wst-nvgbk").show();
                })
                $(this).mouseleave(function () {
                    // $(this).removeClass("on");
                    $(this).find(".list-item").hide();
                    $(".wst-nvgbk").hide();
                })
            }
        });

        $(".navCon_on").hover(function () {
            $(".cateMenu2").show();
        },
		function () {
		    $(".cateMenu2").hide();
		})

    } ();
    
    $("#wst-nvg-cat-box").hover(function() {
    	$(".wst-nvg-cat-gt6").show();
    	$(".wst-nvg-cat-dw").hide();
	}, function() {
		$(".wst-nvg-cat-gt6").hide();
		$(".wst-nvg-cat-dw").show();
	});
    
	$("#wst-nvg-cart").mouseover(function(){
		checkCart();
	});
	$("#wst-nvg-cart").click(function(){
		$(".wst-cart-box").toggle();
	});
	
	$(".wst-cart-box").hover(function() {
		
	}, function() {
		$(".wst-cart-box").hide();
	});
	
	$("#wst-panel-goods").click(function(){
		$("#wst-search-type").val(1);
		$("#wst-panel-goods").css({"background-color":"#fe3689","border":"1px solid #fe3689","color":"#ffffff"});
		$("#wst-panel-shop").css({"background-color":"#F3F3F3","border":"0","color":"#000000"});
		$("#keyword").val("");
		$("#keyword").attr("placeholder","搜索 商品");
	});
	$("#wst-panel-shop").click(function(){
		$("#wst-search-type").val(2);
		$("#wst-panel-shop").css({"background-color":"#fe3689","border":"1px solid #fe3689","color":"#ffffff"});
		$("#wst-panel-goods").css({"background-color":"#F3F3F3","border":"0","color":"#000000"});
		$("#keyword").val("");
		$("#keyword").attr("placeholder","搜索 店铺");
	});
	
	var view = $(window);
	var scrollTimer, resizeTimer, minWidth = 1350;
	function resizeHandler(){
		clearTimeout(scrollTimer);
		scrollTimer = setTimeout(checkScroll, 10);
	}
	
	function checkScroll(){
		if(view.scrollTop()>500){
			if(!$("#mainsearchbox").hasClass("wst-fixedsearch")){
				$("#wst-search-type-box").hide();
				$("#wst-hotsearch-keys").hide();
				$("#wst-logo").height(60);
				$("#wst-logo").css({"margin-top":"0"});
				$("#wst-logo").css({"margin-left":"0"});
				$("#wst-searchbox").css({"margin-top":"10px"});
				$("#wst-search-des-container .des-box").css({"margin-top":"10px"});
				$("#mainsearchbox").addClass("wst-fixedsearch").height(0).animate({height:60},300);
			}
		} else{
			if($("#wst-logo").height()<132){
				//$("#mainsearchbox").removeClass("wst-fixedsearch").animate({height:0},1000);
				$("#wst-search-type-box").show();
				$("#wst-hotsearch-keys").show();
				$("#wst-logo").height(60);
				$("#wst-logo").css({"margin-top":"30px"});
				$("#wst-logo").css({"margin-left":"35px"});
				$("#wst-searchbox").css({"margin-top":"60px"});
				$("#wst-search-des-container .des-box").css({"margin-top":"50px"});
				$("#mainsearchbox").removeClass("wst-fixedsearch");
			}
		}
	}
	view.bind('scroll', resizeHandler);

});

function checkCart(){
	jQuery.post( Think.U('Home/Cart/getCartInfo') ,{"axm":1},function(data) {
		var cart = WST.toJson(data);	
		var html = new Array();
		var flag = false;
		var goodsnum = 0;
		console.log(eval(cart));
		for(var shopId in cart.cartgoods){
			var shop = cart.cartgoods[shopId];
			for(var goodsId in shop.shopgoods){
				var goods = shop.shopgoods[goodsId];
				//if(i<cart.cartgoods.length-1){
					html.push("<div style='border-bottom:1px dotted #E13335'>");
				//}else{
					//html.push("<div>");
				//}【{$goods['attrName']}:{$goods['attrVal']}】
				var url = Think.U('Home/Goods/getGoodsDetails','goodsId='+goods.goodsId);
				html.push(  "<div style='float:left;'>" +
									"<a href='"+url+"'><img src='"+domainURL +"/"+goods.goodsThums+"' width='65' height='65'/></a>" +
									"</div>" +
							"<div style='float:left;width:280px;padding:4px;overflow: hidden;'>");
				if(goods.isGroup==1){
					html.push(  "<a target='_blank' href='"+url+"'>"+goods.goodsName+"</a><span style='color:#e6126a'>【团购】</span><br/>");
				}else{
					html.push(  "<a target='_blank' href='"+url+"'>"+goods.goodsName+"</a><br/>");
				}
				var shopPrice = 0;
				for(var p in goods){
					if(!isNaN(p)){
						if(goods[p].attrName){
							html.push(  goods[p].attrName+"："+goods[p].attrVal+"<br/>");
						}
						shopPrice += parseInt(goods[p].shopPrice);
					}
				}
				if(goods.isGroup==0){
					if(shopPrice!=0){
						goods.shopPrice = shopPrice;
					}
				}
				html.push( "￥"+goods.shopPrice+"元&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;数量："+goods.cnt+
							"</div><div style='clear:both;'></div>" +
							"</div>"
						);
				goodsnum++;
			}
			flag = true;
		}
	
		if(flag){
			html.push(  "<div id='wst-topay' style='text-align:right;margin-top:2px;'><li onclick='topay();'></li></div>");
			$(".wst-nvg-cart-cnt").html(goodsnum);
			$(".wst-nvg-cart-price").html(cart.totalMoney);
			$(".wst-cart-box").html(html.join(""));
		}else{
			
			$(".wst-nvg-cart-cnt").html("0");
			$(".wst-nvg-cart-price").html("0.00");
			$(".wst-cart-box").html("<div style='line-height:100px;text-align:center;font-size:18px;'>购物车中暂无商品</div>");
		}
	});
}
function topay(){
	location.href = Think.U('Home/Cart/getCartInfo','rnd='+Math.round(Math.random()*10000000));
}

function toChangeCity(){
	location.href = Think.U('Home/Index/changeCity');
}

function changeCity(areaId2){
	areaId2 = (areaId2>0)?areaId2:$("#cityId").val();
	
	jQuery.post( Think.U('Home/Index/reChangeCity') ,{"city":areaId2,"changeCity":true},function(data) {
		var currurl = location.href;
			currurl = currurl.toLowerCase();
		if(currurl.indexOf("changecity")!=-1){
			location.href = domainURL +"/index.php";
		}else{
			location.href = location.href;
		}
	});
	
}


function addToFavorite(){
	var a=domainURL ,b="收藏"+wstMallName;
	document.all?window.external.AddFavorite(a,b):window.sidebar&&window.sidebar.addPanel?window.sidebar.addPanel(b,a,""):alert("\u5bf9\u4e0d\u8d77\uff0c\u60a8\u7684\u6d4f\u89c8\u5668\u4e0d\u652f\u6301\u6b64\u64cd\u4f5c!\n\u8bf7\u60a8\u4f7f\u7528\u83dc\u5355\u680f\u6216Ctrl+D\u6536\u85cf\u672c\u7ad9\u3002"),createCookie("_fv","1",30,"/;domain="+domainURL)
}


function getSearchInfo(obj,event){
	var keywords = $.trim($(obj).val());
	var vdata = $(obj).attr("data");
	var lsobjId= vdata+"_list"
		
	if(event.which == 38 || event.which == 40) {
		var len = $("."+vdata+"_op").length;
		if(len>0){
			var currobj = null;
			var currIdx = $("."+vdata+"_op_curr").index();
			if(currIdx>=0){//有當前選擇項
				if(event.which == 40){//下
					$("."+vdata+"_op_curr").removeClass().addClass(vdata+"_op").css({"background-color":"#ffffff"});
					currobj = $("."+vdata+"_op")[currIdx+1];
					$(currobj).removeClass().addClass(vdata+"_op_curr").css({"background-color":"#E9E5E1"});
				}else{//上						
					$("."+vdata+"_op_curr").removeClass().addClass(vdata+"_op").css({"background-color":"#ffffff"});
					currobj = $("."+vdata+"_op")[currIdx-1];
					$(currobj).removeClass().addClass(vdata+"_op_curr").css({"background-color":"#E9E5E1"});
				}
			}else{//當前沒有選擇項
				currobj = $("."+vdata+"_op")[0];
				$(currobj).removeClass().addClass(vdata+"_op_curr").css({"background-color":"#E9E5E1"});
			}
			$(obj).val($(currobj).html());
		}	
	}else if(event.which == 13){		
		optSelect();
	}else{			
		var params = {};
		params.keywords = keywords;
		if(keywords.length<1){		
			$("#"+lsobjId).hide();
			$("#"+lsobjId).html("");
			return;
		}
		var searchType = $("#wst-search-type").val();
		var surl = Think.U('Home/Goods/getKeyList');
    	if(searchType==2){
    		surl = Think.U('Home/Shops/getKeyList');
    	}
		$.post(surl,params,function(rsp){
			var json = WST.toJson(rsp);
			if(json.length>0){
				var html = new Array();
				for(var i=0;i<json.length;i++){		
					html.push("<div style='cursor:pointer;line-height:30px;padding-left:4px;' class='"+vdata+"_op' data='"+vdata+"' onmouseover='optOver(this);' onclick='optSelect(this)'>"+json[i].searchKey+"</div>");
					$("#"+lsobjId).show();
					$("#"+lsobjId).html(html.join(""));
				}
			}else{
				$("#"+lsobjId).hide();
				$("#"+lsobjId).html("");
				return;
			}			
		});	
	}
}

function removeOpt(obj){
	$(obj).parent().remove();
}

function optOver(obj){	
	var vdata = $(obj).attr("data");
	$("."+vdata+"_op_curr").removeClass().addClass(vdata+"_op").css({"background-color":"#ffffff"});
	$(obj).removeClass().addClass(vdata+"_op_curr").css({"background-color":"#E9E5E1"});
	$("#keyword").val($(obj).html());
}

function optSelect(obj){
	$("#btnsch").click();
}



/*************************************用户操作*****************************************/
function login(){
	return location.href= Think.U('Home/Users/login');
}
function logout(){
	jQuery.post(Think.U('Home/Users/logout'),{},function(rsp) {
		location.reload();
	});
}
function regist(){
	return location.href= Think.U('Home/Users/regist');
}
function createCookie(a,b,c,d){
	var d=d?d:"/";
	if(c){
		var e=new Date;
		e.setTime(e.getTime()+1e3*60*60*24*c);
		var f="; expires="+e.toGMTString()
	}else {
		var f="";
	}		
	document.cookie=a+"="+b+f+"; path="+d
}

//刷新验证码
function getVerify() {
    $('.verifyImg').attr('src',Think.U('Home/Users/getVerify','rnd='+Math.random()));
}
function checkLogin(){
	jQuery.post( Think.U('Home/Shops/checkLoginStatus') ,{},function(rsp) {
		var json = WST.toJson(rsp);
		console.log(json.status);
		if(json.status && json.status==-999)location.reload();
	});
}
function createCookie(a,b,c,d){
	var d=d?d:"/";
	if(c){
		var e=new Date;
		e.setTime(e.getTime()+1e3*60*60*24*c);
		var f="; expires="+e.toGMTString()
	}else {
		var f="";
	}		
	document.cookie=a+"="+b+f+"; path="+d
}

//添加广告访问量
function addAccess(aid){
	$.post(Think.U('Home/Index/access'),{id:aid},function(data,textStatus){
		
	});
}


//上傳文件

function getUploadFilename(sfilename,srcpath,thumbpath,fname){
	if(srcpath!="fail"){
		$("#s_"+sfilename).val(srcpath);
		$("#"+fname).val(srcpath);
		if(fname=="goodsImg"){
			$("#goodsThumbs").val(thumbpath);
		}
		$("#preview_"+sfilename+" img").attr("src",ThinkPHP.ROOT+"/"+thumbpath);
		$("#preview_"+sfilename+" img").show();
	}else{
		$("#s_"+sfilename).val("");
		$("#"+fname).val("");
	}
}

function updfile(filename){
	
	var filepath = jQuery("#"+filename).val();
	var patharr = filepath.split("\\");
	var fnames = patharr[patharr.length-1].split(".");
	var ext = (fnames[fnames.length-1]);
		ext = ext.toLocaleLowerCase();	
	var flag = false;
	for(var i=0;i<filetypes.length;i++){
		if(filetypes[i]==ext){
			flag = true;
			break;
		}
	}
	
	if(flag){	
		jQuery("#uploadform_"+filename).submit();
	}else{		
		WST.msg("上传文件类型错误 (文档支持格式："+filetypes.join(",")+")", {icon: 5});		
		jQuery('#uploadform_'+filename)[0].reset();
		return;
	}	
}
function uploadFile(opts){
	var _opts = {};
	_opts = $.extend(_opts,{auto: true,swf: publicurl +'/plugins/webuploader/Uploader.swf'},opts);
	var uploader = WebUploader.create(_opts);
	uploader.on('uploadSuccess', function( file,response ) {
	    var json = WST.toJson(response._raw);
	    if(_opts.callback)_opts.callback(json);
	});
	uploader.on('uploadError', function( file ) {
		WST.msg('上传失败!', {icon: 5});
	});
	uploader.on( 'uploadProgress', function( file, percentage ) {
		if(_opts.progress)_opts.progress(percentage);
	});
}

//导航修改
$(document).ready(function(){
	$(".navCon-cate-title,#wst-nvg-cat-box").hover(function(){
		$("#wst-nvg-cat-box").show();
		$(".navCon-cate-title img").addClass("img180");
	},function(){
		$("#wst-nvg-cat-box").hide();
		$(".navCon-cate-title img").removeClass("img180");	
	})

	$(".navleft ul li").hover(function(){
		$(this).children('a').css('color','#ffffff');
		var img1= $(this).children('span').css('background-image');
		img2=img1.split('.')[1]+'.'+img1.split('.')[2];
		// $(this).children('span').css('background-image','url(www.'+img2+'2.png")');
		$(this).children('em').css('background-image','url('+img0+'/navjiantou2.png)');
	},function(){
		$(this).children('a').css('color','#333333');
		// $(this).children('span').css('background-image','url(www.'+img2+'.png")');
		$(this).children('em').css('background-image','url(/Tpl/default/images/navjiantou.png)');
	})
	$(document).scroll(function(){
			var top = $(document).scrollTop();
			if(top < 1000){
				$(".navleft").css('position','absolute');
				$(".navleft").css('top','1225px');}
			if(top > 1000){
				$(".navleft").css('position','fixed');
				$(".navleft").css('top','45%');
				}

		});

})

