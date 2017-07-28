 "use strict"
 $(function(){
// 表单验证
	(function(){
		$('.my-form').each(function(){
			var that = $(this),
				id = $(this).attr('id');
			var validate = $("#" + id).validate({
				rules:{
					"info[form_tel]":{
						required:true,
						isMobile:true
					}
				},
				messages:{
					"info[form_tel]":{
						required:"手机号码不能为空!",
						isMobile:"请输入正确手机号码!"
					}
				}
			});
		});
	}());
});