<?php
namespace Api\Model;
/**
 * @author peng
 * @date 2017-03
 * @descreption 乐8首充自动注册
 */
class LeRegisterModel{
    private $url=[
        'register_post_url'=>'http://hs.le890.com/member.php?a=reg&act=1',
        'register_yzm_url'=>'http://hs.le890.com/code.php'
    ];
    private $keyName = 'leYzmUpateTime';
    private $code = 'kdsc';
    private $cookie_val = 'f5vqa1faln8uhavg4395q9eas7';
    
    //author: peng descreption:首充注册
    public function registerSc() {
        $header_cookie = 'Cookie:PHPSESSID='.$this->cookie_val.';';
        $username = 'yw'.time();
        $password = mt_rand(100000,999999);
        $response_str = D('Api/TcoinBase')->curlPost($this->url['register_post_url'],[
            'cd'=>$this->code,//验证码
            'channel'=>'youxi0844',
            'ln'=>$username,//游戏账号
            'pw'=>$password
        ],$header_cookie,false,['is_return_header'=>0]);
        if($response_str == 'success') return [
            'status'=>1,
            'info'=>[
            'account'=>$username,
            'password'=>$password
            ]
        ];
        else return [
            'status'=>0,
            'info'=>$response_str
        ];
    }
    
    public function updateToken() {
        $keyName = $this->keyName;
        if(!F($keyName) || (time()-F($keyName)>1200)){  #20分钟刷新一下session
            D('Api/TcoinBase')->curlPost($this->url['register_post_url'],[
                'cd'=>$this->code,//验证码
                'channel'=>'youxi0844',
                'ln'=>'yw1495275282',//游戏账号
                'pw'=>'123456'
            ],'Cookie:PHPSESSID='.$this->cookie_val.';',false,['time_out'=>5]);
            F($keyName,time());
            
        }
        
    }
}