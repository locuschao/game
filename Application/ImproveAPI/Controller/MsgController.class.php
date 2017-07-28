<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class MsgController extends BaseController{
    public function __construct() {
        parent::__construct();
        parent::isLogin();
    }
    
    /**
     * @api {post} Msg/msgList 消息列表
     * @apiParam {Number} type 1 系统消息 2 交易消息
     * @apiParam {Number} p 1 页码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function msgList(){
        $post = getData();
        $_GET['p'] = $post['p'];
        $condition = [
            'userId'=>$_SESSION['userId']
        ];
        $field_arr = [
                'msgId',
                'title',
                'desc',
                'moduleId',
                'firstLevelId',
                'msgContent'
        ];
        $extra = [];
        if($post['type'] == 1){ //系统消息
            $condition['moduleId'] = 1;
            //获取我的未读消息的条数
            $extra['myMsgCount'] = M('msg')->where([
                'userId'=>$condition['userId'],
                'moduleId'=>['gt',1],
                'isRead'=>0
            ])->count();
        }else if($post['type'] == 2){   //系统消息
            $condition['moduleId'] = ['gt',1];
        }else{
            $this->error('参数异常');
        }
        $page = new \Think\Page(M('msg')->where($condition)->count(), 15);
        $data = M('msg')->where($condition)->field($field_arr)
            ->order('isRead,createTime DESC')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        
        if($data){
            foreach($data as $k=>$row){
                $data[$k]['createTime'] = date('Y-m-d',$row['createTime']);
                if($post['type'] == 2) $msg_ids[] = $row['msgId'];
                else if($post['type'] == 1) unset($data[$k]['msgContent']);
            }
            M('msg')->where(['msgId'=>['in',$msg_ids]])->setField([
                'isRead'=>1
            ]);
            $this->ajaxReturn(array_merge([
                'status'=>1,
                'info'=>$data
            ],$extra));
        }else{
            $this->error('没有数据');
        }
    }
    
    
    /**
     * @api {post} Msg/getNotReadMsgCount 获取未读消息条数
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function getNotReadMsgCount() {
        if($count = M('msg')->where([
        'userId'=>$_SESSION['userId'],
        'isRead'=>0
        ])->count()){
            $this->ajaxReturn([
                'status'=>1,
                'count'=>$count,
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'没有未读数据',
            ]);
        }
    }
    
    /**
     * @api {post} Msg/getSystemMsgCon 获取系统消息的详情
     * @apiParam {Number} msgId 消息id
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function getSystemMsgCon() {
        $post = getData();
        if($data = M('msg')->where(['msgId'=>$post['msgId'],'userId'=>$_SESSION['userId']])->getField('msgContent')){
            $this->ajaxReturn([
            'status'=>1,
            'info'=>$data
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} Msg/setMsgRead 把消息的状态改成已读
     * @apiParam {Number} msgId 消息id
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function setMsgRead() {
        $post = getData();
        M('msg')->where([
        'msgId'=>$post['msgId'],
        'userId'=>$_SESSION['userId']
        ])->setField([
            'isRead'=>1
        ]);
    }
}