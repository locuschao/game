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
<script src="/Public/plugins/formValidator/formValidator-4.1.3.js"></script>
<script src="/Public/plugins/plugins/plugins.js"></script>
<script src="/Public/laydate/laydate.js"></script>
	<!--二次开发 引入时间插件-->
<script type="text/javascript" src="/Public/js/jedate.js"></script>
<style type="text/css">
.clickBtn {
	padding: 5px;
	border-radius: 4px;
	cursor: pointer;
	margin-left: 10px;
	background-color: #fafafa;
	border: #ddd solid 1px;
	color: #666;
	background-color: #fafafa;
}

.wst-list thead tr th {
	text-align: center;
	border: #f2f2f2 solid 1px;
}

.wst-list tbody tr td {
	padding: 6px;
	text-align: center;
	border: #f2f2f2 solid 1px;
}

.processed {
	color: #fff;
	background: #f00;
}
/*二次开发 新增样式 start*/
.dataExp{
	margin-bottom: 10px;
	margin-top: 10px;
	margin-left:20px;
}
.dataExp table tr td{
	padding-right: 5px;
}
/*二次开发 新增样式 end*/
</style>
</head>
<script>
	function agreeRefund(id, type) {
		var status = "同意";
		if (type == 0) {
			status = "拒绝";
		}
		Plugins.confirm({
			title : '信息提示',
			content : '确定' + status + '退款吗？',
			okText : '确定',
			cancelText : '取消',
			okFun : function() {
				Plugins.closeWindow();
				Plugins.waitTips({
					title : '信息提示',
					content : '正在操作，请稍后...'
				});
				$.post("<?php echo U('Admin/Refund/agressRefund');?>", {
					id : id,
					type : type
				}, function(data, textStatus) {
					var json = WST.toJson(data);
					if (json.status == '1') {
						Plugins.setWaitTipsMsg({
							content : '操作成功',
							timeout : 1000,
							callback : function() {
								location.reload();
							}
						});
					} else {
						Plugins.closeWindow();
						Plugins.Tips({
							title : '信息提示',
							icon : 'error',
							content : '操作失败!',
							timeout : 1000
						});
					}
				});
				return false;
			}
		});
	}

	//处理订单
	function toEdit(id) {
		var url = "<?php echo U('Admin/Refund/toEditAccount',array('id'=>'__0'));?>".replace('__0',id);
		Plugins.Modal({
			url : url,
			title : '处理退款订单',
			width : 500
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
<body class="wst-page">
	<div class="wst-body">
		<div class='wst-page-content'>
			<form name="form1" method="GET"
				action="<?php echo U('Admin/Refund/refundList');?>">
				<div class='wst-tbar-query' style="padding-left: 20px;">
					售后类型 <select id='type' name="type" style="margin-right: 20px;">
						<option value=''<?php if(empty($_GET['type'])): ?>selected<?php endif; ?> >全部
						</option>
						<option value='1'<?php if($_GET['type'] == 1 ): ?>selected<?php endif; ?> >订单取消
						</option>
						<option value='2'<?php if($_GET['type'] == 2 ): ?>selected<?php endif; ?> >售后订单
						</option>
					</select> 平台处理： <select id='pf_status' name="pf_status"
						style="margin-right: 20px;">
						<option value=''<?php if(empty($_GET['pf_status'])){ echo 'selected'; } ?> >全部
						</option>
						<option value='0'<?php if($_GET['pf_status']=='0'){ echo 'selected'; } ?> >待处理
						</option>
						<option value='1'<?php if($_GET['pf_status'] == 1 ): ?>selected<?php endif; ?> >已退款
						</option>
						<option value='2'<?php if($_GET['pf_status'] == 2 ): ?>selected<?php endif; ?> >已拒绝
						</option>
					</select> 商家处理： <select id='biz_status' name="biz_status">
						<option value=''<?php if(empty($_GET['biz_status'])): ?>selected<?php endif; ?> >全部
						</option>
						<option value='0'<?php if($_GET['biz_status'] == '0' ): ?>selected<?php endif; ?> >待处理
						</option>
						<option value='1'<?php if($_GET['biz_status'] == 1 ): ?>selected<?php endif; ?> >已同意
						</option>
						<option value='2'<?php if($_GET['biz_status'] == 2 ): ?>selected<?php endif; ?> >已拒绝
						</option>
					</select>
					<!--   <select id='shopCatId2' autocomplete="off">
	         <option value='0'>请选择</option>
	     </select> -->
					申请时间<input class="laydate-icon" style="width: 120px"
						name="start_day" placeholder="开始日期" value="<?php echo ($_GET['start_day']); ?>"
						onclick="laydate()"> - <input class="laydate-icon"
						style="width: 120px" name="end_day" value="<?php echo ($_GET['end_day']); ?>"
						placeholder="结束日期" onclick="laydate()"> 订单号： <input
						type='text' id='searchKey' name="searchKey"
						value='<?php echo ($_GET["earchKey"]); ?>' /> <input
						style="border: none; padding: 0; margin: 0; background: #f00; padding: 5px 15px; color: #fff; box-shadow: none; cursor: pointer; border-radius: 0px;"
						type="submit" id="button" value="查询">
				</div>
			</form>
			<!--二次开发 --start-->
			<div class="dataExp">
				<form action="/index.php/Admin/Refund/dataExp" method="post" id="timeForm">
					<table>
						<tr>
							<td>
								申请时间段：
							</td>
							<td><input class="datainp" name="timeStart"  id="datebut" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
							<td>：</td>
							<td><input class="datainp" name="timeEnd" id="datebut1" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
							<td><button type="submit" class="btn btn-primary">导出退款列表数据</button></td>
							<td id="timeMsg"><?php echo ($Msg); ?></td>
						</tr>
					</table>
				</form>
			</div>
			<!-- 二次开发 --end -->
			<table
				class='table table-hover table-striped table-bordered wst-list'
				style="text-align: center;">
				<thead>
					<tr>
						<th width='30'>序号</th>
						<th width='50'>订单编号</th>
						<th width='50'>退款类型</th>
						<th width='50'>退款金额</th>
						<th width='50'>平台处理</th>
						<th width='50'>商家处理</th>
						<th width='50'>申请时间</th>
						<th width='100'>操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($res["list"])): $i = 0; $__LIST__ = $res["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
						<td><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo["orderNo"]); ?></td>
						<td><?php if($vo['type'] == 1): ?>取消订单<?php else: ?>售后退款<?php endif; ?></td>
						<td><?php echo ($vo["apply_money"]); ?></td>
						<td><?php switch($vo["pf_status"]): case "0": ?>待处理<?php break;?> <?php case "1": ?>已退款<?php break;?> <?php case "2": ?>已拒绝<?php break;?> <?php default: endswitch;?></td>
						<td><?php if($vo['type'] == 1): ?>订单取消 <?php else: ?> <?php switch($vo["biz_status"]): case "0": ?>待处理<?php break;?> <?php case "1": ?>已同意<?php break;?> <?php case "2": ?>已拒绝<?php break;?> <?php case "3": ?>商家已拒绝，但平台强制退款<?php break; default: endswitch; endif; ?></td>
						<td><?php echo ($vo["refundTime"]); ?></td>
						<td><?php if(in_array('tk_04',$WST_STAFF['grant'])){ ?> <?php if($vo['type'] == 1): if($vo['pf_status'] == 0): ?><a class='clickBtn '
								href="javascript:toEdit(<?php echo ($vo['id']); ?>)">处理退款</a><?php endif; ?> <a
								class='clickBtn'
								href="<?php echo U('Refund/detail',array('id'=>$vo['orderid']));?>">查看详情</a>
							<?php else: ?> <?php switch($vo['pf_status']): case "0": if($vo['biz_status'] == 1): ?><a class='clickBtn '
								href="javascript:toEdit(<?php echo ($vo['id']); ?>)">处理退款</a><?php endif; ?> <a
								class='clickBtn'
								href="<?php echo U('Refund/detail',array('id'=>$vo['id']));?>">退款详情</a><?php break;?> <?php default: ?>
							<a class='clickBtn'
								href="<?php echo U('Refund/detail',array('id'=>$vo['id']));?>">退款详情</a><?php endswitch; endif; ?> <?php } ?>
							<a class='clickBtn'
							href="<?php echo U('Orders/toview',array('id'=>$vo['orderid']));?>">订单详情</a>
						</td>
					</tr><?php endforeach; endif; else: echo "" ;endif; ?>
					<tr>
						<td colspan='7' align='center'><?php echo ($res['show']); ?></td>
					</tr>
					<!-- <tr>
                <td colspan='7' align='left' style="background:none; text-align:left; padding-left:20px;"><a style="backround:#fafafa; text-align:left;padding:3px 6px;  border:#ccc solid 1px; border-radius:4px; " href="<?php echo U('Refund/exprotExcel');?>">导出excel</a></td>
             </tr> -->
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>