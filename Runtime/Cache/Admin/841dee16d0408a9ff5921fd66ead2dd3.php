<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="/Public/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="/Tpl/Admin/css/AdminLTE.css" rel="stylesheet"/>

    <script src="/Public/js/jquery.min.js"></script>
    <script src="/Tpl/Game/js/layer.min.js"></script>
</head>
<style>
body{
    position:relative;
}
.mask{
    display:none;
    position:absolute;
    left:0;
    top:0;
    background:#000;
    opacity:0.5;
    height:100%;
    width:100%;
}

.content{
    padding:10px 20px;
}
.gameBlock{
    margin-top:10px
}
.tip{
    position:fixed;
    left:50%;
    top:50%;
    text-align:center;
    color:#fff;
    width:80px;
    height:30px;
    line-height:30px;
    margin-left:-40px;
    margin-top:-15px;
    background:#000;
    opacity:0.7;
    border-radius: 4px;
    display:none;
}
.excuting .mask,.excuting .tip{
    display:block;
}
</style>
<body>
<?php
$page = new \Think\Page(M('ttgame')->count(),20); $gameList = M('ttgame')->limit($page->firstRow,$page->listRows) ->order('gameId') ->select(); $page = $page->show(); ?>
<div class="mask"></div>
<div class="content">
    <button class="btn btn-primary" id="updateGame">更新游戏列表</button>
    <div class="gameBlock">
        
        <table class="table table-striped data-table">
                <tr><th>游戏ID</th><th>游戏名称</th><th>游戏系统</th></tr>
                <?php if(is_array($gameList)): $i = 0; $__LIST__ = $gameList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                        <td><?php echo ($vo["gameId"]); ?></td>
                        <td><?php echo ($vo["gameName"]); ?></td>
                        <td><?php echo ($vo["system"]); ?></td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </table>

<div class="pager"><?php echo ($page); ?></div>
        
    </div>
    
</div>
<div class="tip">更新中...</div>
<script>
var mask={
    startHandle:function(){
        $('body').addClass('excuting');
    },
    endHandle:function(){
        $('body').removeClass('excuting');
    }
}

$(function(){
    $('#updateGame').click(function(){
        mask.startHandle();
        $.get('/index.php/Admin/TTGame/addGameListHandle',{},function(re){
            if(re.status){
                layer.msg('更新成功！');
                location.reload()
            }else{
                layer.msg('没有更新!')
            }
            mask.endHandle();
        },'json')
    })
})
</script>
</body>
</html>