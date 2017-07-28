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
	<!--引入时间插件-->
	<script type="text/javascript" src="/Public/js/jedate.js"></script>
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
	   $("#Integral").formValidator({onShow:"",onFocus:"请输入礼包所需积分",onCorrect:"输入正确"});
       $("#beginTime").formValidator({onShow:"",onFocus:"请选择开始时间",onCorrect:"输入正确"});
       $("#endTime").formValidator({onShow:"",onFocus:"请选择结束时间",onCorrect:"输入正确"});
       $("#content").formValidator({onShow:"",onFocus:"请输入礼包内容",onCorrect:"输入正确"});
   });
 //模仿百度输入框 author byMeke
   $(function(){
       $("#gameName").bind('input propertychange', function() {
           var div = '';
           var params = {};
           params.gameName=$('#gameName').val();
           $.post("<?php echo U('Admin/Game/selectGame');?>",params,function(data,textStatus){
               div +="<select class='select_id' name='id' onchange='changeValue()' >";
               div +="<option>请选择</option>";
               $.each(JSON.parse(data), function(key,value) {
				   div +="<option class='form-control wst-ipt' maxLength='25'  value ="+value.id+"  >"+value.gameName+"</option>";
               });
               div +="</select>";
               $('.addgame').html(div);
           });
	   })

   })
	function changeValue(){
        $(".gameId").val('');
        var checkText = $(".select_id").find("option:selected").text();
        var       val = $(".select_id").find("option:selected").val();

        $("#gameName").val(checkText);
        $(".gameId").val(val);
        $('.addgame').empty();
	}

   //处理日期插件 2017-7-20 author Meke
   $(function () {
       $('.datainp').click(function () {
           $(this).attr('id','datebut');
           jeDate({dateCell:'#datebut',isTime:true,format:'YYYY-MM-DD hh:mm:ss'});
           $(this).attr('id','');
       });
   });
  //导出示例代码 2017-7-22 author Meke
   function importExcel(){
     location.href="/index.php/Admin/Game/importExcel/id/0";
   }

   </script>
<body class="wst-page" style="position: relative;">
	<iframe name="upload" style="display: none"></iframe>

	<form name="myform" action="/index.php/Admin/Game/importGameData" method="post"  enctype="multipart/form-data">
		<table class="table table-hover table-striped table-bordered wst-form">
			<tr >
				<th width='150' align='right'>游&nbsp;&nbsp;&nbsp;戏&nbsp;&nbsp;&nbsp;名<font color='red'>*</font>：
				</th>
				<td><input type='text' id='gameName' name='gameName' class="form-control wst-ipt" value='<?php echo ($object["gameName"]); ?>'<?php if($object['shelves']): ?>readonly="readonly"<?php endif; ?>  maxLength='25' />
					<input type='hidden' class="gameId" name='gameId' class="form-control wst-ipt" value=''  maxLength='25' />
					<input type='hidden'  name='typeId' class="form-control wst-ipt" value='<?php echo ($object["id"]); ?>'  maxLength='25' />
                    <div class="addgame"></div>
				</td>
			</tr>
			<tr>
				<th width='150' align='right'>导入游戏充换码<font color='red'>*</font>：</th>
				<td>
					<input type="file" id="upload" name="file" />
					<button type="button" class="btn btn-success" onclick="importExcel()">导出示例Excel</button>
				</td>
			</tr>
			<tr>
				<th width='150' align='right'>所需积分<font color='red'>*</font>：</th>
				<td><input type="text" id="Integral" name="Integral" value="<?php echo ($object["Integral"]); ?>" /></td>
			</tr>
			<tr>
				<th width='150' align='right'>开始时间<font color='red'>*</font>：</th>
				<td><input class="datainp" id="beginTime"  type='text'  placeholder="请选择"  name="beginTime" value="<?php echo ($object["beginTime"]); ?>" /></td>
			</tr>
			<tr>
				<th width='150' align='right'>结束时间<font color='red'>*</font>：</th>
				<td><input class="datainp" id="endTime"  type='text'  placeholder="请选择"  name="endTime" value="<?php echo ($object["endTime"]); ?>" /></td>
			</tr>
			<tr>
				<th width='150' align='right'>是否上架<font color='red'>*</font>：
				</th>
				<td><label><input type='radio'  name='shelves' value="0"<?php if($object['shelves'] == 0): ?>checked="checked"<?php endif; ?>/>是</label>
					<label><input type='radio'  name='shelves' value="1"<?php if($object['shelves'] == 1): ?>checked="checked"<?php endif; ?>/>否</label>
				</td>
			</tr>
			<tr>
				<th width='150' align='right'>是否热门<font color='red'>*</font>：
				</th>
				<td><label><input type='radio'  name='isHot' value="1"<?php if($object['isHot'] == 1): ?>checked="checked"<?php endif; ?> />是</label>
					<label><input type='radio'  name='isHot' value="2"<?php if($object['isHot'] == 2): ?>checked="checked"<?php endif; ?> />否</label>
				</td>
			</tr>
			<tr>
				<th width='150' align='right'>礼包内容<font color='red'>*</font>：</th>
				<td><textarea name="content" id="content" cols=40 rows=4><?php echo ($object["content"]); ?></textarea></td>
			</tr>
			<tr>
				<th width='150' align='right'>使用说明<font color='red'>*</font>：</th>
				<td><textarea name="description"  cols=40 rows=4><?php echo ($object["description"]); ?></textarea></td>
			</tr>
			<td colspan='2' style='padding-left: 250px;'>
				<button type="submit" class="btn btn-success">导入礼包充换码</button>
				<button type="button" class="btn btn-primary" onclick='history.go(-1);'>返&nbsp;回</button>
			</td>
			</tr>
		</table>
	</form>
</body>
</html>