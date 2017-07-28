<?php

/* 
 * 获取游戏一些基本信息接口
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class GameSetController extends BaseController{
    
    /**
     * @api {post} GameSet/GetNotice 获取游戏公告信息
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        "info": {
                'id'=>公告编号
                'title'=>公共title
                'content'=>公告内容
                },
     *       }
            },
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": “暂无数据！”,
     *      }
     */
    public function GetNotice(){
        $data = M('game_notice')->field("id,title,content")->order("sort desc,id desc")->limit(5)->select();
        if($data){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => $data,
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => ''
            ));
        }
    }

    /**
     * @api {post} GameSet/Getadvertising 获取广告信息
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        "info": {
                'adId'=>广告编号
                'adFile'=>广告图片地址
                'adName'=>广告名称
     *          'adURL' =>广告跳转地址
                },
     *       }
            },
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": “暂无数据！”,
     *      }
     */
    public function Getadvertising(){
        $gdurl_config = C('RESOURCE_URL');
        $data = M('game_ads')->field("adId,concat('{$gdurl_config}',adFile) as adFile,adName,adURL")->order("adSort desc,adId desc")->limit(5)->select();
        if($data){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => $data,
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => ''
            ));
        }
    }
    
    /**
     * @api {post} GameSet/GmeComplain 获取广告信息
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        "info": {,
     *       }
            },
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": “暂无数据！”,
     *      }
     */
    public function GmeComplain(){
        $post = getData();
        parent::isLogin();
        $userid = $_SESSION['userId'];//用户ID
        $contact = $post['contact'];
        $content = $post['content'];
        if(!$contact || !$content){
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => ''
            ));
        }
        $data  = array();
        $data['contact'] = $contact;
        $data['content'] =$content;
        $result = M('game_complain')->add($data);
        if($result){
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => "吐槽成功",
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => ''
            ));
        }
    }
}
