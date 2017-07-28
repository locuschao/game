<?php
namespace ImproveAPI\Model;

/**
 * 基础服务类
 */
use Think\Model;

class BaseModel extends Model
{

    /**
     * 用来处理内容中为空的判断
     */
    public function checkEmpty($data, $isDie = false)
    {
        foreach ($data as $key => $v) {
            if (trim($v) == '') {
                if ($isDie)
                    die("{status:-1,'key'=>'$key'}");
                return false;
            }
        }
        return true;
    }
    
    /**
     * @author peng
     * @date 2017-07
     * @descreption 发手机验证码
     */
    public function sendPhoneCode($phone,$msg) {
        vendor('Alidayu.Alidayu');
        $sendMsg = new \Alidayu();
        $code = rand(100000, 999999);
        
        $result = $sendMsg->sendMsg((string)$code,$phone,$msg);
        //$result = $sendMsg->sendMsg('123456',$phone,$msg);
        if($result->sub_msg){
            return [
                'status' => 0,
                'info' => '短信发送失败'
            ];

        }else{
            $_SESSION['registerCode'] = $code;
            $_SESSION['registerCode_expire'] = time()+900;
            $_SESSION['phone'] = $phone;
            return [
                'status' => 1,
                'info' => '短信发送成功',
                'code_token' => session_id()
            ];

        }
        
    }
    
    
    /**
     * @author peng
     * @date 2017-07
     * @descreption 验证手机验证码是否正确,一定要发送code_token过来才能验证
     */
    public function checkRegisterCode()
    {   
        $post = getData();
        
        if(time() > $_SESSION['registerCode_expire']){
            return [
                'status'=>-1,
                'info'=>'验证码过期'
            ];
        }
        if($_SESSION['phone'] != $post['phone']){
            return [
                'status'=>-2,
                'info'=>'手机不匹配'
            ];
        }
        
        if((string)$post['code'] == $_SESSION['registerCode']){
            return [
                'status'=>1,
                'info'=>'验证正确'
            ];
        }else{
            return [
                'status'=>0,
                'info'=>'验证错误'
            ];
        }
        
    }
    

}
;
?>