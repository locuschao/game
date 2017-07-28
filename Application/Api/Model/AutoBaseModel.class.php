<?php
namespace Api\Model;
use Lib\Exp\Aes as Aes;
/**
 * @author peng
 * @date 2017-03
 * @descreption 自动充值基础模型
 */
class AutoBaseModel{
    /**
     * @author peng
     * @date 2017-03
     * @descreption 获取绑定代理的所有信息
     */
    public $agent_info;#代理的信息
    protected $aesClass;
    
    public function getAes() {
        if(!$this->aesClass){
            $this->aesClass = new Aes();
        }
        return $this->aesClass;
    }
    public function getAgentInfo($arr) {
        
        if(!$this->agent_info){

            $info = M('agent')->where([
                
                'id'=>M('shop_version_agent')->where([
                            'shopId'=>$arr['shopId'],
                            'versionId'=>$arr['vid']
                  ])->find()['agentId']
            ]
            )->find();
            $aes = new Aes;
            if($info){
                $info['pay_pwd'] = trim($aes->decrypt($info['pay_pwd']));
                $info['login_pwd'] = trim($aes->decrypt($info['login_pwd']));
            }
            
            $this->agent_info = $info;
            
            return $info;
        }
        return $this->agent_info;
    }
    public function getCookieStr($arr) {

        return M('agent')->where([
            'id'=>M('shop_version_agent')->where([
                            'shopId'=>$arr['shopId'],
                            'versionId'=>$arr['vid']
                  ])->find()['agentId']
        ])->find()['cookie_str'];
    }
    
    
    
    /**
     * @author peng
     * @date 2017-03
     * @descreption 识别验证码接口
     * @param $image_content 文件二进制内容
     * @param $type 验证码类型 默认是4位数字
     * @return eg:array [
     *  'msg'=>'ok',
     *  'result'=>[
     *      'code'=>'fsdf'
     *  ]
     * ]
     */
    public function identifyCode($image_content,$type='n4') {
        $host = "http://jisuyzmsb.market.alicloudapi.com";
        $path = "/captcha/recognize";
        $method = "POST";
        $appcode = "a68c34e7121e4376bd83dbd5c7076a95";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "type={$type}";
        $bodys = "pic=".urlencode(base64_encode($image_content));
        //$bodys = "pic=".urlencode('/9j/4AAQSkZJRgABAgAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAAUADwDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD1e4nS2kMsl0YLZk3iV2BjzyTknoMY7gdawl1aXWJFmg0iI2LDK3M8nlyTkg7SkYywU4yNxGQRjtTPHGnPd+GL1NKty1yI0c+SQN0eSWxzzwDx1PTmsSye01HxPYt4bjBh+zOb/wAnGHXA8oybjyxfJOcv1ySOlvsK10dDdXyxRmG108G6jBkIkcRrtwfkDhTuY+hHX7x6AyaDfpq1jBdw2xt1uULCIzsilwzBtuM56D047Vk6tq1pp0UNte3At7idMRyFN5jUg/vCApyOgA5yRjKgEifRH086bA+nzyizt2W3WUrgsQNxfaRyu7vgcg8Z5rFT9+1zNMkj8YiGyOpmyC6S8mxLmS7dmcBghYJtJA3HuQcDOM4FaGp6hPE0cUMTzXc0og2JI8YjYqcOTnGzIzkAnkcEmuMadZZHvkfyNeF8JH0ViPKL7tu5VYfK+3DmTJHXsSB1Goaxp6xIdTuIbRppEjWRgSu49DkZx8uc7jjgcjHG83YcmT6D4mfWL6+0q4s3sLyzIVsTCUEEZUgkAkkA5BHA9840Lmzv7iQSwXihGUfdZlB+gya43wbDGdU1iI3DTaZ9qM1vcM28SSEYlIY8uMhfmOR8nf5jXXxOIQ0UlveOUYqDC7Yxnjjdxx29MHvURd1qKLutSnp87XGoxqRtLBhuRmB9fXHUd+vfJp0RY3dzIjtEfsu/5O3yqcDOcCiih7Cew2TUp4PLjARiu2UMw53MMn2/iIrXlto7i5mjbI+RW3DqCdyn8wAKKKpFIr2QMULSoSN0qfIPujeEzx/wLj6D3zc8uO4E8MsSFFfAGPVQSfr8x5oopx2KjsRQ2kc0GZNzOrMqyE5ZcEgc/r9fwqvpCLc2zyNvX58AJK4AAUe9FFCWwJbH/9k=');
        $url = $host . $path . "?" . $querys;
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        
        return json_decode(curl_exec($curl),true);
    }
    /**
     * @author peng
     * @date 2017-03
     * @descreption aes加密
     */
    public function encrypt($orig_data) {
        return $this->getAes()->encrypt($orig_data);
    }
    /**
     * @author peng
     * @date 2017-03
     * @descreption aes解密
     */
    public function decrypt($ciphertext) {
        return $this->getAes()->decrypt($ciphertext);
    }
}