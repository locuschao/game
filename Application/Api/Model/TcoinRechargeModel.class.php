<?php
namespace Api\Model;
class TcoinRechargeModel{
    private $url=[
        'voucherDist'=>'http://tcoin.52tt.com/tcoin/admin/dist/single/voucherDist.shtml',
        'vouchers'=>'http://tcoin.52tt.com/tcoin/admin/log/vouchers.shtml',
        'checkBalanceD'=>'http://tcoin.52tt.com/tcoin/admin/dist/rest/single/checkBalanceD.shtml?v=',
        'validAccount'=>'http://tcoin.52tt.com/tcoin/admin/dist/rest/single/validAccount.shtml',
        'getAccountApi'=>'http://ttregister.duapp.com/registerNew?chId=1875555&cpId=Y1875555'
    ];
    /**
     * @author peng
     * @date 2017-02
     * @descreption TT版本的发货
     */
    public function TTautoFahuo($orderId,$order_info){
        if(C('TT_AUTO_RECHARGE')===false) return [
                'status' =>-1,
                'msg'=>'关闭了TT的自动充值功能'
            ];
        $agent_info = D('Api/AutoBase')->getAgentInfo($order_info); #设置并且获得了代理的信息
        
        if(!$agent_info) return [
            'status'=>-1,
            'msg'=>'没有绑定代理'
            ];
        $account = $order_info['account'];
        if($order_info['orderType'] == 2) { #代充
            $comment = '代充';
            if($account == ''){
                return [
                'status'=>-1,
                'msg'=>'代充账号不能为空'
                ];
            }
            $info='';
        }else if($order_info['orderType'] == 1){ #首充
            $comment = '首充';
            if(!$order_info['selfBuildAccount']) {
                $result = $this->getGameIdAndKey($order_info['gameName']);
           
                if($result['status'] == 0){
                    return $result;
                }
                $get_result = $this->getAccount($result['info']);
                if($get_result['status'] == 0) {
                    return $get_result;
                }
                $account = $get_result['info']['account'];
            }else{
                $account = $order_info['selfBuildAccount'];#自建账号
            }
            $info=join('|',[
                    $order_info['userAddress'],
                    $account,
                    $get_result['info']['password']?:'原密码'
                ]).';';
        }else{
            return [
                'status'=>-1,
                'msg'=>'该订单类型不符合自动发货'
            ];
        }
        
        
        $recharge_post = [
                'createAmount'=>$order_info['totalMoney'],#代金券面额
                'createVouchersCount'=>1,#代金券张数
                'accounts'=>$account,
                'gameName'=>$order_info['gameName'],
                'comment'=>$comment
            ];
        
        
        $re = $this->findOutGameIdAndName($recharge_post);
        if($re['status'] == 1) {
            $valiate_arr = $re['info'];
        }else {
            return $re;
        }
        $re = $this->recharge($recharge_post,$valiate_arr);
        if ($re['status']==1) {
            return D('Home/SdkAgent')->_fahuoHandle($orderId,$info);
        }else {
            return [
                'status'=>-1,
                'msg'=>$re['info']
            ];
        }
            
    }
    
    /**
     * @author peng
     * @date 2017-03
     * @descreption 针对首充，根据游戏名找到gameId和gameKey
     */
    public function getGameIdAndKey($gameName) {
        $ttgame = M('ttgame');
        $relation = [
            '去吧皮卡丘'=>207705240,
            '大奇幻时代'=>201608027,
            '问道'=>201607033,
            '我叫MT'=>201508632,
            '斗罗大陆神界传说'=>201508337,
            '少年三国志'=>201610914
        ];
        if($relation[$gameName]){
            if($gameKey = $ttgame->where(['gameId'=>$relation[$gameName]])->getField('gameKey')){
                return [
                    'status'=>1,
                    'info'=>[
                        'gameId'=>$relation[$gameName],
                        'gameKey'=>$gameKey
                    ]
                ];
            }else{
                return [
                    'status'=>0,
                    'info'=>'没有gamekey'
                ];
            }
            
        }else{
            if($gameInfo = M()->query('select * from __PREFIX__ttgame where gameName like "'.$gameName.'%" limit 1')) {
                return [
                'status'=>1,
                'info'=>$gameInfo[0]
                ];
            }else{
                return [
                'status'=>0,
                'info'=>'游戏不在服务内'
                ];
            }
        }
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 找到具体游戏的id和名称 传入游戏名称和账号,针对续充的
     */
    public function findOutGameIdAndName($arr){
        $gameInfo=M()->query('select * from __PREFIX__ttgame where gameName like "'.$arr['gameName'].'%"');
        
        $gameInfoCount=count($gameInfo);
        
        if($gameInfoCount>=1){
            foreach($gameInfo as $row) {
                $validate_re=$this->validate([   #验证账号有效性
                    'accounts'=>$arr['accounts'],
                    'gameId'=>$row['gameId']
                ]);
                if ($validate_re['status']==1) { #验证了有效了
                    $validate_re['info']['gameId']=$row['gameId'];
                    $validate_re['info']['gameName']=$row['gameName'];
                    return [
                        'status'=>1,
                        'info'=>$validate_re['info']
                    ];
                } 
            }
            return $validate_re;
            
        }else{
            return [
                'status'=>0,
                'info'=>'游戏不在服务内'
            ];
        }   
    }
     
    public function recharge($arr,$valiate_arr){
            
        $tcoin=D('Api/TcoinBase');
        
        $createAmount=$arr['createAmount'];//代金券面额
        $createVouchersCount=$arr['createVouchersCount'];//代金券张数
        
        $recharge_data=[
            '_csrf'=>$tcoin->getSecureCode(),
            'accounts'=>$valiate_arr['validedAccounts'][0],
            'balance'=>$valiate_arr['balance'],
            'comment'=>$arr['comment'],
            'counts[0].costTcoin'=>$createAmount*$createVouchersCount*$valiate_arr['discount'],   //应付的人民币,
            'counts[0].createAmount'=>$createAmount,   //应付的人民币,
            'counts[0].createVouchersCount'=>$createVouchersCount,   //应付的人民币,
            'discount'=>$valiate_arr['discount'],
            'effectiveDate'=>date('Y-m-d'),
            'expiryDate'=>date('Y-m-d',strtotime('+ 1 month 2 day')),
            'gameId'=>$valiate_arr['gameId'],
            'gameName'=>$valiate_arr['gameName'],
            'payPwd'=>D('Api/AutoBase')->getAgentInfo()['pay_pwd'],
            //'payPwd'=>'asdf345',	
            'uidStr'=>$valiate_arr['uids'][0],
            'validedAccounts'=>$valiate_arr['validedAccounts'][0]
        ];
        
        if($this->getAmount($recharge_data)<=0) {
            return [
                'status'=>0,
                'info'=>'账户余额不足！'
            ];
        }
        
        preg_match('/(?<=\sid=\"SUBMIT_FORM\"\s)action=\"(.*?)\"/',$tcoin->access($this->url['voucherDist']),$url);
        $recharge_url='http://tcoin.52tt.com'.$url[1];
        
        $post_info=$tcoin->access($recharge_url,$recharge_data,[
            'do_recharge'=>true
        ]);
        
        preg_match('/(?<=Location:\s)\S+/',$post_info,$location);
        if(isset($location[0]) && $location[0] == $this->url['vouchers']) {
            return [
                'status'=>1,
                'info'=>'充值成功'
            ];
        }else{
            preg_match('/alert\(\'密码错误\'\);/m',$post_info,$re);
            if(!empty($re)) {
                return [
                    'status'=>0,
                    'info'=>'支付密码错误'
                ];
            }else{
                return [
                    'status'=>0,
                    'info'=>'未知的失败'
                ];
            }
        }
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 查询账户的剩余余额
     */
    public function getAmount($recharge_data){
        $tcoin=D('Api/TcoinBase');
       
        return $tcoin->getReturnData(
            $tcoin->access($this->url['checkBalanceD'].mt_rand(10000,90000),$recharge_data)
            )['balance'];
             
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 验证账号有效性
     */
     public function validate($data){
        $tcoin=D('Api/TcoinBase');
        $valiate_arr=$tcoin->getReturnData($tcoin->access($this->url['validAccount'],$data));
       
        if(empty($valiate_arr)) return [
            'status'=>0,
            'info'=>'处理异常了'
        ];
        if(empty($valiate_arr['uids'])) {
            return [
                'status'=>0,
                'info'=>'无效账号！'
            ];
        }
        return [
            'status'=>1,
            'info'=>$valiate_arr
        ];
     }
     /**
     * @author peng
     * @date 2017-03
     * @descreption 获取TT账号
     */
    public function getAccount($arr) {
        
        $url = $this->url['getAccountApi'].'&'.'gameId='.$arr['gameId'].'&gameKey='.trim($arr['gameKey']);
        
        $info = D('Api/TcoinBase')->curlPost($url,[],'',true,['is_return_header'=>0]);
        
        if($info['http_code'] != 200){
            return [
            'status'=>0,
            'info'=>'参数个数不对'
            ]; 
        }
        $re_arr = json_decode($info['return_str'],true);
        
        if($re_arr['head']) {
             return [
            'status'=>0,
            'info'=>$re_arr['head']['message']
            ]; 
        }else if($re_arr['account'] != '' && $re_arr['password'] != ''){
            return [
                'status'=>1,
                'info'=>$re_arr
            ];
        }else{
            return [
            'status'=>0,
            'info'=>'获取首充未知的错误'
            ]; 
        }
        
    }
}