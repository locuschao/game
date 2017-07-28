<?php

/* 
 * 我的游戏
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class MyGameController extends BaseController{
    
    /**
     * @api {post} MyGame/myGamelist 我的游戏列表
     * @apiParam {int} p 当前页
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        "info": {
                'gid'=>商品ID
                'version'=>版本
                'id'=>游戏编号
                'gameName'=>游戏名字
                'gameIco'=>游戏图标
                },
     *         "geme_num":我的手游数量
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function myGamelist(){
        $post = getData();
        $userid = $_SESSION['userId'];//用户ID
        $_GET['p'] = $post['p'] ? $post['p'] : 1;
        parent::isLogin();
        $gdurl_config = C('RESOURCE_URL');
        $count_num = M('mygame')->count();
        $page = new \Think\Page($count_num,10);
        $data = M('mygame')->field("m.gid,m.version,g.id,g.gameName,concat('{$gdurl_config}',g.gameIco) as gameIco")->join("as m left join ".C('DB_PREFIX')."game as g on g.id = m.uid")->where("m.uid = {$userid} and m.status = 1")->limit($page->firstRow, $page->listRows)->order("id desc")->select();

        if($data){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => $data,
                'geme_num'  => $count_num
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '暂无数据'
            ));
        }
    }
    
    /**
     * @api {post} MyGame/addMygeme 添加游戏到我的游戏里面
     * @apiParam {int} goodid 商品ID
     * @apiParam {int} gid 游戏ID
     * @apiParam {varchar} version 游戏版本
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        ""info": “添加成功”
            },
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": “数据错误，请重试！”,
     *      }
     */
    public function addMygeme(){
        $post = getData();
        $userid = $_SESSION['userId'];//用户ID
        $goodid = $post['goodid'];//商品ID
        $gid = $post['gid'];//游戏ID
        $version = $post['version'];
        parent::isLogin();

        if(!$goodid || !$gid){
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '数据错误！'
            ));
        }
        //查找用户是否已经关注
        $info = M('mygame')->where("gid = {$goodid}")->find();
        if($info){
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '已收藏！'
            ));
        }
        $datetime = time();
        $data = array(
            'uid'=>$userid,
            'gid'=>$goodid,
            'gameid' => $gid,
            'version' => $version,
            'addtime' => $datetime
        );
        $is_true = M('mygame')->add($data);
        if($is_true){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '收藏成功！'
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '数据错误，请重试！'
            ));
        }
    }
    /**
     * @api {post} MyGame/cancelGame 取消游戏关注
     * @apiParam {goodid} goodid 商品ID
     *       {
     *        "status": 1,
     *        ""info": “已取消”
            },
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": “数据错误，请重试！”,
     *      }
     */
    public function cancelGame(){
        $post = getData();
        $userid = $_SESSION['userId'];//用户ID
        $goodid = $post['goodId'];
        parent::isLogin();
        if(!$goodid){
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '数据错误，请重试！'
            ));
        }
        $is_true = M('mygame')->where("gid = {$goodid} and uid = {$userid}")->setField('status',2);
        if($is_true){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '取消收藏成功！'
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '数据错误，请重试！'
            ));
        }
    }
}
