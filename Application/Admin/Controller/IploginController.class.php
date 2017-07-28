<?php
namespace Admin\Controller;

class IploginController extends BaseController
{

    /**
     * /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('dlxz_00');
        $ip = M('allow_logip')->where(array(
            'id' => 1
        ))->find();
        $this->assign('ip', $ip['ipList']);
        $this->display("Iplogin/list");
    }

    public function addIp()
    {
        $ip = I('ip');
        $info = M('allow_logip')->where(array(
            'id' => 1
        ))->find();
        $allowIP = $info['ipList'];
        
        // 已存在
        $tempArr = explode(',', $allowIP);
        if (in_array($ip, $tempArr)) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        }
        
        if ($info) {
            $data['id'] = 1;
            $data['ipList'] = $ip;
            if ($allowIP) {
                $data['ipList'] = $allowIP . ',' . $ip;
            }
            $rs = M('allow_logip')->save($data);
        } else {
            $data['ipList'] = $ip;
            $rs = M('allow_logip')->add($data);
        }
        if ($rs) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }

    public function saveIp()
    {
        $ip = I('ip');
        $data['id'] = 1;
        
        $tempArr = explode(',', $ip);
        $tempArr = array_unique($tempArr);
        $newIp = implode(',', $tempArr);
        $data['ipList'] = $newIp;
        $rs = M('allow_logip')->save($data);
        if ($rs) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    
    // 去重复数组
    function a_array_unique($array)
    {
        $out = array();
        foreach ($array as $key => $value) {
            if (! in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}
;
?>