/**
 * @author peng
 * @copyright 2016
 * @remark
 */
 var gameListCache={};
 var appendGameList=function (letter,type,key){
    var gameList='';
    //如果有缓存，则不请求服务器
    if(gameListCache[letter+key]!==undefined){
        $('.goodsList').html(gameListCache[letter+key]);
        return;
    }
    $.get('/Game/Goods/goodsType',{
                letter:letter,
                type:type,
                key:key
            },function(data){
                if(data.length==0){
                    $('.goodsList').html('<div class="game-list-tip">暂无数据！</div>');
                    return;
                }
                $.each(data,function(i,row){
                    var url=(type=='sc')?'/Game/Goods/goodsList/gameId/'+row.id+'/o/1.html':'/Game/Validatadc/yanzhen/gameId/'+row.id+'/o/2/goodsType/0.html';
                    gameList+='<a href="'+url+'"	class="goods">\<div class="gameImg"><img src="/'+row.gameIco+'" /></div>\
        	                   <div class="gameName">'+row.gameName+'</div></a>';
                })
                $('.goodsList').html(gameList);
                
                //在gameListCache缓存起来
                if(  (typeof(key)=='string' && key!='') || (typeof(letter)=='string' && letter!='')  ){
                    
                    gameListCache[letter+key]=gameList;
                    
                }
                
            },'json')
 }
$(function(){
        appendGameList('',$('#type').val())
    	$('body').on('click','.nav a,.search',function(){
    	   
            if($(this)[0].tagName=='A'){
                
                if($(this).is('.active')){
                    $(this).removeClass('active');
                    $('[name="letter"]').val('')
                    return;
                }else{
                    
                    $('.nav a').removeClass('active');
                    $(this).addClass('active');
                    $('[name="letter"]').val($(this).data('letter'))
                }
                
            }
            appendGameList($('[name="letter"]').val(),$('#type').val(),$('[name="key"]').val());
        
        })
        
        
})
