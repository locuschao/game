<?php
namespace GameAPI\Controller;

use Think\Model;
use GameAPI\Controller\BaseController;

class UcenterController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }
    // 积分
    public function score()
    {
        parent::isLogin();
        $map['userId'] = session('oto_userId');
        $this->score = M('users')->where($map)->getField('userScore');
        $month['userid'] = session('oto_userId');
        $month['_string'] = "date_sub(curdate(), INTERVAL 30 DAY) <= FROM_UNIXTIME(time)";
        $this->scoreList = M('score_record')->field('score,time,IncDec,type')
            ->where($month)
            ->order('id desc')
            ->select();
        $this->display();
    }
    
    
    //创建目录
    function mkFolder($path)
    {
        if(!is_readable($path))
        {
            is_file($path) or mkdir($path,0700);
        }
    }
    
    //peng
    public function uploadImg(){
        
        $raw_post_data = file_get_contents('php://input', 'r');
        
        preg_match('/(--.*)[\s\S]*charset=utf-8\s*([\s\S]*)\1--/',$raw_post_data,$match);//匹配图片
        
        preg_match('/name="(.*)";.*"/',$raw_post_data,$data);//匹配数据
        $post_data = json_decode($data[1],true);
        
        preg_match('/filename="(.*)"/',$raw_post_data,$picName);
        
        
        $userId = authCode($post_data['userId']);
        $picname = $picName[1];
        $pic_blob = $match[2];
        $picsize = strlen($pic_blob);
        dd($raw_post_data,true);
        dd($picsize,true);
        if ($picname != "") {
            if ($picsize > 512000) { // 限制上传大小
                $arr = array(
                    'msg' => '图片大小不能超过500k',
                    'status' => - 1
                );
                $this->ajaxReturn($arr);
            }
            $type = strstr($picname, '.'); // 限制上传格式
            if ($type != ".gif" && $type != ".jpg" && $type != ".png") {
                $arr = array(
                    'msg' => '图片格式不对！',
                    'status' => - 2
                );
                $this->ajaxReturn($arr);
            }
            $rand = rand(100, 999);
            $data = date('Y-m', time());
            $pics = date("YmdHis") . $rand . $type; // 命名图片名称
                                                    // 上传路径
            $pic_path = "./Upload/users/" . $data . '/' . $pics;
            $this-> mkFolder("./Upload/users/" . $data);
            
            if (file_put_contents($pic_path,$pic_blob)) {
               
                M('users')->where(array(
                    'userId' => $userId
                ))->setField('userPhoto', trim($pic_path, '.'));
                $size = round($picsize / 1024, 2); // 转换成kb
                $arr = array(
                    'name' => $picname,
                    'image' => $pic_path,
                    'size' => $size,
                    'status' => 1,
                    'msg' => '图片上传成功'
                );
                $this->ajaxReturn($arr); // 输出json数据
            } else {
                
                $arr = array(
                    'msg' => '图片上传失败！',
                    'status' => - 3
                );
                $this->ajaxReturn($arr);
            }
        } else {
            $arr = array(
                'msg' => '图片上传失败！',
                'status' => - 4
            );
            $this->ajaxReturn($arr);
        }
        
        
    }
    
    // 上传头像
    public function uploadImg_bak()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        
        $picname = $_FILES['image']['name'];
        $picsize = $_FILES['image']['size'];
      
        if ($picname != "") {
            if ($picsize > 512000) { // 限制上传大小
                $arr = array(
                    'msg' => '图片大小不能超过500k',
                    'status' => - 1
                );
                $this->ajaxReturn($arr);
            }
            $type = strstr($picname, '.'); // 限制上传格式
            if ($type != ".gif" && $type != ".jpg" && $type != ".png") {
                $arr = array(
                    'msg' => '图片格式不对！',
                    'status' => - 2
                );
                $this->ajaxReturn($arr);
            }
            $rand = rand(100, 999);
            $data = date('Y-m', time());
            $pics = date("YmdHis") . $rand . $type; // 命名图片名称
                                                    // 上传路径
            $pic_path = "./Upload/users/" . $data . '/' . $pics;
            $this-> mkFolder("./Upload/users/" . $data);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $pic_path)) {
                M('users')->where(array(
                    'userId' => $userId
                ))->setField('userPhoto', trim($pic_path, '.'));
                $size = round($picsize / 1024, 2); // 转换成kb
                $arr = array(
                    'name' => $picname,
                    'image' => $pic_path,
                    'size' => $size,
                    'status' => 1,
                    'msg' => '图片上传成功'
                );
                $this->ajaxReturn($arr); // 输出json数据
            } else {
                $arr = array(
                    'msg' => '图片上传失败！',
                    'status' => - 3
                );
                $this->ajaxReturn($arr);
            }
        } else {
            $arr = array(
                'msg' => '图片上传失败！',
                'status' => - 4
            );
            $this->ajaxReturn($arr);
        }
    }
    
    // 钱包
    public function wallet()
    {
        $page = I('page', 0, 'intval');
        $userId = authCode(I('userId'));
        $map['userId'] = $userId;
        $balance = M('users')->where($map)->getField('userMoney');
        $field = "m.type,m.money,m.time,m.orderNo,m.balance,m.payWay,m.IncDec";
        $bmap['m.userid'] = $userId;
        $balanceList = M('money_record as m')->where($bmap)
            ->field($field)
            ->order('m.id DESC')
            ->limit($page * 20, 20)
            ->select();
        foreach ($balanceList as $k => $v) {
            $balanceList[$k]['time'] = date('Y-m-d', $v['time']);
            if ($v['type'] == 0) {
                $balanceList[$k]['yongtu'] = '其它';
            } else 
                if ($v['type'] == 1) {
                    $balanceList[$k]['yongtu'] = '购物消费';
                } else 
                    if ($v['type'] == 2) {
                        $balanceList[$k]['yongtu'] = '订单取消';
                    } else 
                        if ($v['type'] == 3) {
                            $balanceList[$k]['yongtu'] = '充值';
                        } else 
                            if ($v['type'] == 4) {
                                $balanceList[$k]['yongtu'] = '余额提现';
                            } else 
                                if ($v['type'] == 5) {
                                    $balanceList[$k]['yongtu'] = '无效订单';
                                } else if($v['type']==11) {
                                    $balanceList[$k]['yongtu'] = '分销提现';
                                }else {
                                    $balanceList[$k]['yongtu'] = '其它';
                                }
        }
        $arr = array();
        $arr['balance'] = $balance;
        $arr['balanceList'] = $balanceList;
        $this->returnJson($arr);
    }
    // 充值
    public function topUp()
    {
        $this->display();
    }
    
    // 余额说明
    public function explain()
    {
        $this->display();
    }
}