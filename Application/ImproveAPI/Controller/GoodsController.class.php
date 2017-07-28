<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class GoodsController extends BaseController
{
    /**
     * @api {post} Goods/goodsDetail //商品详情页
     * @apiParam {Number} goodsId //商品id
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
    public function goodsDetail() {
        $post = getData();
        if(!$attrInfo = M('goods_versions')->where(['goodsId'=>$post['goodsId']])->find()){
            $this->error('数据异常');
        }
        
        $db_prefix = C('DB_PREFIX');
        $data = M('goods_versions')->where([
            //'shopId'=>getShopId(),
            'gameId'=>M('goods')->where(['goodsId'=>$attrInfo['goodsId']])->find()['gameId'],
            'versionsId'=>$attrInfo['versionsId'],
            'goodsFlag'=>1,
            'shopPrice'=>0
        ])->field([
            'gv.id attrid',
            'g.goodsId',
            'ga.gameIco',
            'ga.id gameId',
            'gameName',
            'versionsId',
            'applyTo',
            'attrPrice',
            'low_member_price',
            'mid_member_price',
            'heigh_member_price',
            'vName',
            'gv.id gvid',
            'downloadUrl',
            'g.scopeId',
            
        ])
        ->join('gv left join '.$db_prefix.'goods g on gv.goodsId=g.goodsId 
        left join '.$db_prefix.'game ga on g.gameId=ga.id 
        left join '.$db_prefix.'versions v on v.id=gv.versionsId')
        ->select();
       
        $rank_assoc_key = [
            1=>'heigh_member_price',
            2=>'mid_member_price',
            3=>'low_member_price'
        ];
        $rank = M('users')->where([
                'userId'=>$_SESSION['userId']
            ])->getField('rank');
        
        foreach($data as $k=>$row){
            $zhekou = $row[$rank_assoc_key[$rank]];
            $data[$k]['zhekou'] = number_format($zhekou>0?$zhekou:$row['attrPrice'],1);
            $data[$k]['gameIco'] = C('RESOURCE_URL').$row['gameIco'];
        }
        foreach($data as $row){
            if($row['scopeId'] == 1){
                $res['shouchong'] = $row;
            }else if($row['scopeId'] == 2){
                $res['daichong'] = $row;
            }
        }  
        //查找当前商品游戏是否收藏
        $iscollect = M('mygame')->where("gid = {$attrInfo['goodsId']} and uid = {$_SESSION['userId']}")->find();
        if($iscollect){
            $res['iscollect'] = true;
        }else{
            $res['iscollect'] = false;
        }
        
        if($res) $this->ajaxreturn([
            'status'=>1,
            'info'=>$res
        ]);
        else $this->error('没有数据');
    
    }
    
    /**
     * @api {post} Goods/getCorrectVouchersCount 获取有效狗粮的张数
     * @apiParam {int} consume 需要支付的人民币
     * @apiParam {int} gameid 游戏id
     * @apiParam {int} denominations 充值面额
     * @apiParam {int} versionid 版本id
     */
     
    public function getCorrectVouchersCount() {
        if($num = D('Voucher')->getCorrectVouchersCount(array_merge(getData(),[
            'userid'=>$_SESSION['userId']
        ]))){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$num
            ]);
        }else{
            $this->error('没有可以的狗粮');
        }
    }
    
    /**
     * @api {post} Goods/getVouchers 获取用户狗粮,使用狗粮
     * @apiParam {int} consume 需要支付的人民币
     * @apiParam {int} gameid 游戏id
     * @apiParam {int} denominations 充值面额
     * @apiParam {int} versionid 版本id
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
    public function getVouchers() {
        parent::isLogin();
        $post = getData();
        $list = D('Game/Voucher')->getCorrectVouchers([
            'consume'=>$post['consume'],
            'gameid'=>$post['gameid'],
            'versionid'=>$post['versionid'],
            'userid'=>$_SESSION['userId'],
            'marketPrice'=>$post['denominations'],
        ]);
        
        foreach($list as $k=>$row){
            $arr[$k]['expireTime'] = date('Y-m-d',$row['expireTime']);
            $arr[$k]['goods_name'] = $row['goods_name'];
            $arr[$k]['remark'] = $row['remark'];
            $arr[$k]['id'] = $row['id'];
            $arr[$k]['price'] = $row['price'];
            $arr[$k]['isCheck'] = false;
        }
        
        if($arr){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$arr
            ]);
        }else{
            $this->error('没有代金券');
        }
    }
    
    /**
     * @api {post} Goods/historyAccount 历史账号
     * @apiParam {string} attrid 代充的attrid
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 数据,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function historyAccount() {
        parent::isLogin();
        $post = getData();
        $goods_versions = M('goods_versions')->where(['id'=>$post['attrid']])->find();
        if($res = M('white_list')->field('id,account')
        ->where([
            'vid'=>$goods_versions['versionsId'],
            'gid'=>M('goods')->where(['goodsId'=>$goods_versions['goodsId']])->getField('gameId'),
            'is_del'=>0,
            'userId'=>$_SESSION['userId'],
            'shopId'=>getShopId()
        ])->limit(5)
        ->order('id desc')
        ->select()){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$res
            ]);
        }else{
           $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} Goods/delHistoryAccount 快捷登录的发送手机验证码
     * @apiParam {Number} id 历史记录id
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
     public function delHistoryAccount() {
        parent::isLogin();
        if(M('white_list')->where([
            'userId'=>$_SESSION['userId'],
            'id'=>getData()['id']
        ])->setField(['is_del',1]))
        $this->ajaxReturn([
            'status'=>1,
            'info'=>'修改成功'
        ]);
        else{
            $this->error('修改失败');
        }
     }
}
?>