<?php
/**
 * @author peng	
 * @date 2016-12-31
 * @descreption 代金券
 */
namespace Game\Controller;
use Think\Controller;

class VoucherController extends Controller{

    public function getUserRightVouche(){
        // 得到游戏id 当前游戏版本 条件：is_glabal gameid version consume
        #一种是全站通用   is_glabal=1 其余等于0
        #某个游戏下通用   gameid=1   其余等于0
        #某个游戏的版本下通用    gameid=1 version=2  其余等于0
        #某个版本通用   version=2  其余等于0
        #满多少   consume 其余等于0
        //$attrinfo=M('goods_versions')->find(I('version_id'));
        
        if($list=D('Game/Voucher')->getCorrectVouchers([
            'consume'=>(float)I('consume'),
            'gameid'=>I('gameid'),
            'versionid'=>I('versionid'),
            'userid'=>session('oto_userId'),
            'marketPrice'=>I('market_price'),
            
        ]))
       	$this->success($list) ;
        else
        $this->error('没有相关代金券');
        
        
    }
    
    
    public function checkVoucher($attrInfo,$goodsInfo,$totalMoney){
        
        $vocher_re=D('Game/Voucher')->checkVoucher($attrInfo,$goodsInfo,$totalMoney);
        
        if($vocher_re!==false){
            return $vocher_re;
        }else{
           $this->error('代金券提交异常');
        }
      
    }
    
   
    
}