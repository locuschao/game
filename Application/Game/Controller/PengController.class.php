<?php
namespace Game\Controller;
use Think\Controller;
use Think\Model;
use Lib\Exp\Aes as Aes;
/**
 * @author peng
 * @date 2017-01
 * @descreption 测试控制器
 */
class PengController extends Controller
{   
    
    public function test() {
        //D('ImproveAPI/Msg')->addMsgHook();
       //var_dump(D('Home/SdkAgent')->autoFahuo(3305));
        //echo D('Api/AutoBase')->encrypt('Y24QEb61.');
        //A('GameAPI/Validatadc')->validataAccount();
        
        //echo '<pre>';
        //var_dump(D('Api/TcoinBase')->curlPost('https://statecheck.swiftpass.cn/pay/wappay?token_id=f02bab0a69532d39ca4a73192fb53b7c&service=pay.weixin.wappayv3'));
        //$info =D('Api/TcoinBase')->curlPost('https://statecheck.swiftpass.cn/pay/wappay?token_id=77360075d98ef15825b176aa99ee7227&service=pay.weixin.wappayv3');
        //preg_match('/\<a id="cli".+href="(.+)"\>去支付</a>/',$info,$match);
        //preg_match('/\<a id="cli".*href="(.*)"/',D('Api/TcoinBase')->curlPost('https://statecheck.swiftpass.cn/pay/wappay?token_id=77360075d98ef15825b176aa99ee7227&service=pay.weixin.wappayv3'),$match);
        //var_dump($match);
        //echo '</pre>';
        //exit;
       //echo '<pre>';
//       var_dump(D('Admin/Develop')->finishedRefund(2017733723228));
//       echo '</pre>';
//       exit;
      // echo '<pre>';
//       var_dump(D('Home/SdkAgent')->autoFahuo(3134));
//       echo '</pre>';
//       exit;
        //echo '<pre>';
//        var_dump(D('Api/LeRecharge')->getAccount([
//                'gameId'=>201508121,#代金券面额
//                'gameKey'=>'b3da6ff6193040dc00a1d6be486d807b'
//            ]));
//        echo '</pre>';
        
        //echo '<pre>';
//        var_dump(D('Api/TcoinBase')->curlPost("http://tcoin.52tt.com/tcoin/index.shtml",'','Cookie:_amount=513.14; JSESSIONID=aaad9lWc_4uUm7Sz_qzSv; SERVER_ID=369f21f3-0fb75eb9',false,['time_out'=>5]));
//        echo '</pre>';
//        exit;
//        
        #乐8
  //      $obj["userId"] = 199;
//        $obj["shopId"] = 22;
//        $obj["orderId"] = 3116;
//        $order_info = D('Home/Orders')->getOrderDetails($obj)['order'];
//        $order_info['shopId'] = $obj["shopId"];
//        var_dump(D('Api/LeRecharge')->LeAutoFahuo($obj["orderId"],$order_info));
        #TT
        
  //      
  //      $obj["userId"] = 199;
//        $obj["shopId"] = 18;
//        $obj["orderId"] = 3124;
//        $order_info = D('Home/Orders')->getOrderDetails($obj)['order'];
//        $order_info['shopId'] = $obj["shopId"];
//        
//        var_dump(D('Api/TcoinRecharge')->TTautoFahuo($obj["orderId"],$order_info));

        //$aes = new Aes();
//        echo $aes::AesEncrypt('UYG87f.');
//        $obj["userId"] = 199;
//        $obj["shopId"] = 22;
//        $obj["orderId"] = 3116;
//        $order_info = D('Home/Orders')->getOrderDetails($obj)['order'];
//        $order_info['shopId'] = $obj["shopId"];
//        echo '<pre>';
//        var_dump($order_info,D('Api/AutoBase')->getAgentInfo($order_info));
//        echo '</pre>';
//        exit;
        //$start = microtime(true);
//        $aes = new Aes();
//        echo $aes::AesEncrypt('UYG87f.');
//        echo '<br/>';
//        echo $aes::AesDecrypt('/faLL/4HHO/mhWoIPkZOmA==');
//        echo '<pre>';
//        var_dump(microtime(true)-$start);
//        echo '</pre>';
//        exit;
        //echo '<pre>';
//        var_dump(D('Api/LeRecharge')->LeAutoFahuo(1663,[
//                'lmoney'=>50,#代金券面额
//                'account'=>'ceshi108'
//            ]));
//        echo '</pre>';
   
    }
    public function test1() {
        
        $aes = new Aes();
        echo $aes->encrypt('Defj98');
        echo '<br/>';
        echo $aes->decrypt('1H5QEOv5/9RO12zRQ+S6GA==');
    }
   
}
?>