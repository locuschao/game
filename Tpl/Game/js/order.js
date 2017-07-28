/**
 * 
 */
function attention(_this){
	layer.msg('正在操作');
	var url=$(_this).attr('data-url');
	var login=$(_this).attr('data-login');
	var id=$(_this).attr('data-id');
	   $.ajax({
           type: "POST",
           url: url,
           data: {goodsId:id},
           dataType: "json",
           success: function(data){
        	    if(data.status==0){
        	    	layer.msg('操作成功');
        	    	//tip('操作成功',1,2,0);
        	    	  if($('#attention').hasClass('wxin')){
              	    	$('#attention').removeClass('wxin');
              	    	$('#attention').addClass('wxins');
              	    }else{
              	    	$('#attention').removeClass('wxins');
              	    	$('#attention').addClass('wxin');
              	    }
        	    }else if(data.status==-2){
        	    	layer.msg('请先登录');
        	    	setTimeout(function(){
        	    		location.href=login;
        	    	},1000);
        	    }else if(data.status==-1){
        	    	layer.msg('网络超时');
        	    }
         }
       });
}