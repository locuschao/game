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
<script src="/Public/plugins/plugins/mydate.js"></script>
	<!--  二次开发 引入时间插件-->
<script type="text/javascript" src="/Public/js/jedate.js"></script>
<style type="text/css">
.pagination li {
	list-style-type: none;
	float: left;
	border: solid 1px #ddd;
	padding: 5px 10px;
	margin-top: 0px;
	text-align: center;
}
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
       function toggleIsShow(t,v){
       Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
       $.post("<?php echo U('Admin/Complain/editiIsShow');?>",{id:v,isHandle:t},function(data,textStatus){
            var json = WST.toJson(data);
            if(json.status=='1'){
                Plugins.setWaitTipsMsg({content:'操作成功',timeout:1000,callback:function(){
                    location.reload();
                }});
            }else{
                Plugins.closeWindow();
                Plugins.Tips({title:'信息提示',icon:'error',content:'操作失败!',timeout:1000});
            }
       });
   }
    function del(id){
        Plugins.confirm({title:'信息提示',content:'您确定要删除吗?',okText:'确定',cancelText:'取消',okFun:function(){
            Plugins.closeWindow();
            Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
            $.post("<?php echo U('Admin/Complain/del');?>",{id:id},function(data,textStatus){
                var json = WST.toJson(data);
                if(json.status=='1'){
                    Plugins.setWaitTipsMsg({content:'操作成功',timeout:1000,callback:function(){
                        location.reload();
                    }});
                }else{
                    Plugins.closeWindow();
                    Plugins.Tips({title:'信息提示',icon:'error',content:'操作失败!',timeout:1000});
                }
            });
        }});
    }

    //导出所选
    function ExportSelected(exportStatus)
    {
        var params = {};
        params.id = '';
        $('input[num="list"]:checked').each(function(i,item){
            params.id += $(this).attr('id')+',';
        });
                //所选
        params.exportStatus = exportStatus;
        var jsonText = JSON.stringify(params);
        location.href="/index.php/Admin/Complain/outExcel/id/"+jsonText;
    }

    function BatchDelete()
    {
        var params = {};
        params.id = '';
        $('input[num="list"]:checked').each(function(i,item){
            params.id += $(this).attr('id')+',';
        });
        Plugins.confirm({title:'信息提示',content:'您确定要删除该投诉吗?',okText:'确定',cancelText:'取消',okFun:function(){
            Plugins.closeWindow();
            Plugins.waitTips({title:'信息提示',content:'正在操作，请稍后...'});
            $.post("<?php echo U('Admin/Complain/deletes');?>",params,function(data,textStatus){
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

    $(function(){
        //全选
        $("#selected").click(function(){
            $('input[num="list"]').prop('checked',true);
            $('#cancel').attr('checked',false);

        });
        //取消全选
        $("#cancel").click(function(){
            $('input[num="list"]').prop('checked',false);
            $('#selected').attr('checked',false);
        });

    });
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
	   function toEdit(id) {
		   var url = "<?php echo U('Admin/Refund/forceRefund',array('id'=>'__0'));?>".replace('__0',id);
		   Plugins.Modal({
			   url : url,
			   title : '强制退款',
			   width : 500
		   });
	   }
</script>
<body class='wst-page'>
<!--二次开发 --start-->
<div class="dataExp">
	<form action="/index.php/Admin/Complain/comDataExp" method="post" id="timeForm">
		<table>
			<tr>
				<td>
					投诉时间：
				</td>
				<td><input class="datainp" name="timeStart"  id="datebut" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
				<td>：</td>
				<td><input class="datainp" name="timeEnd" id="datebut1" type="text" placeholder="请选择" value="2016-01-01 00:00:00"  readonly ></td>
				<td><button type="submit" class="btn btn-primary">导出订单投诉数据</button></td>
				<td id="timeMsg"><?php echo ($msg); ?></td>
			</tr>
		</table>
	</form>
</div>
<!-- 二次开发 --end -->
	<form method='get' action="<?php echo U('Admin/Complain/index');?>">
		<div class='wst-tbar'>
			订单号：<input type='text' id='orderNo' name='orderNo'
				class='form-control wst-ipt-10' value='<?php echo ($_GET["orderNo"]); ?>' /> 开始时间:<input
				type="text" name="starttime" placeholder="包含" style='width: 100px;'
				onfocus="MyCalendar.SetDate(this)" value="<?php echo ($_GET['starttime']); ?>">
			结束时间:<input type="text" name="endtime" placeholder="不包含"
				style='width: 100px;' onfocus="MyCalendar.SetDate(this)"
				value="<?php echo ($_GET['endtime']); ?>"> 处理状态： <select name="isHandle"
				style="margin-right: 20px;">
				<option value='-1'<?php if($data["isHandle"] == -1 ): ?>selected<?php endif; ?> >全部
				</option>
				<option value='0'<?php if($data["isHandle"] == 0): ?>selected<?php endif; ?> >未处理
				</option>
				<option value='1'<?php if($data["isHandle"] == 1 ): ?>selected<?php endif; ?> >已处理
				</option>
			</select> &nbsp;
			<button type="submit"
				class="btn btn-primary glyphicon glyphicon-search">查询</button>
		</div>
		<div class="wst-body">
			<?php if($list['error'] == ''): ?><table
				class="table table-hover table-striped table-bordered wst-list">

				<thead>
					<tr>
						<th>序号</th>
						<th width='150' style="text-align: center;">用户名</th>
						<th width='150' style="text-align: center;">订单号</th>
						<th style="text-align: center;">投诉类型</th>

						<th width='150' style="text-align: center;">投诉时间</th>
						<th width='100' style="text-align: center;">处理</th>
						<th width='230' style="text-align: center;">操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data['list'])): $i = 0; $__LIST__ = $data['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
						<td><input type="checkbox" name="id[]" num="list"
							id="<?php echo ($vo["id"]); ?>" /><?php echo ($i); ?></td>
						<td><?php if($vo['userName']): echo ($vo["userName"]); else: echo ($vo["userPhone"]); endif; ?></td>
						<td><?php echo ($vo["orderNo"]); ?></td>

						<td><?php echo ($vo["content"]); ?></td>

						<td><?php echo ($vo['time']); ?></td>
						<td>
							<div class="dropdown">
								<?php if(in_array('ts_04',$WST_STAFF['grant'])){ ?>
								<?php if($vo['isHandle']==0 ): ?><button class="btn btn-danger dropdown-toggle wst-btn-dropdown"
									type="button" data-toggle="dropdown">
									未处理 <span class="caret"></span>
								</button>
								<?php else: ?>
								<button class="btn btn-success dropdown-toggle wst-btn-dropdown"
									type="button" data-toggle="dropdown">
									已处理 <span class="caret"></span>
								</button><?php endif; ?>
								<?php } ?>
								<ul class="dropdown-menu" role="menu">
									<li role="presentation"><a role="menuitem" tabindex="-1"
										href="javascript:toggleIsShow(1,<?php echo ($vo['id']); ?>)">已处理</a></li>
									<li role="presentation"><a role="menuitem" tabindex="-1"
										href="javascript:toggleIsShow(0,<?php echo ($vo['id']); ?>)">未处理</a></li>
								</ul>

							</div>
						</td>
						<td>
							<?php if($vo["type"] == 0): ?><a class="btn btn-default" href="javascript:toEdit(<?php echo ($vo['orderId']); ?>)">强制退款</a><?php endif; ?>
							<a class="btn btn-default glyphicon glyphicon-pencil"
							href="<?php echo U('Admin/Orders/toView',array('id'=>$vo['orderId']));?>">查看</a>
							&nbsp; <?php if(in_array('ts_04',$WST_STAFF['grant'])){ ?> <a
							class="btn btn-default glyphicon glyphicon-trash"
							onclick="javascript:del(<?php echo ($vo['id']); ?>)">刪除
								</button> <?php } ?></td>
					</tr><?php endforeach; endif; else: echo "" ;endif; ?>
					<tr>
						<td colspan='8'><?php if(in_array('ts_04',$WST_STAFF['grant'])){ ?>
							<input type="checkbox" name="selected" id="selected" />全选 <input
							type="checkbox" name="cancel" id="cancel" />取消 <input
							type="button" onclick="javascript:BatchDelete()" value="批量删除" />
							<?php } ?> <!--  <input type="button" onclick="javascript:ExportSelected(0)" value="导出所选"/>
                    <input type="button" onclick="javascript:ExportSelected(1)" value="导出全部"/>
                    <input type="button" onclick="javascript:ExportSelected(2)" value="导出查询"/> -->

						</td>
					</tr>
					<tr>
						<td colspan='8' align='center'><?php echo ($data['show']); ?></td>
					</tr>
				</tbody>
			</table>
			<?php else: ?> <?php echo ($list['error']); endif; ?>
		</div>
	</form>
</body>
</html>