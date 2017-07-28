//获取url的值
function request(paras)
    { 
        var url = location.href; 
        var paraString = url.substring(url.indexOf("?")+1,url.length).split("&"); 
        var paraObj = {} 
        for (i=0; j=paraString[i]; i++){ 
        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length); 
        } 
        var returnValue = paraObj[paras.toLowerCase()]; 
        if(typeof(returnValue)=="undefined"){ 
        return ""; 
        }else{ 
        return returnValue; 
        } 
    }
//优惠券页面排序函数
function sort(sort){
	var youhui_type = parseInt(request("youhui_type"));
	var breaks = parseInt(request("breaks"));
	switch(sort)
	{
	case 1:
		window.location.href="/Home/Youhui/getyouhui";
		break;
	case 2:
		window.location.href="/Home/Youhui/getyouhui?breaks=1";
	 	break;
	case 3:
	  	window.location.href="/Home/Youhui/getyouhui?youhui_type=1";
	  break;
	case 4:
		window.location.href="/Home/Youhui/getyouhui?youhui_type=2";	
	  break;
	}
}

//领取优惠券函数
function clickreceive(id,end_time){

			layer.config({
			    extend: 'extend/layer.ext.js'
			});
			layer.confirm('您确定领取该优惠券吗？', {
		  		btn: ['是的','取消'] //按钮
			}, function(){
			var aj = $.ajax( {  
                url:"/Home/Youhui/getyouhuiid.html",
                data:{  
                      id : id,
                      end_time:end_time
                },  
               type:'post', 
               cache:false,   
              success:function(data) {  
              data = eval("("+data+")");
              layer.msg(data.msg);
              	if (data.ceiling==1) {return;}
              setTimeout(function(){
              	if (data.url) {location.href=data.url;}else{location.reload();}
              },1000);
          		},  
          error : function() {   
               alert("异常！");  
          		}  
         });

		  
		});    
       
}