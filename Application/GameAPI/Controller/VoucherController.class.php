<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;
/**
 * @author peng
 * @date 2017-04
 * @descreption 购买会员
 */
class VoucherController extends BaseController
{
    public function myVoucherList() {
        $userId = authCode(I('userId'));
        $get=I('get.');
        $user_voucher=M('user_voucher');
        if(!$get['type']){
            $condition=[
            'userId'=>$userId
            ];
        }else if($get['type']==1){
            $condition=[
            'userId'=>$userId,
            'num'=>['gt',0],
            #date('Ymd',strtotime("+ $row[validTime] day",$row['add_time'])) >= date('Ymd')
            'expireTime'=>['egt',time()]
            ];
        }else if($get['type']==2){
            $condition=[
            'userId'=>$userId,
            '_complex'=>[
                '_logic'=>'or',
                'num'=>0,
                'expireTime'=>['lt',time()]
                ]
            ];
            
        }
        
        $page = new \Think\Page($user_voucher->where($condition)->count(), 10); // 实例化分页类 传入总记录数和每页显示的记录数
        
        $data=$user_voucher->where($condition)
        ->limit($page->firstRow,$page->listRows)
        ->order('id desc')
        ->select();
       
        foreach($data as $k=>$row){
            $data[$k]['expireTime']=date('Y-m-d H:i',$row['expireTime']);
        }
        if($data){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'没有数据'
            ]);
        }
    }
}