<?php
namespace Admin\Controller;

/**
 * 积分商城控制器
 */
class IntegralGoodsController extends BaseController
{

    /**
     * 分页查询积分商品类型
     */
    public function cats()
    {
        $m = D('IntegralGoods');
        $list = $m->getCatAndChild();
        $this->assign('List', $list);
        $this->display("/integralgoods/cats/cats_list");
    }

    /**
     * 跳到新增/编辑积分商品类型页面
     */
    public function catsToEdit()
    {
        $this->isLogin();
        $m = D('IntegralGoods');
        $object = array();
        if (I('id', 0) > 0) {
            $object = $m->catsGet(I('id', 0));
        } else {
            // getModel获取表中字段默认值
            $object = $m->getModel('__PREFIX__integral_cats');
        }
        $this->assign('object', $object);
        $this->view->display('IntegralGoods/cats/cats_edit');
    }

    /**
     * 新增/修改积分商品类型操作
     */
    public function catsEdit()
    {
        $this->isAjaxLogin();
        $m = D('IntegralGoods');
        $rs = array();
        if (I('id', 0) > 0) {
            $rs = $m->catsEdit();
        } else {
            $rs = $m->catsInsert();
        }
        $this->ajaxReturn($rs);
    }

    /**
     * 修改商品类型名称
     */
    public function editName()
    {
        $this->isAjaxLogin();
        $m = D('IntegralGoods');
        $rs = array(
            'status' => - 1
        );
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('spfl_02');
            $rs = $m->editName();
        }
        $this->ajaxReturn($rs);
    }

    /**
     * 显示商品类型是否显示/隐藏
     */
    public function editiIsShow()
    {
        $this->isAjaxLogin();
        $m = D('IntegralGoods');
        $rs = $m->editiIsShow();
        $this->ajaxReturn($rs);
    }

    /**
     * 删除商品类型操作
     */
    public function catsDel()
    {
        $this->isAjaxLogin();
        $m = D('IntegralGoods');
        $rs = $m->catsDel();
        $this->ajaxReturn($rs);
    }

    /**
     * 跳转到添加/编辑商品页面
     */
    public function toEdit()
    {
        $this->isLogin();
        $id = I('id');
        $m = D('IntegralGoods');
        if ($id > 0) {
            $goods = $m->get($id);
        } else {
            $goods = $m->getModel('__PREFIX__integral');
        }
        $goods['cats'] = $m->getCatAndChild();
        $this->assign('goods', $goods);
        $this->display('IntegralGoods/edit');
    }

    /**
     * 增加积分商品前上传图片
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
            $image->thumb(180, 140, \Think\Image::IMAGE_THUMB_FILLED)->save($path1_thumb);
            $_SESSION['path1_thumb'] = substr($path1_thumb, 1);
            $res = "缩略图上传成功";
        } elseif (move_uploaded_file($_FILES['fileToUpload2']['tmp_name'], $path2)) {
            $_SESSION['path2'] = substr($path2, 1);
            // 生成缩略图
            $path2_thumb = $path . 'thumb' . $name2;
            $image = new \Think\Image();
            $image->open($path2);
            $image->thumb(180, 140, \Think\Image::IMAGE_THUMB_FILLED)->save($path2_thumb);
            $_SESSION['path2_thumb'] = substr($path2_thumb, 1);
            $res = "图片2上传成功";
        } elseif (move_uploaded_file($_FILES['fileToUpload3']['tmp_name'], $path3)) {
            $_SESSION['path3'] = substr($path3, 1);
            // 生成缩略图
            $path3_thumb = $path . 'thumb' . $name3;
            $image = new \Think\Image();
            $image->open($path3);
            $image->thumb(180, 140, \Think\Image::IMAGE_THUMB_FILLED)->save($path3_thumb);
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
        $m = D('IntegralGoods');
        $obj['goodsImg'] = $_SESSION['path1'];
        $obj['goodsThums'] = $_SESSION['path1_thumb'];
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
        $m = D('IntegralGoods');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类
                                                                    // 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        // 获取积分商品分类
        $integralCatModel = M('IntegralCats');
        $integralCat = $integralCatModel->where('catFlag = 1')->select();
        $this->assign('integralCat', $integralCat);
        // 查询条件
        $this->assign('goodsName', I('goodsName'));
        $this->assign('goodsCat', I('goodsCat'));
        $this->display('IntegralGoods/list');
    }

    /**
     * 删除单个积分商品
     */
    public function del()
    {
        $this->isLogin();
        $id = intval(I('id'));
        $m = M('Integral');
        $data['status'] = $m->where('goodsId=' . $id)->setField('goodsFlag', 0);
        $this->ajaxReturn($data);
    }

    /**
     * 上架、下架积分商品
     */
    public function editiIsSale()
    {
        $this->isAjaxLogin();
        $m = D('IntegralGoods');
        $rs = $m->editiIsSale();
        $this->ajaxReturn($rs);
    }
}
;
?>