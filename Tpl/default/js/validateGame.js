$(function(){
    var gameSelect=$('#game');
    var versionSelect=$('#versions');
    $('body').on('change','#game,#versions',function(){
        getIsMiao();
    })
    function getIsMiao(){
        var allowVersion=new Array(
            '手游狗版本',
            'TT语音版',
            '乐8版本'
        );
        var chooseVersion=versionSelect.find('option:selected').text();
        var game=gameSelect.find('option:selected').text();
        if(!in_array(chooseVersion,allowVersion) || game=='--请选择--'){
            $('#isMiaoTr input').prop('disabled',true);
            return;
        }
        
        $.post(ThinkPHP.U('Goods/checkGame'),{app_name:game,chooseVersion:chooseVersion},function(re){
            
        	if(re.status==1){
        	   $('#isMiaoTr input').prop('disabled',false);
        	}else if(re.status==-2){
        	   //$('#isMiaoTr input').prop('disabled',false);
        	}else{
        	   $('#isMiaoTr input').prop('disabled',true);
        	}
        },'json')
    }
    function in_array(search,array){
        for(var i in array){
            if(array[i]==search){
                return true;
            }
        }
        return false;
    }
})