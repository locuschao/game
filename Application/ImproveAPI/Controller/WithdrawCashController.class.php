<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class WithdrawCashController extends BaseController
{
    public function __construct() {
        parent::__construct();
        parent::isLogin();
    }
    
    /**
     * @api {post} WithdrawCash/applyList 余额提现申请列表
     * @apiParam {Number} p 页码
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
    public function applyList() {
        $post = getData();
        $_GET['p'] = $post['p']; 
        $status_arr = [
            0=>'待处理',
            1=>'已通过',
            -1=>'拒绝',
            3=>'不通过'
        ];
        $condition = [
            'userId' => $_SESSION['userId']
        ];
        $page = new \Think\Page(M('ps_tixian')->where($condition)->count(), 15);
        if($data = M('ps_tixian')->where($condition)
            ->order('id DESC')
            ->limit($page->firstRow,$page->listRows)
            ->select()){
                foreach($data as $k=>$row){
                    $data[$k]['txStatus'] = $status_arr[$row['txStatus']];
                    $data[$k]['txTime'] = substr($row['txTime'], 0, 10);
                    $data[$k]['orderNo'] = $row['orderNo']?:$row['id'];
                }
                $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
                ]);
        }else $this->error('没有数据');
    }
    
    /**
     * @api {post} WithdrawCash/bankList 银行列表
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
    public function bankList() {
        if($data =  M('banks')->where(array(
            'bankFlag' => 1
        ))->select()){
            $this->ajaxReturn([
            'status'=>1,
            'info'=>$data
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} WithdrawCash/withdrawHandle 余额提现提交申请
     * @apiParam {Number} param 简写成下面
     * ['bankName'];
       ['bankNo'];
        ['inputMoney'];
        ['bankUserName'];
        ['payPwd'];
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
    public function withdrawHandle()
    {
        $this->ajaxReturn(D('ImproveAPI/Withdraw')->tixianHandle());
    }
}