<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class JoinController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
    }
    
    // 协议
    public function protocol()
    {
        $isExis = M('shops')->where(array(
            'userId' => session('oto_userId')
        ))->find();
        
        if ($isExis) {
            // 已经申请过
            if ($isExis['shopStatus'] == 1) {
                $this->redirect(U('Join/myShop', '', 0, 0));
            } else {
                $this->redirect(U('Join/tip', '', 0, 0));
            }
            return;
        }
        $this->display();
    }
    
    // 我是商家
    public function myShop()
    {
        $map['userId'] = session('oto_userId');
        $map['shopStatus'] = 1;
        $map['shopFlag'] = 1;
        $this->shopInfo = M('shops')->where($map)
            ->field('shopName,shopSn')
            ->find();
        // 本月订单
        $month['isPay'] = 1;
        $month['_string'] = "date_sub(curdate(), INTERVAL 30 DAY) <= FROM_UNIXTIME(paytime)";
        $this->monthCount = M('orders')->where($month)->count();
        // 本月总成交金额
        $this->monthMoney = M('orders')->where($month)->SUM('needPay');
        
        // 今天数据
        $today['isPay'] = 1;
        $today['_string'] = "to_days(FROM_UNIXTIME(paytime)) = to_days(now())";
        $this->todayCount = M('orders')->where($today)->count();
        $this->todayMoney = M('orders')->where($today)->SUM('needPay');
        
        $this->display();
    }
    
    // 系统 活动公告
    public function sysNotice()
    {
        $map['msgFlag'] = 1;
        $uid = session('oto_userId');
        $map['_string'] = "receiveUserId IS NULL or receiveUserId=$uid";
        $this->info = M('messages')->where($map)
            ->order('id DESC')
            ->select();
        $this->display();
    }
    
    // 加盟
    public function toJoin()
    {
        $isExis = M('shops')->where(array(
            'userId' => session('oto_userId')
        ))->find();
        if ($isExis) {
            // 已经申请过
            if ($isExis['shopStatus'] == 1) {
                $this->redirect(U('Join/myShop', '', 0, 0));
            } else {
                $this->redirect(U('Join/tip', '', 0, 0));
            }
            return;
        }
        $info = $this->getCity(0);
        $this->assign('areaInfo', $info);
        $this->sort = M('goodsCats')->where(array(
            'catFlag' => 1,
            'isShow' => 1,
            'parentId' => 0
        ))
            ->field('catId,catName')
            ->select();
        $this->display();
    }
    
    // 提示
    public function tip()
    {
        $isExis = M('shops')->where(array(
            'userId' => session('oto_userId')
        ))->getField('shopStatus');
        $info = '';
        switch ($isExis) {
            case 0:
                $info = "请耐心等待审核";
                break;
            case 1:
                $info = "你已经是商家";
                break;
            case - 1:
                $info = "你的申请已被拒绝";
                break;
            case - 2:
                $info = "店铺停止营业";
                break;
        }
        $this->info = $info;
        $this->display();
    }
    
    // 获取省
    public function getCity($parentid = 0)
    {
        $m = M('areas');
        $I_parentId = I('parentId');
        if (isset($parentid) && ! $I_parentId) {
            $pid = $parentid;
        } else {
            $pid = I('parentId') ? I('parentId') : 0;
        }
        $map['areaFlag'] = 1;
        // $map['isShow']=1;
        $field = "areaId,areaName";
        $map['parentId'] = $pid;
        $res = $m->where($map)
            ->field($field)
            ->select();
        if (IS_AJAX) {
            $this->ajaxReturn($res);
        } else {
            return $res;
        }
    }
    
    // 商户入驻
    public function toJoinHandle()
    {
        $data['userId'] = session('oto_userId');
        $isExis = M('shops')->where(array(
            'userId' => $data['userId']
        ))->find();
        if ($isExis) {
            // 已经申请过
            $this->ajaxReturn(array(
                'status' => - 2
            ));
            return;
        }
        $data['shopCompany'] = I('shopname');
        $data['shopName'] = I('shopname');
        $data['goodsCatId1'] = I('sort');
        $data['shopImg'] = 'Upload/shops/default_shop_ico.jpg';
        $data['shopAddress'] = I('shopAddr');
        $data['shopTel'] = I('phone');
        $data['idNo'] = I('identity');
        $data['permitNo'] = I('permit');
        $data['idImg'] = I('identity_zhen');
        $data['idRevImg'] = I('identity_fan');
        $data['permitImg'] = I('permitImg');
        $data['areaId1'] = I('province');
        $data['areaId2'] = I('city');
        $data['areaId3'] = I('area');
        $data['email'] = I('email');
        $data["shopStatus"] = 0;
        $data["shopAtive"] = 1;
        $data["shopFlag"] = 1;
        $data["createTime"] = date('Y-m-d H:i:s');
        $data["serviceStartTime"] = 9;
        $data["serviceEndTime"] = 23;
        $data["shopSn"] = mt_rand(10000, 99999);
        $data["isInvoice"] = 0;
        $data["userName"] = I('name');
        $db = M('shops');
        if ($db->create($data) != false) {
            $res = $db->add();
            if ($res) {
                // 申请成功
                $this->ajaxReturn(array(
                    'status' => 0
                ));
            } else {
                // 失败
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
            }
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }

    Public function uppic()
    {
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        if ($upload->upload('./upload/toJoin/')) {
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if ($info['0']['type'] != 'image/jpeg' && $info['0']['type'] != 'image/jpg' && $info['0']['type'] != 'image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png') {
            echo "2";
            exit();
        }
        if ($info['0']['size'] > $MAX_SIZE)
            echo "上传的文件大小超过了规定大小";
        
        if ($info['0']['size'] == 0)
            echo "请选择上传的文件";
        
        switch ($info['0']['error']) {
            case 0:
                echo 'upload/toJoin/' . $file_newname;
                break;
            case 1:
                echo "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case 2:
                echo "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 3:
                echo "文件只有部分被上传";
                break;
            case 4:
                echo "没有文件被上传";
                break;
        }
    }
}