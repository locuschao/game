<?php
namespace Common\Model;

/**
 * 公共模型方法,author peng 
 */
class UtilModel
{
    function curl_post( $url , $arr=array() ){
    
        $curl=curl_init();
       
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HEADER,0);
        curl_setopt($curl,CURLOPT_TIMEOUT,60);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$arr);
        
        $data=curl_exec($curl);
       
        curl_close($curl);
        return $data;
    }
    
    /**
     * @remark 生成签名 
     */
    function createSignture($nonce,$timestamp){
        
        define('TOKEN','9wj.df8h?[8.s5ert');
        $tmpArr = array(TOKEN,$nonce,$timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        return sha1( $tmpStr );
        
    }
    
    /**
     * @author peng	
     * @date 2017-01
     * @descreption 如果是平台则不需要判断是否登录
     */
    public function checkIsPingTai(){
    	return $_SESSION['oto_mall']['WST_STAFF'] && (trim($_GET['is_pt'])==1 || trim($_POST['is_pt'])==1);
        
    }
    
}