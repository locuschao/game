<?php
/**
 *日志记录，按照"Ymd.log"生成当天日志文件
 * 日志路径为：入口文件所在目录/diy_logs/$type/当天日期.log.php，例如 /logs/error/20120105.log.php
 * @param string $type 日志类型，对应logs目录下的子文件夹名
 * @param string $content 日志内容
 * @return bool true/false 写入成功则返回true
 */
function writelog($type="",$content=""){
    if(!$content || !$type){
        return FALSE;
    }    
    $dir=getcwd().DIRECTORY_SEPARATOR.'diy_logs'.DIRECTORY_SEPARATOR.$type;
    
    if(!is_dir($dir)){ 
        if(!mkdir($dir,0777,true)){
            return false;
        }
    }
    $filename=$dir.DIRECTORY_SEPARATOR.date("Ymd",time()).'.log.php';   
    $logs=include $filename;
    if($logs && !is_array($logs)){
        unlink($filename);
        return false;
    }
    $logs[]=array("time"=>date("Y-m-d H:i:s"),"content"=>$content);
    $str="<?php \r\n return ".var_export($logs, true).";";
    if(!$fp=@fopen($filename,"wb")){
        return false;
    }           
    if(!fwrite($fp, $str))return false;
    fclose($fp);
    return true;
}
    
/**
 * 判断是否手机访问
 */
function WSTIsMobile()
{
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
    $mobile_browser = '0';
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser ++;
    if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser ++;
    if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser ++;
    if (isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser ++;
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    $mobile_agents = array(
        'w3c ',
        'acs-',
        'alav',
        'alca',
        'amoi',
        'audi',
        'avan',
        'benq',
        'bird',
        'blac',
        'blaz',
        'brew',
        'cell',
        'cldc',
        'cmd-',
        'dang',
        'doco',
        'eric',
        'hipt',
        'inno',
        'ipaq',
        'java',
        'jigs',
        'kddi',
        'keji',
        'leno',
        'lg-c',
        'lg-d',
        'lg-g',
        'lge-',
        'maui',
        'maxo',
        'midp',
        'mits',
        'mmef',
        'mobi',
        'mot-',
        'moto',
        'mwbp',
        'nec-',
        'newt',
        'noki',
        'oper',
        'palm',
        'pana',
        'pant',
        'phil',
        'play',
        'port',
        'prox',
        'qwap',
        'sage',
        'sams',
        'sany',
        'sch-',
        'sec-',
        'send',
        'seri',
        'sgh-',
        'shar',
        'sie-',
        'siem',
        'smal',
        'smar',
        'sony',
        'sph-',
        'symb',
        't-mo',
        'teli',
        'tim-',
        'tosh',
        'tsm-',
        'upg1',
        'upsi',
        'vk-v',
        'voda',
        'wap-',
        'wapa',
        'wapi',
        'wapp',
        'wapr',
        'webc',
        'winw',
        'winw',
        'xda',
        'xda-'
    );
    if (in_array($mobile_ua, $mobile_agents))
        $mobile_browser ++;
    if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser ++;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser = 0;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser ++;
    if ($mobile_browser > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * 邮件发送函数
 *
 * @param
 *            string to 要发送的邮箱地址
 * @param
 *            string subject 邮件标题
 * @param
 *            string content 邮件内容
 * @return array
 */
function WSTSendMail($to, $subject, $content)
{
    require_cache(VENDOR_PATH . "PHPMailer/class.smtp.php");
    require_cache(VENDOR_PATH . "PHPMailer/class.phpmailer.php");
    $mail = new PHPMailer();
    // 装配邮件服务器
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = $GLOBALS['CONFIG']['mailSmtp'];
    $mail->SMTPAuth = $GLOBALS['CONFIG']['mailAuth'];
    $mail->Username = $GLOBALS['CONFIG']['mailUserName'];
    $mail->Password = $GLOBALS['CONFIG']['mailPassword'];
    $mail->CharSet = 'utf-8';
    // 装配邮件头信息
    $mail->From = $GLOBALS['CONFIG']['mailUserName'];
    $mail->AddAddress($to);
    $mail->FromName = $GLOBALS['CONFIG']['mailSendTitle'];
    $mail->IsHTML(true);
    // 装配邮件正文信息
    $mail->Subject = $subject;
    $mail->Body = $content;
    // 发送邮件
    $rs = array();
    if (! $mail->Send()) {
        $rs['status'] = 0;
        $rs['msg'] = $mail->ErrorInfo;
        return $rs;
    } else {
        $rs['status'] = 1;
        return $rs;
    }
}

/**
 * 发送短信
 * 此接口要根据不同的短信服务商去写，这里只是一个参考
 *
 * @param string $phoneNumer
 *            手机号码
 * @param string $content
 *            短信内容
 */
function WSTSendSMS($phoneNumer, $content)
{
    $url = '短信结果';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 设置否输出到页面
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 设置连接等待时间
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    $data = curl_exec($ch);
    curl_close($ch);
    return "$data";
}

/**
 * 字符串替换
 *
 * @param string $str
 *            要替换的字符串
 * @param string $repStr
 *            即将被替换的字符串
 * @param int $start
 *            要替换的起始位置,从0开始
 * @param string $splilt
 *            遇到这个指定的字符串就停止替换
 */
function WSTStrReplace($str, $repStr, $start, $splilt = '')
{
    $newStr = substr($str, 0, $start);
    $breakNum = - 1;
    for ($i = $start; $i < strlen($str); $i ++) {
        $char = substr($str, $i, 1);
        if ($char == $splilt) {
            $breakNum = $i;
            break;
        }
        $newStr .= $repStr;
    }
    if ($splilt != '' && $breakNum > - 1) {
        for ($i = $breakNum; $i < strlen($str); $i ++) {
            $char = substr($str, $i, 1);
            $newStr .= $char;
        }
    }
    return $newStr;
}

/**
 * 循环删除指定目录下的文件及文件夹
 *
 * @param string $dirpath
 *            文件夹路径
 */
function WSTDelDir($dirpath)
{
    $dh = opendir($dirpath);
    while (($file = readdir($dh)) !== false) {
        if ($file != "." && $file != "..") {
            $fullpath = $dirpath . "/" . $file;
            if (! is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                WSTDelDir($fullpath);
                rmdir($fullpath);
            }
        }
    }
    closedir($dh);
    $isEmpty = 1;
    $dh = opendir($dirpath);
    while (($file = readdir($dh)) !== false) {
        if ($file != "." && $file != "..") {
            $isEmpty = 0;
            break;
        }
    }
    return $isEmpty;
}

/**
 * 获取网站域名
 */
function WSTDomain()
{
    $server = $_SERVER['HTTP_HOST'];
    $http = is_ssl() ? 'https://' : 'http://';
    return $http . $server . __ROOT__;
}

/**
 * 获取系统根目录
 */
function WSTRootPath()
{
    return dirname(dirname(dirname(dirname(__File__))));
}

/**
 * 获取网站根域名
 */
function WSTRootDomain()
{
    $server = $_SERVER['HTTP_HOST'];
    $http = is_ssl() ? 'https://' : 'http://';
    return $http . $server;
}

/**
 * 设置当前页面对象
 *
 * @param
 *            int 0-用户 1-商家
 */
function WSTLoginTarget($target = 0)
{
    $WST_USER = session('WST_USER');
    $WST_USER['loginTarget'] = $target;
    session('WST_USER', $WST_USER);
}

/**
 * 生成缓存文件
 */
function WSTDataFile($name, $path = '', $data = array())
{
    $key = C('DATA_CACHE_KEY');
    $name = md5($key . $name);
    if (is_array($data) && ! empty($data)) {
        if ($data['mallLicense'] == '') {
            if (stripos($data['mallTitle'], 'Powered By o2omall') === false)
                $data['mallTitle'] = $data['mallTitle'] . " - Powered By o2omall";
        }
        $data = serialize($data);
        if (C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            // 数据压缩
            $data = gzcompress($data, 3);
        }
        if (C('DATA_CACHE_CHECK')) { // 开启数据校验
            $check = md5($data);
        } else {
            $check = '';
        }
        $data = "<?php\n//" . sprintf('%012d', $expire) . $check . $data . "\n?>";
        $result = file_put_contents(DATA_PATH . $path . $name . ".php", $data);
        clearstatcache();
    } else 
        if (is_null($data)) {
            unlink(DATA_PATH . $path . $name . ".php");
        } else {
            if (file_exists(DATA_PATH . $path . $name . '.php')) {
                $content = file_get_contents(DATA_PATH . $path . $name . '.php');
                if (false !== $content) {
                    $expire = (int) substr($content, 8, 12);
                    if (C('DATA_CACHE_CHECK')) { // 开启数据校验
                        $check = substr($content, 20, 32);
                        $content = substr($content, 52, - 3);
                        if ($check != md5($content)) { // 校验错误
                            return null;
                        }
                    } else {
                        $content = substr($content, 20, - 3);
                    }
                    if (C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                        // 启用数据压缩
                        $content = gzuncompress($content);
                    }
                    $content = unserialize($content);
                    return $content;
                }
            }
            return null;
        }
}

/**
 * 建立文件夹
 *
 * @param string $aimUrl            
 * @return viod
 */
function WSTCreateDir($aimUrl)
{
    $aimUrl = str_replace('', '/', $aimUrl);
    $aimDir = '';
    $arr = explode('/', $aimUrl);
    $result = true;
    foreach ($arr as $str) {
        $aimDir .= $str . '/';
        if (! file_exists_case($aimDir)) {
            $result = mkdir($aimDir, 0777);
        }
    }
    return $result;
}

/**
 * 建立文件
 *
 * @param string $aimUrl            
 * @param boolean $overWrite
 *            该参数控制是否覆盖原文件
 * @return boolean
 */
function WSTCreateFile($aimUrl, $overWrite = false)
{
    if (file_exists_case($aimUrl) && $overWrite == false) {
        return false;
    } elseif (file_exists_case($aimUrl) && $overWrite == true) {
        WSTUnlinkFile($aimUrl);
    }
    $aimDir = dirname($aimUrl);
    WSTCreateDir($aimDir);
    touch($aimUrl);
    return true;
}

/**
 * 删除文件
 *
 * @param string $aimUrl            
 * @return boolean
 */
function WSTUnlinkFile($aimUrl)
{
    if (file_exists_case($aimUrl)) {
        unlink($aimUrl);
        return true;
    } else {
        return false;
    }
}

function WSTLogResult($filepath, $word)
{
    if (! file_exists_case($filepath)) {
        WSTCreateFile($filepath);
    }
    $fp = fopen($filepath, "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y-%m-%d %H:%M:%S", time()) . "\n" . $word . "\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

function WSTReadExcel($file)
{
    Vendor("PHPExcel.PHPExcel");
    Vendor("PHPExcel.PHPExcel.IOFactory");
    return PHPExcel_IOFactory::load(WSTRootPath() . "/Upload/" . $file);
}

function GetIpLookup($ip = '')
{
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($res)) {
        return false;
    }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if (! isset($jsonMatches[0])) {
        return false;
    }
    $json = json_decode($jsonMatches[0], true);
    if (isset($json['ret']) && $json['ret'] == 1) {
        $json['ip'] = $ip;
        unset($json['ret']);
    } else {
        return false;
    }
    return $json;
}

function p($params)
{
    echo '<pre>';
    print_r($params);
}

/**
 * 动态生成表
 *
 * @return boolean|string
 */
function content_get_codes_table()
{
    $conftable = include APP_PATH . '/Common/Conf/table.php';
    $table = C('DB_PREFIX') . 'shopcodes_' . $conftable['num'];
    $model = new \Think\Model();
    $shopcodes_table_status = $model->query("SHOW TABLE STATUS LIKE '" . $table . "'");
    if (! $shopcodes_table_status)
        return false;
    if ($shopcodes_table_status[0]['Auto_increment'] >= 99999) {
        $num = intval($conftable['num']) + 1;
        $shopcodes_table = 'shopcodes_' . $num;
        $create_table = C('DB_PREFIX') . $shopcodes_table;
        $q1 = $model->execute("
				CREATE TABLE `$create_table` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`s_id` int(10) unsigned NOT NULL,
				`s_cid` smallint(5) unsigned NOT NULL,
				`s_len` smallint(5) DEFAULT NULL,
				`s_codes` text,
				`s_codes_tmp` text,
				PRIMARY KEY (`id`),
				KEY `s_id` (`s_id`),
				KEY `s_cid` (`s_cid`),
				KEY `s_len` (`s_len`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        $q2 = file_put_contents(APP_PATH . '/Common/Conf/table.php', "<?php\r\n return array('num'=>" . $num . ");\r\n?>");
        if (! $q1 || ! $q2)
            return false;
    } else {
        $num = intval($conftable['num']);
        $shopcodes_table = 'shopcodes_' . $num;
    }
    return $shopcodes_table;
}

/**
 * 生成夺宝码
 * CountNum @ 生成个数
 * len @生成长度
 * sid @商品ID
 */
function content_get_go_codes($CountNum = null, $len = null, $sid = null)
{
    $conftable = include APP_PATH . '/Common/Conf/table.php';
    if (empty($conftable))
        return false;
    $table = C('DB_PREFIX') . 'shopcodes_' . $conftable['num'];
    $num = ceil($CountNum / $len);
    $code_i = $CountNum;
    $model = new \Think\Model();
    // $CountNum 小于3000时执行
    if ($num == 1) {
        $codes = array();
        for ($i = 1; $i <= $CountNum; $i ++) {
            $codes[$i] = 10000000 + $i;
        }
        shuffle($codes);
        $codes = serialize($codes);
        $query = $model->execute("INSERT INTO `$table` (`s_id`, `s_cid`, `s_len`, `s_codes`,`s_codes_tmp`) VALUES ('$sid', '1','$CountNum','$codes','$codes')");
        unset($codes);
        return $query;
    }
    
    $query_1 = true;
    // $num = 2; 100
    for ($k = 1; $k < $num; $k ++) {
        $codes = array();
        for ($i = 1; $i <= $len; $i ++) {
            $codes[$i] = 10000000 + $code_i;
            $code_i --;
        }
        shuffle($codes);
        $codes = serialize($codes);
        $query_1 = $model->execute("INSERT INTO `$table` (`s_id`, `s_cid`, `s_len`, `s_codes`,`s_codes_tmp`) VALUES ('$sid', '$k','$len','$codes','$codes')");
        unset($codes);
    }
    
    $CountNum = $CountNum - (($num - 1) * $len);
    $codes = array();
    
    for ($i = 1; $i <= $CountNum; $i ++) {
        $codes[$i] = 10000000 + $code_i;
        $code_i --;
    }
    shuffle($codes);
    $codes = serialize($codes);
    $query_2 = $model->execute("INSERT INTO `$table` (`s_id`, `s_cid`,`s_len`, `s_codes`,`s_codes_tmp`) VALUES ('$sid', '$num','$CountNum','$codes','$codes')");
    unset($codes);
    return $query_1 && $query_2;
}

function getMillisecond()
{
    list ($t1, $t2) = explode(' ', microtime());
    return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

// 把px替换成em
function pxToem($matches)
{
    $em = intval($matches[0]) / 16;
    return str_replace($matches[0], $em . 'em', $matches[0]);
}
//支付打印日志
function logResult($file='data/payLog.txt',$word){
    echo $file;
    $fp = fopen($file, "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . date('Y-m-d H:i:s') . "\n" . $word . "\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * @param $array2D
 * @return array
 * @des 二维数组去重
 * @date 2017-7-22
 * @author Meke
 */
function array_unique_fb($array2D){
    foreach ($array2D as $v){
        $v=implode(',',$v);
        $temp[]=$v;
    }
    $temp=array_unique($temp);
    foreach ($temp as $k => $v){
        $temp[$k]=explode(',',$v);
    }
    return $temp;
}

/**
 * @param $array2D
 * @return array
 * @des 二维数组去重
 * @date 2017-7-22
 * @author Meke
 */
function unique_2d_array_by_key($_2d_array,$unique_key) {
     $tmp_key[] =array();
      foreach($_2d_array as $key=> &$item) {
        if(is_array($item) && isset($item[$unique_key]) ) {
           if( in_array($item[$unique_key],$tmp_key) ) {
                  unset($_2d_array[$key]);
           }else{
                  $tmp_key[] =$item[$unique_key];
           }
       }
     }
       return$_2d_array;
   }

/**
 * @param $utf8Data
 * @param string $sRetFormat
 * @return string
 * @des 中文转化成字母
 * @date 2017-7-26
 * @author Meke
 */
function encode($utf8Data, $sRetFormat='head'){
    $sGBK = iconv('UTF-8', 'GBK', $utf8Data);
    $aBuf = array();
    for ($i=0, $iLoop=strlen($sGBK); $i<$iLoop; $i++) {
        $iChr = ord($sGBK{$i});
        if ($iChr>160)
            $iChr = ($iChr<<8) + ord($sGBK{++$i}) - 65536;
        if ('head' === $sRetFormat)
            $aBuf[] = substr(zh2py($iChr),0,1);
        else
            $aBuf[] = zh2py($iChr);
    }
    if ('head' === $sRetFormat)
        return implode('', $aBuf);
    else
        return implode(' ', $aBuf);
}

/**
 * @des 中文转换到拼音(每次处理一个字符)
 * @param number $iWORD 待处理字符双字节
 * @return string 拼音
 * @date 2017-7-26
 * @author Meke
 */
function zh2py($iWORD) {
    if($iWORD>0 && $iWORD<160 ) {
        return chr($iWORD);
    } elseif ($iWORD<-20319||$iWORD>-10247) {
        return '';
    } else {
        foreach (returnArry() as $py => $code) {
            if($code > $iWORD) break;
            $result = $py;
        }
        return $result;
    }
}

/**
 * @des拼音字符转换图
 * @var array
 * @date 2017-7-26
 * @author Meke
 */
function returnArry()
{
    return $_aMaps = array(
        'a' => -20319, 'ai' => -20317, 'an' => -20304, 'ang' => -20295, 'ao' => -20292,
        'ba' => -20283, 'bai' => -20265, 'ban' => -20257, 'bang' => -20242, 'bao' => -20230, 'bei' => -20051, 'ben' => -20036, 'beng' => -20032, 'bi' => -20026, 'bian' => -20002, 'biao' => -19990, 'bie' => -19986, 'bin' => -19982, 'bing' => -19976, 'bo' => -19805, 'bu' => -19784,
        'ca' => -19775, 'cai' => -19774, 'can' => -19763, 'cang' => -19756, 'cao' => -19751, 'ce' => -19746, 'ceng' => -19741, 'cha' => -19739, 'chai' => -19728, 'chan' => -19725, 'chang' => -19715, 'chao' => -19540, 'che' => -19531, 'chen' => -19525, 'cheng' => -19515, 'chi' => -19500, 'chong' => -19484, 'chou' => -19479, 'chu' => -19467, 'chuai' => -19289, 'chuan' => -19288, 'chuang' => -19281, 'chui' => -19275, 'chun' => -19270, 'chuo' => -19263, 'ci' => -19261, 'cong' => -19249, 'cou' => -19243, 'cu' => -19242, 'cuan' => -19238, 'cui' => -19235, 'cun' => -19227, 'cuo' => -19224,
        'da' => -19218, 'dai' => -19212, 'dan' => -19038, 'dang' => -19023, 'dao' => -19018, 'de' => -19006, 'deng' => -19003, 'di' => -18996, 'dian' => -18977, 'diao' => -18961, 'die' => -18952, 'ding' => -18783, 'diu' => -18774, 'dong' => -18773, 'dou' => -18763, 'du' => -18756, 'duan' => -18741, 'dui' => -18735, 'dun' => -18731, 'duo' => -18722,
        'e' => -18710, 'en' => -18697, 'er' => -18696,
        'fa' => -18526, 'fan' => -18518, 'fang' => -18501, 'fei' => -18490, 'fen' => -18478, 'feng' => -18463, 'fo' => -18448, 'fou' => -18447, 'fu' => -18446,
        'ga' => -18239, 'gai' => -18237, 'gan' => -18231, 'gang' => -18220, 'gao' => -18211, 'ge' => -18201, 'gei' => -18184, 'gen' => -18183, 'geng' => -18181, 'gong' => -18012, 'gou' => -17997, 'gu' => -17988, 'gua' => -17970, 'guai' => -17964, 'guan' => -17961, 'guang' => -17950, 'gui' => -17947, 'gun' => -17931, 'guo' => -17928,
        'ha' => -17922, 'hai' => -17759, 'han' => -17752, 'hang' => -17733, 'hao' => -17730, 'he' => -17721, 'hei' => -17703, 'hen' => -17701, 'heng' => -17697, 'hong' => -17692, 'hou' => -17683, 'hu' => -17676, 'hua' => -17496, 'huai' => -17487, 'huan' => -17482, 'huang' => -17468, 'hui' => -17454, 'hun' => -17433, 'huo' => -17427,
        'ji' => -17417, 'jia' => -17202, 'jian' => -17185, 'jiang' => -16983, 'jiao' => -16970, 'jie' => -16942, 'jin' => -16915, 'jing' => -16733, 'jiong' => -16708, 'jiu' => -16706, 'ju' => -16689, 'juan' => -16664, 'jue' => -16657, 'jun' => -16647,
        'ka' => -16474, 'kai' => -16470, 'kan' => -16465, 'kang' => -16459, 'kao' => -16452, 'ke' => -16448, 'ken' => -16433, 'keng' => -16429, 'kong' => -16427, 'kou' => -16423, 'ku' => -16419, 'kua' => -16412, 'kuai' => -16407, 'kuan' => -16403, 'kuang' => -16401, 'kui' => -16393, 'kun' => -16220, 'kuo' => -16216,
        'la' => -16212, 'lai' => -16205, 'lan' => -16202, 'lang' => -16187, 'lao' => -16180, 'le' => -16171, 'lei' => -16169, 'leng' => -16158, 'li' => -16155, 'lia' => -15959, 'lian' => -15958, 'liang' => -15944, 'liao' => -15933, 'lie' => -15920, 'lin' => -15915, 'ling' => -15903, 'liu' => -15889, 'long' => -15878, 'lou' => -15707, 'lu' => -15701, 'lv' => -15681, 'luan' => -15667, 'lue' => -15661, 'lun' => -15659, 'luo' => -15652,
        'ma' => -15640, 'mai' => -15631, 'man' => -15625, 'mang' => -15454, 'mao' => -15448, 'me' => -15436, 'mei' => -15435, 'men' => -15419, 'meng' => -15416, 'mi' => -15408, 'mian' => -15394, 'miao' => -15385, 'mie' => -15377, 'min' => -15375, 'ming' => -15369, 'miu' => -15363, 'mo' => -15362, 'mou' => -15183, 'mu' => -15180,
        'na' => -15165, 'nai' => -15158, 'nan' => -15153, 'nang' => -15150, 'nao' => -15149, 'ne' => -15144, 'nei' => -15143, 'nen' => -15141, 'neng' => -15140, 'ni' => -15139, 'nian' => -15128, 'niang' => -15121, 'niao' => -15119, 'nie' => -15117, 'nin' => -15110, 'ning' => -15109, 'niu' => -14941, 'nong' => -14937, 'nu' => -14933, 'nv' => -14930, 'nuan' => -14929, 'nue' => -14928, 'nuo' => -14926,
        'o' => -14922, 'ou' => -14921,
        'pa' => -14914, 'pai' => -14908, 'pan' => -14902, 'pang' => -14894, 'pao' => -14889, 'pei' => -14882, 'pen' => -14873, 'peng' => -14871, 'pi' => -14857, 'pian' => -14678, 'piao' => -14674, 'pie' => -14670, 'pin' => -14668, 'ping' => -14663, 'po' => -14654, 'pu' => -14645,
        'qi' => -14630, 'qia' => -14594, 'qian' => -14429, 'qiang' => -14407, 'qiao' => -14399, 'qie' => -14384, 'qin' => -14379, 'qing' => -14368, 'qiong' => -14355, 'qiu' => -14353, 'qu' => -14345, 'quan' => -14170, 'que' => -14159, 'qun' => -14151,
        'ran' => -14149, 'rang' => -14145, 'rao' => -14140, 're' => -14137, 'ren' => -14135, 'reng' => -14125, 'ri' => -14123, 'rong' => -14122, 'rou' => -14112, 'ru' => -14109, 'ruan' => -14099, 'rui' => -14097, 'run' => -14094, 'ruo' => -14092,
        'sa' => -14090, 'sai' => -14087, 'san' => -14083, 'sang' => -13917, 'sao' => -13914, 'se' => -13910, 'sen' => -13907, 'seng' => -13906, 'sha' => -13905, 'shai' => -13896, 'shan' => -13894, 'shang' => -13878, 'shao' => -13870, 'she' => -13859, 'shen' => -13847, 'sheng' => -13831, 'shi' => -13658, 'shou' => -13611, 'shu' => -13601, 'shua' => -13406, 'shuai' => -13404, 'shuan' => -13400, 'shuang' => -13398, 'shui' => -13395, 'shun' => -13391, 'shuo' => -13387, 'si' => -13383, 'song' => -13367, 'sou' => -13359, 'su' => -13356, 'suan' => -13343, 'sui' => -13340, 'sun' => -13329, 'suo' => -13326,
        'ta' => -13318, 'tai' => -13147, 'tan' => -13138, 'tang' => -13120, 'tao' => -13107, 'te' => -13096, 'teng' => -13095, 'ti' => -13091, 'tian' => -13076, 'tiao' => -13068, 'tie' => -13063, 'ting' => -13060, 'tong' => -12888, 'tou' => -12875, 'tu' => -12871, 'tuan' => -12860, 'tui' => -12858, 'tun' => -12852, 'tuo' => -12849,
        'wa' => -12838, 'wai' => -12831, 'wan' => -12829, 'wang' => -12812, 'wei' => -12802, 'wen' => -12607, 'weng' => -12597, 'wo' => -12594, 'wu' => -12585,
        'xi' => -12556, 'xia' => -12359, 'xian' => -12346, 'xiang' => -12320, 'xiao' => -12300, 'xie' => -12120, 'xin' => -12099, 'xing' => -12089, 'xiong' => -12074, 'xiu' => -12067, 'xu' => -12058, 'xuan' => -12039, 'xue' => -11867, 'xun' => -11861,
        'ya' => -11847, 'yan' => -11831, 'yang' => -11798, 'yao' => -11781, 'ye' => -11604, 'yi' => -11589, 'yin' => -11536, 'ying' => -11358, 'yo' => -11340, 'yong' => -11339, 'you' => -11324, 'yu' => -11303, 'yuan' => -11097, 'yue' => -11077, 'yun' => -11067,
        'za' => -11055, 'zai' => -11052, 'zan' => -11045, 'zang' => -11041, 'zao' => -11038, 'ze' => -11024, 'zei' => -11020, 'zen' => -11019, 'zeng' => -11018, 'zha' => -11014, 'zhai' => -10838, 'zhan' => -10832, 'zhang' => -10815, 'zhao' => -10800, 'zhe' => -10790, 'zhen' => -10780, 'zheng' => -10764, 'zhi' => -10587, 'zhong' => -10544, 'zhou' => -10533, 'zhu' => -10519, 'zhua' => -10331, 'zhuai' => -10329, 'zhuan' => -10328, 'zhuang' => -10322, 'zhui' => -10315, 'zhun' => -10309, 'zhuo' => -10307, 'zi' => -10296, 'zong' => -10281, 'zou' => -10274, 'zu' => -10270, 'zuan' => -10262, 'zui' => -10260, 'zun' => -10256, 'zuo' => -10254
    );
}

/**
 * @des 转化微妙级
 */
function get_millisecond()
{
    list($usec, $sec) = explode(" ", microtime());
    $msec=round($usec*1000);
    return $msec;

}