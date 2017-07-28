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
	<style>
		/*二次开发 新增样式 start*/
		.dataExp{
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.dataExp table tr td{
			padding-right: 5px;
		}
		/*二次开发 新增样式 end*/
	</style>
</head>
<script>
   function del(id){
	   Plugins.confirm({title:'信息提示',content:'您确定要删除该游戏分类吗?',okText:'确定',cancelText:'取消',okFun:function(){
		   Plugins.closeWindow();
		   Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
		   $.post("<?php echo U('Admin/Game/del');?>",{id:id},function(data,textStatus){
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

   
	//设置热门
   function changeHotStatus(id,v){
	   Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
	   $.post("<?php echo U('Admin/Game/changeHotStatus');?>",{id:id,status:v},function(data,textStatus){
				var json = WST.toJson(data);
				if(json.status=='0'){
					Plugins.setWaitTipsMsg({content:'操作成功',timeout:1000,callback:function(){
					    location.reload();
					}});
				}else{
					Plugins.Tips({title:'信息提示',icon:'error',content:'操作失败!',timeout:1000});
				    location.reload();
				}
	   });
   }
   /*二次开发  添加的js --start*/
   function isNull(data){
	   return (data == "" || data == undefined || data == null) ?  true: false;
   }
   $(function () {
	   $('.datainp').click(function () {
		   if($(this).attr('id') != 'datebut')
		   {
			   $('#datebut').attr('id','datebut1');
			   $(this).attr('id','datebut');

		   }
		   jeDate({dateCell:'#datebut',isTime:true,format:'YYYY-MM-DD hh:mm:ss'});
	   });
   });
   /*二次开发 添加的js  --end  */
   </script>
<body class='wst-page'>
	<form method='POST' action="<?php echo U('Admin/Game/gameList');?>">
		<div class='wst-tbar'>
			游戏名称：<input type='text' id='gameName' name='gameName'
				class='form-control wst-ipt-10' value='<?php echo ($params["gameName"]); ?>' />
			<button type="submit"
				class="btn btn-primary glyphicon glyphicon-search">查询</button>
			<?php if(in_array('dplb_01',$WST_STAFF['grant'])){ ?>
			<a class="btn btn-success glyphicon glyphicon-plus"
				href="<?php echo U('Admin/Game/toEdit');?>" style='float: right'>新增</a>
			<?php } ?>
		</div>
	</form>
	<!--二次开发 --start-->
	<div class="dataExp">
		<form action="/index.php/Admin/Game/dataExp" method="post" id="timeForm">
			<table>
				<tr>
					<td>
						游戏类型：
					</td>

					<td>
						<input type="radio" value="0" id="all" name="type" checked> <label for="all">全部</label>
						<input type="radio" value="1" id="yes" name="type" ><label for="yes">热门</label>
						<input type="radio" value="2" id="no" name="type" ><label for="no">非热门</label>
					</td>

					<td><button type="submit" class="btn btn-primary">导出游戏列表数据</button></td>
					<td id="timeMsg"><?php echo ($Msg); ?></td>
				</tr>
			</table>
		</form>
	</div>
	<!-- 二次开发 --end -->
	<div class='wst-body'>
		<table class="table table-hover table-striped table-bordered wst-list">
			<thead>
				<tr>
					<th width='30'>分类ID</th>
					<th width='100'>游戏名称</th>
					<th width='100'>图标</th>
					<th width='100'>是否热门</th>
					<th width='100'>游戏类型</th>
					<th width='30'>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($Page['root'])): $i = 0; $__LIST__ = $Page['root'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
					<td><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['gameName']); ?></td>
					<td><img src="/<?php echo ($vo['gameIco']); ?>" width='50' height='50' /></td>
					<td><?php if($vo['isHot'] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
					<td>
						<?php
 foreach($vo['gameType'] as $v){ echo "<span class='btn btn-primary'>".$arr[$v]."</span>"."  ";} ?>
					</td>
					<td><?php if($vo['isHot'] == 1): ?><button type="button" class="btn btn-danger "
							onclick="javascript:changeHotStatus(<?php echo ($vo['id']); ?>,0)">取消热门</button>
						<?php else: ?>
						<button type="button" class="btn btn-danger "
							onclick="javascript:changeHotStatus(<?php echo ($vo['id']); ?>,1)">设为热门</button><?php endif; ?> <?php if(in_array('dplb_02',$WST_STAFF['grant'])){ ?> <a
						class="btn btn-default glyphicon glyphicon-pencil"
						href="<?php echo U('Admin/Game/toEdit',array('id'=>$vo['id'],'src'=>'index'));?>">修改</a>&nbsp;
						<?php } ?> <!--<?php if(in_array('dplb_03',$WST_STAFF['grant'])){ ?>
						<button type="button"
							class="btn btn-default glyphicon glyphicon-trash"
							onclick="javascript:del(<?php echo ($vo['id']); ?>)">
							刪除
							</button>
							<?php } ?>--></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
				<tr>
					<td colspan='11' align='center'><?php echo ($Page['pager']); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>