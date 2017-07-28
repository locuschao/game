<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class AddressController extends BaseController
{

    public $userid;

    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
        if (session('oto_userId')) {
            $this->userid = session('oto_userId');
        }
    }

    public function addAddress()
    {
        $info = $this->getCity(0);
        $this->assign('areaInfo', $info);
        $this->display();
    }
    
    // 获取省
    public function getCity($parentid = 0)
    {
        $m = M('areas');
        // 测试empty isset
        $I_parentId = I('parentId');
        if (isset($parentid) && empty($I_parentId)) {
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
    
    // 添加
    public function Add()
    {
        $map['userId'] = session('oto_userId');
        $map['userName'] = I('userName');
        $map['userPhone'] = I('userPhone');
        $map['areaId1'] = I('areaId1');
        $map['areaId2'] = I('areaId2');
        $map['areaId3'] = I('areaId3');
        $map['address'] = I('address');
        $map['createTime'] = date('Y-m-d H:i:s', time());
        $r = M('user_address')->add($map);
        if ($r) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    
    // 查询 地址
    public function getUserAddr()
    {
        $field = "addressId,userName,userPhone,address,areaId1,areaId2,areaId3,isDefault,communityId";
        $map['addressFlag'] = 1;
        $map['isDefault'] = 1;
        $map['userId'] = session('oto_userId');
        $addrInfo = M('user_address')->field($field)
            ->order('isDefault DESC')
            ->where($map)
            ->limit(1)
            ->select();
        $db = M('areas');
        foreach ($addrInfo as $k => $v) {
            $addrInfo[$k]['province'] = $db->where(array(
                'areaId' => $v['areaId1']
            ))->getField('areaName');
            $addrInfo[$k]['city'] = $db->where(array(
                'areaId' => $v['areaId2']
            ))->getField('areaName');
            $addrInfo[$k]['area'] = $db->where(array(
                'areaId' => $v['areaId3']
            ))->getField('areaName');
            $addrInfo[$k]['community'] = M('communitys')->where(array(
                'communityId' => $v['communityId']
            ))->getField('communityName');
        }
        $this->ajaxReturn($addrInfo);
    }
    
    // 收货地址
    public function myAddr()
    {
        $addr = M('user_address')->where(array(
            'addressFlag' => 1,
            'userId' => session('oto_userId')
        ))->select();
        $ares = M('areas')->select();
        foreach ($addr as $k => $v) {
            foreach ($ares as $a => $av) {
                if ($v['areaId1'] == $av['areaId']) {
                    $addr[$k]['province'] = $av['areaName'];
                }
                if ($v['areaId2'] == $av['areaId']) {
                    $addr[$k]['city'] = $av['areaName'];
                }
                if ($v['areaId3'] == $av['areaId']) {
                    $addr[$k]['area'] = $av['areaName'];
                }
            }
        }
        $this->addr = $addr;
        $this->display();
    }
    // 设置为默认地址
    public function setDefaultAddr()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $id = I('addrid');
        M()->startTrans();
        $A = M('user_address')->where(array(
            'userId' => session('oto_userId')
        ))->setField(array(
            'isDefault' => 0
        ));
        $B = M('user_address')->where(array(
            'userId' => session('oto_userId'),
            'addressId' => $id
        ))->setField(array(
            'isDefault' => 1
        ));
        if ($A !== false && $B !== false) {
            M()->commit();
            $this->ajaxReturn(array(
                'status' => - 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            M()->rollback();
        }
    }
    // 删除地址
    public function delAddr()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $id = I('addrid');
        $A = M('user_address')->where(array(
            'userId' => session('oto_userId'),
            'addressId' => $id
        ))->delete();
        if ($A) {
            $this->ajaxReturn(array(
                'status' => - 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    // 添加地址
    public function addAddr()
    {
        parent::isLogin();
        $this->province = $this->getCity();
        $this->display();
    }
    // 添加地址处理
    public function addAddrHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $map['userId'] = session('oto_userId');
        $map['userName'] = I('userName');
        $map['userPhone'] = I('userPhone');
        $map['areaId1'] = I('areaId1');
        $map['areaId2'] = I('areaId2');
        $map['areaId3'] = I('areaId3');
        $map['address'] = I('address');
        $map['createTime'] = date('Y-m-d H:i:s', time());
        $r = M('user_address')->add($map);
        if ($r) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }

    public function editAddr()
    {
        parent::isLogin();
        $id = I('id');
        $addr = M('user_address')->where(array(
            'addressId' => $id
        ))->find();
        $this->addr = $addr;
        $this->province = $this->getCity();
        $this->city = $this->getCity($addr['areaId1']);
        $this->area = $this->getCity($addr['areaId2']);
        $this->display();
    }
    // 保存编辑地址
    public function editAddrHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $map['userId'] = session('oto_userId');
        $map['addressId'] = I('addrid');
        $map['userName'] = I('userName');
        $map['userPhone'] = I('userPhone');
        $map['areaId1'] = I('areaId1');
        $map['areaId2'] = I('areaId2');
        $map['areaId3'] = I('areaId3');
        $map['address'] = I('address');
        $r = M('user_address')->save($map);
        if ($r) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
}
