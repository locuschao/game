<?php
namespace ImproveAPI\Model;
use Think\Model;
class UserModel extends Model
{ 
    public $rank_text = [
            1=>'钻石VIP特权',
            2=>'白金VIP特权',
            3=>'黄金VIP特权'
    ];
    
    /**
     * @author peng
     * @date 2017-07
     * @descreption 生成用户的唯一的8位起ID编号
     */
    public function createUserId(){
        $id = M('orderids')->add(array('rnd' => microtime(true)));
        $len = strlen($id);
        if($len>7) return $id;
        else return mt_rand(pow(10,7-$len),pow(10,8-$len)-1).$id;
    }
    
}