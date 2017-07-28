<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class ValidateController extends BaseController
{
    /**
     * @api {post} Validate/validateAccount 验证账号
     * @apiParam {Number} goodsId 代充的商品id
     * @apiParam {Number} account 游戏账号
     * @apiSuccess {object} result 
     *       {
     *        'status'=>1,
                'info'=>'验证成功',
                'type'=>'shouchong'
     *        
     *       }
       *       {
     *        'status'=>2,
                'info'=>'验证成功',
                'type'=>'xuchong'
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function validateAccount(){
        $post = getData();
        //$post['goodsId'] = 625;
        //$post['account'] = '100557';
        $re = D('Game/Validatadc')->_validataAccount([
            'versions' =>M('goods_versions')->where(['id'=>$post['goodsId']])->find()['versionsId'],
            'account' => $post['account'],
            'goodsType' => 0,//普通商品
            'goodsId' => $post['goodsId'],
            'gameId' => M('goods')->where(['goodsId'=>$post['goodsId']])->find()['gameId'],
            'from' =>'submit_order'
        ]);
        
        if($re['status'] == 0){
            
            $this->returnJson([
                'status'=>1,
                'info'=>'验证成功',
                'type'=>'shouchong'
            ]);
        }else if($re['status'] == -2){
            $this->returnJson([
                'status'=>2,
                'info'=>'验证成功',
                'type'=>'daichong'
            ]);
        }else {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '验证失败'
            ));
        }
        
    }
    
    /**
     * @api {post} Validate/delRecordAccount 删除以往充值过的账号
     * @apiParam {Number} id 历史账号id
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function delRecordAccount() {
        $post = getData();
        if(M('white_list')->where(array(
            'userId' => $_SESSIOIN['userId'],
            'id' => $post['id']
        ))->setField([
            'is_del'=>1
        ])){
            $this->success('删除成功');
        }else{
            
            $this->error('删除失败');
        }
    }
}
