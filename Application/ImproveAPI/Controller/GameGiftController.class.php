<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class GameGiftController extends BaseController
{
    /**
     * @api {post} GameGift/gameGiftList //礼包列表
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
    public function gameGiftList() {
        //判断用户是否登录,显示不同的列表数据
        if($data = D('GameGift')->queryByPage()){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'info'=>''
            ]);
        }
    }

    /**
     * @api {post} GameGift/gameGiftDetail //礼包详情
     * @apiParam {Number}giftId //礼包id
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
    public function gameGiftDetail(){
        parent::isLogin();
        $post = getData();
        $condition['b.id']=$post['giftId'];
        if($data = D('GameGift')->querybyDetail($condition)){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'info'=>''
            ]);
        }
    }

    /**
     * @api {post} GameGift/gameGiftCode //获取礼包充换码
     * @des 利用事务
     * @apiParam {Number}giftId //礼包id
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
   /* public function gameGiftCode(){
        parent::isLogin();
        $post = getData();
        M()->startTrans();
        $gameCodes=M('game_code')->where(['ggId'=>$post['giftId'],'received'=>0])->find();
        if($gameCodes){
            $res1 = M('game_code')->where(['id'=>$gameCodes['id']])->save(['received'=>1]);
            $res2 = M('game_gift')->where(['id'=>$post['giftId'],'remainNumber'=>['gt',0]])->setDec('remainNumber',1);
            $res3 = M('game_gift_log')->add([
                'userId'=>session('userId'),
                'gameType'=>$post['giftId'],
                'giftCode'=>$gameCodes['gameCode'],
                'updateTime' => date('Y-m-d H:i:s',time())
            ]);
           if($res1 && $res2 && $res3){
               M()->commit();
               $this->ajaxReturn([
                   'status'=>1,
                   'info'=>$gameCodes['gameCode']
               ]);
           }else{
               M()->rollback();
               $this->error('领取失败');
           }
        }else{
            $this->error('没有可领取的充换码');
        }
    }*/

    /**
     * @api {post} GameGift/gameGiftCode //获取礼包充换码
     * @des 没用事务
     * @apiParam {Number}giftId //礼包id
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
    public function gameGiftCode(){
        parent::isLogin();
        $post = getData();
        if(M('game_gift_log')->where(['userId'=>session('userId'),'gameType'=>$post['giftId']])->select()){
            $this->error('你已经领取过了');
        }
        if($gameCodes=M('game_code')->where(['ggId'=>$post['giftId'],'received'=>0])->find()){
            $res1 = M('game_code')->where(['id'=>$gameCodes['id']])->save(['received'=>1]);
            if($res1){
                $res2 = M('game_gift')->where(['id'=>$post['giftId'],'remainNumber'=>['gt',0]])->setDec('remainNumber',1);
                if(!$res2){
                    $this->error('已经领完，敬请期待');
                }
                $res3 = M('game_gift_log')->add([
                    'userId'=>session('userId'),
                    'gameType'=>$post['giftId'],
                    'giftCode'=>$gameCodes['gameCode'],
                    'updateTime' => date('Y-m-d H:i:s')
                ]);
                if($res2 && $res3){
                    $this->ajaxReturn([
                        'status'=>1,
                        'info'=>$gameCodes['gameCode']
                    ]);
                }else{
                    $this->error('领取失败');
                }
            }else{
                $this->error('领取失败');
            }
        }else{
            $this->error('没有可领取的充换码');
        }
    }

    /**
     * @api {post} GameGift/selectGameGift //查询用户领取的礼包
     * @apiParam {Number}userId //用户id
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
    public function selectGameGift(){
        //parent::isLogin();
        session('userId',1);
        if($data = D('GameGift')->queryByUserGift()){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'info'=>''
            ]);
        }
    }


}
?>