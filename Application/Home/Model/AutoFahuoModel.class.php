<?php
namespace Home\Model;
/**
 * @author peng
 * @copyright 2016
 * @remark 自动发货模型
 */


class AutoFahuoModel extends BaseModel
{
    public function autoHandle($post_data){
    	
        $url=C('SDK_FAHUO_URL');
        
        $util_model=D('Common/Util');
        $first_data=$util_model->curl_post($url);
        $data=json_decode($first_data,true);
        
        if ( $data['auth_id'] ) {
            $timestamp=time();
            $re = $util_model->curl_post( $url,array_merge([
                //验证参数
                'sign'=>$util_model->createSignture($data['auth_id'],$timestamp),
                'timestamp'=>$timestamp,
                'auth_id'=>$data['auth_id'],
                
            ],$post_data) );
            
            return json_decode($re,true);
            
        }else{
            return [
                'error'=>1,
                'msg'=>'握手失败'
            ];
        }

    }
    
    
    
    
}