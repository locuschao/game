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
<script src="/Public/laydate/laydate.js"></script>
	<!--二次开发 引入时间插件-->
<script type="text/javascript" src="/Public/js/jedate.js"></script>
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
   function handle(id){
	   Plugins.confirm({title:'信息提示',content:'您确定此提现已完成吗?',okText:'确定',cancelText:'取消',okFun:function(){
		   Plugins.closeWindow();
		   Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
		   $.post("<?php echo U('Admin/Tixian/psTixianHandle');?>",{id:id},function(data,textStatus){
					var json = WST.toJson(data);
					if(json.status=='0'){
						Plugins.setWaitTipsMsg({content:'操作成功',timeout:800,callback:function(){
							location.reload();
						}});
					}else{
						Plugins.closeWindow();
						parent.showMsg({msg:'操作失败!',status:'danger'});
					}
				});
	      }});
   }

   /*二次开发  添加的js --start*/
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
	<form method='POST' action="<?php echo U('Admin/Tixian/psTixianList');?>">
		<div class='wst-tbar'>
			用户名<input type='text' id='userName' name='userName'
				class='form-control wst-ipt-10' value='<?php echo ($userName); ?>' /> 提现ID<input
				type='text' id='txID' name='txID' class='form-control wst-ipt-10'
				value='<?php echo ($txID); ?>' /> 提现日期：<input class="laydate-icon"
				onclick="laydate()" type='text' id='starDay' name='starDay'
				value='<?php echo ($starDay); ?>' /> - <input class="laydate-icon"
				onclick="laydate()" type='text' id='endDay' name='endDay'
				value='<?php echo ($endDay); ?>' /> 状态<select name="txStatus">
				<option value="0"<?php if($txStatus == 0): ?>selected<?php endif; ?>>待处理
				</option>
				<option value="1"<?php if($txStatus == 1): ?>selected<?php endif; ?>>已同意
				</option>
				<!--  	<option value="-1" <?php if($txStatus == -1): ?>selected<?php endif; ?>>已拒绝</option> -->
			</select>
			<button type="submit"
				class="btn btn-primary glyphicon glyphicon-search">查询</button>
		</div>
	</form>
	<!--二次开发 --start-->
	<div class="dataExp">
		<form action="/index.php/Admin/Tixian/psExp" method="post" id="timeForm">
			<table>
				<tr>
					<td>
						提现时间：
					</td>
					<td><input class="datainp" name="timeStart"  id="datebut" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
					<td>：</td>
					<td><input class="datainp" name="timeEnd" id="datebut1" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
					<td><button type="submit" class="btn btn-primary">导出个人提现数据</button></td>
					<td id="timeMsg"><?php echo ($msg); ?></td>
				</tr>
			</table>
		</form>
	</div>
	<!-- 二次开发 --end -->
	<div class='wst-body'>
		<table class="table table-hover table-striped table-bordered wst-list">
			<thead>
				<tr>
					<th width='30'>提现ID</th>
					<th width='80'>手机号</th>
					<th width='80'>提现人姓名</th>
					<th width='80'>卡号</th>
					<th width='80'>所属银行</th>
					<th width='80'>提现金额</th>
					<th width='80'>提现时间</th>
					<th width='30'>提现状态</th>
					<th width='30'>处理</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($Page['root'])): $i = 0; $__LIST__ = $Page['root'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
					<td><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['userPhone']); ?></td>
					<td><?php echo ($vo['trueName']); ?>&nbsp;</td>
					<td><?php echo ($vo['bankNo']); ?>&nbsp;</td>
					<td><?php echo ($vo['bankName']); ?>&nbsp;</td>
					<td><?php echo ($vo['txMoney']); ?>&nbsp;</td>
					<td><?php echo ($vo['txTime']); ?>&nbsp;</td>
					<td><?php if($vo['txStatus'] == 0): ?>待处理<?php elseif($vo['txStatus'] == 1): ?>已同意<?php else: ?>已拒绝<?php endif; ?></td>
					<td><?php if(in_array('grtx_01',$WST_STAFF['grant'])){ ?> <?php if($vo['txStatus'] == 0): ?><button type="button" class="btn btn-default "
							onclick="javascript:handle(<?php echo ($vo['id']); ?>)">
							完成
							</buttona>
							<?php else: ?>
							已完成<?php endif; ?> <?php } ?> <!--   <button type="button" class="btn btn-default " onclick="javascript:del(<?php echo ($vo['id']); ?>)">刪除</buttona> -->
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