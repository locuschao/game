<?php
namespace Admin\Controller;

/**
 * 刮刮卡后台控制器
 */
class GgkController extends BaseController
{
    // 刮刮卡活动列表
    public function indexlist()
    {
        $this->isLogin();
        $type = (int) I('type');
        $data = D('Ggk')->indexlist($type);
        D('Home/Ggk')->timeout();
        $this->checkPrivelege('ggkz_01');
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->display();
    }
    // 刮刮卡中奖管理
    public function manage()
    {
        $this->isLogin();
        $id = (int) I('id');
        $sendstutas = (int) I('sendstutas');
        $data = D('Home/Ggk')->getmanage($id, $sendstutas);
        D('Home/Ggk')->timeout();
        $this->checkPrivelege('ggkg_06');
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign('num', $data['num']);
        $this->display();
    }
    
    // 卖家编辑中奖用户的基本信息
    public function update()
    {
        $this->isLogin();
        $this->checkPrivelege('ggkg_09');
        $this->checkAjaxPrivelege('ggkg_09');
        $id = I('id');
        $xg = I('xg');
        if (empty($xg)) {
            $data = D('Home/Ggk')->update($id);
            $this->assign('data', $data);
            $this->display('ggk/udjz');
        } else {
            $kdnum = I('kdnum');
            $prize = I('prize');
            $expressId = I('expressId');
            $save = array(
                'id' => $id,
                'kdnum' => $kdnum,
                'prize' => $prize,
                'expressId' => $expressId
            );
            $return = M('ggk_record')->save($save);
            if ($return !== fales) {
                $data['status'] = 1;
            } else {
                $data['status'] = 2;
            }
            $this->ajaxReturn($data);
        }
    }
    
    // 确定发奖
    public function sendstutas()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('ggkg_07');
        $id = (int) I('id');
        $sn = I('sn');
        if (empty($id)) {
            $where['sn'] = $sn;
        } else {
            $where['id'] = $id;
        }
        $where['sendstutas'] = 0;
        $data['sendtime'] = time();
        $data['sendstutas'] = 1;
        $return = M('ggk_record')->where($where)->save($data);
        if ($return !== false) {
            $status['status'] = 1;
        } else {
            $status['status'] = 2;
        }
        $this->ajaxReturn($status);
    }
    
    // 刮刮卡审核列表
    public function checklist()
    {
        $this->isLogin();
        $this->checkPrivelege('ggks_05');
        $type = (int) I('type');
        D('Home/Ggk')->timeout();
        $data = D('Ggk')->checklist($type);
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->display();
    }
    
    // 拒绝申请
    public function refuse()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('ggks_09');
        $data['id'] = (int) I('id');
        $data['admin_check_status'] = 2;
        $save = M('biz_ggk')->save($data);
        if ($save !== fales) {
            $this->ajaxReturn(array(
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 2
            ));
        }
    }
    
    // 发布页 and 修改页 and 修改页
    public function release()
    {
        $this->isLogin();
        $this->checkPrivelege('ggks_02');
        $id = (int) I('id');
        $data = M('biz_ggk')->find($id);
        $this->assign('data', $data);
        $this->display('ggk/add');
    }
    
    // 处理申请
    public function releaserun()
    {
        $this->isLogin();
        $ggk = $_POST;
        $data = D('Ggk')->checkfrom($ggk);
        if ($data['return']['status'] === 1) {
            $biz_save = M('biz_ggk')->save($data['ggk']);
            unset($data['ggk']['biz_apply_status']);
            unset($data['ggk']['admin_check_status']);
            if (M('ggk')->field('id')->find($data['ggk']['id'])) {
                $save = M('ggk')->save($data['ggk']);
            } else {
                $save = M('ggk')->add($data['ggk']);
            }
            if ($save !== fales && $biz_save !== fales) {
                $this->ajaxReturn($data['return']);
            }
        } elseif ($data['return']['status'] === 2) {
            $this->ajaxReturn($data['return']);
        }
    }
    
    // 删除中奖名单
    public function delcheat()
    {
        $this->isLogin();
        $this->checkPrivelege('ggkg_08');
        $id = (int) I('id');
        $del = M('ggk_record')->delete($id);
        if ($del !== fales) {
            $this->ajaxReturn(array(
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 2
            ));
        }
    }
    
    // 删除活动
    public function ggkdel()
    {
        $this->isLogin();
        $this->checkPrivelege('ggkz_03');
        $id = (int) I('id');
        $del = M('ggk')->delete($id);
        $del_b = M('biz_ggk')->delete($id);
        $del_re = M('ggk_record')->where('ggkId=' . $id)->delete();
        if ($del !== fales && $del_re !== fales && $del_b !== fales) {
            $this->ajaxReturn(array(
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 2
            ));
        }
    }
    
    // 结束活动
    public function overggk()
    {
        $this->isLogin();
        $this->checkPrivelege('ggkz_04');
        $id = (int) I('id');
        $data['id'] = $id;
        $data['state'] = 2;
        $save = M('ggk')->save($data);
        $save = M('biz_ggk')->save($data);
        if ($save !== fales) {
            $this->ajaxReturn(array(
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 2
            ));
        }
    }
}

?>

