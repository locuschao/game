$(function(){
   
    $('body').on('click','.self-create',function(){
        $(this).closest('.content').addClass('show-self-create')
    }).on('click','.system-create',function(){
        $('#self_block').remove();
        $('.close-btn').trigger('click');
    }).on('click','.close-btn',function(){
        $('body').addClass('close-btn-click');
    })
    
    if($('.selfBlock').data('dllink') == '') {
        $('.close-btn').trigger('click');
        $('#self_block').remove();
    }
})

