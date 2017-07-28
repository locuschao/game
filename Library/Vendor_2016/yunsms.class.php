<?php

class yunsms
{

    private static $logfile;

    private $account;

    private $password;

    private $sendUrl = "http://203.86.7.117:8888/sms.aspx";

    public function __construct($userid = '', $password = '')
    {
        if (! empty($userid) && ! empty($password)) {
            $this->account = $userid;
            $this->password = $password;
        } else {
            // 读数据库配置，读文件，读变量
            $this->account = 'rhtest';
            $this->password = '123123';
        }
    }

    public function sendMsg($phone, $content)
    {
        if (empty($this->account) || empty($this->password)) {
            return array(
                'status' => 0,
                'msg' => '帐号或密码为空'
            );
        }
        if (empty($phone)) {
            return array(
                'status' => 0,
                'msg' => '手机号码为空'
            );
        }
        if (empty($content)) {
            return array(
                'status' => 0,
                'msg' => '短信内容不能为空'
            );
        }
        if (! $this->checkmobile($phone)) {
            return array(
                'status' => 0,
                'msg' => '手机号码格式不正确'
            );
        }
        $content = urlencode($this->_safe_replace($this->characet($content)));
        $request = $this->sendUrl . '?action=send&userid=22&account=' . $this->account . '&password=' . $this->password . '&mobile=' . $phone . '&content=' . $content;
        $xml = $this->https_request($request);
        if (! empty($xml)) {
            $result = simplexml_load_string($xml, 'SimpleXMLElement');
            if ($result->returnstatus == 'Success') {
                $info = "成功发送条数：{$result->successCounts}，当前余额：{$result->remainpoint}，说明：{$result->message}";
                $this->log($info);
                return array(
                    'status' => 1,
                    'msg' => '短信发送成功'
                );
            } elseif ($result->returnstatus == 'Faild') {
                $info = "发送失败条数：{$result->successCounts}，当前余额：{$result->remainpoint}，说明：{$result->message}";
                $this->log($info);
                return array(
                    'status' => 0,
                    'msg' => '短信发送失败'
                );
            }
        } else {
            $info = "未知，短信平台出问题";
            $this->log($info);
            return array(
                'status' => 0,
                'msg' => '短信平台出问题'
            );
        }
    }
    
    // 余额及已发送量查询接口
    public function overage()
    {
        if (empty($this->account) || empty($this->password)) {
            return array(
                'status' => 0,
                'msg' => '帐号或密码为空'
            );
        }
        $request = $this->sendUrl . '?action=overage&userid=13&account=' . $this->account . '&password=' . $this->password;
        $xml = $this->https_request($request);
        if (! empty($xml)) {
            return simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            return null;
        }
    }
    
    // 状态报告接口
    public function report()
    {
        if (empty($this->account) || empty($this->password)) {
            return array(
                'status' => 0,
                'msg' => '帐号或密码为空'
            );
        }
        $request = 'http://203.86.7.117:8888/statusApi.aspx?action=query&userid=13&account=' . $this->account . '&password=' . $this->password;
        $xml = $this->https_request($request);
        if (! empty($xml)) {
            return simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            return null;
        }
    }
    
    // 上行接口
    public function upmsg()
    {
        if (empty($this->account) || empty($this->password)) {
            return array(
                'status' => 0,
                'msg' => '帐号或密码为空'
            );
        }
        $request = 'http://203.86.7.117:8888/callApi.aspx?action=query&userid=13&account=' . $this->account . '&password=' . $this->password;
        $xml = $this->https_request($request);
        if (! empty($xml)) {
            return simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            return null;
        }
    }
    
    // 非法关键词查询
    public function checkkeyword($content)
    {
        if (empty($this->account) || empty($this->password)) {
            return array(
                'status' => 0,
                'msg' => '帐号或密码为空'
            );
        }
        $request = $this->sendUrl . '?action=checkkeyword&userid=13&account=' . $this->account . '&password=' . $this->password . '&content=' . $content;
        $xml = $this->https_request($request);
        if (! empty($xml)) {
            return simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            return null;
        }
    }

    private function log($content)
    {
        $log_dir = './data';
        self::$logfile = './data/errsms' . date('Y_m_d') . '.log';
        if (! is_dir($log_dir)) {
            if (! mkdir($log_dir, 0777)) {
                // exit('创建log目录失败');
            } else {
                self::write($content);
            }
        } else {
            self::write($content);
        }
    }

    public function checkmobile($mobilephone)
    {
        $mobilephone = trim($mobilephone);
        if (preg_match("/^1[34578]\d{9}$/", $mobilephone)) {
            return $mobilephone;
        } else {
            return false;
        }
    }
    
    // 写日志
    private static function write($content)
    {
        $tab = "\r\n";
        $date_url = '[' . date('Y-m-d H:i:s') . '] ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['REQUEST_URI'] . $tab;
        $content = $date_url . $content . $tab;
        self::isBak();
        $hd = fopen(self::$logfile, 'ab'); // ab追加模式
        flock($hd, LOCK_EX);
        fwrite($hd, $content);
        flock($hd, LOCK_UN);
        fclose($hd);
    }
    
    // 备份日志
    private static function bak()
    {
        // 设置备份到新文件名
        $bak_name = self::$logfile . '.' . time() . '.bak';
        return rename(self::$logfile, $bak_name);
    }
    
    // 读取并判断日志文件大小
    private static function isBak()
    {
        
        // 如果日志文件不存在
        if (! file_exists(self::$logfile)) {
            touch(self::$logfile);
            return self::$logfile;
        }
        
        // 判断日志文件是否大于1M
        if (filesize(self::$logfile) >= 1024 * 1024) {
            // 如果备份成功创建新文件
            if (self::bak()) {
                touch(self::$logfile);
                return self::$logfile;
            } else {
                return self::$logfile;
            }
        } else {
            return self::$logfile;
        }
    }

    /**
     * https请求（支持GET和POST）
     * 
     * @param
     *            $url
     * @param string $data            
     * @return mixed
     */
    private function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 如果$data不是空使用POST
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    private function characet($data)
    {
        if (! empty($data)) {
            $fileType = mb_detect_encoding($data, array(
                'UTF-8',
                'GBK',
                'LATIN1',
                'BIG5'
            ));
            if ($fileType != 'UTF-8') {
                $data = mb_convert_encoding($data, 'utf-8', $fileType);
            }
        }
        return $data;
    }

    /**
     * 安全过滤函数
     *
     * @param
     *            $string
     * @return string
     */
    private function _safe_replace($string)
    {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        return $string;
    }
}