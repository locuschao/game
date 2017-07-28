<?php
namespace Admin\Controller;

/**
 * 积分商城控制器
 */
class IntegralController extends BaseController
{

    /**
     * 跳转到添加/编辑商品页面
     */
    public function toedit()
    {
        $this->isLogin();
        $id = I('id');
        if ($id > 0) {
            $m = D('Integral');
            $goods = $m->get($id);
            $this->assign('goods', $goods);
        }
        $this->display('integral/edit');
    }

    /**
     * 增加晒单分享前上传图片
     */
    public function uploadImg()
    {
        // 上传路径和图片命名
        $time = date('Y-m');
        $path = "./Upload/goods/$time/";
        if (! is_dir($path)) {
            mkdir(iconv('UTF-8', 'GBK', $path), 0777, true);
        }
        $name1 = date('YmdHsi') . rand() . '.jpg';
        $path1 = $path . $name1;
        $name2 = date('YmdHsi') . rand() . '.jpg';
        $path2 = $path . $name2;
        $name3 = date('YmdHsi') . rand() . '.jpg';
        $path3 = $path . $name3;
        $res = 0;
        if (move_uploaded_file($_FILES['fileToUpload1']['tmp_name'], $path1)) {
            $_SESSION['path1'] = substr($path1, 1);
            // 生成缩略图
            $path1_thumb = $path . 'thumb' . $name1;
            $image = new \Think\Image();
            $image->open($path1);
            $image->thumb(150, 150, \Think\Image::IMAGE_THUMB_FILLED)->save($path1_thumb);
            $_SESSION['path1_thumb'] = substr($path1_thumb, 1);
            $res = "缩略图上传成功";
        } elseif (move_uploaded_file($_FILES['fileToUpload2']['tmp_name'], $path2)) {
            $_SESSION['path2'] = substr($path2, 1);
            // 生成缩略图
            $path2_thumb = $path . 'thumb' . $name2;
            $image = new \Think\Image();
            $image->open($path2);
            $image->thumb(150, 150, \Think\Image::IMAGE_THUMB_FILLED)->save($path2_thumb);
            $_SESSION['path2_thumb'] = substr($path2_thumb, 1);
            $res = "图片2上传成功";
        } elseif (move_uploaded_file($_FILES['fileToUpload3']['tmp_name'], $path3)) {
            $_SESSION['path3'] = substr($path3, 1);
            // 生成缩略图
            $path3_thumb = $path . 'thumb' . $name3;
            $image = new \Think\Image();
            $image->open($path3);
            $image->thumb(150, 150, \Think\Image::IMAGE_THUMB_FILLED)->save($path3_thumb);
            $_SESSION['path3_thumb'] = substr($path3_thumb, 1);
            $res = "图片3上传成功";
        }
        echo json_encode($res);
    }

    /**
     * 添加/修改积分商品操作
     */
    public function edit()
    {
        $this->isLogin();
        $id = intval(I('id'));
        $m = D('Integral');
        $obj['goodsImg'] = $_SESSION['path1'];
        $obj['goodsThumbs'] = $_SESSION['path1_thumb'];
        $obj['path2'] = $_SESSION['path2'];
        $obj['path2_thumb'] = $_SESSION['path2_thumb'];
        $obj['path3'] = $_SESSION['path3'];
        $obj['path3_thumb'] = $_SESSION['path3_thumb'];
        if ($id > 0) {
            $rs = $m->edit($obj, $id);
            $data['status'] = $rs;
        } else {
            if ($obj['goodsImg'] == false) {
                $data['status'] = 0;
            } else {
                $rs = $m->insert($obj);
                $data['status'] = $rs;
            }
        }
        // 清除上一次新增商品图片时的session
        unset($_SESSION['path1']);
        unset($_SESSION['path1_thumb']);
        unset($_SESSION['path2']);
        unset($_SESSION['path2_thumb']);
        unset($_SESSION['path3']);
        unset($_SESSION['path3_thumb']);
        $this->ajaxReturn($data);
    }

    /**
     * 分页显示积分商品列表
     */
    public function index()
    {
        $this->isLogin();
        $m = D('Integral');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类
                                                                    // 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display('integral/list');
    }

    /**
     * 删除单个积分商品
     */
    public function del()
    {
        $this->isLogin();
        $id = intval(I('id'));
        $m = M('IntegralGoods');
        $data['status'] = $m->where('goodsId=' . $id)->setField('goodsFlag', 0);
        $this->ajaxReturn($data);
    }

    /**
     * 分页显示订单列表
     */
    public function ordersList()
    {
        $this->isLogin();
        $m = D('Integral');
        // $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类
                                                                    // 传入总记录数和每页显示的记录数
                                                                    // $page['pager'] = $pager->show();
                                                                    // $this->assign('Page',$page);
        $this->display('integral/orders_list');
    }
}
;
?>