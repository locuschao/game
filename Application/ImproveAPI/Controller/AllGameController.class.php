<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class AllGameController extends BaseController{
    /**
     * @api {post} AllGame/gameList 全部游戏页面
     * @apiParam {object} param{
         'p'=>1,
            'zhekou_order'=>'asc',
            //'letter'=>'a',
            //'hot'=>1, 解释：相当于主页的type = 3 （热门游戏）
            // 'gameType'=>1,
            //'newest'=>1  解释：相当于主页的type = 2（最新上架）
            // is_tuijian  解释：相当于主页的type = 1（商家推荐）
            //  keyword     
            //  goodsId    商品id
     }
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": gameName	 游戏名
                downloadNumber	下载量
                gameCapacity	apk大小
                gameIco	"Upload/gamecate/2016-07/578600b3f07d0.png"
                applyTo	"" 
                tagName 标签名(recommend hot huobao)
                zhekou	折扣
     *         
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function gameList() {
        if($data = D('AllGame')->queryByPage()){
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
    
    //author: peng descreption:游戏类型
    public function gameTypeList() {
        if($data = M('game_type')->select()){
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
    