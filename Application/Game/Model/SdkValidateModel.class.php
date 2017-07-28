<?php
namespace Game\Model;
/**
 * @author peng
 * @date 2017-01
 * @descreption sdk验证接口
 */
class SdkValidateModel extends BaseModel
{
    public function checkAccount($arr){
        
        return D('Common/Util')->curl_post( C('CHECK_ACCOUNT_URL'),[
            'username'=>$arr['username'],
            'app_name'=>$arr['app_name']
        ]);
           
    }
    /*public function getShopIdByAgent($arr){
         return D('Common/Util')->curl_post( C('CHECK_ACCOUNT_URL'),[
            'username'=>$arr['username']
            ]);   
            
    }*/
}