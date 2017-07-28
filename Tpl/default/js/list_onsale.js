$(document).ready(function(){
    $('.imgPreview').imagePreview();
    //任意面额商品的推荐
    $('body').on('click','.btn-group',function(){
        if($(this).hasClass('open')){
            $(this).removeClass('open');
        }else{
            $(this).addClass('open');
        }
        return false;
    }).on('click','.dropdown-menu li,.huobao_btn',function(){
        var parent_ul = $(this).closest('ul');
        var this_obj = $(this);
        var this_value = $(this).data('value');
        var post_obj = {goodsId:$(this).closest('td').data('goodsid')};
        var extra = this_obj.find('.extra');
        if(parent_ul.is('.hot_menu')){
            post_obj.hot = this_value;
        }else if(parent_ul.is('.tuijian_menu')){
            post_obj.shop_recommend = this_value;
        }else{
            post_obj.is_huobao = 'huobao_request';
        }
        $.post(_url_+'opGoods',post_obj,function(re){
            if(re.status == 1){
                if(extra.text()=='取消'){
                    extra.text('');
                }else{
                    extra.text('取消');
                }
                layer.msg('操作成功');
            }else{
                layer.msg(re.info);
            }
        },'json')
    })
    
    $('body').click(function(){
        $('.btn-group').removeClass('open');
    })
    
});