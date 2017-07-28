/**
 * @author peng	
 * @date 2017-01-12
 * @descreption 
 */
 $(function(){
    
    $('body').on('click','.row :checkbox',function(){
        controller.change($(this))
        
    })
 })
 var model={
    voucher_arr:[]
 }
 var controller={
    getVoucherList:function(){
        $.post('/Game/Voucher/getUserRightVouche',{
            consume:$('#totalMoney').text(),
            versionid:model.attrid,
            gameid:gameid,
            market_price:marketPrice
            },function(re){
            $('.voucher-block').empty();
        	if(re.status){
        	   var voucher_block=$('.voucher-block');
               voucher_block.append('<div class="row">使用说明：每张代金券仅能使用一次</div>')
        	   $.each(re.info,function(i,row){
        	       if(row.discount_amount>0) row.price=(row.discount_amount*model.zhekou*0.1).toFixed(2)
        	       voucher_block.append('<div class="row"><div class="form-control"><input type="checkbox" value="'+row.id+'" data-price="'+row.price+'"/></div><div class="form-control">'+row.goods_name+'</div></div>')
        	   })
        	   
        	}
            
        },'json')
    },
    submitVoucher:function(){
        model.voucher_arr=[];
        $('.row :checkbox:checked').each(function(){
            model.voucher_arr.push($(this).val())
            $('#vouchers').val(model.voucher_arr.join(','))
        })
        
    },
    manyGoodsAmount:function(){
        $('.row :checkbox:checked').each(function(){
            controller.change($(this))
        })
        
    }
    ,change:function(obj){
            var totalMoney=$('#totalMoney');
            var totalMoney_val=parseFloat(totalMoney.text());
            var re;
            var afterReduce;
            var real_reduce_money;//真正抵消的金额
            
            if(obj.prop('checked')){
                
                afterReduce=parseFloat(totalMoney_val-parseFloat(obj.data('price')));
                
                if(totalMoney_val==0 && $('.voucher-block :checkbox:checked').length>0){
                    //layer.msg('抵消金额不可大于消费金额');
                    obj.prop('checked',false);
                    return;
                } 
                if(afterReduce<=0) obj.data('real_reduce_money',totalMoney_val);
                else obj.data('real_reduce_money',obj.data('price'));
                re=afterReduce;
                
            }else{
                
                re=parseFloat(totalMoney_val+parseFloat(obj.data('real_reduce_money')));
                
            }
            re=re<=0?0:re;
            totalMoney.text(re.toFixed(2))
    }
    
 }


