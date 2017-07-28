<?php
namespace Home\Model;

/**
 * 优惠券模型
 */
class YouhuiModel extends BaseModel
{

    public function youhui_list($limit = 15, $val = 1)
    {
        switch ($val) {
            case '2':
                $where['is_effect'] = 1;
                break;
            case '3':
                $where['is_effect'] = 2;
                break;
            case '4':
                $where['is_effect'] = 0;
                break;
        }
        $m = M('youhui');
        $field = array(
            'id',
            'name',
            'icon',
            'begin_time',
            'end_time',
            'total_fee',
            'breaks_menoy',
            'is_effect',
            'create_time',
            'youhui_type'
        );
        $where['supplier_id'] = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $list = $m->where($where)
            ->field($field)
            ->order('id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach ($list as $key => $value) {
            $list[$key]['begin_time'] = date('Y/m/d H:i', $list[$key]['begin_time']);
            $list[$key]['end_time'] = date('Y/m/d H:i', $list[$key]['end_time']);
        }
        $list_return = array(
            'list' => $list,
            'show' => $show
        );
        return $list_return;
    }

    /**
     * 获取列表[获取启用的区域信息]
     */
    public function queryShowByList($parentId)
    {
        $m = M('areas');
        return $m->where('areaFlag=1 and isShow = 1 and parentId=' . (int) $parentId)->select();
    }

    public function check_error($data, $info)
    {
        $endtime = strtotime($data['end_time']);
        $newtime = time();
        $i = 0;
        // if ($data['areaId2']) {$data['city_id']=$data['areaId2'];}else{$data['city_id']=$data['areaId1'];}
        unset($data['areaId2']);
        unset($data['areaId1']);
        if (empty($data['icon']) && empty($info[fileToUpload1])) {
            $data['img'] = '列表图没有上传';
            $i ++;
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
        }
        
        unset($data['check_shop_name']);
        unset($data['articleContent']);
        
        unset($data['img3']);
        unset($data['cats_id']);
        return $data;
    }
    
    // 商户优惠券审核记录
    public function loglist($effect, $limit = 10)
    {
        $field = array(
            'id',
            'name',
            'admin_check_status',
            'biz_apply_status',
            'create_time'
        );
        $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $m = M('youhui_biz_submit');
        $where = array(
            'supplier_id=' . $shopId
        );
        if ($effect != '') {
            $where['admin_check_status'] = 0;
        }
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $show = $Page->show();
        $list = $m->where($where)
            ->field($field)
            ->order('create_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $list_data = array(
            'list' => $list,
            'show' => $show
        );
        return $list_data;
    }
    
    // 修改页内容输出前处理
    public function runreleasedata($data, $check)
    {
        // 时间戳转为时间
        $data['begin_time'] = date('Y/m/d H:i', $data['begin_time']);
        $data['end_time'] = date('Y/m/d H:i', $data['end_time']);
        // 城市id转城市名称
        $areas = M('areas')->field('areaName,parentId')->find($data['city_id']);
        if ($areas['parentId'] == 0) {
            if ($data['city_id'] == 0) {
                $data['areaName'] = "全国可用";
            } else {
                $data['areaName'] = $areas['areaName'];
            }
            $data['areaid'] = $data['city_id'];
        } else {
            $areas['parent'] = M('areas')->field('areaName')->find($areas['parentId']);
            $data['areaName'] = $areas['areaName'];
            $data['areaid'] = $data['city_id'];
            $data['areaparent'] = $areas['parent'];
            $data['areaparent']['areaid'] = $areas['parentId'];
        }
        if ($check == 'release') {
            $m = M('youhui_biz_submit');
        } else {
            $m = M('youhui');
        }
        // 优惠券类别输出
        switch ($data['youhui_scope']) {
            // 商户分类输出
            case '2':
                $data['shop_cat'] = $m->field('shop_cat_type,shop_cat_id')->find($data['id']);
                $data['shop_cat_type'] = $data['shop_cat']['shop_cat_type'];
                $data['shop_cat_id'] = $data['shop_cat']['shop_cat_id'];
                switch ($data['shop_cat_type']) {
                    case '1':
                        $data['scopeval'] = M('shops')->field('shopId,shopName')->find($data['shop_cat_id']);
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval1' checked value='" . $data['scopeval']['shopId'] . "'>商户优惠券：" . $data['scopeval']['shopName'] . "</label><input  type='hidden' name='supplier_id' value='" . $data['supplier_id'] . "'>";
                        break;
                    case '2':
                        $data['scopeval'] = M('shops_cats')->field('catId,catName')->find($data['shop_cat_id']);
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval2' checked value='" . $data['scopeval']['catId'] . "'>一级分类优惠券：" . $data['scopeval']['catName'] . "</label><input  type='hidden' name='supplier_id' value='" . $data['supplier_id'] . "'>";
                        break;
                    case '3':
                        $data['scopeval'] = M('shops_cats')->field('catId,catName')->find($data['shop_cat_id']);
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval3' checked value='" . $data['scopeval']['catId'] . "'>二级分类优惠券：" . $data['scopeval']['catName'] . "</label><input  type='hidden' name='supplier_id' value='" . $data['supplier_id'] . "'>";
                        break;
                }
                unset($data['shop_cat']);
                break;
            // 商品分类输出
            case '3':
                $data['good_id'] = $m->field('good_id')->find($data['id']);
                $data['good_id'] = explode(',', $data['good_id']['good_id']);
                for ($i = 0; $i < count($data['good_id']); $i ++) {
                    $data['scope'][] = M('goods')->field('goodsId,goodsName')->find($data['good_id'][$i]);
                    $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval[]' checked value='" . $data['scope'][$i]['goodsId'] . "'>" . $data['scope'][$i]['goodsName'] . "</label>";
                }
                unset($data['good_id']);
                unset($data['scope']);
                break;
            // 品牌分类
            case '4':
                $data['brand_id'] = $m->field('brand_id')->find($data['id']);
                $data['brand_id'] = explode(',', $data['brand_id']['brand_id']);
                for ($i = 0; $i < count($data['brand_id']); $i ++) {
                    $data['scope'][] = M('brands')->field('brandId,brandName')->find($data['brand_id'][$i]);
                    $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval[]' checked value='" . $data['scope'][$i]['brandId'] . "'>" . $data['scope'][$i]['brandName'] . "</label>";
                }
                unset($data['scope']);
                unset($data['brand_id']);
                break;
            // 商城分类
            case '5':
                $data['scopeval'] = M('goods_cats')->field('catId,catName')->find($data['deal_cate_id']);
                switch ($data['deal_cate_type']) {
                    case '1':
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='deal_cate_id1' checked value='" . $data['scopeval']['catId'] . "'>商城一级分类优惠券：" . $data['scopeval']['catName'] . "</label><input  type='hidden' name='deal_cate_type' value='" . $data['scopeval']['catId'] . "'>";
                        break;
                    
                    case '2':
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='deal_cate_id2' checked value='" . $data['scopeval']['catId'] . "'>商城二级分类优惠券：" . $data['scopeval']['catName'] . "</label><input  type='hidden' name='deal_cate_type' value='" . $data['scopeval']['catId'] . "'>";
                        break;
                    
                    case '3':
                        $data['scopeval'][]['check'] = "<label style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='deal_cate_id3' checked value='" . $data['scopeval']['catId'] . "'>商城三级分类优惠券：" . $data['scopeval']['catName'] . "</label><input  type='hidden' name='deal_cate_type' value='" . $data['scopeval']['catId'] . "'>";
                        break;
                }
                unset($data['scope']);
                unset($data['deal_cate_id']);
                break;
        }
        
        return $data;
    }
    // 商户已使用页面
    public function shoprecord()
    {
        $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $count = M('youhui_user_link')->where('shopId=' . $shopId)->count();
        $Page = new \Think\Page($count, 9);
        $data['show'] = $Page->show();
        $list = M('youhui_use_record')->where('shopId=' . $shopId)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('useTime desc')
            ->select();
        foreach ($list as $key => $vo) {
            $username = M('users')->field('loginName')->find($vo['userId']);
            $list[$key]['username'] = $username['loginName'];
            $youhuiname = M('youhui')->field('name')->find($vo['youhui_id']);
            $list[$key]['youhuiname'] = $youhuiname['name'];
        }
        $data['list'] = $list;
        return $data;
    }

    public function check_goods($search)
    {
        $where['goodsName'] = array(
            'like',
            '%' . $search . '%'
        );
        $data = M('goods')->field('goodsId,goodsName')
            ->where($where)
            ->select();
        return $data;
    }

    public function check_brand($search)
    {
        $where['brandName'] = array(
            'like',
            '%' . $search . '%'
        );
        $data = M('brands')->field('brandId,brandName')
            ->where($where)
            ->select();
        return $data;
    }

    public function check_cat($search)
    {
        $where['catName'] = array(
            'like',
            '%' . $search . '%'
        );
        $data = M('goods_cats')->field('catId,catName')
            ->where($where)
            ->select();
        return $data;
    }

    public function runcheckshop_m($data)
    {
        if ($data['supplier_id'] == '' || $data['supplier_id'] == '请选择') {
            $return['status'] = 0;
            $return['msg'] = "搜索商户并选择商户";
            return $return;
        } else {
            if ($data['cate_child_id'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 3;
                $return['msg'] = "<label  style='margin-lift:5px;'><input id='input_cate_child' type='checkbox' name='scopeval3' checked value='" . $data['cate_child_id'] . "'>二级分类优惠券：" . $data['cate_child_text'] . "</label>";
                return $return;
            }
            if ($data['cate_id'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 2;
                $return['msg'] = "<label  style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval2' checked value='" . $data['cate_id'] . "'>一级分类优惠券：" . $data['cate_text'] . "</label>";
                return $return;
            }
            
            $return['status'] = 1;
            $return['level'] = 1;
            $return['msg'] = "<label style='margin-lift:5px;'><input id='input_shop' type='checkbox' name='scopeval1' checked value='" . $data['supplier_id'] . "'>商户优惠券：" . $data['supplier_text'] . "</label>";
            return $return;
        }
    }
    
    // 查询商品所属商户id
    public function check_goodssupplier($search, $shopId)
    {
        $where['goodsName'] = array(
            'like',
            '%' . $search . '%'
        );
        $where['shopId'] = $shopId;
        $data = M('goods')->field('goodsId,goodsName')
            ->where($where)
            ->select();
        return $data;
    }
    // 处理添加优惠券表单商户分类判断
    public function runcheckshop_m2($data)
    {
        if ($data['cate_id'] == '请选择') {
            $return['status'] = 0;
            $return['msg'] = "请选择分类";
            return $return;
        } else {
            if ($data['cate_child_id'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 3;
                $return['msg'] = "<label id='twoinput' style='margin-lift:5px;'><input id='input_cate_child' type='checkbox' name='scopeval3' checked value='" . $data['cate_child_id'] . "'>二级分类优惠券：" . $data['cate_child_text'] . "</label>";
                return $return;
            }
            if ($data['cate_id'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 2;
                $return['msg'] = "<label id='oneinput' style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='scopeval2' checked value='" . $data['cate_id'] . "'>一级分类优惠券：" . $data['cate_text'] . "</label>";
                return $return;
            }
            
            $return['status'] = 1;
            $return['level'] = 1;
            $return['msg'] = "<label style='margin-lift:5px;'><input id='input_shop' type='checkbox' name='scopeval1' checked value='" . $data['supplier_id'] . "'>商户优惠券：" . $data['supplier_text'] . "</label>";
            return $return;
        }
    }
    
    // 处理添加优惠券表单商城分类判断
    public function runcheckdealcat($data)
    {
        if ($data['deal_cateid'] == '请选择') {
            $return['status'] = 0;
            $return['msg'] = "请选择分类";
            return $return;
        } else {
            if ($data['deal_cateid3'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 3;
                $return['msg'] = "<label id='twoinput' style='margin-lift:5px;'><input id='input_cate_child' type='checkbox' name='deal_cate_id3' checked value='" . $data['deal_cateid3'] . "'>三级分类优惠券：" . $data['deal_catetext3'] . "</label>";
                return $return;
            }
            if ($data['deal_cateid2'] != '请选择') {
                $return['status'] = 1;
                $return['level'] = 2;
                $return['msg'] = "<label id='oneinput' style='margin-lift:5px;'><input id='input_cate' type='checkbox' name='deal_cate_id2' checked value='" . $data['deal_cateid2'] . "'>二级分类优惠券：" . $data['deal_catetext2'] . "</label>";
                return $return;
            }
            
            $return['status'] = 1;
            $return['level'] = 1;
            $return['msg'] = "<label style='margin-lift:5px;'><input id='input_shop' type='checkbox' name='deal_cate_id1' checked value='" . $data['deal_cateid'] . "'>一级分类优惠券：" . $data['deal_catetext'] . "</label>";
            return $return;
        }
    }
    
    // 用户部分
    // 获取优惠列表页输出内容
    public function getyouhuilist($sort = '')
    {
        // 获取优惠列表字段
        $field = array(
            'id',
            'name',
            'icon',
            'list_brief',
            'total_num',
            'end_time',
            'user_count',
            'youhui_type',
            'is_effect',
            'breaks_menoy',
            'total_fee',
            'supplier_id',
            'youhui_scope',
            'good_id',
            'shop_cat_id',
            'deal_cate_id',
            'brand_id',
            'city_id',
            'create_time'
        );
        $m = M('youhui');
        // 判断前端返回的排序字段
        $order = 'create_time desc';
        if ($sort['breaks']) {
            $order = 'user_count desc';
        }
        if ($sort['youhui_type'] == 1) {
            $where[] = 'youhui_type=0';
        }
        if ($sort['youhui_type'] == 2) {
            $where[] = 'youhui_type=1';
        }
        // 分页
        $where['is_effect'] = 1;
        $newtime = time();
        $where['begin_time'] = array(
            'LT',
            $newtime
        );
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 9);
        $data['show'] = $Page->show();
        $data['list'] = $m->field($field)
            ->order($order)
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 热门列表
        $data['hot'] = $m->field($field)
            ->order('user_count desc')
            ->where('is_effect=1')
            ->limit(10)
            ->select();
        // 处理输出数据
        for ($i = 0; $i < count($data['list']); $i ++) {
            // 剩余券数
            $data['list'][$i]['sy'] = $data['list'][$i]['total_num'] - $data['list'][$i]['user_count'];
            $data['list'][$i]['shopname'] = M('shops')->field('shopName')->find($data['list'][$i]['supplier_id']);
            // 获取城市
            if ($data['list'][$i]['city_id'] == 0) {
                $data['list'][$i]['areaName'] = '全国可用';
            } else {
                $data['list'][$i]['areaName'] = M('areas')->field('areaName')
                    ->where('areaId=' . $data['list'][$i]['city_id'])
                    ->find();
                $data['list'][$i]['areaName'] = $data['list'][$i]['areaName']['areaName'];
            }
            
            // 获取弹出各种类型的优惠券输出内容
            switch ($data['list'][$i]['youhui_scope']) {
                case '2':
                    $data['list'][$i]['youhui_cat'] = M('shops_cats')->field('catName')->find($data['list'][$i]['shop_cat_id']);
                    break;
                case '3':
                    $where = array(
                        'goodsId' => array(
                            'in',
                            explode(',', $data['list'][$i]['good_id'])
                        )
                    );
                    $data['list'][$i]['youhui_cat'] = M('goods')->where($where)
                        ->field('goodsId,goodsName')
                        ->select();
                    break;
                case '4':
                    $where = array(
                        'brandId' => array(
                            'in',
                            explode(',', $data['list'][$i]['brand_id'])
                        )
                    );
                    $data['list'][$i]['youhui_cat'] = M('brands')->where($where)
                        ->field('brandName')
                        ->select();
                    break;
                case '5':
                    $data['list'][$i]['youhui_cat'] = M('goods_cats')->field('catName')->find($data['list'][$i]['deal_cate_id']);
                    break;
            }
        }
        return $data;
    }
    // 判断用户优惠券是否过期或无效或是否领完
    public function checkeffective()
    {
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        $m = M('youhui_user_link');
        $data = $m->join('oto_youhui ON oto_youhui_user_link.youhui_id = oto_youhui.id')
            ->field('oto_youhui.id,oto_youhui.end_time,oto_youhui.is_effect,oto_youhui.total_num,oto_youhui.user_count')
            ->where('oto_youhui_user_link.user_id=' . $userid, 'oto_youhui_user_link.u_is_effect=1')
            ->select();
        $newtime = time();
        $count = count($data);
        for ($i = 0; $i < $count; $i ++) {
            // 判断是否被领完
            $sy = $data[$i]['total_num'] - $data[$i]['user_count'];
            if ($sy == 0) {
                M('youhui')->execute("update __PREFIX__youhui set is_effect='2' where id=" . $data[$i]['id']);
            }
            // 判断是否过期
            if ($newtime > $data[$i]['end_time']) {
                $data[$i]['is_effect'] = 0;
                $m->execute("update __PREFIX__youhui_user_link set u_is_effect='0' where youhui_id=" . $data[$i]['id']);
                M('youhui')->execute("update __PREFIX__youhui set is_effect='0' where id=" . $data[$i]['id']);
            }
            // 判断是否有效
            if ($data[$i]['is_effect'] == 0) {
                M('youhui_user_link')->execute("update __PREFIX__youhui_user_link set u_is_effect='0' where youhui_id=" . $data[$i]['id']);
            }
        }
    }
    
    // 判断优惠券是否用完
    public function checkeuseover()
    {
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        $m = M('youhui_user_link');
        $data = $m->where('user_id=' . $userid)
            ->where('u_is_effect=1')
            ->select();
        foreach ($data as $key => $vo) {
            if ($vo['surplus'] == '0') {
                $m->execute("update __PREFIX__youhui_user_link set u_is_effect='0' where youhui_id=" . $data['youhui_id'] . "user_id=" . $userid);
            }
        }
    }
    
    // 只检查页面优惠券是否过期或领完
    public function overdue()
    {
        $where['is_effect'] = array(
            'in',
            '1,2'
        );
        $data = M('youhui')->field('id,end_time,total_num,user_count')
            ->where($where)
            ->select();
        $newtime = time();
        $count = count($data);
        for ($i = 0; $i < $count; $i ++) {
            // 判断是否领完
            $sy = $data[$i]['total_num'] - $data[$i]['user_count'];
            if ($sy == 0) {
                M('youhui')->execute("update __PREFIX__youhui set is_effect='2' where id=" . $data[$i]['id']);
            }
            // 判断是否过期
            if ($newtime > $data[$i]['end_time']) {
                M('youhui_user_link')->execute("update __PREFIX__youhui_user_link set u_is_effect='0' where youhui_id=" . $data[$i]['id']);
                M('youhui')->execute("update __PREFIX__youhui set is_effect='0' where id=" . $data[$i]['id']);
            }
        }
    }
    // 获取用户优惠券页面内容
    public function getuserlist($type = 1)
    {
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        if ($type == 2) {
            $m = M('youhui_use_record');
            // 所需字段
            $field = array(
                'oto_youhui.name,oto_youhui_use_record.*'
            );
            // 分页
            $where['userId'] = $userid;
            $count = $m->where($where)->count();
            $Page = new \Think\Page($count, 20);
            $data['show'] = $Page->show();
            $record = $m->join('oto_youhui ON oto_youhui_use_record.youhui_id = oto_youhui.id')
                ->field($field)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('oto_youhui_use_record.useTime desc')
                ->where($where)
                ->select();
            foreach ($record as $key => $vo) {
                $shopName = M('shops')->field('shopName')->find($vo['shopId']);
                $record[$key]['shopName'] = $shopName['shopName'];
            }
            $data['ture'] = 'ture';
            $data['list'] = $record;
            return $data;
            die();
        }
        // ture则已过期内容
        if ($type == 3) {
            $where['oto_youhui_user_link.u_is_effect'] = '0';
        }
        if ($type == 1) {
            $where['oto_youhui_user_link.u_is_effect'] = '1';
        }
        $m = M('youhui_user_link');
        $where['oto_youhui_user_link.user_id'] = $userid;
        $where['oto_youhui_user_link.surplus'] = array(
            'GT',
            0
        );
        // 分页
        $count = $m->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $data['show'] = $Page->show();
        // 所需字段
        $field = array(
            'oto_youhui.name,oto_youhui.breaks_menoy,oto_youhui.youhui_type,oto_youhui.supplier_id,oto_youhui.breaks_menoy,oto_youhui.total_fee,oto_youhui_user_link.*,oto_youhui.end_time,oto_youhui.begin_time,oto_youhui.youhui_scope,oto_youhui_user_link.u_is_effect,oto_youhui.is_effect'
        );
        
        $data['list'] = $m->join('oto_youhui ON oto_youhui_user_link.youhui_id = oto_youhui.id')
            ->join('oto_users ON oto_youhui_user_link.user_id = oto_users.userId')
            ->field($field)
            ->where($where)
            ->order('create_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        // 内容数组合并和处理
        for ($i = 0; $i < count($data['list']); $i ++) {
            $data['list'][$i]['supplier_name'] = M('shops')->where('shopId=' . $data['list'][$i]['supplier_id'])
                ->field('shopName')
                ->find();
            $data['list'][$i]['supplier_name'] = $data['list'][$i]['supplier_name']['shopName'];
            $data['list'][$i]['id'] = $data['list'][$i]['youhui_id'];
        }
        $data['user_id'] = $userid;
        return $data;
    }
}
?>
