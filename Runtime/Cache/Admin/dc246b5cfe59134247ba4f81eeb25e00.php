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
   function changeHotGiftStatus(id,v){
	   Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
	   $.post("<?php echo U('Admin/Game/changeHotGiftStatus');?>",{id:id,status:v},function(data,textStatus){
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

   //设置是否下架
   function changeShelvesGiftStatus(id,v){
       Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
       $.post("<?php echo U('Admin/Game/changeShelvesGiftStatus');?>",{id:id,status:v},function(data,textStatus){
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
	<form method='POST' action="<?php echo U('Admin/Game/gameGiftList');?>">
		<div class='wst-tbar'>
			游戏礼包名称：<input type='text' id='gameName' name='gameName'
				class='form-control wst-ipt-10' value='' />
			<input type="radio" value="0" id="all" name="isHot" checked> <label for="all">全部</label>
			<input type="radio" value="1" id="yes" name="isHot" ><label for="yes">热门</label>
			<input type="radio" value="2" id="no" name="isHot" ><label for="no">非热门</label>
			<button type="submit"
				class="btn btn-primary glyphicon glyphicon-search">查询</button>
			<?php if(in_array('dplb_01',$WST_STAFF['grant'])){ ?>
			<a class="btn btn-success glyphicon glyphicon-plus"
				href="<?php echo U('Admin/Game/listImportGameData');?>" style='float: right'>新增游戏礼包</a>
			<?php } ?>
		</div>
	</form>
	<div class='wst-body'>
		<table class="table table-hover table-striped table-bordered wst-list">
			<thead>
				<tr>
					<th width='30'>分类ID</th>
					<th width='70'>游戏礼包名称</th>
					<th width='50'>充换积分</th>
					<th width='100'>开始时间</th>
					<th width='100'>结束时间</th>
					<th width='50'>礼包总数</th>
					<th width='50'>礼包剩余数量</th>
					<th width='70'>礼包内容</th>
					<th width='50'>使用说明</th>
					<th width='50'>是否热门</th>
					<th width='50'>是否上架</th>
					<th width='30'>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($Page['root'])): $i = 0; $__LIST__ = $Page['root'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
					<td><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['gameName']); ?></td>
					<td><?php echo ($vo['Integral']); ?></td>
					<td><?php echo ($vo['beginTime']); ?></td>
					<td><?php echo ($vo['endTime']); ?></td>
					<td><?php echo ($vo['totalNumber']); ?></td>
					<td><?php echo ($vo['remainNumber']); ?></td>
					<td><?php echo ($vo['content']); ?></td>
					<td><?php echo ($vo['description']); ?></td>
					<td><?php if($vo['isHot'] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
					<td><?php if($vo['shelves'] == 0): ?>是<?php else: ?>否<?php endif; ?></td>
					<td><?php if($vo['isHot'] == 1): ?><button type="button" class="btn btn-danger "
							onclick="javascript:changeHotGiftStatus(<?php echo ($vo['id']); ?>,2)">取消热门</button>
						<?php else: ?>
						<button type="button" class="btn btn-danger "
							onclick="javascript:changeHotGiftStatus(<?php echo ($vo['id']); ?>,1)">热门</button><?php endif; ?>
						<?php if($vo['shelves'] == 0): ?><button type="button" class="btn btn-danger "
									onclick="javascript:changeShelvesGiftStatus(<?php echo ($vo['id']); ?>,1)">取消上架</button>
							<?php else: ?>
							<button type="button" class="btn btn-danger "
									onclick="javascript:changeShelvesGiftStatus(<?php echo ($vo['id']); ?>,0)">上架</button><?php endif; ?>
						<?php if(in_array('dplb_02',$WST_STAFF['grant'])){ ?> <a
						class="btn btn-default glyphicon glyphicon-pencil"
						href="<?php echo U('Admin/Game/listImportGameData',array('id'=>$vo['id'],'src'=>'index'));?>">导入游戏充换码</a>&nbsp;
						<?php } ?>
					</td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
				<tr>
					<td colspan='11' align='center'><?php echo ($Page['pager']); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>