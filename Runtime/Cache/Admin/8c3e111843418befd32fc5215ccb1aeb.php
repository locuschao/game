<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo ($CONF['mallTitle']); ?>后台管理中心</title>
<link href="/Public/plugins/bootstrap/css/bootstrap.min.css"
	rel="stylesheet">
<link href="/Tpl/Admin/css/AdminLTE.css" rel="stylesheet" type="text/css" />
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
<body class='wst-page'>
	<form method='POST' action="<?php echo U('Admin/Game/versionsList');?>">
		<div class='wst-tbar'>
			版本名称：<input type='text' id='gameName' name='gameName'
				class='form-control wst-ipt-10' value='<?php echo ($params["gameName"]); ?>' />
			<button type="submit"
				class="btn btn-primary glyphicon glyphicon-search">查询</button>
			<?php if(in_array('dplb_01',$WST_STAFF['grant'])){ ?>
			<a class="btn btn-success glyphicon glyphicon-plus"
				href="<?php echo U('Admin/Game/versionsEdit');?>" style='float: right'>新增</a>
			<?php } ?>
		</div>
	</form>
	<div class='wst-body'>
		<table class="table table-hover table-striped table-bordered wst-list">
			<thead>
				<tr>
					<th width='30'>版本ID</th>
					<th width='100'>版本名称</th>
					<th width='30'>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($Page['root'])): $i = 0; $__LIST__ = $Page['root'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
					<td><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['vName']); ?></td>
					<td><?php if(in_array('dplb_02',$WST_STAFF['grant'])){ ?> <a
						class="btn btn-default glyphicon glyphicon-pencil"
						href="<?php echo U('Admin/Game/versionsToEdit',array('id'=>$vo['id'],'src'=>'index'));?>">修改</a>&nbsp;
						<?php } ?> <?php if(in_array('dplb_03',$WST_STAFF['grant'])){ ?>
						<button type="button"
							class="btn btn-default glyphicon glyphicon-trash"
							onclick="javascript:del(<?php echo ($vo['id']); ?>)">
							刪除
							</buttona>
							<?php } ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
				<tr>
					<td colspan='11' align='center'><?php echo ($Page['pager']); ?></td>
				</tr>
				<tr>
					<td colspan='11' align='center'><a href="/index.php/Admin/Game/versionsListExp"> <button type="button" class="btn btn-primary">导出游戏版本数据</button></a></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>