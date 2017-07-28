<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class AgentController extends BaseController{
    
    public function __construct() {
        parent::__construct();
        //parent::isLogin();
    }
    
    /**
     * @api {post} Agent/agentIndex 代理首页
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info":   //'agentTotalPrice' 累积销售 累积佣金 我的佣金
        //'agentBalance'  可提现佣金
        //'agentPayPrice' 已提现订单佣金
        //myCostomerCount 我的客户个数,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function agentIndex() {
        $userid = $_SESSION['userId'];
        $userInfo = M('users')->field('agentTotalPrice,agentBalance,agentPayPrice')->where(array(
            'userId' =>$userid
        ))->find();
        
        $data['myCostomerCount'] = M('users')->where(['partnerId'=>$userid])->count();
        $data['shareUrl'] = 'http://192.168.3.9/Game/Register/register/?partner='.urlencode(authCode($userinfo['userId'],'ENCODE'));
        $info = array_merge($userInfo,$data);
        if($info) $this->ajaxReturn([
            'status'=>1,
            'info'=>$info
        ]); else $this->error('没有数据');
    }
    
    /**
     * @api {post} Agent/myMember 我的客户
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 
     *userPhoto 头像 ，userPhone 会员名称
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function myMember() {
        $post = getData();
        $condition = [
            'partnerId'=>$_SESSION['userId']
        ];
        $page = new \Think\Page(M('users')->where($condition)->count(),10);
        $data = M('users')->where($condition)->field('userId userCode,userPhoto,userPhone')
        ->limit($page->firstRow, $page->listRows)
        ->select();
        foreach($data as $k=>$row){
            //$data[$k]['usercode'] = _encrypt($row['userCode'], 'ENCODE');
            $data[$k]['userPhoto'] = C('RESOURCE_URL').$row['userPhoto'];
            $data[$k]['userPhone'] = hidePhone($row['userPhone']);
        }
        if($data) $this->ajaxReturn([
            'status'=>1,
            'info'=>$data
        ]);
        else $this->error('没有会员');
    }
    
    /**
     * @api {post} Agent/getFenchengList 获取全部分拥订单
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
           }
     */
    public function getFenchengList(){
        $post = getData();
        $condition = [
            'is_fencheng'=>1,
            'o.userId'=>['in',M('users')->where(['partnerId'=>$_SESSION['userId']])->getField('userId',true)]
        ];
        $page = new \Think\Page(M('orders')->where($condition)->count(),20);
        $data = M('orders')->where($condition)->field('orderNo,needPay,gain_price,o.isAgent,goodsId,gv.rank,u.userPhone')
        ->join('o inner join '.C('DB_PREFIX').'order_goods og on o.orderId=og.orderId')
        ->join('inner join '.C('DB_PREFIX').'goods_voucher gv on og.goodsId=gv.id')
        ->join('inner join '.C('DB_PREFIX').'users u on o.userId=u.userId')
        ->join('left join '.C('DB_PREFIX').'percentage_log p on o.orderId=p.orderId')
        ->limit($page->firstRow, $page->listRows)
        ->order('isAgent,p.id desc')
        ->select();
        
        foreach(M('profit_setting')->where([
        'tg_level'=>M('users')->where(['userId'=>$_SESSION['userId']])->getField('rank')
        ])->select() as $k=>$row){
            $buyer_money_arr[$row['gm_level']] = $row['return_profit'];
        }
       
        foreach($data as $k=>$row){
            $data[$k]['userPhone'] = $row['userPhone']?hidePhone($row['userPhone']):$row['userName'];
            if($row['isAgent'] == 0){
                $data[$k]['statusText'] = '未结算';
                $data[$k]['gain_price'] = $buyer_money_arr[$row['rank']];
            }else if($row['isAgent'] == 1){
                $data[$k]['statusText'] = '已清算';
            }
            unset($data[$k]['isAgent']);
        }
       
        if($data) $this->ajaxReturn([
            'status'=>1,
            'info'=>$data
        ]);
        else $this->error('没有数据');
    }
    
    /**
     * @api {post} Agent/apply 处理佣金提现到余额或者佣金提现到银行卡
     * @apiParam {string} bankUserName 银行的用户名
     * @apiParam {string} bankName 银行名称
     * @apiParam {string} bankNum 银行号码
     * @apiParam {number} applyPrice 申请的数额
     * @apiParam {int} applyType 申请类型 0：申请到银行卡 1：申请到余额
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     *     }
     */
    public function apply()
    {
       $this->ajaxReturn(D('Agent')->checkApply());
    }
    
}