<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class VoucherController extends BaseController{
    /**
     * @api {post} Voucher/myVoucherList 我的狗粮
     * @apiParam {Number} type 1 未使用 2已使用
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
    public function myVoucherList(){
        parent::isLogin();
        if($data = D('Voucher')->getMyVoucher()){
            foreach($data as $k=>$row){
                $data[$k]['expireTime']=date('Y-m-d H:i',$row['expireTime']);
            }
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
}