<?php
namespace Home\Model;

/**
 * 刮刮卡模型
 */
class GgkModel extends BaseModel
{
    // 获取刮刮卡列表
    public function shoplist($type)
    {
        $where['shopId'] = $_SESSION['oto_mall']['WST_USER']['shopId'];
        switch ($type) {
            case '1':
                $where = array();
                break;
            case '2':
                $where['state'] = 1;
                break;
            case '3':
                $where['state'] = 2;
                break;
            case '4':
                $where['state'] = 3;
                break;
        }
        $m = M('ggk');
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $data['show'] = $show;
        $field = array(
            'joinnum,id,title,statdate,enddate,state'
        );
        $list = $m->where($where)
            ->field($field)
            ->order('id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach ($list as $key => $vo) {
            // 判断活动是否到期
            if ($vo['enddate'] < time()) {
                $save['id'] = $vo['id'];
                $save['state'] = 2;
                $m->save($save);
                $list[$key]['state'] = 2;
            }
        }
        $data['list'] = $list;
        return $data;
    }
    
    // 处理添加表单
    public function checkfrom($ggk)
    {
        $data['ggk'] = $ggk;
        $data['ggk']['statdate'] = strtotime($data['ggk']['statdate']);
        $data['ggk']['enddate'] = strtotime($data['ggk']['enddate']);
        $data['ggk']['admin_check_status'] = 0;
        $data['ggk']['shopId'] = $_SESSION['oto_mall']['WST_USER']['shopId'];
        if ($data['ggk']['second'] || $data['ggk']['secondnums']) {
            if (! ($data['ggk']['second'] && $data['ggk']['secondnums'])) {
                $data['return']['msg'] = "二等奖填写错误";
                $data['return']['status'] = 2;
                return $data;
            }
        }
        if ($data['ggk']['third'] || $data['ggk']['thirdnums']) {
            if (! ($data['ggk']['third'] && $data['ggk']['thirdnums'])) {
                $data['return']['msg'] = "三等奖填写错误";
                $data['return']['status'] = 2;
                return $data;
            }
        }
        $data['return']['status'] = 1;
        return $data;
    }
    
    // 判断活动是否过期
    public function timeout()
    {
        $where['state'] = array(
            'neq',
            '2'
        );
        $data = M('ggk')->field('id,enddate')
            ->where($where)
            ->select();
        $newtime = time();
        foreach ($data as $key => $value) {
            // 判断是否过期
            if ($newtime > $data[$key]['enddate']) {
                M('ggk')->execute("update __PREFIX__ggk set state='2' where id=" . $data[$key]['id']);
            }
        }
    }
    
    // 获取中奖用户数据
    public function update($id)
    {
        $data = M('ggk_record')->field('id,ggkId,userId,kdnum,prize,shopId,expressId')->find($id);
        $ggk = M('ggk')->field('fist,second,third,title')->find($data['ggkId']);
        $user = M('users')->field('loginName')->find($data['userId']);
        $express = M('express')->field('id,expressCompany')
            ->where(array(
            'shopId=' . $data['shopId']
        ))
            ->select();
        $data['express'] = $express;
        $data['jp'] = array(
            $ggk['fist'],
            $ggk['second'],
            $ggk['third']
        );
        $data['title'] = $ggk['title'];
        $data['userName'] = $user['loginName'];
        return $data;
    }
    
    // 审核记录页
    public function loglist($effect = '', $limit = 10)
    {
        $field = array(
            'id',
            'title',
            'admin_check_status',
            'biz_apply_status'
        );
        $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $m = M('biz_ggk');
        $where = array(
            'shopId=' . $shopId
        );
        if ($effect != '') {
            $where['admin_check_status'] = 0;
        }
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $list = $m->where($where)
            ->field($field)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('id desc')
            ->select();
        $data = array(
            'list' => $list,
            'show' => $show
        );
        return $data;
    }
    
    // 刮刮卡列表
    public function getggklist()
    {
        $newtime = time();
        $where['statdate'] = array(
            'LT',
            $newtime
        );
        $where['state'] = 1;
        $m = M('ggk');
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $m->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach ($list as $key => $value) {
            $shopName = M('shops')->field('shopName')->find($value['shopId']);
            $list[$key]['shopName'] = $shopName['shopName'];
        }
        $data = array(
            'list' => $list,
            'show' => $show
        );
        return $data;
    }
    
    // 物流方式发奖
    public function kdsendstutas($id, $kdnum, $wuliu)
    {
        foreach ($id as $key => $vo) {
            $data['id'] = $vo;
            $data['kdnum'] = $kdnum[$key];
            $data['expressId'] = $wuliu;
            $data['sendstutas'] = 1;
            $data['sendtime'] = time();
            $save = M('ggk_record')->save($data);
        }
        
        if ($save !== fales) {
            $return['status'] = 1;
        } else {
            $return['status'] = 2;
        }
        return $return;
    }
    
    // 查询物流信息
    public function getwuliumsg($id)
    {
        $express = M('ggk_record')->field('expressId,kdnum')->find($id);
        $pinyin = M('express')->field('pinyin,expressCompany')->find($express['expressId']);
        $snoopy = new \Org\Util\Snoopy();
        $action = "http://www.kuaidi100.com/query?type=" . $pinyin['pinyin'] . "&postid=" . $express['kdnum'];
        $snoopy->referer = "http://www.kuaidi100.com/"; // 伪装来源页地址 http_referer
        $snoopy->rawheaders["Pragma"] = "no-cache"; // cache 的http头信息
        $snoopy->rawheaders["X_FORWARDED_FOR"] = "222.73.40.174"; // 伪装ip
        $snoopy->fetch($action); // 获取所有内容
        $arr = $snoopy->results;
        $array = json_decode($arr);
        $data = array();
        if (! empty($array->data)) {
            foreach ($array->data as $k => $value) {
                $data['msg'][$k]['time'] = $value->time;
                $data['msg'][$k]['context'] = $value->context;
            }
            
            sort($data['msg']);
            $data['state'] = $array->state;
            $data['state'] = "配送中";
            if ($array->state == 3) {
                $status['sendstutas'] = 2;
                M('ggk_record')->where(array(
                    'id=' . $id
                ))->save($status);
                $data['state'] = "已签收";
            }
            
            $data['nu'] = $array->nu;
            $data['gs'] = $pinyin['expressCompany'];
        } else {
            $data['status'] = 2;
        }
        return $data;
    }
    
    // 游戏页面数据
    public function getgame($ggkId)
    {
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        // 获取刮刮卡数据
        $data = M('ggk')->find($ggkId);
        // 获取当前用户使用当前刮刮卡的数据
        $m_r = M('ggk_record');
        $where['ggkId'] = $ggkId;
        $where['userId'] = $userid;
        $record = $m_r->where($where)->find();
        // 数据为空则增加
        if (! $record['id']) {
            $record = array(
                'ggkId' => $ggkId,
                'userId' => $userid,
                'shopId' => $data['shopId'],
                'sn' => 0,
                'time' => time(),
                'prize' => 0,
                'sendtime' => 0,
                'phone' => $_SESSION['oto_mall']['WST_USER']['userPhone']
            );
            $m_r->data($record)->add();
            $record['usenums'] = 0;
            M('ggk')->where('id=' . $ggkId)->setInc('joinnum');
        }
        // 判断剩余抽奖次数
        if ($record['usenums'] >= $data["canrqnums"]) {
            $data['isgua'] = 0; // 已用完所有次数
        } else {
            $data['isgua'] = 1; // 可以继续刮
        }
        $data['usercount'] = (int) $record['usenums'];
        // 判断是否过期
        $timestamp = time();
        if ($timestamp >= $data["statdate"] && $timestamp <= $data["enddate"]) {
            $data['isover'] = 1;
        } else {
            M('ggk')->execute("update oto_ggk set state=2 where id=" . $data['id']);
            M('biz_ggk')->execute("update oto_biz_ggk set state=2 where id=" . $data['id']);
            $data['isover'] = 0;
        }
        if ($data['state'] == 2) {
            $data['isover'] = 0;
        }
        // 是否中奖
        $data['islottery'] = (int) $record['islottery'];
        if ($data['islottery'] == 1) {
            // 中几等奖
            $data['prize'] = $record['prize'];
        }
        // 处理收货地址
        if ($record['addressId'] != 0) {
            $data['area'] = D('Ggk')->getress($userid, $ggkId, $record['addressId'], 1);
            $data['area']['isress'] = 1;
        } else {
            $data['area'] = D('Ggk')->getress($userid, $ggkId, '', 2);
            $data['area']['isress'] = 0;
        }
        
        // 中奖后输出的数据
        $data['prizeStr'] = '<p style="line-height:30px">一等奖: ' . $data['fist'];
        // $data['prizeStr'].='&nbsp;&nbsp;奖品数量:'.$data['fistnums'];
        $data['prizeStr'] .= '</p>';
        if ($data['second']) {
            $data['prizeStr'] .= '<p style="line-height:30px">二等奖: ' . $data['second'];
            // $data['prizeStr'].='&nbsp;&nbsp;奖品数量:'.$data['secondnums'];
            $data['prizeStr'] .= '</p>';
        }
        if ($data['third']) {
            $data['prizeStr'] .= '<p style="line-height:30px">三等奖: ' . $data['third'];
            // $data['prizeStr'].='&nbsp;&nbsp;奖品数量:'.$data['thirdnums'];
            $data['prizeStr'] .= '</p>';
        }
        
        return $data;
    }
    
    // 随机抽奖
    public function get_rand($proArr, $total)
    {
        $result = 4;
        $randNum = mt_rand(1, $total);
        foreach ($proArr as $k => $v) {
            if ($v['v'] > 0) { // 奖项存在或者奖项之外
                if ($randNum > $v['start'] && $randNum <= $v['end']) {
                    $result = $k;
                    break;
                }
            }
        }
        return $result;
    }
    
    // 中奖管理内容
    public function getmanage($id, $sendstutas = '')
    {
        $where['ggkId'] = $id;
        $where['islottery'] = 1;
        if (! empty($sendstutas)) {
            if ($sendstutas == 1) {
                $where['sendstutas'] = 0;
            } elseif ($sendstutas == 2) {
                $where['sendstutas'] = array(
                    'in',
                    '1,2,3'
                );
            }
        }
        $m = M('ggk_record');
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $m->where($where)
            ->order('id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $ggkdata = M('ggk')->field('fistlucknums,secondlucknums,thirdlucknums,fist,second,third,fistnums,secondnums,thirdnums')->find($id);
        $jpallnums = $ggkdata['fistnums'] + $ggkdata['secondnums'] + $ggkdata['thirdnums'];
        $num['jpallnums'] = $jpallnums;
        $num['zjnums'] = 0;
        $num['sendnums'] = 0;
        foreach ($list as $key => $vo) {
            switch ($vo['prize']) {
                case '1':
                    $list[$key]['jp'] = $ggkdata['fist'];
                    break;
                case '2':
                    $list[$key]['jp'] = $ggkdata['second'];
                    break;
                case '3':
                    $list[$key]['jp'] = $ggkdata['third'];
                    break;
            }
            $user = M('users')->field('loginName')->find($vo['userId']);
            $list[$key]['userName'] = $user['loginName'];
            if ($vo['islottery'] != 0) {
                $num['zjnums'] ++;
            }
            if ($vo['sendstutas'] != 0) {
                $num['sendnums'] ++;
            }
            if ($vo['addressId'] != 0) {
                $list[$key]['ress'] = D('Home/Ggk')->getress($vo['userId'], $vo['id'], $vo['addressId']);
            }
        }
        
        $num['id'] = $id;
        $data = array(
            'list' => $list,
            'show' => $show,
            'num' => $num
        );
        
        return $data;
    }
    
    // 查询中奖码
    public function checksn($sn, $ggkid)
    {
        $sn = trim($sn);
        $where['sn'] = $sn;
        if ($ggkid = '') {
            $where['ggkId'] = $ggkid;
        }
        $data = M('ggk_record')->where($where)
            ->field('id,sendstutas,ggkId,prize')
            ->find();
        
        if ($data['sendstutas'] != 0) {
            $status['status'] = 4;
            return $status;
        }
        if (empty($data['id'])) {
            $status['status'] = 2;
        } else {
            switch ($data['prize']) {
                case '1':
                    $field = array(
                        'fist'
                    );
                    break;
                case '2':
                    $field = array(
                        'second'
                    );
                    break;
                case '3':
                    $field = array(
                        'third'
                    );
                    break;
            }
            $title = M('ggk')->field($field)->find($data['ggkId']);
            $status['title'] = $title['fist'];
            $status['id'] = $data['id'];
            $status['status'] = 1;
        }
        return $status;
    }
    // 处理exel需要导出的数据
    public function runrecord($id)
    {
        $data = M('ggk_record')->where(array(
            'ggkId=' . $id,
            'islottery=1'
        ))
            ->order('sendstutas desc')
            ->select();
        foreach ($data as $key => $vo) {
            $ggk = M('ggk')->field('fist,second,third')->find($vo['ggkId']);
            switch ($vo['prize']) {
                case '1':
                    $data[$key]['jp'] = $ggk['fist'];
                    break;
                case '2':
                    $data[$key]['jp'] = $ggk['second'];
                    break;
                case '3':
                    $data[$key]['jp'] = $ggk['third'];
                    break;
            }
            $user = M('users')->field('loginName')->find($vo['userId']);
            $data[$key]['userName'] = $user['loginName'];
            $data[$key]['time'] = date('Y-m-d H:i', $vo['time']);
            if (! $data[$key]['phone']) {
                $data[$key]['phone'] = '空';
            }
            switch ($vo['sendstutas']) {
                case '0':
                    $data[$key]['sendstutas'] = '未发奖';
                    $data[$key]['sendtime'] = '待定';
                    break;
                case '1':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '配送中';
                    break;
                case '2':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '已签收';
                    break;
                case '3':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '以其他方式发奖';
                    break;
            }
            if ($vo['addressId'] != 0) {
                $ress = M('user_address')->where(array(
                    'userId=' . $vo['userId']
                ))
                    ->field(('postCode,addressFlag,createTime'), true)
                    ->order('isDefault desc')
                    ->find($vo['addressId']);
                for ($i = 1; $i <= 3; $i ++) {
                    $area['areaId' . $i] = M('areas')->field('areaName')->find($ress['areaId' . $i]);
                    $ressall['areaId' . $i] = $area['areaId' . $i]['areaName'];
                }
                $community = M('communitys')->field('communityName')->find($ress['communityId']);
                $data[$key]['ress'] = $ressall['areaId1'] . '-' . $ressall['areaId2'] . '-' . $ressall['areaId3'] . '-' . $community['communityName'] . '-' . $ress['address'] . '-' . $ress['userName'] . '-' . $ress['userPhone'];
            } else {
                $data[$key]['ress'] = "未选择收货地址";
            }
            
            // 排序
            $arr[$key][] = $data[$key]['id'];
            $arr[$key][] = $data[$key]['userName'];
            $arr[$key][] = $data[$key]['phone'];
            $arr[$key][] = $data[$key]['sn'];
            $arr[$key][] = $data[$key]['time'];
            $arr[$key][] = $data[$key]['jp'];
            $arr[$key][] = $data[$key]['sendstutas'];
            $arr[$key][] = $data[$key]['sendtime'];
            $arr[$key][] = $data[$key]['ress'];
        }
        return $arr;
    }

    /**
     * 导出数据为excel表格
     * 
     * @param $data 一个二维数组,结构如同从数据库查出来的数组            
     * @param $title excel的第一行标题,一个数组,如果为空则没有标题            
     * @param $filename 下载的文件名
     *            @examlpe
     *            $stu = M ('User');
     *            $arr = $stu -> select();
     *            exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
     */
    public function exportexcel($data = array(), $title = array(), $filename = 'report')
    {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        // 导出xls 开始
        echo "<table border='1' style='font-size:18px;'><tr>";
        if (! empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "UTF-8", '<th>' . $v . '</th>');
            }
            $title = implode("\t ", $title);
            echo "$title\n";
        }
        echo "</tr>";
        if (! empty($data)) {
            foreach ($data as $key => $val) {
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck] = iconv("UTF-8", "UTF-8", '<td>' . $cv . '</td>');
                }
                echo "<tr>";
                echo implode("\n", $data[$key]);
                echo "</tr>";
            }
            
            // echo implode("\n",$data);
        }
        echo "</table>";
    }
    
    // 检查中奖码页面
    public function checksnlist($sn)
    {
        $sn = trim($sn);
        $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $where['shopId'] = $shopId;
        $where['sn'] = array(
            'like',
            '%' . $sn . '%'
        );
        $data = M('ggk_record')->where($where)
            ->order('sendstutas')
            ->select();
        if (empty($data)) {
            $arr['status'] = 2;
            return $arr;
        }
        foreach ($data as $key => $vo) {
            $ggk = M('ggk')->field('fist,second,third')->find($vo['ggkId']);
            switch ($vo['prize']) {
                case '1':
                    $data[$key]['jp'] = $ggk['fist'];
                    break;
                case '2':
                    $data[$key]['jp'] = $ggk['second'];
                    break;
                case '3':
                    $data[$key]['jp'] = $ggk['third'];
                    break;
            }
            $user = M('users')->field('loginName')->find($vo['userId']);
            $data[$key]['userName'] = $user['loginName'];
            $data[$key]['time'] = date('Y-m-d H:i', $vo['time']);
            switch ($vo['sendstutas']) {
                case '0':
                    $data[$key]['sendstutas'] = '未发奖';
                    $data[$key]['sendtime'] = '待定';
                    break;
                case '1':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '配送中';
                    break;
                case '2':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '已签收';
                    break;
                case '3':
                    $data[$key]['sendtime'] = date('Y-m-d H:i', $vo['sendtime']);
                    $data[$key]['sendstutas'] = '以其他方式发奖';
                    break;
            }
            if (! $data[$key]['Phone']) {
                $data[$key]['Phone'] = '空';
            }
            $arr['srt'] .= "<tr>";
            $arr['srt'] .= "<td>" . $data[$key]['id'] . "</td>" . "<td>" . $data[$key]['sn'] . "</td>" . "<td>" . $data[$key]['jp'] . "</td>" . "<td id='ressId" . $data[$key]['id'] . "' value='" . $data[$key]['addressId'] . "'>" . $data[$key]['sendstutas'] . "</td>" . "<td>" . $data[$key]['sendtime'] . "</td>" . "<td>" . $data[$key]['userName'] . "</td><td>";
            if ($vo['sendstutas'] == 0) {
                $arr['srt'] .= "<a style='cursor: pointer;' onclick='javascript:runcheat(" . $vo['id'] . ")'>[发奖]</a> |";
            }
            $arr['srt'] .= "<a style='cursor: pointer;' onclick='javascript:del(" . $vo['id'] . ")'>[删除]</a>";
            $arr['srt'] .= "</td></tr>";
            
            $arr['ress'] = $vo['addressId'];
            $arr['id'] = $vo['id'];
        }
        $arr['status'] = 1;
        
        return $arr;
    }

    /**
     * 产生随机字符串
     *
     * 产生一个指定长度的随机字符串,并返回给用户
     *
     * @access public
     * @param int $len
     *            产生字符串的位数
     * @return string
     */
    public function randstr($len = 6)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        // characters to build the password from
        mt_srand((double) microtime() * 1000000 * getmypid());
        // seed the random number generater (must be done)
        $password = '';
        while (strlen($password) < $len)
            $password .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $password;
    }
    
    // 买家中奖纪录
    public function gitlist($userid)
    {
        $where['islottery'] = 1;
        $where['userId'] = $userid;
        $m = M('ggk_record');
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $m->where($where)
            ->order('sendstutas')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        
        foreach ($list as $key => $vo) {
            $ggk = M('ggk')->field('title,fist,second,third')->find($vo['ggkId']);
            $list[$key]['title'] = $ggk['title'];
            switch ($vo['prize']) {
                case '1':
                    $list[$key]['jp'] = $ggk['fist'];
                    break;
                case '2':
                    $list[$key]['jp'] = $ggk['second'];
                    break;
                case '3':
                    $list[$key]['jp'] = $ggk['third'];
                    break;
            }
            $list[$key]['time'] = date('Y-m-d H:i', $vo['time']);
            if (! $list[$key]['phone']) {
                $list[$key]['Phone'] = '空';
            }
            if ($vo['addressId'] != 0) {
                $list[$key]['ress'] = D('Ggk')->getress($vo['userId'], $vo['id'], $vo['addressId']);
            }
        }
        $data = array(
            'list' => $list,
            'show' => $show
        );
        return $data;
    }
    
    // 获取收货地址数据
    public function getress($userid, $ggkid, $addressId = '', $type)
    {
        if ($addressId != 0) {
            $data[0] = M('user_address')->field(('postCode,addressFlag,createTime'), true)
                ->order('isDefault desc')
                ->find($addressId);
        } else {
            $data = M('user_address')->where(array(
                'userId=' . $userid
            ))
                ->field(('postCode,addressFlag,createTime'), true)
                ->order('isDefault desc')
                ->select();
        }
        
        $sn = M('ggk_record')->where(array(
            'userId=' . $userid,
            'ggkId=' . $ggkid
        ))
            ->field('sn')
            ->find();
        
        foreach ($data as $key => $vo) {
            for ($i = 1; $i <= 3; $i ++) {
                $area['areaId' . $i] = M('areas')->field('areaName')->find($vo['areaId' . $i]);
                $data[$key]['areaId' . $i] = $area['areaId' . $i]['areaName'];
            }
            $community = M('communitys')->field('communityName')->find($vo['communityId']);
            $data[$key]['communityName'] = $community['communityName'];
            if ($type == 2) {
                $arr['str'] .= '<p><label>';
                $arr['str'] .= '<input type="radio" name="address" value="' . $data[$key]['addressId'] . '">' . $data[$key]['areaId1'] . '&nbsp;' . $data[$key]['areaId2'] . '&nbsp;' . $data[$key]['areaId3'] . '&nbsp;' . $community['communityName'] . '&nbsp;' . $data[$key]['address'] . '&nbsp;' . $data[$key]['userName'] . '&nbsp;' . $data[$key]['userPhone'];
                $arr['str'] .= '</label></p>';
            } else {
                $arr['str'] = $data[$key]['areaId1'] . '&nbsp;' . $data[$key]['areaId2'] . '&nbsp;' . $data[$key]['areaId3'] . '&nbsp;' . $community['communityName'] . '&nbsp;' . $data[$key]['address'] . '&nbsp;' . $data[$key]['userName'] . '&nbsp;' . $data[$key]['userPhone'];
            }
        }
        $arr['sn'] = $sn['sn'];
        return $arr;
    }
}

?>