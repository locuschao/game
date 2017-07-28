/**
 * @author peng
 * @copyright 2016
 * @remark 加上登录判断
 */

var checkLogin=function(){
    if(!is_login){
        location.href="/Game/Login/login.html";
        return false;
    }else{
        return true;
    }
}


$(function(){
	//全部订单，待发货，待付款，已完成切换
	$('body').on('click','.orderMenu',function(){
		var index=$(this).index();
		$('.orderMenu').removeClass('orderNavActive');
		$(this).addClass('orderNavActive');
		$('.orderList').hide();
		$('.orderList').eq(index).show();
	})

	//点击底部菜单切换 
	$('body').on('click','.footerNav',function(){
        /**
         * @author peng
         * @copyright 2016
         * @remark 加上登录判断
         */
        if(!$(this).is('.goHome')) {
            if(!checkLogin()) return;
        }
        

		var index=$(this).index();
        /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
         if($('#page1').length==0){
            switch(index){
                case 0 :
                location.href='/Game';
                break;
                case 1:
                location.href='/Game/Index/index/r/order';
                break;
                case 2:
                location.href='/Game/Index/index/r/my';
                break;
            }
            
            return;
         }
		/**
		 * 魏永就
		 * 16-12-15
		 * 添加的功能：点击不同的底部菜单，顶栏的右上角切换不同的图片盒链接
		 */
		var countStr;
		if($('#notReadCount').val() == 0)
		{
			countStr = '';
		}else{
			countStr =  '<span class="badge">'+$('#notReadCount').val() + '</span>';
		}
		if(index > 0)
		{
			$('.ps_or_login').after('<div class="mess" onclick="">'+countStr+'</div>');
			$('.mess').attr('onclick','location.href="/Game/Mess/mess/r/Index_index"');
			$('.ps_or_login').remove();
		}else{
			$('.mess').after('<div class="ps_or_login" onclick=""><img src="/Tpl/Game/image/user1.png"></div>');
			$('.ps_or_login').attr('onclick',"location.href='/Game/Index/index/r/my'");
			$('.mess').remove();
		}
		
		$('.footer_title').removeClass('footer_title_active');
		$('.footer_img').each(function(){
			var dimg=$(this).attr('data-default');
			$(this).find('img').attr('src',dimg);
		});
		$(this).find('.footer_img').find('img').attr('src',$(this).find('.footer_img').attr('data-active'));
		$(this).find('.footer_title').addClass('footer_title_active');
		$('.hideDiv').hide();
		$('.hideDiv').eq(index).show();
		$('.titleNav').hide();
		$('.titleNav').eq(index).show();
		if(index==0){
			$('.logo').removeClass('nobg');
			$('.mess').css('width','40px');
		}else{
			$('.mess').css('width','60px');
			$('.logo').addClass('nobg');
		}
	})
	

	$('body').on('click','.orderList .del',function(){
		var id=$(this).attr('data-id');
		layer.confirm('确定删除此订单吗？', {icon: 3, title:'删除订单'}, function(index){
			$.ajax({
				type:'POST',
				dataType:'json',
				data:{oid:id},
				url:'/Game/Orders/delOrder',
				success:function(data){
					if(data.status==0){
						layer.msg('删除成功');
						$('.del_'+id).remove();
					}
				}
			})
			  layer.close(index);
	    });
	})
	
	//加载更多公告
	$('body').on('click','.loadMoreNotice',function(){
	   var page=parseInt($('#noticePage').val());
	   $('#noticePage').val(parseInt(page)+1);
		$.ajax({
			type:'POST',
			dataType:'json',
			data:{page:page},
			url:'/Game/Index/Notice',
			success:function(data){
				if(isDefine(data)){
					if(data.length>0){
						var html='';
						for(var i=0;i<data.length;i++){
							html+='	<a href="/Game/Index/noticeDetail/id/'+data[i].id+'"><div class="ub lh45 back-ff bor-b ti10  "><span class="notice_color">【公告】</span>'+data[i].title+' </div></a>';
						}
					$('.loadMoreNotice').before(html);
					}
				}else{
					$('.loadMoreNotice').hide();
				}
			}
		})
	})
	
})



