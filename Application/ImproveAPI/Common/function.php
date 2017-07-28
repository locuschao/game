<?php
function requestHeader() {
    static $header;
    foreach ($_SERVER as $key => $value) { 
        if ('HTTP_' == substr($key, 0, 5)) { 
            $headers[str_replace('_', '-', substr($key, 5))] = $value; 
        } 
    }
    if($header) return $header;
    else{
        $header = [
        'token' => $headers['TOKEN'],
        'shopId' =>$headers['SHOPID'],
        'version' =>$headers['VERSION'],
        'client' =>$headers['CLIENT']
        ];
        return $header;
    }
    
}

/**
* 验证手机号是否正确
* @author honfei
* @param number $mobile
*/
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('/^1[3|4|5|7|8][0-9]{9}$/', $mobile) ? true : false;
}


function getShopId(){
    return requestHeader()['shopId'];
}

/**
 * @author peng
 * @date 2017-07
 * @descreption 获取来自axious的参数
 */
function getData(){
    
    $input = json_decode($_POST['data']?:$_GET['data'],true);
    
    foreach($input as $k=>$value){
        $input[$k] = addslashes($value);
    }
    
    return $input;
}

/**
 * @author peng
 * @date 2017-07
 * @descreption 隐藏电话的部分
 */
function hidePhone($phone) {
    return substr($phone,0,3).'****'.substr($phone,-4);
}
