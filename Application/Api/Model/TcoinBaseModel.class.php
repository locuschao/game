<?php
namespace Api\Model;
class TcoinBaseModel{
    private $secure_code=0;
    private $url=[
        'login'=>'http://tcoin.52tt.com/tcoin/login'
    ];
    function getCookie($agent_info = '') {
        $agent_info = D('Api/AutoBase')->getAgentInfo()?:$agent_info;
        
        if(!$agent_info){
            writelog('error','代理没设置!');
            return false;
        }
        if(!$this->getSecureCode()){
            writelog('error','获取安全码失败!');
            return false;
        }
       
        
        preg_match_all('/(?<=\bSet-Cookie:\s)(\S+)=(\S+)\b/',$this->curlPost($this->url['login'],[
            'username'=>$agent_info['username'],
            'password'=>$agent_info['login_pwd'],
            'submit'=>'',
            '_csrf'=>$this->getSecureCode()
        ]),$match);
        
        $cookie = $match[2];
      
        if(count($cookie) != 3) {
            writelog('error','登录失败!');
            return false;
        }
        
        return 'Cookie:_amount='.$cookie[0].'; JSESSIONID='.$cookie[1].'; SERVER_ID='.$cookie[2];
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 获取session的cookie
     */
    function getCookieStr() {
        $agent_info = D('Api/AutoBase')->getAgentInfo();
       
        if(!$agent_info['cookie_str']) {
            if(!$cookie_str=$this->getCookie()) {
                return false;
            }
            
            if(!M('agent')->where([
            'id'=>$agent_info['id']
            ])->save([
            'cookie_str'=>$cookie_str
            ])) {
                writelog('error','保存cookie_str失败');
                
                return false;
            }
            
            return $cookie_str;
        }
        return $agent_info['cookie_str'];
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 直接访问某个地址
     */
    
    function access($url,$post_arr=array(),$extend=array()) {
        
        if(!$this->getCookieStr()) {
            return false;
        }
        
        for($i=0;$i<2;$i++) {
            
            $curl_info=$this->curlPost($url,$post_arr,$this->getCookieStr(),true);
           
            if(isset($extend['do_recharge'])) return $curl_info['return_str'];
            
            if($curl_info['http_code']!=200) {
                #session失效了，清除缓存cookie
                M('agent')->where([
                'id'=>D('Api/AutoBase')->getAgentInfo()['id']
                ])->save([
                'cookie_str'=>''
                ]);
                
            }else{
                return $curl_info['return_str'];
            }
            sleep(1);//停止1秒重新发起登录
        }
        return false;
        
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 得到安全码
     */
    
    function getSecureCode(){
        if($this->secure_code) return $this->secure_code;
        $contentStr=$this->curlPost($this->url['login'].'.shtml','','',false,['is_return_header'=>0]);
        
        preg_match('/(?<=name=\"_csrf\" content=)\"(.+?)\"/',$contentStr,$match);
        if(!empty($match)){
            $this->secure_code=$match[1];
            return $match[1];
        }else{
            return false;
        }
    }
    
    /**
     * @param $url是post请求的地址，
     * @param $arr是post提交的数据，
     * @param $cookie_str是cookie头部信息，
     * @param $is_getinfo是得到更多的返回信息,
     * @param $extend是未来的拓展参数
     */
    function curlPost( $url , $arr=array() , $cookie_str='' , $is_getinfo=false ,$extend=array()){
        
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        
        curl_setopt($curl,CURLOPT_HEADER,isset($extend['is_return_header'])?$extend['is_return_header']:1);
        //curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cookie:b=2;a=3'));
        curl_setopt($curl,CURLOPT_TIMEOUT,isset($extend['time_out'])?$extend['time_out']:20);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        //允许访问https
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        if($cookie_str) {
            $header_arr =  [$cookie_str]; 
            
        }else if(!empty($arr)){
            $header_arr =  ['Content-Type:application/x-www-form-urlencoded'];//表示以form形式提交表单
        }
        
        if($header_arr)
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header_arr);  
        
        if(!empty($arr))
        curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($arr));//key=value&的形式提交，兼容性更好
        
        if($is_getinfo) {
            $data=[
                'return_str'=>curl_exec($curl),
                'http_code'=>curl_getinfo($curl, CURLINFO_HTTP_CODE)
            ];
        }else{
            $data=curl_exec($curl);
        }
        
        curl_close($curl);
        return $data;
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 得到curl的数据部分
     */
    public function getReturnData($data){
        preg_match('/{.*}/',$data,$json);
        return json_decode($json[0],true);    
    }
}