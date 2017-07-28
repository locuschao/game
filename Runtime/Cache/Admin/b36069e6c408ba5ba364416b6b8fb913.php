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
	margin-top: 20px;
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
		$("#gameName").formValidator({onShow:"",onFocus:"游戏名称不能超过20个字符",onCorrect:"输入正确"}).inputValidator({min:1,max:20,onError:"游戏名称不符合要求,请确认"});
		$("#versions").formValidator({onShow:"",onFocus:"请输入游戏版本",onCorrect:"输入正确"}).inputValidator({min:1,max:20,onError:"游戏版本不能为空,请确认"});
   });
 

	//修改结束 2015-4-23
   function edit(){
	
	   var params = {};
	   params.id = $('#id').val();
	   params.gameName = $('#gameName').val();
	   var version='';
	   $('.versions').each(function(){
		   	if($(this).is(":checked")){
		   		version+=$(this).val()+',';
		   	}
	   })
	   version=version.substring(0,version.length-1);
	   params.gameIco = $('#adFile').val();
	   params.downLoadUrl = $('#downLoadUrl').val();
	   params.version = version;
	   params.isHot =$('input:radio[name=isHot]:checked').val();
	   //2017-7-19 增加了一个所属分类
       var gameType='';
       $('.gameType').each(function(){
           if($(this).is(":checked")){
               gameType+=$(this).val()+',';
           }
       })
       gameType=gameType.substring(0,gameType.length-1);
       params.gameType =gameType;
	   //拒绝的时候不用上传
	   if(params.gameIco=='' && params.shopStatus!=-1 ){
		   Plugins.Tips({title:'信息提示',icon:'error',content:'请上传图片!',timeout:1000});
		   return;
	   }
	   Plugins.waitTips({title:'信息提示',content:'正在提交数据，请稍后...'});
	   $.post("<?php echo U('Admin/Game/ajaxEdit');?>",params,function(data,textStatus){
			var json = WST.toJson(data);
			if(json.status=='0'){
				Plugins.setWaitTipsMsg({ content:'操作成功',timeout:1000,callback:function(){
					history.go(-1);
				   //location.href='<?php echo U("Admin/Game/gameList");?>';
				}});
			}else{
				Plugins.closeWindow();
				Plugins.Tips({title:'信息提示',icon:'error',content:'操作失败!',timeout:1000});
			}
		});
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
	   }
	
	
   </script>
<body class="wst-page" style="position: relative;">
	<iframe name="upload" style="display: none"></iframe>
	<form id="uploadform_Filedata" autocomplete="off"
		style="top: 70px; position: absolute; left: 160px; z-index: 10;"
		enctype="multipart/form-data" method="POST" target="upload"
		action="<?php echo U('Home/Shops/uploadPic');?>">
		<div style="position: relative;">
			<input id="adFile" name="adFile" class="form-control wst-ipt"
				type="text" value="<?php echo ($object["gameIco"]); ?>" readonly
				style="margin-right: 4px; float: left; margin-left: 8px; width: 250px;" />
			<div class="div1">
				<div class="div2">浏览</div>
				<input type="file" class="inputstyle" id="Filedata" name="Filedata"
					onchange="updfile('Filedata');">
			</div>
			<div style="clear: both;"></div>
			<div>&nbsp;图片大小:80 x 80 (px)(格式为 gif, jpg, jpeg, png)</div>
			<input type="hidden" name="dir" value="gamecate"> <input
				type="hidden" name="width" value="1400"> <input
				type="hidden" name="folder" value="Filedata"> <input
				type="hidden" name="sfilename" value="Filedata"> <input
				type="hidden" name="fname" value="adFile"> <input
				type="hidden" id="s_Filedata" name="s_Filedata" value="">
		</div>
	</form>


	<form name="myform" method="post" id="myform" autocomplete="off">
		<input type='hidden' id='id' value='<?php echo ($object["id"]); ?>' />

		<table class="table table-hover table-striped table-bordered wst-form">
			<tr>
				<th width='150' align='right'>游戏名<font color='red'>*</font>：
				</th>
				<td><input type='text' id='gameName' name='gameName'
					class="form-control wst-ipt" value='<?php echo ($object["gameName"]); ?>'
					maxLength='25' /></td>
			</tr>
			<tr>
				<th width='150' align='right'>是否是热门游戏<font color='red'>*</font>：
				</th>
				<td><label><input type='radio' id='isHot' name='isHot' value="1"<?php if($object['isHot'] == 1): ?>checked="checked"<?php endif; ?>/>是</label>
					<label><input type='radio' id='isHot' name='isHot' value="0"<?php if($object['isHot'] == 0): ?>checked="checked"<?php endif; ?>/>否</label></td>
			</tr>
			<tr style="height: 180px;">
				<th align='right'>游戏图标<font color='red'>*</font>：
				</th>
				<td>
					<div id="preview_Filedata">
						<img id='preview' src='/<?php echo ($object["gameIco"]); ?>'
							style="width: 90px; height: 90px;"
						<?php if($object['gameIco'] =='' ): ?>style='display:none'<?php endif; ?>
						/>
					</div>
				</td>
			</tr>
			<tr class="gameVersions">
				<th align='right'>游戏版本<font color='red'>*</font>：
				</th>
				<td><?php if(is_array($object['allVersions'])): $i = 0; $__LIST__ = $object['allVersions'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><label><input
						class="versions" type="checkbox" value="<?php echo ($vo["id"]); ?>"
					<?php if($vo['checked'] == 1): ?>checked<?php endif; ?> /><?php echo ($vo["vName"]); ?> </label><?php endforeach; endif; else: echo "" ;endif; ?></td>
			</tr>
			<tr>
				<th width='150' align='right'>游戏类型<font color='red'>*</font>：
				</th>
				<td><?php if(is_array($object['allGameType'])): $i = 0; $__LIST__ = $object['allGameType'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($i % 2 );++$i;?><label><input
						class="gameType"  type="checkbox" value="<?php echo ($vv["typeId"]); ?>" <?php if($vv['checked'] == 1): ?>checked<?php endif; ?> /><?php echo ($vv["typeName"]); ?> </label><?php endforeach; endif; else: echo "" ;endif; ?></td>
			</tr>
			<tr class="gameVersions">
				<th align='right'>官网下载地址<font color='red'>*</font>：
				</th>
				<td><input type="text" style="width:70%;" id="downLoadUrl" name="downLoadUrl" value="<?php echo ($object['downLoadUrl']); ?>"></td>
			</tr>
			<td colspan='2' style='padding-left: 250px;'>
				<button type="submit" class="btn btn-success">保&nbsp;存</button>
				<button type="button" class="btn btn-primary" onclick='history.go(-1);'>返&nbsp;回</button>
			</td>
			</tr>
		</table>
	</form>
</body>
</html>