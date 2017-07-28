
function isnum(val,id){
	if (isNaN(val)) {
	    input_e=$("#"+id);
	    layer.tips('您输入的不是数字', '#'+id);
	    input_e.val("").focus();
	    return;
	 }
}

function mix1max5(val,id){
	var res=/^[1-5]{1}$/;
	if (!res.test(val)) {
            input_e=$("#"+id);
            layer.tips('必须1-5之间的数字', '#'+id);
            input_e.val("").focus();
            }
}

function checkname(val){
  var titlename= $('#titlename');
          $.ajax({  
                  url:"/Home/Ggk/checkname.html",
                  data:{title:val},
                  type:'post', 
                 cache:false,   
                success:function(data) {
                   if (data.status==1) {
                    titlename.attr('color','red');
                    titlename.html('<img src="/Public/plugins/formValidator/themes/Default/images/onError.gif">'+data.msg);
                    titlename.attr('value',data.status);
                    return;
                   }

                   if (data.status==2) {
                    titlename.attr('color','green');
                    titlename.html('<img src="/Public/plugins/formValidator/themes/Default/images/onCorrect.gif">'+data.msg);
                    titlename.attr('value',data.status);
                   }
                   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
}

function checktime(){
    var begin_time = $("#statdate");
      var end_time = $("#enddate");
      begin_time.blur(function(){
       var aj = $.ajax({  
                  url:"/Home/Youhui/checkedtime.html",
                  data:{end_time:end_time.val(),begin_time:begin_time.val()},
                  type:'post', 
                 cache:false,   
                success:function(data) {
                if (data.status==1) {
                  $("#bT").html('<img src="/Public/plugins/formValidator/themes/Default/images/onError.gif">'+data.msg);
                  $("#checkT").attr('value',data.status);
                }

                if (data.status==2) {
                  $("#bT").html('');
                  $("#checkT").attr('value',data.status);
                }
                   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
      });
       end_time.blur(function(){
       var aj = $.ajax({  
                  url:"/Home/Youhui/checkedtime.html",
                  data:{end_time:end_time.val(),begin_time:begin_time.val()},
                  type:'post', 
                 cache:false,   
                success:function(data) {
                 if (data.status==1) {
                  $("#eT").html('<img src="/Public/plugins/formValidator/themes/Default/images/onError.gif">'+data.msg);
                  $("#checkT").attr('value',data.status);
                }

                if (data.status==2) {
                  $("#eT").html('');
                  $("#bT").html('');
                  $("#checkT").attr('value',data.status);
                }
                   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
      });
}



function addfrom(){
  var title = $('#title').val();
	if (!title) {layer.msg('没有填写活动名称'); return;}
  var sttxt = $('#sttxt').val();
	if (!sttxt) {layer.msg('没有填写中奖提示'); return;}
  var statdate = $('#statdate').val();
	if (!statdate) {layer.msg('没有填写开始时间'); return;}
  var enddate = $('#enddate').val();
	if (!enddate) {layer.msg('没有填写结束时间'); return;}
  var info = $('#info').val()
	if (!info) {layer.msg('没有填写活动说明'); return;}
  var endinfo = $('#endinfo').val();
	if (!endinfo) {layer.msg('没有填写活动结束说明'); return;}
  var fist = $('#fist').val();
	if (!fist) {layer.msg('没有填写一等奖奖品设置'); return;}
  var fistnums = $('#fistnums').val();
	if (!fistnums) {layer.msg('没有填写一等奖奖品数量'); return;}
  var allpeople = $('#allpeople').val();
	if (!allpeople) {layer.msg('没有填写预计活动的人数'); return;}
  var canrqnums = $('#canrqnums').val();
	if (!canrqnums) {layer.msg('没有填写每人最多允许抽奖次数'); return;}
  var second = $('#second').val();
  var secondnums = $('#secondnums').val();
  var third = $('#third').val();
  var thirdnums = $('#thirdnums').val();
  var showgit = $("input[name='showgit']:checked").val();
  var titlename = $('#titlename').attr('value');
  if (titlename!=2) {layer.msg("该优惠券名称已经被使用");return;}
  var checkT = $('#checkT').attr('value');
  if (checkT!=2) {layer.msg("时间选择错误");return;}
  var biz_apply_status=$('#biz_apply_status').val();
  var aj = $.ajax({  
                  url:"/Home/Ggk/runadd.html",
                  data:{
                      title:title,
                      sttxt:sttxt,
                      statdate:statdate,
                      enddate:enddate,
                      info:info,
                      endinfo:endinfo,
                      fist:fist,
                      fistnums:fistnums,
                      allpeople:allpeople,
                      canrqnums:canrqnums,
                      second:second,
                      secondnums:secondnums,
                      third:third,
                      thirdnums:thirdnums,
                      showgit:showgit,
                      biz_apply_status:biz_apply_status
                  },
                  type:'post', 
                 cache:false,   
                beforeSend:function(){layer.load(0)},
                success:function(data) {
                  layer.closeAll('loading');
                 if (data.status==1) {
                layer.msg('提交成功，等待管理员审核活动',{icon:1,time:2000},function(){
                  window.location.href="/Home/Ggk/shoplist.html";
                    });
                }
                if (data.status==2) {
                layer.msg(data.msg,{icon:2});
                }   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
}


function changefrom(){
var title = $('#title').val();
  if (!title) {layer.msg('没有填写活动名称'); return;}
  var sttxt = $('#sttxt').val();
  if (!sttxt) {layer.msg('没有填写中奖提示'); return;}
  var statdate = $('#statdate').val();
  if (!statdate) {layer.msg('没有填写开始时间'); return;}
  var enddate = $('#enddate').val();
  if (!enddate) {layer.msg('没有填写结束时间'); return;}
  var info = $('#info').val()
  if (!info) {layer.msg('没有填写活动说明'); return;}
  var endinfo = $('#endinfo').val();
  if (!endinfo) {layer.msg('没有填写活动结束说明'); return;}
  var fist = $('#fist').val();
  if (!fist) {layer.msg('没有填写一等奖奖品设置'); return;}
  var fistnums = $('#fistnums').val();
  if (!fistnums) {layer.msg('没有填写一等奖奖品数量'); return;}
  var allpeople = $('#allpeople').val();
  if (!allpeople) {layer.msg('没有填写预计活动的人数'); return;}
  var canrqnums = $('#canrqnums').val();
  if (!canrqnums) {layer.msg('没有填写每人最多允许抽奖次数'); return;}
  var second = $('#second').val();
  var secondnums = $('#secondnums').val();
  var third = $('#third').val();
  var thirdnums = $('#thirdnums').val();
  var showgit = $("input[name='showgit']:checked").val();
  var xg=$('#xg').val();
  var id=$('#id').val();
  var biz_apply_status=$('#biz_apply_status').val();
  var aj = $.ajax({  
                  url:"/Home/Ggk/runadd.html",
                  data:{
                      id:id,
                      xg:xg,
                      title:title,
                      sttxt:sttxt,
                      statdate:statdate,
                      enddate:enddate,
                      info:info,
                      endinfo:endinfo,
                      fist:fist,
                      fistnums:fistnums,
                      allpeople:allpeople,
                      canrqnums:canrqnums,
                      second:second,
                      secondnums:secondnums,
                      third:third,
                      thirdnums:thirdnums,
                      showgit:showgit,
                      biz_apply_status:biz_apply_status
                      
                  },
                  type:'post', 
                 cache:false,   
                beforeSend:function(){layer.load(0)},
                success:function(data) {
                  layer.closeAll('loading');
                 if (data.status==1) {
                layer.msg('提交成功，等待管理员审核活动',{icon:1});
                window.location.href="/Home/Ggk/shoplist.html";
                }
                if (data.status==2) {
                layer.msg(data.msg,{icon:2});
                }   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
}