<?php
namespace Home\Controller;

/**
 * 优惠券控制器
 */
class YouhuiController extends BaseController
{

    public function shopindex()
    {
        $this->isShopLogin();
        D('Youhui')->overdue();
        $m = D('Youhui');
        $val = (int) I('val');
        $data = $m->youhui_list(10, $val);
        $status = session('status');
        switch ($status) {
            case '1':
                $this->assign('return', '1');
                session('status', null);
                break;
            case '2':
                $this->assign('return', '2');
                session('status', null);
                break;
            case '3':
                $this->assign('return', '3');
                session('status', null);
                break;
        }
        $this->assign("umark", "youhuilist");
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->display('shops/Youhui/list');
    }
    // 优惠券添加页面
    public function add()
    {
        $this->isShopLogin();
        // 获取商品分类信息
        $m = D('Home/ShopsCats');
        $USER = session('WST_USER');
        $shop_cats = $m->queryByList($USER['shopId'], 0);
        $this->assign('shop_cats', $shop_cats);
        // 获取地区分类
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        $this->assign('supplier_id', $USER['shopId']);
        $this->assign('formurl', U('Home/Youhui/upload'));
        $this->assign('biz_apply_status', '1');
        $this->assign("umark", "youhuilist");
        $this->display('shops/Youhui/add');
    }
    // 查找商户
    public function check_shop()
    {
        // 查找商户
        $m = D('Admin/Shops');
        $str = "<option>请选择</option>";
        $page = $m->queryByPage();
        foreach ($page['root'] as $key => $value) {
            $str .= "<option id='supplier_cat' value=";
            $str .= $value['shopId'] . ">";
            $str .= $value['shopName'];
            $str .= "</option>";
        }
        echo $str;
    }
    // 查找商户分类
    public function check_shop_cats_child()
    {
        $id = I('id');
        $shop_cats_child = M('shops_cats')->where('parentId=' . $id)->select();
        echo $str_o = "<option>请选择</option>";
        foreach ($shop_cats_child as $key => $value) {
            $str = "<option value='" . $value['catId'] . "'>" . $value['catName'] . "</option>";
            echo $str;
        }
    }
    
    // 查找商户子分类
    public function check_shopcat()
    {
        $shopid = I('shopid');
        $m = D('Home/ShopsCats');
        $data = $m->queryByList($shopid, 0);
        echo $str_o = "<option>请选择</option>";
        foreach ($data as $key => $value) {
            $str = "<option value='" . $value['catId'] . "'>" . $value['catName'] . "</option>";
            echo $str;
        }
    }

    /**
     * 列表查询[获取启用的区域信息]
     */
    public function queryShowByList()
    {
        $m = D('Youhui');
        $list = $m->queryShowByList(I('parentId'));
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }
    // 获取商城分类以及下级分类
    public function getdealcats()
    {
        $id = I('id');
        $deal_cate = M('goods_cats')->field('catId,catName')
            ->order('catId')
            ->where('parentId=' . $id)
            ->select();
        echo $str_o = "<option>请选择</option>";
        foreach ($deal_cate as $key => $value) {
            $str = "<option value='" . $value['catId'] . "'>" . $value['catName'] . "</option>";
            echo $str;
        }
    }
    // 处理添加优惠券内容
    public function upload()
    {
        $this->isShopLogin();
        $return_data = $_POST;
        $return_data['supplier_id'] = $_SESSION['oto_mall']['WST_USER']['shopId'];
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array(
            'jpg',
            'gif',
            'png',
            'jpeg'
        ); // 设置附件上传类型
        $upload->rootPath = './Upload/'; // 设置附件上传根目录
        $upload->savePath = 'Youhui/'; // 设置附件上传（子）目录
        $info = $upload->upload();
        $model = D('Youhui');
        $return_data = $model->check_error($return_data, $info);
        // 错误返回数据重新添加
        if ($return_data['error'] > 0) {
            $this->assign('return_data', $return_data);
            $m = D('Home/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            // 获取地区分类
            $m = D('Admin/Areas');
            $this->assign('areaList', $m->queryShowByList(0));
            $this->assign("umark", "youhuilist");
            $this->display('shops/Youhui/add');
        }
        unset($return_data['error']);
        $m = M('youhui_biz_submit');
        $insert = $m->data($return_data)->add();
        session('status', '1');
        $this->redirect('Home/Youhui/shopindex');
    }
    
    // 优惠券修改页
    public function updata()
    {
        $this->isShopLogin();
        if ($id = (int) I('id')) {
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
                'begin_time',
                'user_limit',
                'list_brief',
                'end_time',
                'is_effect',
                'icon',
                'breaks_menoy',
                'youhui_type',
                'youhui_scope,deal_cate_type,deal_cate_id'
            );
            $data = M('youhui_biz_submit')->where('youhui_id=' . $id)
                ->field($field)
                ->find();
            $m = D('Home/Youhui');
            $data = $m->runreleasedata($data);
            $this->assign('return_data', $data);
            $this->assign('formurl', U('Home/Youhui/updatarun'));
            $this->assign('whatpage', 'x');
            $this->assign('biz_apply_status', '2');
            $this->assign("umark", "youhuilist");
            $this->display('shops/Youhui/add');
        } else {
            session('status', '3');
            $this->redirect('Home/Youhui/shopindex');
        }
    }
    // 处理优惠券修改页内容
    public function updatarun()
    {
        $this->isShopLogin();
        // 处理修改
        $youhui_data = $_POST;
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array(
            'jpg',
            'gif',
            'png',
            'jpeg'
        ); // 设置附件上传类型
        $upload->rootPath = './Upload/'; // 设置附件上传根目录
        $upload->savePath = 'Youhui/'; // 设置附件上传（子）目录
        $info = $upload->upload();
        $model = D('Youhui');
        $return_data = $model->check_error($youhui_data, $info);
        if ($return_data['error'] > 0) {
            $this->assign('return_data', $return_data);
            $m = D('Admin/Areas');
            $this->assign('areaList', $m->queryShowByList(0));
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            $this->assign("umark", "youhuilist");
            $this->display('shops/Youhui/add');
        }
        $return_data['create_time'] = time();
        unset($return_data['error']);
        $save = M('youhui_biz_submit')->save($return_data);
        if ($save !== false) {
            session('status', '2');
            $this->redirect('Home/Youhui/shopindex');
        } else {
            session('status', '3');
            $this->redirect('Home/Youhui/shopindex');
        }
    }
    // 商户删除
    public function down_youhui()
    {
        $this->isShopLogin();
        $id = (int) I('id');
        $m = M('youhui_biz_submit');
        $data['id'] = $m->where('youhui_id=' . $id)
            ->field('id')
            ->find();
        $data['id'] = (int) $data['id']['id'];
        $data['biz_apply_status'] = 3;
        $data['admin_check_status'] = 0;
        $data['create_time'] = time();
        $save = $m->save($data);
        if (false !== $save || 0 !== $save) {
            $arr = array(
                'status' => 1
            );
            echo json_encode($arr);
        } else {
            $arr = array(
                'status' => 0
            );
            echo json_encode($arr);
        }
    }
    
    // 商户优惠券等待审核列表
    public function youhui_log()
    {
        $this->isShopLogin();
        $effect = (int) I('effect');
        $m = D('Youhui');
        $data = $m->loglist($effect, 15);
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign("umark", "youhuilist");
        $this->display('shops/Youhui/youhui_log');
    }
    // 商户已使用优惠券列表
    public function shoprecord()
    {
        $this->isShopLogin();
        $data = D('Home/Youhui')->shoprecord();
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign("umark", "youhuiused");
        $this->display('Shops/Youhui/record');
    }
    
    // 查询商品
    public function querygoods()
    {
        $goodsname = I('goodsName');
        $data = D('Youhui')->check_goods($goodsname);
        foreach ($data as $key => $value) {
            $str .= "<option id='supplier_cat' value=";
            $str .= $value['goodsId'] . ">";
            $str .= $value['goodsName'];
            $str .= "</option>";
        }
        echo $str;
    }
    
    // 查询品牌
    public function querybrand()
    {
        $brandname = I('brandName');
        $data = D('Youhui')->check_brand($brandname);
        foreach ($data as $key => $value) {
            $str .= "<option id='supplier_cat' value=";
            $str .= $value['brandId'] . ">";
            $str .= $value['brandName'];
            $str .= "</option>";
        }
        echo $str;
    }
    
    // 查询分类
    public function querycat()
    {
        $catname = I('catsName');
        $data = D('Youhui')->check_cat($catname);
        foreach ($data as $key => $value) {
            $str .= "<option id='supplier_cat' value=";
            $str .= $value['catId'] . ">";
            $str .= $value['catName'];
            $str .= "</option>";
        }
        echo $str;
    }
    
    // 处理添加优惠券表单商户分类判断
    public function runcheckshop()
    {
        $data = $_POST;
        $data = D('Youhui')->runcheckshop_m($data);
        echo json_encode($data);
    }
    
    // 处理添加优惠券表单商城分类判断
    public function runcheckdealcat()
    {
        $data = $_POST;
        $data = D('Youhui')->runcheckdealcat($data);
        echo json_encode($data);
    }
    
    // 查询商品
    public function querygoodssupplier()
    {
        $goodsname = I('goodsName');
        $shopId = I('shopId');
        if ($shopId == "") {
            $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        }
        $data = D('Youhui')->check_goodssupplier($goodsname, $shopId);
        foreach ($data as $key => $value) {
            $str .= "<option id='supplier_cat' value=";
            $str .= $value['goodsId'] . ">";
            $str .= $value['goodsName'];
            $str .= "</option>";
        }
        echo $str;
    }
    // 查询商户
    public function querycatsupplier()
    {
        $m = D('Home/ShopsCats');
        $shopId = I('shopId');
        if ($shopId == "") {
            $shopId = $_SESSION['oto_mall']['WST_USER']['shopId'];
        }
        $data = $m->queryByList($shopId, 0);
        echo $str_o = "<option>请选择</option>";
        foreach ($data as $key => $value) {
            $str = "<option value='" . $value['catId'] . "'>" . $value['catName'] . "</option>";
            echo $str;
        }
    }

    public function runcheckshop2()
    {
        $data = $_POST;
        $data = D('Youhui')->runcheckshop_m2($data);
        echo json_encode($data);
    }
    
    // 领取页
    public function getyouhui()
    {
        $model = D('Youhui');
        $sort['youhui_type'] = I('youhui_type');
        $sort['breaks'] = I('breaks');
        $model->overdue();
        $data = $model->getyouhuilist($sort);
        $this->assign('list', $data['list']);
        $this->assign('hot', $data['hot']);
        $this->assign('show', $data['show']);
        $this->display('Youhui/getyouhui');
    }
    
    // 领取优惠券处理
    public function getyouhuiid()
    {
        $id = (int) I('id');
        $userid = $_SESSION['oto_mall']['WST_USER']['userId'];
        // 未登录返回
        if (! $userid) {
            echo json_encode(array(
                'msg' => '请先登录再领取优惠券',
                'url' => U('/Home/Users/login')
            ));
            die();
        }
        // 判断是否已经领取过相同的优惠券
        $m = M('youhui_user_link');
        $where_user = array(
            'youhui_id=' . $id,
            'userid=' . $userid
        );
        $find = $m->where($where_user)->find($id);
        $youhui = M('youhui')->where()
            ->field('user_limit,supplier_id,total_num,user_count,is_effect')
            ->find($id);
        // 判断优惠券是否领完
        $sy = $youhui['total_num'] - $youhui['user_count'];
        if ($sy <= 0) {
            echo json_encode(array(
                'msg' => '该优惠券已经领完。'
            ));
            die();
        }
        if ($youhui['is_effect'] == 0) {
            echo json_encode(array(
                'msg' => '该优惠券已经过期。'
            ));
            die();
        }
        if ($find) {
            // 时间判断
            if ($find['get_time'] < strtotime('today')) {
                $find['todayget'] = 0;
            }
            // 上限判断
            if ($find['todayget'] >= $youhui['user_limit']) {
                echo json_encode(array(
                    'msg' => '您已到达该优惠券的领取上限，请明天再试',
                    'ceiling' => 1
                ));
                die();
            }
            // 通过判断更新数据
            $find['surplus'] = strval($find['surplus'] + 1);
            $find['get_time'] = strval(time());
            $find['todayget'] = strval($find['todayget'] + 1);
            $find['shop_id'] = $youhui['supplier_id'];
            $result = $m->save($find);
            if (false !== $result) {
                M('Youhui')->where('id=' . $id)->setInc('user_count');
                echo json_encode(array(
                    'msg' => '已成功领取优惠券！'
                ));
            } else {
                echo json_encode(array(
                    'msg' => '出错！请稍后再试'
                ));
            }
        } else {
            // 未领取过新增记录
            $data = array(
                'youhui_id' => $id,
                'user_id' => $userid,
                'surplus' => 1,
                'get_time' => time(),
                'shop_id' => $youhui['supplier_id'],
                'todayget' => 1,
                'u_is_effect' => 1
            );
            $m->data($data)->add();
            M('Youhui')->where('id=' . $id)->setInc('user_count');
            echo json_encode(array(
                'msg' => '已成功领取优惠券！'
            ));
        }
    }
    // 用户查看自己优惠券页面
    public function userlist()
    {
        $this->isUserLogin();
        $type = (int) I('type');
        // 检查是否无效
        D('Youhui')->checkeffective();
        $data = D('Youhui')->getuserlist($type);
        $this->assign('umark', 'userlist');
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->assign('ture', $data['ture']);
        $this->display('Users/youhui/userlist');
    }
    
    // 用户删除优惠券
    public function del_user_youhui()
    {
        $this->isUserLogin();
        $youhui_id = I('youhui_id');
        $user_id = I('user_id');
        $rslt = M('youhui_user_link')->where('youhui_id=' . $youhui_id, 'user_id=' . $user_id)->delete();
        if ($rslt !== false) {
            $arr = array(
                'status' => 1
            );
            echo json_encode($arr);
        } else {
            $arr = array(
                'status' => 0
            );
            echo json_encode($arr);
        }
    }
    // 用户删除已使用优惠券
    public function del_use_record()
    {
        $this->isUserLogin();
        $id = I('id');
        $rslt = M('youhui_use_record')->where('id=' . $id)->delete();
        if ($rslt !== false) {
            $arr = array(
                'status' => 1
            );
            echo json_encode($arr);
        } else {
            $arr = array(
                'status' => 0
            );
            echo json_encode($arr);
        }
    }
    
    // 检测新建和修改时候优惠券名称是否重复
    public function checkedname()
    {
        $name = I('name');
        $sql = "select name from oto_youhui_biz_submit where name='$name' and admin_check_status<>2";
        $youhui = M()->query($sql);
        if ($youhui[0]['name']) {
            $this->ajaxReturn(array(
                'msg' => '该优惠券名称已经被使用',
                'status' => 1
            ));
        } else {
            $this->ajaxReturn(array(
                'msg' => '可以使用该名称',
                'status' => 2
            ));
        }
    }
    
    // 检测提交时间
    public function checkedtime()
    {
        $begin_time = strtotime(I('begin_time'));
        $end_time = strtotime(I('end_time'));
        $newtime = time();
        if ($begin_time) {
            if ($begin_time <= $newtime) {
                $bdata['msg'] = '开始时间小于或等于现在时间';
                $bdata['status'] = 1;
                $bdata['timetype'] = 1;
            } else {
                $bdata['status'] = 2;
            }
            
            if ($end_time) {
                if ($begin_time >= $end_time) {
                    $bdata['msg'] = '开始时间大于或等于结束时间';
                    $bdata['status'] = 1;
                    $bdata['timetype'] = 1;
                } else {
                    $bdata['status'] = 2;
                }
            }
        }
        
        if ($end_time) {
            if ($end_time <= $newtime) {
                $edata['msg'] = '结束时间小于或等于现在时间';
                $edata['status'] = 1;
                $edata['timetype'] = 2;
            } else {
                $edata['status'] = 2;
            }
            if ($end_time && $begin_time) {
                if ($end_time <= $begin_time) {
                    $edata['msg'] = '结束时间小于或等于开始时间';
                    $edata['status'] = 1;
                    $edata['timetype'] = 2;
                } else {
                    $edata['status'] = 2;
                }
            }
            $this->ajaxReturn($edata);
        }
        $this->ajaxReturn($bdata);
    }
}

?>