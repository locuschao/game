     function getshopcat(){
        var getshopid="#shop_select";
        var shopid=$(getshopid).val();
         var aj = $.ajax( {  
                url:"/Home/Youhui/check_shopcat.html",
                data:{  
                      shopid : shopid
                },  
               type:'post', 
               cache:false,   
              success:function(data) {  
             if(data){  
                 $('#shop_cats').html(data);
                 $('#shop_cats2').html('<option>请选择</option>');
             }
          },  
          error : function() {   
               alert("异常！");  
          }  
         });
       }

    function getshopchildcat(){
    var shop_cats="#shop_cats";
    var shop_cats=$(shop_cats).val();
     var aj = $.ajax( {  
            url:"/Home/Youhui/check_shop_cats_child.html",
            data:{  
                  id : shop_cats
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
         if(data){  
             $('#shop_cats2').html(data);
         }
      },  
      error : function() {   
           alert("异常！");  
      }  
 });
    
   }

   function getAreaList(objId,parentId,t,id){
     var params = {};
     params.parentId = parentId;
     $('#'+objId).empty();
     if(t<1){
       $('#areaId3').empty();
       $('#areaId3').html('<option value="">请选择</option>');
     }else{
       if(t==1 && $('#areaId2').find("option:selected").text()!=''){
         shopMap.setZoom();
         shopMap.setCity($('#areaId2').find("option:selected").text());
         $('#showLevel').val(shopMap.getZoom());
       }
     }
     var html = [];
     $.post("/Home/Youhui/queryShowByList.html",params,function(data,textStatus){
        html.push('<option value="">请选择</option>');
      var json = WST.toJson(data);
      if(json.status=='1' && json.list && json.list.length>0){
        var opts = null;
        for(var i=0;i<json.list.length;i++){
          opts = json.list[i];
          html.push('<option value="'+opts.areaId+'" '+((id==opts.areaId)?'selected':'')+'>'+opts.areaName+'</option>');
        }
      }
      $('#'+objId).html(html.join(''));
      if(id)getCommunitys();
     });
   }

   function checkshop(){
         var shopName=$("#check_shop").val();
          var aj = $.ajax( {  
            url:"/Home/Youhui/check_shop.html",
            data:{  
                  shopName : shopName
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
         if(data){  
             $('#shop_select').html(data);
             $('#shop_cats').html("<option>请选择</option>");
             $('#shop_cats2').html("<option>请选择</option>");
         }else{
           $('#shop_select').html("<option value=''>搜索不到商户</option>");
         }
      },  
      error : function() {   
           alert("异常！");  
      }  
 });
   };

      // 优惠范围
      function getgoods(){
    var getgoods=$("#check_goods").val();
    var shopId=$("input[name='supplier_id']").val();
     var aj = $.ajax( {  
            url:"/Home/Youhui/querygoodssupplier.html",
            data:{  
                  goodsName : getgoods,
                  shopId : shopId
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
         if(data){  
             $('#select_ting').html(data);
         }
      },  
      error : function() {   
           alert("异常！");  
      }  
 });
   }

   function getcats(){
    var getcats=$("#check_cats").val();
     var aj = $.ajax( {  
            url:"/Home/Youhui/querycat.html",
            data:{  
                  catsName : getcats
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
         if(data){  
             $('#select_ting').html(data);
         }
      },  
      error : function() {   
           alert("异常！");  
      }  
 });
   }
     function getbrand(){
    var getbrand=$("#check_brand").val();
     var aj = $.ajax( {  
            url:"/Home/Youhui/querybrand.html",
            data:{  
                  brandName : getbrand
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
         if(data){  
             $('#select_ting').html(data);
         }
      },  
      error : function() {   
           alert("异常！");  
      }  
 });
   }

      function addyouhuiscope(){
         var scopeval= $('#scopeval');
         var id = $('#select_ting').val();
         var name = $('#select_ting').find("option:selected").text();
         var input_id ="input_"+id;
         var srt="<label><input id='"+input_id+"' type='checkbox' name='scopeval[]' checked value='"+id+"'>"+name+"</label>";
         var input_id="#"+input_id;
         var input_val=$(input_id).val();
        
        if (typeof(input_val)=='undefined') {
          scopeval.append(srt);
        }else{
          layer.msg("已经选择了该项，不能重复选择");
        }     
      }

      

    function removescopeval(){
      $('#scopeval').empty();
    }

    function addyouhuishopscope(){
        layer_e();
        var supplier=$('#shop_select');
        var cate=$('#shop_cats');
        var cate_child=$('#shop_cats2');
        var scopeval= $('#scopeval');

        //获取商户值
        supplier_id=supplier.val();
        supplier_text=supplier.find("option:selected").text();
        cate_id=cate.val();
        cate_text=cate.find("option:selected").text();
        cate_child_id=cate_child.val();
        cate_child_text=cate_child.find("option:selected").text();
        //处理值
        var aj = $.ajax( {  
            url:"/Home/Youhui/runcheckshop.html",
            data:{  
                  supplier_id : supplier_id,
                  supplier_text : supplier_text,
                  cate_id : cate_id,
                  cate_text : cate_text,
                  cate_child_id : cate_child_id,
                  cate_child_text : cate_child_text,
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
             data = eval("("+data+")");
             var input_shop="#input_shop";
             var input_cate="#input_cate"
             var input_cate_child="#input_cate_child";
                if(data.status!==0){
                  if (data.level==1) {
                      if (typeof($(input_shop).val())=='undefined') {
                        $('#scopeval').empty();
                         scopeval.append(data.msg);
                          return;
                      }else{
                              layer.msg('已选择商户优惠券');
                            }
                    }

                      if (data.level==2) {
                          if (typeof($(input_shop).val())=='undefined') {
                                  if (typeof($(input_cate).val())=='undefined') {
                                  scopeval.append(data.msg);
                                  $('#twoinput').remove();
                                  return;
                              }else{
                                  $('#oneinput').remove();
                                  $('#twoinput').remove();
                                  scopeval.append(data.msg);
                                  return;
                              }    
                            }else{
                              layer.msg('已选择商户优惠券');
                            }
                      } 

                      if (data.level==3) {
                       if (typeof($(input_shop).val())=='undefined') {
                        if (typeof($(input_cate).val())=='undefined') {
                            if (typeof($(input_cate_child).val())=='undefined') {
                              scopeval.append(data.msg);
                              return;
                          }else{
                              $('#twoinput').remove();
                              scopeval.append(data.msg);
                              return;
                          }  
                        }else{
                          layer.msg("已选择一级分类");
                        }
                       }else{
                              layer.msg('已选择商户优惠券');
                            }                        
                      } 
                    

               }else{
                layer.msg(data.msg);
               }
      },  
          error : function() {   
           alert("异常！");  
      }  
 });
      }

    function getscope(){
        $('#scopeval').empty();
        var scope=$('#youhui_scope');
        var scopetr=$('#scopetr');
        var scope_val=scope.val();
        if (scope_val==1) {
          $("#aftertr").remove();
          $("#aftertr").remove();
        }
        if (scope_val==2) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var shopId=$("input[name='supplier_id']").val();
          var srt2="<tr id='aftertr'><th width='200' align='right'>店铺分类：</th><td><select name='shop_cats' id='shop_cats' onchange='javascript:getshopchildcat()'><option>请选择</option></select>&nbsp;<select name='shop_cats2' id='shop_cats2'><option>请选择</option></select><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_shop_sbt' onclick='javascript:addyouhuishopscope()'>选择范围</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";   
          scopetr.after(srt2);
          var aj = $.ajax( {  
                  url:"/Home/Youhui/querycatsupplier.html",
                  data:{shopId:shopId},
                  type:'post', 
                 cache:false,   
                success:function(data) {  
               if(data){  
                   $('#shop_cats').html(data);
               }
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
        }
        if (scope_val==3) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt="<tr id='aftertr'><th width='200' align='right'>选择商品：</th><td><input style='margin-right: 5px;'  type='text' id='check_goods'><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_goods_sbt' onclick='javascript:getgoods()'>搜索商品</span><select name='good_id' style='margin-right: 5px;' id='select_ting'><option value=''>请选择</option></select><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' onclick='javascript:addyouhuiscope()' id='check_goods_sbt'>选择范围</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";
          scopetr.after(srt);

        }
        if (scope_val==4) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt =" <tr id='aftertr'><th width='200' align='right'>选择品牌：</th><td><input style='margin-right: 5px;' type='text' id='check_brand'><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_goods_sbt' onclick='javascript:getbrand()'>搜索品牌</span><select name='brand_id' style='margin-right: 5px;' id='select_ting'><option value=''>请选择</option></select><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' onclick='javascript:addyouhuiscope()' id='check_brand_sbt'>选择范围</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";
          scopetr.after(srt);

        }
        if (scope_val==5) {
          $("#aftertr").remove();
          $("#aftertr").remove();
           var srt ="<tr id='aftertr'><th width='200' align='right'>商城分类：</th><td><select id='deal_cate' onchange='javascript:getdealcat2(2)'><option>请选择</option></select>&nbsp;<select id='deal_cate2' onchange='javascript:getdealcat2(3)'><option>请选择</option></select>&nbsp;<select  id='deal_cate3'><option>请选择</option></select><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_shop_sbt' onclick='javascript:adddealcate()'>选择分类</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>"
          scopetr.after(srt);
          var aj = $.ajax( {  
                  url:"/Home/Youhui/getdealcats.html",
                  data:{id:0},
                  type:'post', 
                 cache:false,   
                success:function(data) {  
               if(data){  
                   $('#deal_cate').html(data);
               }
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
        }

      }

        //商城分类点击选中
        function adddealcate(){
        layer_e();
        var deal_cate=$('#deal_cate');
        var deal_cate2=$('#deal_cate2');
        var deal_cate3=$('#deal_cate3');
        var scopeval= $('#scopeval');

        deal_cateid=deal_cate.val();
        deal_catetext=deal_cate.find("option:selected").text();
        deal_cateid2=deal_cate2.val();
        deal_catetext2=deal_cate2.find("option:selected").text();
        deal_cateid3=deal_cate3.val();
        deal_catetext3=deal_cate3.find("option:selected").text();

        var aj = $.ajax( {  
            url:"/Home/Youhui/runcheckdealcat.html",
            data:{  
                  deal_cateid : deal_cateid,
                  deal_catetext : deal_catetext,
                  deal_cateid2 : deal_cateid2,
                  deal_catetext2 : deal_catetext2,
                  deal_cateid3 : deal_cateid3,
                  deal_catetext3 : deal_catetext3,
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
             data = eval("("+data+")");
             var input_shop="#input_shop";
             var input_cate="#input_cate"
             var input_cate_child="#input_cate_child";
                if(data.status!==0){
                  if (data.level==1) {
                      if (typeof($(input_shop).val())=='undefined') {
                        $('#scopeval').empty();
                         scopeval.append(data.msg);
                          return;
                      }else{
                              layer.msg('已经选择了一级分类');
                            }
                    }

                      if (data.level==2) {
                          if (typeof($(input_shop).val())=='undefined') {
                                  if (typeof($(input_cate).val())=='undefined') {
                                  scopeval.append(data.msg);
                                  $('#twoinput').remove();
                                  return;
                              }else{
                                  $('#oneinput').remove();
                                  $('#twoinput').remove();
                                  scopeval.append(data.msg);
                                  return;
                              }    
                            }else{
                              layer.msg('已经选择了一级分类');
                            }
                      } 

                      if (data.level==3) {
                       if (typeof($(input_shop).val())=='undefined') {
                        if (typeof($(input_cate).val())=='undefined') {
                            if (typeof($(input_cate_child).val())=='undefined') {
                              scopeval.append(data.msg);
                              return;
                          }else{
                              $('#twoinput').remove();
                              scopeval.append(data.msg);
                              return;
                          }  
                        }else{
                          layer.msg("已经选择了二级分类");
                        }
                       }else{
                              layer.msg('已经选择了一级分类');
                            }                        
                      } 
                    

               }
      },  
          error : function() {   
           alert("异常！");  
      }  
 });
      }

      //商户优惠范围
      
             function getgoodssupplier(){
            var getgoods=$("#check_goods").val();
           var aj = $.ajax( {  
                  url:"/Home/Youhui/querygoodssupplier.html",
                  data:{  
                        goodsName : getgoods
                  },  
                 type:'post', 
                 cache:false,   
                success:function(data) {  
               if(data){  
                   $('#select_ting').html(data);
               }
            },  
            error : function() {   
                 alert("异常！");  
            }  
       });
         }

      function getscopesupplier(){
        $('#scopeval').empty();
        var scope=$('#youhui_scope');
        var scopetr=$('#scopetr');
        var scope_val=scope.val();
        if (scope_val==1) {
          $("#aftertr").remove();
          $("#aftertr").remove();
        }
        if (scope_val==2) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt2="<tr id='aftertr'><th width='200' align='right'>商户分类：</th><td><select id='shop_cats' onchange='javascript:getshopchildcat()'><option>请选择</option></select>&nbsp;<select id='shop_cats2'><option>请选择</option></select><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_shop_sbt' onclick='javascript:addyouhuishopscope2()'>选择分类</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";   
          scopetr.after(srt2);
          scopetr.after(srt);
           var aj = $.ajax( {  
                  url:"/Home/Youhui/querycatsupplier.html",
                  type:'post', 
                 cache:false,   
                success:function(data) {  
               if(data){  
                   $('#shop_cats').html(data);
               }
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
        }
        if (scope_val==3) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt="<tr id='aftertr'><th width='200' align='right'>选择商品：</th><td><input style='margin-right: 5px;'  type='text' id='check_goods'><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_goods_sbt' onclick='javascript:getgoodssupplier()'>搜索商品</span><select name='good_id' style='margin-right: 5px;' id='select_ting'><option value=''>请选择</option></select><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' onclick='javascript:addyouhuiscope()' id='check_goods_sbt'>选择商品</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";
          scopetr.after(srt);

        }
        if (scope_val==4) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt =" <tr id='aftertr'><th width='200' align='right'>选择品牌：</th><td><input style='margin-right: 5px;' type='text' id='check_brand'><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_goods_sbt' onclick='javascript:getbrand()'>搜索品牌</span><select name='brand_id' style='margin-right: 5px;' id='select_ting'><option value=''>请选择</option></select><span style='margin-right: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' onclick='javascript:addyouhuiscope()' id='check_brand_sbt'>选择品牌</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>";
          scopetr.after(srt);

        }
        if (scope_val==5) {
          $("#aftertr").remove();
          $("#aftertr").remove();
          var srt ="<tr id='aftertr'><th width='200' align='right'>商城分类：</th><td><select id='deal_cate' onchange='javascript:getdealcat2(2)'><option>请选择</option></select>&nbsp;<select id='deal_cate2' onchange='javascript:getdealcat2(3)'><option>请选择</option></select>&nbsp;<select  id='deal_cate3'><option>请选择</option></select><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:#33AAFF;cursor:pointer' id='check_shop_sbt' onclick='javascript:adddealcate()'>选择分类</span><span style='margin-right: 5px;margin-left: 5px;display:inline-block;width:80px;height: 20px;line-height:20px;color: #fff;text-align: center; background:orange;cursor:pointer' id='check_shop_sbt' onclick='javascript:removescopeval()'>清空选择</span></td></tr>"
          scopetr.after(srt);
          var aj = $.ajax( {  
                  url:"/Home/Youhui/getdealcats.html",
                  data:{id:0},
                  type:'post', 
                 cache:false,   
                success:function(data) {  
               if(data){  
                   $('#deal_cate').html(data);
               }
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
        }

      }
      function getdealcat2(type){
        if (type==2) { 
          var id=$('#deal_cate').val();
          var deal_cate=$('#deal_cate2');
        }
      if (type==3) { 
        var id=$('#deal_cate2').val();
        var deal_cate=$('#deal_cate3');
      }
      var aj = $.ajax({  
                  url:"/Home/Youhui/getdealcats.html",
                  data:{id:id},
                  type:'post', 
                 cache:false,   
                success:function(data) {  
                   deal_cate.html(data); 
            },  
            error : function() {   
                 alert("异常！");  
            }
       });

       
      }

      function addyouhuishopscope2(){
        layer_e();
        var cate=$('#shop_cats');
        var cate_child=$('#shop_cats2');
        var scopeval= $('#scopeval');
        //获取商户值
        cate_id=cate.val();
        cate_text=cate.find("option:selected").text();
        cate_child_id=cate_child.val();
        cate_child_text=cate_child.find("option:selected").text();
        //处理值
        var aj = $.ajax( {  
            url:"/Home/Youhui/runcheckshop2.html",
            data:{  
                  cate_id : cate_id,
                  cate_text : cate_text,
                  cate_child_id : cate_child_id,
                  cate_child_text : cate_child_text,
            },  
           type:'post', 
           cache:false,   
          success:function(data) {  
             data = eval("("+data+")");
             var input_shop="#input_shop";
             var input_cate="#input_cate"
             var input_cate_child="#input_cate_child";
                if(data.status!==0){
                  if (data.level==2) {
                      if (typeof($(input_shop).val())=='undefined') {
                              if (typeof($(input_cate).val())=='undefined') {
                              scopeval.append(data.msg);
                              $('#twoinput').remove();
                              return;
                          }else{
                              $('#oneinput').remove();
                              $('#twoinput').remove();
                              scopeval.append(data.msg);
                              return;
                          }    
                        }
                  } 
                  if (data.level==3) {
                   if (typeof($(input_shop).val())=='undefined') {
                    if (typeof($(input_cate).val())=='undefined') {
                        if (typeof($(input_cate_child).val())=='undefined') {
                          scopeval.append(data.msg);
                          return;
                      }else{
                          $('#twoinput').remove();
                          scopeval.append(data.msg);
                          return;
                      }  
                    }else{
                      layer.msg("已选择一级分类");
                    }
                   }                       
                  } 
               }
      },  
          error : function() {   
           alert("异常！");  
      }  
 });
      }

function lastchecke(){

        if (!$("input[name='name']").val()) {layer.msg("没有填写优惠券名称");return;}
        if (!$("input[name='begin_time']").val()) {layer.msg("没有填写开始时间");return;}
        if (!$("input[name='end_time']").val()) {layer.msg("没有填写结束时间");return;}
        if (!$("input[name='user_limit']").val()) {layer.msg("没有填写总条数");return;}
        if (!$("input[name='total_fee']").val()) {layer.msg("没有填写消费金额");return;}
        if (!$("textarea[name='list_brief']").val()) {layer.msg("没有填写优惠券简介");return;}
        if (!$("input[name='breaks_menoy']").val()) {layer.msg("没有填写折扣/减免额度");return;}
        if (!$("input[name='total_num']").val()) {layer.msg("没有填写优惠券总条数");return;}
        if ($('#youhui_scope').val()=='请选择') {layer.msg("没有选择优惠范围");return;}
        if (!$('#fileToUpload1').val()) {if (!$('#fileToUpimg').attr('value')) {layer.msg('没有上传图片');return;}}
        if ($('#youhuiname').attr('value')!=2) {layer.msg("该优惠券名称已经被使用");return;}
        if ($('#checkT').attr('value')!=2) {layer.msg("时间选择错误");return;}
        $("#tijiao").attr('type','submit');
        $("#tijiao").trigger('click');
      }

  
 
  function checkyouhui_type(value,id){
        if (isNaN(value)) {
        input_e=$("#"+id);
        layer.tips('您输入的不是数字', '#'+id);
        input_e.val("").focus();
        }

        if ($("#zhekou input").is(':checked')) {
          var breaks=$("#breaks_menoy").val();
          if (breaks.length>3) {
            input_e=$("#breaks_menoy");
            layer.tips('请按照格式填写折扣额度', '#breaks_menoy');
            input_e.val("").focus();
          }

          if(breaks.length==2){
            input_e=$("#breaks_menoy");
            layer.tips('请按照格式填写折扣额度', '#breaks_menoy');
            input_e.val("").focus();
          }

          if (breaks.length==3) {
            var res=/^\d\.\d$/;
            if (!res.test(breaks)) {
            input_e=$("#breaks_menoy");
            layer.tips('请按照格式填写折扣额度', '#breaks_menoy');
            input_e.val("").focus();
            }
          }
           

        }
  }

function checkname(val){
  if (!val) {
      youhuiname.attr('color','red');
      youhuiname.text('没有填写优惠券名称');
      return;
  }
  var youhuiname= $('#youhuiname');
         var aj = $.ajax({  
                  url:"/Home/Youhui/checkedname.html",
                  data:{name:val},
                  type:'post', 
                 cache:false,   
                success:function(data) {
                 // data = eval("("+data+")");  
                   if (data.status==1) {
                    youhuiname.attr('color','red');
                    youhuiname.html('<img src="/Public/plugins/formValidator/themes/Default/images/onError.gif">'+data.msg);
                    youhuiname.attr('value',data.status);
                    return;
                   }

                   if (data.status==2) {
                    youhuiname.attr('color','green');
                    youhuiname.html('<img src="/Public/plugins/formValidator/themes/Default/images/onCorrect.gif">'+data.msg);
                    youhuiname.attr('value',data.status);
                   }
                   
            },  
            error : function() {   
                 alert("异常！");  
            }
       });
}

function checktime(){
    var begin_time = $("input[name='begin_time']");
      var end_time = $("input[name='end_time']");
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

