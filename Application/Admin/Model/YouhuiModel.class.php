<?php
namespace Admin\Model;

/**
 * 优惠券模型
 */
class YouhuiModel extends BaseModel
{
    // 优惠券提交内容处理
    public function check_error($data, $info)
    {
        $endtime = strtotime($data['end_time']);
        $newtime = time();
        $i = 0;
        if (empty($data['icon'])) {
            if (empty($info['fileToUpload1'])) {
                $data['img'] = '列表图没有上传';
                $i ++;
            }
        }
        if ($endtime < $newtime) {
            $data['msg'] = "结束时间小于现在时间。";
            $data['begin_time'] = date('Y/m/d H:i', $data['begin_time']);
            $data['end_time'] = date('Y/m/d H:i', $data['end_time']);
            $i ++;
        }
        $data['error'] = $i;
        if ($data['error'] > 0) {
            return $data;
        }
        if ($data['img3'] != 1) {
            $data['icon'] = __ROOT__ . "/Upload/" . $info['fileToUpload1']['savepath'] . $info['fileToUpload1']['savename'];
        }
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['create_time'] = time();
        switch ($data['youhui_scope']) {
            case '2':
                if ($data['scopeval1']) {
                    $data['shop_cat_type'] = 1;
                    $data['shop_cat_id'] = $data['scopeval1'];
                    unset($data['scopeval1']);
                }
                if ($data['scopeval2']) {
                    $data['shop_cat_type'] = 2;
                    $data['shop_cat_id'] = $data['scopeval2'];
                    unset($data['scopeval2']);
                }
                if ($data['scopeval3']) {
                    $data['shop_cat_type'] = 3;
                    $data['shop_cat_id'] = $data['scopeval3'];
                    unset($data['scopeval3']);
                }
                break;
            case '3':
                if ($data['scopeval']) {
                    $data['good_id'] = implode(',', $data['scopeval']);
                    unset($data['scopeval']);
                }
                break;
            case '4':
                if ($data['scopeval']) {
                    $data['brand_id'] = implode(',', $data['scopeval']);
                    unset($data['scopeval']);
                }
                break;
            case '5':
                if ($data['deal_cate_id1']) {
                    $data['deal_cate_type'] = 1;
                    $data['deal_cate_id'] = $data['deal_cate_id1'];
                    unset($data['deal_cate_id1']);
                }
                if ($data['deal_cate_id2']) {
                    $data['deal_cate_type'] = 2;
                    $data['deal_cate_id'] = $data['deal_cate_id2'];
                    unset($data['deal_cate_id2']);
                }
                if ($data['deal_cate_id3']) {
                    $data['deal_cate_type'] = 3;
                    $data['deal_cate_id'] = $data['deal_cate_id3'];
                    unset($data['deal_cate_id3']);
                }
                break;
                break;
        }
        unset($data['check_shop_name']);
        unset($data['articleContent']);
        unset($data['error']);
        unset($data['img3']);
        unset($data['areaId1']);
        unset($data['cats_id']);
        return $data;
    }
    // 获取优惠券主表内容
    public function index_list($limit = 25, $search = false)
    {
        $m = M('Youhui');
        $field = array(
            'id',
            'name',
            'city_id',
            'total_num',
            'total_fee',
            'user_count',
            'pub_by',
            'supplier_id',
            'create_time',
            'end_time',
            'youhui_scope',
            'is_effect',
            'breaks_menoy',
            'youhui_type'
        );
        $count = $m->count();
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        if ($search) {
            $where['name'] = array(
                'like',
                '%' . $search . '%'
            );
            $list = $m->field($field)
                ->where($where)
                ->order('id desc,create_time')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        } else {
            $list = $m->field($field)
                ->order('id desc,create_time')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        }
        $areas_m = M('areas');
        $shops_m = M('shops');
        foreach ($list as $key => $value) {
            $areas = $areas_m->field('areaName')->find($value['city_id']);
            if ($areas['areaName'] == 0) {
                $value['city_id'] = "全国可用";
            } else {
                $value['city_id'] = $areas['areaName'];
            }
            $value['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $value['end_time'] = date('Y-m-d H:i', $value['end_time']);
            $data[] = $value;
        }
        $list_data = array(
            'list' => $data,
            'show' => $show
        );
        return $list_data;
    }
    
    // 获取审核表内容
    public function getshopup_log($limit = 25, $search = false)
    {
        $m = M('youhui_biz_submit');
        $field = array(
            'id',
            'name',
            'city_id',
            'breaks_menoy',
            'total_fee',
            'supplier_id',
            'create_time',
            'biz_apply_status',
            'youhui_type',
            'admin_check_status'
        );
        $where = array();
        if ($search) {
            $where['admin_check_status'] = '0';
        }
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $list = $m->field($field)
            ->where($where)
            ->order('create_time desc,is_effect')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $areas_m = M('areas');
        $shops_m = M('shops');
        foreach ($list as $key => $value) {
            $areas = $areas_m->field('areaName')->find($value['city_id']);
            if ($areas['areaName'] == 0) {
                $value['city_id'] = "全国可用";
            } else {
                $value['city_id'] = $areas['areaName'];
            }
            $shops = $shops_m->field('shopName')->find($value['supplier_id']);
            $value['supplier_id'] = $shops['shopName'];
            $value['create_time'] = date('Y-m-d H:i', $value['create_time']);
            
            if ($value['admin_check_status'] == 2) {
                $value['msg'] = "该申请已被拒绝";
            }
            
            if ($value['admin_check_status'] == 1 && $value['biz_apply_status'] == 1) {
                $value['msg'] = "已通过新增申请";
            }
            if ($value['admin_check_status'] == 1 && $value['biz_apply_status'] == 2) {
                $value['msg'] = "已通过修改申请";
            }
            if ($value['admin_check_status'] == 1 && $value['biz_apply_status'] == 3) {
                $value['msg'] = "已通过删除申请";
            }
            
            if ($value['admin_check_status'] == 0 && $value['biz_apply_status'] == 1) {
                $value['msg'] = "<a class='btn btn-default glyphicon glyphicon-pencil' href=";
                $value['msg'] .= U('Admin/Youhui/release', array(
                    'id' => $value['id']
                ));
                $value['msg'] .= ">发布</a>&nbsp;&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $value['id'] . ")'>拒绝</buttona>";
            }
            
            if ($value['admin_check_status'] == 0 && $value['biz_apply_status'] == 3) {
                $value['msg'] = "<button type='button' class='btn btn-default glyphicon glyphicon-pencil' onclick='javascript:down_youhui(" . $value['id'] . ")'>删除</buttona>&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $value['id'] . ")'>拒绝</buttona>";
            }
            
            if ($value['admin_check_status'] == 0 && $value['biz_apply_status'] == 2) {
                $value['msg'] = "<a class='btn btn-default glyphicon glyphicon-pencil' href=";
                $value['msg'] .= U('Admin/Youhui/release', array(
                    'id' => $value['id'],
                    'xg' => '1'
                ));
                $value['msg'] .= ">修改</a>&nbsp;&nbsp;<button type='button' class='btn btn-default glyphicon glyphicon-trash' onclick='javascript:refuse(" . $value['id'] . ")'>拒绝</buttona>";
            }
            $data[] = $value;
        }
        
        $list_data = array(
            'list' => $data,
            'show' => $show
        );
        return $list_data;
    }
    
    // 处理显示用户领取到的优惠券列表
    public function getuserlist($youhui_id = '', $user_id = '')
    {
        $m = M('youhui_user_link');
        // 判断是否读取指定的用户优惠券
        $where = array();
        if ($youhui_id || $user_id) {
            $where = array(
                'oto_youhui_user_link.youhui_id=' . $youhui_id,
                'oto_youhui_user_link.user_id=' . $user_id
            );
        }
        // 分页
        $count = $m->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        // 需要查询的字段
        $field = array(
            'oto_youhui.name,oto_youhui.breaks_menoy,oto_youhui.youhui_type,oto_youhui.supplier_id,oto_youhui.breaks_menoy,oto_youhui.total_fee,oto_youhui_user_link.*,oto_users.loginName'
        );
        // 查询优惠表和用户表的内容
        $data['list'] = $m->join('oto_youhui ON oto_youhui_user_link.youhui_id = oto_youhui.id')
            ->join('oto_users ON oto_youhui_user_link.user_id = oto_users.userId')
            ->order('oto_youhui_user_link.get_time desc')
            ->field($field)
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 内容数组合并和处理
        for ($i = 0; $i < count($data['list']); $i ++) {
            $data['list'][$i]['supplier_id'] = M('shops')->where('shopId=' . $data['list'][$i]['supplier_id'])
                ->field('shopName')
                ->find();
            $data['list'][$i]['supplier_id'] = $data['list'][$i]['supplier_id']['shopName'];
        }
        $data['show'] = $show;
        return $data;
    }
}
?>
 