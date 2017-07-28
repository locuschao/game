<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo ($CONF['mallTitle']); ?>后台管理中心登录</title>
<link href="/Public/plugins/bootstrap/css/bootstrap.min.css"
	rel="stylesheet">
<link rel="stylesheet" href="/Tpl/Admin/css/login.css">
<!--[if lt IE 9]>
      <script src="/Public/js/html5shiv.min.js"></script>
      <script src="/Public/js/respond.min.js"></script>
      <![endif]-->
<script src="/Public/js/jquery.min.js"></script>
<script src="/Public/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/Public/js/common.js"></script>
<script src="/Public/plugins/plugins/plugins.js"></script>
</head>
<script>
   $(function(){
	   $('.form-actions').click(function(){login()});
	   $(document).keypress(function(e) { 
		   if(e.which == 13) {  
			   login();  
		   } 
	   }); 
   })
   function login(){
	   var params = {};
	   params.loginName = $.trim($('#loginName').val());
	   params.loginPwd = $.trim($('#loginPwd').val());
	   params.id = $('#id').val();
	   if(params.loginName==''){
		   Plugins.Tips({title:'信息提示',icon:'error',content:'请输入账号!',timeout:1000});
		   $('#loginName').focus();
		   return;
	   }
	   if(params.loginPwd==''){
		   Plugins.Tips({title:'信息提示',icon:'error',content:'请输入密码!',timeout:1000});
		   $('#loginPwd').focus();
		   return;
	   }
	   Plugins.waitTips({title:'信息提示',content:'正在登录，请稍后...'});
		$.post("<?php echo U('Admin/index/login');?>",params,function(data,textStatus){
			var json = WST.toJson(data);
			if(json.status=='1'){
				Plugins.setWaitTipsMsg({ content:'登录成功',timeout:1000,callback:function(){
					location.href='<?php echo U("Admin/Index/index");?>';
				}});
			}else if(json.status=='-2'){
				Plugins.closeWindow();
				Plugins.Tips({title:'信息提示',icon:'error',content:'IP受限!',timeout:1000});
			}else{
				Plugins.closeWindow();
				Plugins.Tips({title:'信息提示',icon:'error',content:'账号或密码错误!',timeout:1000});
			}
		});
   }
   
   </script>
<body>
	<div id="loginbox">
		<div class='logo'></div>
		<form id="loginform" class="form-vertical" />
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">账号：</span><input type="text" name='loginName'
						id='loginName' placeholder="用户名" />
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">密码：</span><input type="password"
						name='loginPwd' id='loginPwd' placeholder="密码" />
				</div>
			</div>
		</div>
		<div class="form-actions"></div>
		</form>
	</div>
</body>
</html>