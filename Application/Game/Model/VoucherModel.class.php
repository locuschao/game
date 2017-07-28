<?php
namespace Game\Model;

use Think\Model;

/**
 * 代金券模块
 */
class VoucherModel
{
    /**
     * @author peng
     * @date 2017-01
     * @param [
            'consume'=>(float)I('consume'),
            'gameid'=>I('gameid'),
            'versionid'=>I('versionid'),
            'userid'=>session('oto_userId')
        ]
     */        
    public function getCorrectVouchers($arr){
        $where=[
            'is_global'=>1,
            'consume'=>[['gt',0],['elt',$arr['consume']]],
            '_string'=>'(game_id='.$arr['gameid'].' && version_id='.$arr['versionid'].') || (game_id='.$arr['gameid'].' && version_id=0) || (game_id=0 && version_id='.$arr['versionid'].')',
            '_logic'=>'or',
            'discount_amount'=>[['lt',floatval($arr['marketPrice'])],['gt',0]]
            
        ];
        $condition=[
        '_complex'=>$where,
        'userId'=>$arr['userid'],
        'num'=>['gt',0],
        'expireTime'=>['egt',time()],
        //'price'=>['lt',$arr['consume']]
        ];
        $reduce_sum=0;
        
        $data=M('user_voucher')->where($condition)->order('price')->select();
        
        if(!empty($data)){
            return $data;
        }else{
            return false;
        }               
        
    }
                
    public function sumVoucherPrice($ids){
        return M('user_voucher')->where(['id'=>['in',$ids]])->sum('price');
        
    }                                                
    /**
     * @author peng
     * @date 2017-02
     * @param $totalMoney是需要支付的金额
     */    
    public function checkVoucher($attrInfo,$goodsInfo,$totalMoney){
        if($totalMoney<=0){
            return 0;
        }
        if(I('vouchers')=='') return 0;
        $reduce_money=0;
        
        foreach($voucherinfo=M('user_voucher')->where([
            'id'=>['in',I('vouchers')],
            'userId'=>session('oto_userId'),
            'expireTime'=>['egt',time()]
        ])->select() as $row){
            if(!$row['num']>0) return false;
            if($row['is_global']!=1){
                if($row['consume']>$totalMoney){
                    
                    return false;
                }else if($row['game_id']>0 && $row['version_id']>0 && $row['version_id']!=$attrInfo['versionsId'] && $row['game_id']!=$goodsInfo['gameId']){
                    
                    return false;
                }else if($row['version_id'] >0 && $row['version_id']!=$attrInfo['versionsId'] && $row['game_id']==0){
                    
                    return false;
                }else if($row['game_id'] >0 && $row['game_id']!=$goodsInfo['gameId'] && $row['version_id']==0){
                    
                    return false;
                }
                
            }
            if($row['discount_amount']>0) $row['price']=(round($attrInfo['attrPrice']/$goodsInfo['marketPrice'],2))*$row['discount_amount'];
            $reduce_money+=$row['price'];  #reduce_money抵消掉的金额
        }
        
        return $totalMoney<$reduce_money?$totalMoney:$reduce_money;
    }
    public function voucherFahuo($order_id){
       
        $order_info=M('orders')->find($order_id);
        $order_goods_info=M('order_goods')->where(['orderId'=>$order_id])->find();
        $goods_info=M('goods_voucher')->find($order_goods_info['goodsId']);
        $userid=$order_info['userId'];
        $userinfo=M('users')->find($userid);
        if($order_info['orderStatus']==3){
            M('autofahuo_log')->add([
                'userid'=>$userid,
                'order_id'=>$order_id,
                'time'=>time(),
                'status'=>0,
                'remark'=>'订单已经是完成状态'
            ]);
            return false;
        }
        
        M()->startTrans();
        
        $voucher_ids=M('voucher_goods_relation')->where(['goods_id'=>$order_goods_info['goodsId']])->getField('voucher_id',true);
        $voucherList=M('voucher')->where(['id'=>['in',$voucher_ids]])->select();
        $re1=true;
        $timestamp=time();
        foreach($voucherList as $row){
           if(!M('user_voucher')->add([
                'userId'=>$userid,
                'goods_name'=>$row['name'],
                'voucher_id'=>$row['id'],
                'consume'=>$row['consume'],
                'price'=>$row['money'],
                'game_id'=>$row['gameId'],
                'version_id'=>$row['versionId'],
                'is_global'=>$row['is_global'],
                'num'=>$order_goods_info['goodsNums'],
                'remark'=>$row['remark'],
                'expireTime'=>strtotime("+ ".($row['validTime']-1)." day 23 hour 59 minutes",$timestamp),
                'discount_amount'=>$row['discount_amount'],
                'add_time'=>$timestamp
            ])) $re1=false;
            
        }
        $re2=$this->joinProcess($userinfo['rank'],$goods_info['rank'],$userid);
        
        $re3=M('orders')->where(['orderId'=>$order_id])->setField('orderStatus',3);
        if($re1 && $re2 && $re3){
            M()->commit();
            //添加消息
            D('ImproveAPI/Msg')->addMsgHook([
                'info'=>$order_info,
                'moduleId'=>4,
                'changeType'=>3,
            ]);
            M('autofahuo_log')->add([
                'userid'=>$userid,
                'order_id'=>$order_id,
                'time'=>time(),
                'status'=>1,
                'remark'=>'平台商品的代金券发货成功'
            ]);
            return true;
        }else{
            M()->rollback();
            $error_info=[
                'userid'=>$userid,
                'order_id'=>$order_id,
                'time'=>time(),
                'status'=>0,
                'remark'=>var_export([$re1,$re2,$re3,$re4,$re5],true)
            ];
            M('autofahuo_log')->add($error_info);
            return $error_info;
        }
    }
    
    /**
     * @author peng	
     * @date 2017-01-05
     * @descreption 成为会员或升级动作
     */
     
    public function joinProcess($rank,$goods_rank,$userid){
        if($rank==0 || ($rank==2 && $goods_rank==1) || ($rank==3 && $goods_rank<3)){
            return M('users')->where(['userId'=>$userid])->setField('rank',$goods_rank);
            
        }else{
            return true;
        }
    }
    
    function has_buy($rank,$goods_rank){
        
        switch($rank){
            case 0 : return false;
            case 1 : return true;
            case 2 : return !($goods_rank<2); 
            case 3 : return !($goods_rank<3);
        }
     }
     public function cancelOrderHook($orderInfo){
        $user_voucher=M('user_voucher');
        if($orderInfo['voucherIds'] && $orderInfo['voucher_reduce']>0){   //确保实际上有代金过了
            if(in_array($orderInfo['orderStatus'],[-2,1])){
                $user_voucher->where(['id'=>['in',$orderInfo['voucherIds']]])->setInc('num',1);
            }
        }
            
     }
     
    
}