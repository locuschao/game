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
<!--[if lt IE 9]>
      <script src="/Public/js/html5shiv.min.js"></script>
      <script src="/Public/js/respond.min.js"></script>
      <![endif]-->
<script src="/Public/js/jquery.min.js"></script>
<script src="/Public/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/Public/js/common.js"></script>
<script src="/Public/plugins/plugins/plugins.js"></script>
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
		$('.datainp1').click(function () {
			if($(this).attr('id') != 'datebu')
			{
				$('#datebu').attr('id','datebu1');
				$(this).attr('id','datebu');

			}
			jeDate({dateCell:'#datebu',isTime:true,format:'YYYY-MM-DD hh:mm:ss'});
		});
	});
	function todayTime() {
		var todayStart = '<?php echo ($todayStart); ?>';
		var todayEnd = '<?php echo ($todayEnd); ?>';
		$('.datainp1').eq(0).val(todayStart);
		$('.datainp1').eq(1).val(todayEnd);
	}
	/*二次开发 添加的js  --end  */
   </script>
<body class='wst-page'>
	<form method="GET" action="<?php echo U('Admin/Orders/index');?>">
		<input type="hidden" name="isGroup" value="<?php echo ($isGroup); ?>" /> <input
			type="hidden" name="isSeckill" value="<?php echo ($isSeckill); ?>" />
		<div class='wst-tbar'>
			店铺：<input type='text' name='shopName' id='shopName'
				value='<?php echo ($shopName); ?>' /> 订单：<input type='text' name='orderNo'
				id='orderNo' value='<?php echo ($orderNo); ?>' />
			充值类型：<select name="type">
			<option value="">请选择</option>
			<?php if($_GET['type'] == 1): ?><option value="1" selected>首充号</option>
				<?php else: ?>
				<option value="1">首充号</option><?php endif; ?>
			<?php if($_GET['type'] == 2): ?><option value="2" selected>会员首充</option>
				<?php else: ?>
				<option value="2">会员首充</option><?php endif; ?>
			<?php if($_GET['type'] == 3): ?><option value="3" selected>首充号代充</option>
				<?php else: ?>
				<option value="3">首充号代充</option><?php endif; ?>
			<?php if($_GET['type'] == 4): ?><option value="4" selected>会员首代</option>
				<?php else: ?>
				<option value="4">会员首代</option><?php endif; ?>

		</select>
			游戏版本：
			<select name="version">
				<option value="0">请选择</option>
				<?php if(is_array($versions)): $i = 0; $__LIST__ = $versions;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["id"] == $_GET['version']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["vName"]); ?></option><?php endif; ?>
					<option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["vName"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
			游戏名称：<input type="text" name="gameName" value="<?php echo ($_GET['gameName']); ?>">
			订单状态：
			<select name="orderStatus">
				<option value="">请选择</option>
				<option value="1">等待发货</option>
				<option value="-2">待付款</option>
				<option value="2">已发货</option>
				<option value="-3">退款中</option>
				<option value="-4">买家取消订单</option>
				<option value="-5">退款成功</option>
				<option value="-6">拒绝退款</option>
				<option value="3">已完成</option>
				<option value="-7">商家取消订单</option>
				<option value="-8">平台拒绝退款</option>
				<option value="-9">订单无效</option>
			</select>
			<br><br>
			订单时间段：<input class="datainp1" name="timeS"  id="datebu" type="text" placeholder="请选择" value="<?php echo ($_GET['timeS']); ?>"  readonly >：
			<input class="datainp1" name="timeE" id="datebu1" type="text" placeholder="请选择" value="<?php echo ($_GET['timeE']); ?>"  readonly >

			<!-- 订单状态：  <select name='orderStatus' id='orderStatus'>
             <option value='-9999'>请选择</option>
             <option value='-2'>未支付</option>
             <option value='0'>未受理</option>
             <option value='1'>已受理</option>
             <option value='2'>打包中</option>
             <option value='3'>配送中</option>
             <option value='4'>已到货</option>
             <option value='-100'>用户取消</option>
             <option value='-3'>用户拒收</option>
             <option value='-4'>店铺同意拒收</option>
             <option value='-5'>店铺不同意拒收</option>
          </select> -->
			<!--<button onclick="todayTime()" class="btn btn-primary">今天时间段</button>-->
			<input type="button" onclick="todayTime()" value="今天时间段">
			<button type="submit" class="btn btn-primary glyphicon glyphicon-search">查询</button>
		</div>
	</form>
	<!--二次开发 --start-->
	<div class="dataExp">
		<form action="/index.php/Admin/Orders/dataExp" method="post" id="timeForm">
			<table>
				<tr>
					<td>
						订单时间段：
					</td>
					<td><input class="datainp" name="timeStart"  id="datebut" type="text" placeholder="请选择" value="<?php echo ($todayStart); ?>"  readonly ></td>
					<td>：</td>
					<td><input class="datainp" name="timeEnd" id="datebut1" type="text" placeholder="请选择" value="<?php echo ($todayEnd); ?>"  readonly ></td>
					<td><button type="submit" class="btn btn-primary">导出订单列表数据</button></td>
					<td id="timeMsg"><?php echo ($Msg); ?></td>
				</tr>
			</table>
		</form>
	</div>
	<!-- 二次开发 --end -->
	<div class="wst-body">
		<table class="table table-hover table-striped table-bordered wst-list">
			<?php if(is_array($Page['root'])): $key = 0; $__LIST__ = $Page['root'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?><thead>
				<tr>
					<th colspan='6'><?php echo ($key); ?>.订单：<?php echo ($vo['orderNo']); ?><span
						style='margin-left: 100px;'><?php echo ($vo['shopName']); ?></span></th>
				</tr>
			</thead>
			<tbody>
            
				<tr>
					<td>
						<div style='width: 150px;'>
							<img style='margin: 2px;' src="/<?php echo ($vo['goodsThums']); ?>"
								height="50" width="50" title='<?php echo ($goods[' goodsName']); ?>'/>
						</div>
					</td>
					<td><?php echo ($vo['scope']); ?></td>
					<td><?php echo ($vo['gameName']); ?></td>
					<td><?php echo ($vo["vName"]); ?></td>
					<td>￥<?php echo ($vo['needPay']); ?><!--<br /> <?php if($vo['orderStatus'] > 0): if($vo['payType'] == 2 ): ?>微信支付<?php elseif($vo['payType'] == 1 ): ?>支付宝支付<?php else: ?>余额支付<?php endif; ?> <?php else: ?>
						待付款<?php endif; ?>-->
					</td>
					<td>原价：<?php echo ($vo["marketPrice"]); ?>，数量：<?php echo ($vo["goodsNums"]); ?>，总价：<?php echo $vo['marketPrice']*$vo['goodsNums'] ?></td>
					<td>下单时间：<?php echo ($vo['createTime']); ?></td>
					
                    <td><?php if($vo['fahuoTime'] != ''): ?>发货时间：<?php echo ($vo['fahuoTime']); endif; ?></td>
					<td><?php if($vo['fahuoTime'] > 0): ?>发货方式：
                            <?php if($vo["fahuoType"] == 0): ?>手动<?php elseif(($vo["fahuoType"] == 1) or ($vo["fahuoType"] == 2)): ?>自动<?php endif; endif; ?>
                    </td>
                    
                    <td><?php if($vo["orderStatus"] == 1): ?>等待发货 <?php elseif($vo["orderStatus"] == -2): ?>待付款 <?php elseif($vo["orderStatus"] == 2): ?>已发货 <?php elseif($vo["orderStatus"] == -3): ?>退款中 <?php elseif($vo["orderStatus"] == -4): ?>买家取消订单<?php elseif($vo["orderStatus"] == -5): ?>退款成功 <?php elseif($vo["orderStatus"] == -6): ?>拒绝退款 <?php elseif($vo["orderStatus"] == 3): ?>已完成 <?php elseif($vo["orderStatus"] == -7): ?>商家取消订单<?php elseif($vo["orderStatus"] == -8): ?>平台拒绝退款<?php elseif($vo["orderStatus"] == -9): ?>订单无效<?php endif; ?></td>
					<td><a class="btn btn-primary glyphicon"
						href="<?php echo U('Admin/Orders/toView',array('id'=>$vo['orderId']));?>"">查看</a>&nbsp;
					</td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
			<tr>
				<td colspan='6' align='center'><?php echo ($Page['pager']); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</body>
</html>