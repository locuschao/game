<?php
namespace Home\Controller;

/**
 * 刮刮卡控制器
 */
class GgkController extends BaseController
{
    // 刮刮卡商户列表
    public function shoplist()
    {
        $this->isShopLogin();
        $type = (int) I('val');
        $data = D('Ggk')->shoplist($type);
        $status = session('status');
        $this->assign('list', $data['list']);
        $this->assign('show', $show);
        $this->assign('umark', 'ggkshoplist');
        $this->display('Shops/ggk/shoplist');
    }
    // 刮刮卡中奖管理
    public function manage()
    {
        $this->isShopLogin();
        $id = (int) I('id');
        $sendstutas = (int) I('sendstutas');
        $data = D('Home/Ggk')->getmanage($id, $sendstutas);
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign('num', $data['num']);
        $this->assign('umark', 'ggkshoplist');
        $this->display('Shops/ggk/manage');
    }
    
    // 查找中奖码
    public function checksn()
    {
        $this->isShopLogin();
        $sn = I('sn');
        $ggkid = (int) I('id');
        $status = D('Ggk')->checksn($sn, $ggkid);
        $this->ajaxReturn($status);
    }
    
    // 中奖名单导出exel
    public function exportexcel()
    {
        $id = (int) I('id');
        $arr = D('Ggk')->runrecord($id);
        $fildName = array(
            'id',
            '用户名称',
            '手机号码',
            '中奖码',
            '中奖时间',
            '奖品',
            '是否发奖',
            '发奖时间',
            '收货信息'
        );
        $ggk = M('ggk')->field('title')->find($id);
        D('ggk')->exportexcel($arr, $fildName, $ggk['title'] . '-中奖名单');
    }
    
    // 物流方式发奖
    public function kdsendstutas()
    {
        $this->isShopLogin();
        $id = I('id');
        $kdnum = I('kdnum');
        $wuliu = I('wuliu');
        $data = D('Ggk')->kdsendstutas($id, $kdnum, $wuliu);
        $this->ajaxReturn($data);
    }
    
    // 获取物流数据
    public function getwuliumsg()
    {
        $this->isShopLogin();
        $id = I('id');
        $data = D('Ggk')->getwuliumsg($id);
        
        $this->ajaxReturn($data);
    }
    
    // 获取卖家的物流信息
    public function getwuliu()
    {
        $this->isShopLogin();
        $shopid = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $where['isShow'] = 1;
        $where['expressFlag'] = 1;
        $where['shopId'] = $shopid;
        $data['str'] = M('express')->field('id,expressCompany')
            ->where($where)
            ->select();
        if (empty($data['str'])) {
            $data['status'] = 2;
        } else {
            $data['status'] = 1;
        }
        $this->ajaxReturn($data);
    }
    
    // 其他方式发奖
    public function sendstutas()
    {
        $this->isShopLogin();
        $id = (int) I('id');
        $sn = I('sn');
        if (empty($id)) {
            $where['sn'] = $sn;
        } else {
            $where['id'] = $id;
        }
        $where['sendstutas'] = 0;
        $data['sendtime'] = time();
        $data['sendstutas'] = 3;
        $return = M('ggk_record')->where($where)->save($data);
        if ($return !== false) {
            $status['status'] = 1;
        } else {
            $status['status'] = 2;
        }
        $this->ajaxReturn($status);
    }
    
    // 删除中奖名单
    public function delcheat()
    {
        $this->isShopLogin();
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
    
    // 卖家编辑中奖用户的基本信息
    public function update()
    {
        $this->isUserLogin();
        $id = I('id');
        $xg = I('xg');
        if (empty($xg)) {
            $data = D('Ggk')->update($id);
            $this->assign('data', $data);
            $this->display('Shops/ggk/udjz');
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
    
    // 结束活动
    public function overggk()
    {
        $this->isShopLogin();
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
    // 删除活动
    public function ggkdel()
    {
        $this->isShopLogin();
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
    
    // 添加\编辑刮刮卡表单
    public function add()
    {
        $this->isShopLogin();
        $id = (int) I('id');
        $biz_apply_status = (int) I('biz_apply_status');
        if (empty($biz_apply_status)) {
            $this->redirect('Home/Ggk/add', array(
                'biz_apply_status' => 1
            ));
        }
        $this->assign('umark', 'ggkshoplist');
        $this->assign('biz_apply_status', $biz_apply_status);
        if ($biz_apply_status == 2 && $id) {
            $data = M('biz_ggk')->find($id);
            $this->assign('data', $data);
            $this->assign('xg', 'x');
            $this->assign('from', 'changefrom');
        } else {
            $this->assign('from', 'addfrom');
        }
        $this->display('Shops/ggk/add');
    }
    
    // 刮刮卡处理添加\编辑内容
    public function runadd()
    {
        $this->isShopLogin();
        $ggk = $_POST;
        $data = D('Ggk')->checkfrom($ggk);
        switch ($data['return']['status']) {
            case '1':
                if ($data['ggk']['xg'] === 'x') {
                    unset($data['ggk']['xg']);
                    
                    $save = M('biz_ggk')->save($data['ggk']);
                    if ($save !== fales) {
                        $this->ajaxReturn($data['return']);
                    }
                } else {
                    $add = M('biz_ggk')->add($data['ggk']);
                    if ($add !== fales) {
                        $this->ajaxReturn($data['return']);
                    }
                }
                break;
            case '2':
                $this->ajaxReturn($data['return']);
                break;
        }
    }
    
    // 检查活动名称是否重复
    public function checkname()
    {
        $this->isShopLogin();
        $title = I('title');
        $where['title'] = $title;
        $ggk = M('ggk')->where($where)
            ->field('title')
            ->find();
        if ($ggk['title']) {
            $this->ajaxReturn(array(
                'msg' => '该活动名称已经被使用',
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'msg' => '可以使用该名称',
                'status' => 2
            ));
        }
    }
    
    // 审核记录页
    public function ggklog()
    {
        $this->isShopLogin();
        $effect = (int) I('effect');
        $data = D('ggk')->loglist($effect, 10);
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign('umark', 'ggkshoplist');
        $this->display('Shops/ggk/ggklog');
    }
    
    // 检查中奖码页面
    public function checksnlist()
    {
        $this->isShopLogin();
        $sn = I('sn');
        if ($sn != '') {
            $data = D('Ggk')->checksnlist($sn);
            $this->ajaxReturn($data);
        }
        $this->assign('umark', 'ggkchecksnlist');
        $this->display('Shops/ggk/checksn');
    }
    
    // 刮刮卡列表
    public function getggklist()
    {
        $this->isUserLogin();
        $data = D('Ggk')->getggklist();
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->display('Ggk/list');
    }
    
    // 买家中奖纪录
    public function gitlist()
    {
        $this->isUserLogin();
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        D('Home/Ggk')->timeout();
        $data = D('Ggk')->gitlist($userid, $type);
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        
        $this->assign('umark', 'gitlist');
        $this->display('Users/ggk/gitlist');
    }
    
    // 玩游戏页面
    public function getggk()
    {
        $this->isUserLogin();
        D('Home/Ggk')->timeout();
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        $ggkId = (int) I('ggkid');
        $data = D('Ggk')->getgame($ggkId);
        
        $this->assign('data', $data);
        $this->display('Ggk/getggk');
    }
    
    // 抽奖方法
    public function getluck()
    {
        $this->isUserLogin();
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        $id = (int) I('id');
        if (! $id) {
            $this->redirect('Home/Ggk/getggklist');
        }
        $where['ggkId'] = $id;
        $where['userId'] = $userid;
        // 抽奖次数
        $m_r = M('ggk_record');
        $usenums = $m_r->field('usenums')
            ->where($where)
            ->find();
        $m = M('ggk');
        $Lottery = $m->find($id);
        // /判断活动是否结束
        if ($Lottery['enddate'] < time()) {
            echo '{"isover":"1","usenums":"' . $usenums['usenums'] . '","prizetype":"","msg":"' . $Lottery['endinfo'] . '"}';
            exit();
        }
        // ////判断活动是否超过次数
        $canrqnums = (int) $Lottery['canrqnums'];
        if ($usenums['usenums'] == $canrqnums) {
            echo '{"isover":"2","usenums":"' . $usenums['usenums'] . '","prizetype":"","msg":"抽奖次数已用完!"}';
            exit();
        }
        // /抽奖开始运行
        $firstNum = intval($Lottery['fistnums']);
        $secondNum = intval($Lottery['secondnums']);
        $thirdNum = intval($Lottery['thirdnums']);
        $multi = intval($Lottery['canrqnums']); // 最多抽奖次数
                                              // 中奖概率 = 奖品总数/(预估活动人数*每人抽奖次数)
        $prize_arr = array(
            '0' => array(
                'id' => 1,
                'prize' => '一等奖',
                'v' => $firstNum,
                'start' => 0,
                'end' => $firstNum
            ),
            '1' => array(
                'id' => 2,
                'prize' => '二等奖',
                'v' => $secondNum,
                'start' => $firstNum,
                'end' => $firstNum + $secondNum
            ),
            '2' => array(
                'id' => 3,
                'prize' => '三等奖',
                'v' => $thirdNum,
                'start' => $firstNum + $secondNum,
                'end' => $firstNum + $secondNum + $thirdNum
            ),
            '3' => array(
                'id' => 4,
                'prize' => '谢谢参与',
                'v' => (intval($Lottery['allpeople'])) * $multi - ($firstNum + $secondNum + $thirdNum),
                'start' => $firstNum + $secondNum + $thirdNum,
                'end' => intval($Lottery['allpeople']) * $multi
            )
        );
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val;
        }
        
        // print_r($arr);
        // -------------------------------
        // 随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
        // -------------------------------
        if ($Lottery['allpeople'] == 1) {
            $rid = 1;
        } else {
            $rid = D('Ggk')->get_rand($arr, intval($Lottery['allpeople']) * $multi);
        }
        $winprize = $prize_arr[$rid - 1]['prize'];
        $zjl = false;
        switch ($rid) {
            case 1:
                
                if ($Lottery['fistlucknums'] > $Lottery['fistnums']) {
                    $zjl = false;
                    $winprize = '谢谢参与';
                } else {
                    $zjl = true;
                    $m->execute("update oto_ggk  set fistlucknums=fistlucknums+1 where id=" . $id);
                }
                break;
            case 2:
                if ($Lottery['secondlucknums'] > $Lottery['secondnums']) {
                    $zjl = false;
                    $winprize = '谢谢参与';
                } else {
                    // 判断是否设置了2等奖&&数量
                    if (empty($Lottery['second']) && empty($Lottery['secondnums'])) {
                        $zjl = false;
                        $winprize = '谢谢参与';
                    } else { // 输出中了二等奖
                        $zjl = true;
                        $m->execute("update oto_ggk  set secondnums=secondnums+1 where id=" . $id);
                    }
                }
                break;
            
            case 3:
                if ($Lottery['thirdlucknums'] > $Lottery['thirdnums']) {
                    $zjl = false;
                    $winprize = '谢谢参与';
                } else {
                    if (empty($Lottery['third']) && empty($Lottery['thirdnums'])) {
                        $zjl = false;
                        $winprize = '谢谢参与';
                    } else {
                        $zjl = true;
                        $m->execute("update oto_ggk set thirdnums=thirdnums+1 where id=" . $id);
                    }
                }
                break;
            default:
                $zjl = false;
                $winprize = '谢谢参与';
                break;
        }
        
        if ($zjl) {
            if ($rid >= 1 && $rid <= 3) {
                // 中奖写入数据库
                $m_r->where($where)->setInc('usenums');
                $data['sn'] = uniqid($userid);
                $data['islottery'] = 1;
                $data['prize'] = $rid;
                $m_r->where($where)->save($data);
                unset($usenums);
                echo '{"success":"1","usenums":"' . $usenums['usenums'] . '","sn":"' . $sn . '","prizetype":"' . $rid . '","msg":"' . $winprize . '"}';
                exit();
            } else {
                $m_r->where($where)->setInc('usenums');
                echo '{"success":"0","usenums":"' . $usenums['usenums'] . '","prizetype":"' . $rid . '","msg":"' . $winprize . '"}';
                unset($usenums);
                
                exit();
            }
        } else {
            $m_r->where($where)->setInc('usenums');
            echo '{"success":"0","usenums":"' . $usenums['usenums'] . '","prizetype":"' . $rid . '","msg":"' . $winprize . '"}';
            unset($usenums);
            exit();
        }
    }
    
    // 用户收货地址处理
    public function getress()
    {
        $tj = (int) I('tj');
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        $ggkid = (int) I('id');
        if ($tj === 1) {
            $ress['addressId'] = (int) I('ress');
            $save = M('ggk_record')->where(array(
                'ggkId=' . $ggkid,
                'userId=' . $userid
            ))
                ->data($ress)
                ->save();
            if ($save !== false) {
                $data['status'] = 1;
            } else {
                $data['status'] = 2;
            }
        } else {
            $data = D('Ggk')->getress($userid, $ggkid);
        }
        $this->ajaxReturn($data);
    }
}
?>