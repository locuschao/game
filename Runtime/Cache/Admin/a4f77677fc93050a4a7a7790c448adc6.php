<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo ($CONF['shopTitle']['fieldValue']); ?>后台管理中心</title>
<link href="/Public/plugins/bootstrap/css/bootstrap.min.css"
	rel="stylesheet">
<link href="/Tpl/Admin/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<link href="/Tpl/Admin/css/upload.css" rel="stylesheet" type="text/css" />

<!--[if lt IE 9]>
      <script src="/Public/js/html5shiv.min.js"></script>
      <script src="/Public/js/respond.min.js"></script>
      <![endif]-->
<script src="/Public/js/jquery.min.js"></script>
<script src="/Public/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/Public/js/common.js"></script>
<script src="/Public/plugins/plugins/plugins.js"></script>
<script src="/Public/plugins/formValidator/formValidator-4.1.3.js"></script>
<script src="/Public/plugins/kindeditor/kindeditor.js"></script>
<script src="/Public/plugins/kindeditor/lang/zh_CN.js"></script>
<script type="text/javascript"
	src="http://webapi.amap.com/maps?v=1.3&key=2b53397482f52f92dcb7c528d99d9fca"></script>
<script type="text/javascript" src="/Tpl/Admin/js/upload.js"></script>

</head>
<style>
img {
	max-width: 100px;
}

.ATRoot {
	height: 22px;
	line-height: 22px;
	margin-left: 5px;
	clear: both;
	cursor: pointer;
}

.ATNode {
	margin-left: 5px;
	line-height: 22px;
	margin-left: 17px;
	clear: both;
	cursor: pointer;
}

.Hide {
	display: none;
}

dl.areaSelect {
	padding: 0 5px;
	display: inline-block;
	width: 100%;
	margin-bottom: 0; /*border:1px solid #eee;*/
}

dl.areaSelect:hover {
	border: 1px solid #E5CD29;
}

dl.areaSelect:hover dd {
	display: block;
}

dl.areaSelect dd {
	float: left;
	margin-left: 20px;
	cursor: pointer;
}

#preview {
	width: 152px;
	height: 152px;
	max-width: 152px;
}

#preview_Filedata img {
	width: 152px;
	height: 152px;
	max-width: 152px;
}
</style>
<script>
   var shopMap = null;
   var toolBar = null;
   var filetypes = ["gif","jpg","png","jpeg"];
   var ThinkPHP = window.Think = {
	        "ROOT"   : ""
	}
   $(function () {
   		//展开按钮
   		$("#expendAll").click(function(){
   			if ($(this).prop('checked')==true) {$("dl.areaSelect dd").removeClass('Hide')}
   			else{$("dl.areaSelect dd").addClass('Hide')}
   		})
	   $.formValidator.initConfig({
		   theme:'Default',mode:'AutoTip',formID:"myform",debug:true,submitOnce:true,onSuccess:function(){
				   edit();
			       return false;
			},onError:function(msg){
		}});
		$("#typeName").formValidator({onShow:"",onFocus:"游戏类型不能超过10个字符",onCorrect:"输入正确"}).inputValidator({min:1,max:20,onError:"游戏类型不符合要求,请确认"});
   });

	//修改结束 2015-4-23
   function edit(){
	   var params = {};
	   params.id = $('#id').val();
	   params.typeName = $('#typeName').val();
	   Plugins.waitTips({title:'信息提示',content:'正在提交数据，请稍后...'});
	   $.post("<?php echo U('Admin/Game/addGameType');?>",params,function(data,textStatus){
			var json = WST.toJson(data);
			if(json.status=='0'){
				Plugins.setWaitTipsMsg({ content:'操作成功',timeout:1000,callback:function(){
				   location.href='<?php echo U("Admin/Game/listType");?>';
				}});
			}else{
				Plugins.closeWindow();
				Plugins.Tips({title:'信息提示',icon:'error',content:'操作失败!',timeout:1000});
			}
		});
   }
   /*function addVersion(){
		var html="<tr>"+$('#gameVersionsHtml').html()+"</tr>";
		$('.gameVersions:last').after(html);
	}
	
	   function del(id){
		   Plugins.confirm({title:'信息提示',content:'您确定要删除该游戏版本吗?',okText:'确定',cancelText:'取消',okFun:function(){
			   Plugins.closeWindow();
			   Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
			   $.post("<?php echo U('Admin/Game/delVersions');?>",{id:id},function(data,textStatus){
						var json = WST.toJson(data);
						if(json.status=='1'){
							Plugins.setWaitTipsMsg({content:'操作成功',timeout:1000,callback:function(){
								location.reload();
							}});
						}else{
							Plugins.closeWindow();
							parent.showMsg({msg:'操作失败!',status:'danger'});
						}
					});
		      }});
	   }*/
   </script>
<body class="wst-page" style="position: relative;">

	<form name="myform" method="post" id="myform" autocomplete="off">
		<input type='hidden' id='id' value='<?php echo ($gameType["typeId"]); ?>' />

		<table class="table table-hover table-striped table-bordered wst-form">
			<tr>
				<th width='150' align='right'>游戏类型名称<font color='red'>*</font>：
				</th>
				<td><input type='text' id='typeName' name='typeName'
					class="form-control wst-ipt" value='<?php echo ($gameType["typeName"]); ?>' maxLength='25' /></td>
			</tr>
			<td colspan='2' style='padding-left: 250px;'>
				<button type="submit" class="btn btn-success">保&nbsp;存</button>
				<button type="button" class="btn btn-primary"
					onclick='javascript:location.href="<?php echo U('Admin/Game/listType');?>"'>返&nbsp;回</button>
			</td>
			</tr>
		</table>
	</form>
</body>
</html>