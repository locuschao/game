<?php
namespace ImproveAPI\Controller;
use Think\Controller;
/**
 * @author peng
 * @date 2017-06
 * @descreption 首页
 */
class AppIndexController extends BaseController{
    /**
     * @api {post} AppIndex/goodsList 首页商品列表
     * @apiParam {int} type 商品类型 1推荐 2最新上架 3.热门
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
    public function goodsList() {
        $get=getData();
        $condition = [
            'g.shopPrice'=>0,
            'scopeId'=>1,
            'g.goodsFlag'=>1,
            'g.isSale'=>1
            //'g.shopId'=>getShopId()
        ];
        if($get['type'] == 1){
            $condition['shop_recommend'] = ['gt',0];
        }else if($get['type'] == 2){
            $order = 'g.createTime desc';
        }else if($get['type'] == 3){
            $condition['hot'] = ['gt',0];
        }else{
            $this->error('请求异常');
        }
        $db_prefix = C('DB_PREFIX');
        $data = M('goods')->join('g left join '.$db_prefix.'game ga on g.gameId=ga.id left join '.$db_prefix.'goods_versions gv on gv.goodsId=g.goodsId' )
        ->field('ga.gameName,ga.downloadNumber,ga.gameCapacity,ga.gameIco,
        g.applyTo,g.is_huobao,g.hot,g.shop_recommend,if(heigh_member_price,heigh_member_price,attrPrice) zhekou,g.goodsId')
        ->where($condition)->order($order)
        ->limit(6)
        ->select();
        $data = D('AllGame')->filter($data);
        
        if($data){
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