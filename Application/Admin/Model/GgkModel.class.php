<?php
namespace Admin\Model;

/**
 * 刮刮卡模型
 */
class GgkModel extends BaseModel
{
    // 获取刮刮卡列表
    public function indexlist($type)
    {
        $m = M('ggk');
        switch ($type) {
            case '1':
                $where['state'] = 1;
                break;
            case '2':
                $where['state'] = 2;
                break;
            case '3':
                $where['state'] = 3;
                break;
        }
        $count = $m->where($where)->count();
        $page = new \Think\Page($count, 15);
        $show = $page->show();
        $list = $m->where($where)
            ->order('id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        
        foreach ($list as $key => $vo) {
            $shopn = M('shopName')->field('shopName')->find($vo['shopId']);
            $list[$key]['shopName'] = $shopn['shopName'];
        }
        $data = array(
            'list' => $list,
            'show' => $show
        );
        return $data;
    }
    // 获取审核列表
    public function checklist($type = '')
    {
        $field = array(
            'id,title,statdate,enddate,biz_apply_status,admin_check_status'
        );
        $where = array();
        if ($type === 1) {
            $where['admin_check_status'] = 0;
        }
        $m = M('biz_ggk');
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $m->where($where)
            ->field($field)
            ->order('id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach ($list as $key => $vo) {
            if ($list[$key]['admin_check_status'] == 2) {
                $list[$key]['msg'] = "该申请已被拒绝";
            }
            if ($list[$key]['admin_check_status'] == 1 && $list[$key]['biz_apply_status'] == 1) {
                $list[$key]['msg'] = "已通过新增申请";
            }
            if ($list[$key]['admin_check_status'] == 1 && $list[$key]['biz_apply_status'] == 2) {
                $list[$key]['msg'] = "已通过修改申请";
            }
            if ($list[$key]['admin_check_status'] == 1 && $list[$key]['biz_apply_status'] == 3) {
                $list[$key]['msg'] = "已通过删除申请";
            }
            if ($list[$key]['admin_check_status'] == 0 && $list[$key]['biz_apply_status'] == 1) {
                $list[$key]['msg'] = "<a class='btn btn-default glyphicon glyphicon-pencil' href=";
                $list[$key]['msg'] .= U('Admin/Ggk/release', array(
                    'id' => $list[$key]['id']
                ));
                $list[$key]['msg'] .= ">发布</a>&nbsp;&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $list[$key]['id'] . ")'>拒绝</buttona>";
            }
            
            if ($list[$key]['admin_check_status'] == 0 && $list[$key]['biz_apply_status'] == 3) {
                $list[$key]['msg'] = "<button type='button' class='btn btn-default glyphicon glyphicon-pencil' onclick='javascript:over(" . $list[$key]['id'] . ")'>结束</buttona>&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $list[$key]['id'] . ")'>拒绝</buttona>";
            }
            
            if ($list[$key]['admin_check_status'] == 0 && $list[$key]['biz_apply_status'] == 2) {
                $list[$key]['msg'] = "<a class='btn btn-default glyphicon glyphicon-pencil' href=";
                $list[$key]['msg'] .= U('Admin/Ggk/release', array(
                    'id' => $list[$key]['id'],
                    'xg' => '1'
                ));
                $list[$key]['msg'] .= ">修改</a>&nbsp;&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $list[$key]['id'] . ")'>拒绝</buttona>";
            }
        }
        $data = array(
            'list' => $list,
            'show' => $show
        );
        return $data;
    }
    // 验证申请审核的数据
    public function checkfrom($ggk)
    {
        $data['ggk'] = $ggk;
        $data['ggk']['statdate'] = strtotime($data['ggk']['statdate']);
        $data['ggk']['enddate'] = strtotime($data['ggk']['enddate']);
        $data['ggk']['admin_check_status'] = 1;
        if ((int) $data['ggk']['biz_apply_status'] === 1) {
            $data['ggk']['state'] = 1;
        }
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
}

?>