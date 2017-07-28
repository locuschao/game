<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta
	content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'
	name='viewport'>
<meta http-equiv="Expires" content="0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<meta http-equiv="Cache" content="no-cache">
<title><?php echo ($CONF['mallTitle']); ?>后台管理中心</title>
	
<meta
	content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'
	name='viewport'>
<link href="/Public/plugins/bootstrap/css/bootstrap.min.css"
	rel="stylesheet">
<link href="/Tpl/Admin/css/font-awesome.min.css" rel="stylesheet"
	type="text/css" />
<!-- Ionicons -->
<link href="/Tpl/Admin/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!-- Theme style -->
<link href="/Tpl/Admin/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<script src="/Public/js/jquery.min.js"></script>
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
          <script src="/Public/js/html5shiv.min.js"></script>
          <script src="/Public/js/respond.min.js"></script>
        <![endif]-->

<script src="/Public/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/Tpl/Admin/js/jquery-ui.min.js" type="text/javascript"></script>
<!-- AdminLTE App -->
<script src="/Tpl/Admin/js/AdminLTE/app.js" type="text/javascript"></script>
<script src="/Public/js/common.js"></script>
<script src="/Public/plugins/plugins/plugins.js"></script>

</head>
<script>
	      $(function () {
	    	  $('#pageContent').height(WST.pageHeight()-98);
	    	  getTask();
	      });
	      $(window).resize(function() {
	    	  $('#pageContent').height(WST.pageHeight()-98);
	      });
	      function logout(){
	    	  Plugins.confirm({ title:'信息提示',content:'您确定要退出系统吗?',okText:'确定',cancelText:'取消',okFun:function(){
	   		     Plugins.closeWindow();
	   		     Plugins.waitTips({title:'信息提示',content:'正在退出系统...'});
	   		     $.post("<?php echo U('Admin/Index/logout');?>",{},function(data,textStatus){
	   		    	  location.reload();
	   		     });
	          }});
	      }
	      function getTask(){
	    	  $.post("<?php echo U('Admin/Index/getTask');?>",{},function(data,textStatus){
	  			    var json = WST.toJson(data);
	  			    if(json.status==1){
	  			    	if(json.goodsNum>0){
	  			    		$('#goodsTips').html(json.goodsNum).show();
	  			    	}else{
	  			    		$('#goodsTips').hide();
	  			    	}
	  			    	if(json.shopsNum>0){
	  			    		$('#shopsTips').html(json.shopsNum).show();
	  			    	}else{
	  			    		$('#shopsTips').hide();
	  			    	}
	  			    	setTimeout("getTask();",10000);
	  			    }
	    	  });
	      }
	      function cleanCache(){
	    	  Plugins.waitTips({title:'信息提示',content:'正在清除缓存，请稍后...'});
	    	  $.post("<?php echo U('Admin/Index/cleanAllCache');?>",{},function(data,textStatus){
	    		  var json = WST.toJson(data);
	    		  if(json.status==1)Plugins.setWaitTipsMsg({content:'缓存清除成功!',timeout:1000});
	    	  });
	      }
	    </script>
<body class="skin-blue">
	<!-- header logo: style can be found in header.less -->
	<header class="header">
		<a href="index.html" class="logo">在线商城后台管理</a>
		<nav class="navbar navbar-static-top" role="navigation">
			<!-- Sidebar toggle button-->
			<a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas"
				role="button"> <span class="sr-only">Toggle navigation</span> <span
				class="icon-bar"></span> <span class="icon-bar"></span> <span
				class="icon-bar"></span>
			</a>
			<div class="navbar-right">
				<ul class="nav navbar-nav">
			<!-- 		<li class="dropdown user user-menu"><a href="<?php echo WSTDomain();?>"
						target='_blank'> <i class="glyphicon glyphicon-home"></i> <span>前台&nbsp;</span>
					</a></li> -->
					<li class="dropdown user user-menu"><a
						href="javascript:cleanCache()"> <i
							class="glyphicon glyphicon glyphicon-refresh"></i> <span>清除缓存</span>
					</a></li>
					<li class="dropdown user user-menu"><a href="#"
						class="dropdown-toggle" data-toggle="dropdown"> <i
							class="glyphicon glyphicon-user"></i> <span><?php echo session('WST_STAFF.staffName');?>&nbsp;<i
								class="caret"></i></span>
					</a>
						<ul class="dropdown-menu">
							<!-- User image -->
							<li class="user-header bg-light-blue"><img
								src="/<?php echo session('WST_STAFF.staffPhoto');?>"
								class="img-circle" alt="<?php echo session('WST_STAFF.roleName');?>" />
								<p>
									<?php echo session('WST_STAFF.staffName');?> -
									<?php echo session('WST_STAFF.roleName');?> <small>职员编号：<?php echo ($WST_STAFF["staffNo"]); ?></small>
								</p></li>
							<!-- Menu Body -->
							<li class="user-body" style='display: none'>
								<div class="col-xs-4 text-center">
									<a href="#">Followers</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Sales</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Friends</a>
								</div>
							</li>
							<!-- Menu Footer-->
							<li class="user-footer">
								<div class="pull-left">
									<a href="<?php echo U('Admin/Staffs/toEditPass');?>" target='pageContent'
										class="btn btn-default btn-flat">修改密码</a>
								</div>
								<div class="pull-right">
									<a href="javascript:logout()" class="btn btn-default btn-flat">退出系统</a>
								</div>
							</li>
						</ul></li>
				</ul>
			</div>
		</nav>
	</header>
	<div class="wrapper row-offcanvas row-offcanvas-left">
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="left-side sidebar-offcanvas">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- Sidebar user panel -->
				<div class="user-panel">
					<div class="pull-left image">
						<img src="/Tpl/Admin/images/default_admin_ico.png" class="img-circle"
							alt="<?php echo session('WST_STAFF.staffName');?>" />
					</div>
					<div class="pull-left info">
						<p>Hello, <?php echo session('WST_STAFF.staffName');?></p>
					</div>
				</div>
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<ul class="sidebar-menu">

					<?php if(in_array('hygl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>用户管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('hylb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Users/index');?>" target='pageContent'>用户列表</a></li>
							<?php } ?>
							<!--   <?php if(in_array('hylb_01',$WST_STAFF['grant'])){ ?>
					            <li><a href="<?php echo U('Admin/Users/toEdit/');?>" target='pageContent'>添加会员</a></li>
					            <?php } ?>
                                <?php if(in_array('hyzh_00',$WST_STAFF['grant'])){ ?>
					            <li><a href="<?php echo U('Admin/Users/queryAccountByPage');?>" target='pageContent' >账号管理</a></li>
					            <?php } ?> -->
						</ul></li>
					<?php } ?>


					<?php if(in_array('dpgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>商铺管理</span> <small
							id='shopsTips'  class="badge pull-right bg-green">3</small>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('dplb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Shops/index');?>" target='pageContent'>商铺列表</a></li>
							<?php } ?>

							<?php if(in_array('dplb_01',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Shops/toEdit');?>"
								target='pageContent'>添加商铺</a></li>
							<?php } ?>

							<?php if(in_array('dpsh_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Shops/queryPeddingByPage');?>"
								target='pageContent'>商铺审核</a></li>
							<?php } ?>
						</ul></li>
					<?php } ?>


					<?php if(in_array('spgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>商品管理</span> <small
							id='goodsTips' style='display: none'
							class="badge pull-right bg-green">0</small>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('splb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Goods/index');?>" target='pageContent'>商品列表</a></li>
							<?php } ?>
                            
                            
							<?php if(in_array('spsh_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Goods/queryPenddingByPage');?>"
								target='pageContent'>商品审核</a></li>
							<?php } ?>
                            <!--
                            @author peng
                            @date 2017-01
                            @descreption 加上平台商品
                            -->
                            <!--<?php if(in_array('splb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Home/Goods/add',['umark'=>add,'is_pt'=>1]);?>" target='pageContent'>添加平台商品</a></li>
							
							<?php } ?>-->
						</ul></li>
					<?php } ?>
					<?php if(in_array('ddgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>订单管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('ddlb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Orders/index');?>"
								target='pageContent'>商家订单列表</a></li>
							<?php } ?>
                            
							<?php if(in_array('tk_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Refund/refundList');?>"
								target='pageContent'>退款列表<small id='refundTips'
									style='display: none' class="badge pull-right bg-green">0</small></a></li>
							<?php } ?>

							<?php if(in_array('ts_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Complain/index');?>"
								target='pageContent'>订单投诉<small id='complainTips'
									style='display: none' class="badge pull-right bg-green">0</small>
							</a></li>
							<?php } ?>
                            <?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
                            							<li><a href="<?php echo U('Admin/Voucher/orders');?>" target='pageContent'>购买会员订单</a></li>
                            							<?php } ?>

							<!--   <?php if(in_array('tk_00',$WST_STAFF['grant'])){ ?>
                                <li><a href="<?php echo U('Admin/Orders/queryRefundByPage');?>" target='pageContent' >退款列表</a></li>
                                <?php } ?> -->
						</ul></li>
					<?php } ?>
					<?php if(in_array('yxlb_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>游戏管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('yxlb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/gameList');?>"
								target='pageContent'>游戏列表</a></li>
							<?php } ?>

							<?php if(in_array('yxlb_02',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/toEdit');?>" target='pageContent'>添加游戏</a></li>
							<?php } ?>

							<!--author Meke -->
							<?php if(in_array('yybb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/listType');?>"
								   target='pageContent'>添加游戏类型</a></li>
							<?php } ?>

							<?php if(in_array('yybb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/versionsList');?>"
								target='pageContent'>游戏版本</a></li>
							<?php } ?>
                            
                            <!--auther peng -->
                            <?php if(in_array('yxlb_02',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/TTGame/addGameList');?>" target='pageContent'>更新TT游戏列表</a></li>
							<?php } ?>
                            
						</ul></li>
					<?php } ?>
					<?php if(in_array('yxlb_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>礼包管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('yxlb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/listGameGift');?>"
								   target='pageContent'>礼包列表</a></li>
							<?php } ?>

							<?php if(in_array('yxlb_02',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Game/listImportGameData');?>" target='pageContent'>导入礼包</a></li>
							<?php } ?>

						</ul></li>
					<?php } ?>
					<?php if(in_array('txgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>提现管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('sjtx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Tixian/bizTixianList');?>"
								target='pageContent'>商家提现</a></li>
							<?php } ?>

							<?php if(in_array('grtx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Tixian/psTixianList');?>"
								target='pageContent'>个人提现</a></li>
							<?php } ?>
						</ul></li>
					<?php } ?>

					<?php if(in_array('fxgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>分销管理</span>
					</a>
						<ul class="treeview-menu">
							<!--<?php if(in_array('fxsz_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Agent/settingIndex');?>"
								target='pageContent'>分销设置</a></li>
							<?php } ?>-->
                            <?php if(in_array('fxsz_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/PlateformAgent/index');?>"
								target='pageContent'>分销设置</a></li>
							<?php } ?>

							<?php if(in_array('fxtx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Agent/applyIndex');?>"
								target='pageContent'>提现管理</a></li>
							<?php } ?>
							<!--<li><a href="<?php echo U('Admin/Agent/revenueLogIndex');?>" target='pageContent' >分佣日志</a></li>-->
							
                            <!--<?php if(in_array('fxdd_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Agent/orderIndex');?>"
								target='pageContent'>订单管理</a></li>
							<?php } ?>-->
                            
                            <?php if(in_array('fxdd_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/PlateformAgent/orderIndex');?>"
								target='pageContent'>订单管理</a></li>
							<?php } ?>

							<!--<?php if(in_array('fxls_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Agent/historyOrderIndex');?>"
								target='pageContent'>历史订单管理</a></li>
							<?php } ?>-->

							<?php if(in_array('fxhy_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Agent/usersIndex');?>"
								target='pageContent'>分销会员</a></li>
							<?php } ?>
						</ul></li>
					<?php } ?>




					<!--       <?php if(in_array('wzgl_00',$WST_STAFF['grant'])){ ?>
                        <li class="treeview">
                            <a href="pages/mailbox.html">
                                <span> 文章管理</span>
                            </a>
                            <ul class="treeview-menu">
                               <?php if(in_array('wzlb_00',$WST_STAFF['grant'])){ ?>
                               <li><a href="<?php echo U('Admin/Articles/index');?>" target='pageContent' >文章列表</a></li>
                               <?php } ?>
                               <?php if(in_array('wzfl_00',$WST_STAFF['grant'])){ ?>
					           <li><a href="<?php echo U('Admin/ArticleCats/index');?>" target='pageContent' >文章分类</a></li>
					           <?php } ?>
                               <?php if(in_array('wzlb_01',$WST_STAFF['grant'])){ ?>
					           <li><a href="<?php echo U('Admin/Articles/toEdit');?>" target='pageContent' >添加文章</a></li>
					           <?php } ?>
                            </ul>
                        </li>
                        <?php } ?> -->

					<?php if(in_array('xtgl_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>系统管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('jsgl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Roles/index');?>" target='pageContent'>角色管理</a></li>
							<?php } ?>
							<?php if(in_array('zylb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Staffs/index');?>"
								target='pageContent'>职员管理</a></li>
							<?php } ?>
							<?php if(in_array('dlrz_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/LogLogins/index');?>"
								target='pageContent'>登录日志</a></li>
							<?php } ?>
						</ul></li>
					<?php } ?>

					<?php if(in_array('kf_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>客服管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('setQQ_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/CustomerServer/index');?>"
								target='pageContent'>设置QQ</a></li>
							<?php } ?>

							<?php if(in_array('kfdd_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/CustomerServer/orders');?>"
								target='pageContent'>客服订单</a></li>
							<?php } ?>

						</ul></li>
					<?php } ?>
                    
                    <!--
                    @author peng
                    @date 2016-12
                    @descreption 添加财务统计
                    -->
                    <?php if(in_array('scsz_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>财务统计</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Statistic/index');?>"
								target='pageContent'>每天成功失败统计</a></li>
							<?php } ?>
                            
                            <?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Statistic/paymentCount');?>"
								target='pageContent'>每天各种支付统计</a></li>
							<?php } ?>
                            
							<?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Statistic/countByPayType');?>" target='pageContent'>综合统计</a></li>
							<?php } ?>

							

						</ul></li>
					<?php } ?>
                    
                    <!--
                    @author peng
                    @date 2017-01-12
                    @descreption 添加代金券
                    -->
                    <?php if(in_array('scsz_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>代金券管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Voucher/addVoucher');?>"
								target='pageContent'>添加代金券</a></li>
							<?php } ?>
                            
                            <?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
                            <li><a href="<?php echo U('Admin/VoucherGoods/addGoods');?>" target='pageContent'>添加商品</a></li>
                            <?php } ?>
                            
							<?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Voucher/index');?>" target='pageContent'>代金券列表</a></li>
							<?php } ?>
                            
                            <?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/VoucherGoods/goodsList');?>" target='pageContent'>商品列表</a></li>
							<?php } ?>
							
                            
						</ul></li>
					<?php } ?>
                    
                    
                    <?php if(in_array('scsz_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>代理管理</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/AgentManager/index');?>"
								target='pageContent'>代理列表</a></li>
							<?php } ?>
                            
                            <?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/AgentManager/applyList');?>"
								target='pageContent'>申请列表</a></li>
							<?php } ?>
                            
						</ul></li>
					<?php } ?>
                    

					<?php if(in_array('scsz_00',$WST_STAFF['grant'])){ ?>
					<li class="treeview"><a href="#"> <span>商城设置</span>
					</a>
						<ul class="treeview-menu">
							<?php if(in_array('scxx_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Index/toMallConfig');?>"
								target='pageContent'>商城信息</a></li>
							<?php } ?>

							<?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Ads/index');?>" target='pageContent'>广告管理</a></li>
							<?php } ?>
                                                        
                                                        <?php if(in_array('gggl_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/GameAds/index');?>" target='pageContent'>游戏广告管理</a></li>
							<?php } ?>

							<?php if(in_array('ymdp_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Shops/hotShop');?>"
								target='pageContent'>热门店铺设置</a></li>
							<?php } ?>

							<?php if(in_array('bmd_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/White/index');?>" target='pageContent'>首充白名单</a></li>
							<?php } ?>

							<?php if(in_array('gglb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Notice/index');?>"
								target='pageContent'>公告管理</a></li>
							<?php } ?>
                                                        
                                                        <?php if(in_array('gglb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Noticegame/index');?>"
								target='pageContent'>游戏公告管理</a></li>
							<?php } ?>
                                                        
                                                        <?php if(in_array('gglb_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/GameComplain/index');?>"
								target='pageContent'>游戏投诉管理</a></li>
							<?php } ?>

							<?php if(in_array('dlxz_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/Iplogin/index');?>"
								target='pageContent'>登录IP限制</a></li>
							<?php } ?>
							
							<?php if(in_array('dlxz_00',$WST_STAFF['grant'])){ ?>
							<li><a href="<?php echo U('Admin/autoReceiving/index');?>"
								target='pageContent'>自动收货设置</a></li>
							<?php } ?>

							<!--                 <?php if(in_array('yhgl_00',$WST_STAFF['grant'])){ ?>
					            <li><a href="<?php echo U('Admin/Banks/index');?>" target='pageContent'>银行管理</a></li>
					            <?php } ?>
					            <?php if(in_array('zfgl_00',$WST_STAFF['grant'])){ ?>
					            <li><a href="<?php echo U('Admin/Payments/index');?>" target='pageContent'>支付管理</a></li>
					            <?php } ?> -->

						</ul></li>
					<?php } ?>
                    
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>

		<!-- Right side column. Contains the navbar and content of the page -->
		<aside class="right-side">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<h1>
					<small>后台管理中心</small>
				</h1>
			</section>

			<!-- Main content -->
			<section class="content" style='margin: 0px; padding: 0px;'>
				<iframe id='pageContent' name='pageContent'
					src="<?php echo U('Admin/Index/toMain');?>" width='100%' height='100%'
					frameborder="0"></iframe>
			</section>
			<!-- /.content -->
		</aside>
		<!-- /.right-side -->
	</div>
	<!-- ./wrapper -->
</body>
</html>