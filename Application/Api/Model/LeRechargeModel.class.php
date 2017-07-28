<?php
namespace Api\Model;
/**
 * @author peng
 * @date 2017-03
 * @descreption 乐8自动充值
 */
class LeRechargeModel{
    private $url=[
        'index'=>'http://gh.le890.com/',
        'recharge'=>'http://gh.le890.com/?m=member&a=pay',
        'validate_user'=>'http://gh.le890.com/?m=member&a=check_username',
        'le8_index'=>"http://tcoin.52tt.com/tcoin/index.shtml",
        'register_post_url'=>'http://hs.le890.com/member.php?a=reg&act=1',
        'register_yzm_url'=>'http://hs.le890.com/code.php'
    ];
    private $order_info;
    
    
    /**
     * @author peng
     * @date 2017-03
     * @descreption 乐8版本的发货，现在仅支持续充
     */
    public function LeAutoFahuo($orderId,$order_info){
        $this->order_info = $order_info;
        
        if($this->order_info['orderType'] == 1){ #首充
            if($order_info['selfBuildAccount']){ #自建首充
                $account = $order_info['selfBuildAccount'];
                $psword = '原密码';
                
            }else{ #系统自动首充
                $registerInfo = D('Api/LeRegister')->registerSc();
                if($registerInfo['status'] == 1){
                    $account = $registerInfo['info']['account'];
                    $psword = $registerInfo['info']['password'];
                }else{
                    return [
                    'status'=>-1,
                    'msg'=>$registerInfo['info']
                    ];
                }
                
            }
            $this->order_info['account'] = $account;
            $info = join('|',[
                    $order_info['userAddress'],
                    $account,
                    $psword
                ]).';';
        }else if($this->order_info['orderType'] == 2){ #代充
            $info = '';
        }else{
            return [
                'status'=>-1,
                'msg'=>'该订单类型不符合自动发货'
            ];
        }
        
        $re = $this->recharge();
        if ($re['status']==1) {
            return D('Home/SdkAgent')->_fahuoHandle($orderId,$info);
        }else {
            return [
                'status'=>-6,#表示充值失败
                'msg'=>$re['info']
            ];
        }
            
    }
    
    public function recharge() {
        
        if(C('LE8_AUTO_RECHARGE')===false) {
            return [
            'status'=>0,
            'info'=>'配置文件没有开启自动充值'
            ];
        }
        $agent_info = D('Api/AutoBase')->getAgentInfo($this->order_info); #设置并且获得了代理的信息
        if(!$agent_info) return [
            'status'=>0,
            'info'=>'没有绑定代理'
            ];
        if(!$this->checkUsername()){
            return [
            'status'=>0,
            'info'=>'用户名无效'
            ];
        }
        $recharge_info = json_decode(D('Api/TcoinBase')->curlPost('http://gh.le890.com/?m=member&a=pay',[
            'username'=>$this->order_info['account'],
            'lmoney'=>$this->order_info['totalMoney'],
            //'pay_pwd'=>'345sdr,d',
            'pay_pwd'=>$agent_info['pay_pwd'],
            'act'=>1
            
        ],$this->getToken(),false,['is_return_header'=>0]),true);
        if($recharge_info['code'] == '1000') {
            return [
                'status'=>1
            ];
        }else if($recharge_info['code'] == '1001') {
            return [
                'status'=>0,
                'info'=>'支付密码错误'
            ];
            
        }else {
            return [
                'status'=>0,
                'info'=>'未知的错误'
            ];
        }
    }
    public function checkUsername($account='') {
        $account = $this->order_info['account']?:$account;
        return D('Api/TcoinBase')->curlPost($this->url['validate_user'].'&uname='.$account,'&stamp='.time(),$this->getToken(),false,['is_return_header'=>0])=='success';
    }
    public function getToken() {
        $autoBase = D('Api/AutoBase');
        for($i=0;$i<2;$i++) {
            if($i == 0) $cookie_str = $autoBase->getAgentInfo()['cookie_str'];
            else if($i == 1) $cookie_str = $autoBase->getCookieStr();
            $header_cookie = 'Cookie:PHPSESSID='.$cookie_str.';';
            if(!$header_cookie || D('Api/TcoinBase')->curlPost($this->url['recharge'],'',$header_cookie,true)['http_code']!='200') {
                $this->login();
            }else {
                return $header_cookie;
            }
            
        }
    }
    public function login(){
        $autoBase = D('Api/AutoBase');
        $agent_info = $autoBase->getAgentInfo();
        $yzminfo = $this->yzm();
        $header_cookie = 'Cookie:PHPSESSID='.$yzminfo[1].';';
        $identifyRe = $autoBase->identifyCode($yzminfo[2]);#自动识别验证码
        
        if($identifyRe['msg'] != 'ok') {
            return [
                'status'=>0,
                'info'=>'验证码识别错误'
            ];
            
        }
        
        $info = D('Api/TcoinBase')->curlPost($this->url['index'],[
            'm'=>'login',
            'a'=>'index',
            'act'=>1,
            'username'=>$agent_info['username'],
            'password'=>$agent_info['login_pwd'],
            'checkCode'=>$identifyRe['result']['code']
        ],$header_cookie,true);
        
        if(M('agent')->where([
        'id'=>$agent_info['id']
        ])->save([
        'cookie_str'=>$yzminfo[1]
        ])!==false)
        return ['status'=>1];
        else return [
        'status'=>0,
        'info'=>'保存token失败'
        ];
        
    }
    public function yzm($code_url='http://gh.le890.com/checkcode.php') {
        $info = D('Api/TcoinBase')->curlPost($code_url,[],'',false,['is_return_header'=>1]);
        preg_match('/PHPSESSID=(.*?);[\s\S]*Vary: Accept-Encoding\s*([\s\S]*)/',$info,$match);
        return $match;
        
    }
    /**
     * @author peng
     * @date 2017-03
     * @descreption 此方法被计划任务调用
     */
    public function updateToken() {
        $keyName = 'le8UpdateToken';
        
        if(!F($keyName) || (time()-F($keyName)>1200)){  #20分钟刷新一下session
            foreach(M('agent')->select() as $row){
                switch($row['sdk_type']){
                    case 1:
                        $token = 'Cookie:PHPSESSID='.$row['cookie_str'].';';
                        $url = $this->url['recharge'];
                        break;
                    case 2:
                        $token = $row['cookie_str'];
                        $url = $this->url['le8_index'];
                        break;
                }
                D('Api/TcoinBase')->curlPost($url,'',$token,false,['time_out'=>5]);#5秒超时
            }
            F($keyName,time());
        }
    }
    
    

}