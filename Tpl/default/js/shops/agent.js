$(function(){
   
	$('.agent-block').on('click','#bindBtn,#unbind',function(){
	   $('#bindBtn,#unbind').prop('disabled',true).text('处理中···');
       
	   var select=$('.row select option:selected');
	   $.post(__url__+'/doBind',{
	       username:$('#username').val(),
           password:$('#password').val(),
           pay_pwd:$('#pay_pwd').val(),
           versionId:versionId
           //code:$('#code').val()
    	},function(data){
            if(data.info=='验证码错误'){
              $('.verifyImg').trigger('click');
            		
            }
            WST.msg(data.info, {icon: 1});
            $('#bindBtn,#unbind').prop('disabled',false).text('绑定');
            
            if(data.status==1) {
                $('#username').val('');
                $('#password').val('');
                $('#code').val('');
                location.reload();
            }
        },'json');
	})
    
    $('body').on('click','#cancel_apply',function(){
        $('.agent-block').removeClass('apply-mode');
    }).on('click','#apply_update',function(){
        $('.agent-block').addClass('apply-mode');
        
    }).on('click','#apply_submit',function(){
        $.post(__url__+'/applyUpdate',{
            agentId:agentId,
            update_reason:$('#reason').val(),
            versionId:versionId,
            id:id
           
    	},function(data){
    	   
        WST.msg(data.info, {icon: 1});
        $('#bindBtn').prop('disabled',false).text('绑定');
        
        if(data.status==1) {
        
        location.href = '/Home/TTAgent/applyList/umark/TTAgent_applyList.html';
        }
        },'json');
    }).on('click','.show_pwd',function(){
        $(this).hide().prevAll('input').attr('type','text');
        $('.hide_pwd').show();
    }).on('click','.hide_pwd',function(){
        $(this).hide().prevAll('input').attr('type','password');
        $('.show_pwd').show();
    })
    
    if(id!='0') $('#apply_update').trigger('click');
})