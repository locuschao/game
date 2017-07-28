<?php
namespace ImproveAPI\Model;
use Think\Model;
class VoucherModel extends Model
{
    //获取我的狗粮列表
    public function getMyVoucher() {
        $get=getData();
        $_GET['p'] = $get['p'];
        $user_voucher=M('user_voucher');
        if(!$get['type']) return null;
        if($get['type']==1){
            $condition=[
            'userId'=>$_SESSION['userId'],
            'num'=>['gt',0],
            'expireTime'=>['egt',time()]
            ];
        }else if($get['type']==2){
            $condition=[
            'userId'=>$_SESSION['userId'],
            '_complex'=>[
                '_logic'=>'or',
                'num'=>0,
                'expireTime'=>['lt',time()]
                ]
            ];
            
        }
        $page = new \Think\Page($user_voucher->where($condition)->count(), 10); // 实例化分页类 传入总记录数和每页显示的记录数
        return $user_voucher->where($condition)
        ->field('goods_name,expireTime')
        ->limit($page->firstRow,$page->listRows)
        ->order('id desc')
        ->select();
    }
    
    //获取有效狗粮的条数
    public function getCorrectVouchersCount($arr){
        $where=[
            'is_global'=>1,
            'consume'=>[['gt',0],['elt',$arr['consume']]],
            '_string'=>'(game_id='.$arr['gameid'].' && version_id='.$arr['versionid'].') || (game_id='.$arr['gameid'].' && version_id=0) || (game_id=0 && version_id='.$arr['versionid'].')',
            '_logic'=>'or',
            'discount_amount'=>[['lt',floatval($arr['marketPrice'])],['gt',0]]
            
        ];
        
        return M('user_voucher')->where([
            '_complex'=>$where,
            'userId'=>$arr['userid'],
            'num'=>['gt',0],
            'expireTime'=>['egt',time()],
        ])->count();
                      
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @param $totalMoney是需要支付的金额
     */    
    public function checkVoucher($attrInfo,$goodsInfo,$totalMoney,$data){
        if($totalMoney<=0){
            return 0;
        }
        if($data['vouchers']=='') return 0;
        $reduce_money=0;
        
        foreach($voucherinfo=M('user_voucher')->where([
            'id'=>['in',$data['vouchers']],
            'userId'=>$data['userId'],
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

}