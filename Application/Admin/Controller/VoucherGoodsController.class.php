<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 商品管理
 */
class VoucherGoodsController extends BaseController
{
    /**
     * @author peng
     * @date 2017-01-12
     * @descreption 商品列表
     */
    public function goodsList(){
        $where = '';
        if(IS_POST)
        {
            $name = I('post.name');
            $where = "name = '$name'";
        }
        $count = M('GoodsVoucher')->count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $arr = M('GoodsVoucher as g')->where($where)->join('oto_staffs as s on s.staffId =g.create_userid ')->field('g.id,g.name,g.price,g.add_time,g.isSale,g.rank,s.loginName,g.pictureUrl')->limit($page->firstRow.','.$page->listRows)->order('g.isSale desc,g.add_time desc')->select();
        foreach ($arr as $key =>$value)
        {
            $arr[$key]['voucher'] = M('VoucherGoodsRelation as go')->join('oto_voucher as v on go.voucher_id = v.id where go.goods_id='.$value['id'])->field('v.name')->select();
        }
        $this->assign('goodsArr',$arr);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * @author peng
     * @date 2017-01-12
     * @descreption 添加列表
     */

    public function addGoods(){
        $voucherModel = M('voucher');
        $pageSize = 10;
        $count      = $voucherModel->count();// 查询满足要求的总记录数
        $size = ceil($count / $pageSize);
        $start = 0;
        $list = array();
        for($i=1;$i<=$size;$i++)
        {
            $list[] = $voucherModel->field('id,name,validTime')->order('add_time desc')->limit($start.','.$pageSize)->select();
            $start = $start + $pageSize;
        }
        $this->assign('size',$size);
        $this->assign('list',$list);// 赋值数据集
        $this->display();
    }
    /**
     * @author peng
     * @date 2017-01-12
     * @description 添加商品动作
     */
    public function addAction(){
        if(IS_AJAX)
        {
            $data = I('post.');
            $goodsName = $data['goodsName'];
            $isExit = M('goodsVoucher')->where("name='$goodsName'")->find();
            if($isExit)
            {
                $this->ajaxReturn(array(
                    'status'=>2,
                    'msg'=>'该商品名已经存在，请重写其它商品名'
                ));
            }
            M()->startTrans();
            $goodsVouArr = array(
                'name'=>$data['goodsName'],
                'price'=>$data['money'],
                'add_time'=>time(),
                'create_userid'=>session('')['WST_STAFF']['staffId'],
                'isSale'=>0,
                'rank'=>$data['rank'],
                'pictureUrl'=>$data['pictureUrl']
            );
            $goodsVouRes = M('goodsVoucher')->add($goodsVouArr);
            $goodsVouRetData = array();
            if($goodsVouRes)
            {
                foreach ($data['idArr'] as $key => $value)
                {
                    $goodsVouRetData[$key]['voucher_id'] = $value;
                    $goodsVouRetData[$key]['goods_id'] = $goodsVouRes;
                }
            }
            $goodsVouRetRes = M('voucherGoodsRelation')->addAll($goodsVouRetData);
            if($goodsVouRetRes)
            {
                M()->commit();
                $this->ajaxReturn(array(
                    'status'=>0,
                    'msg'=>'添加成功'
                ));
            }else{
                M()->rollback();
                $this->ajaxReturn(array(
                    'status'=>1,
                    'msg'=>'添加失败'
                ));
            }
        }
    }

    /**
     * @author peng
     * @date 2017-01-12
     * @descreption 修改商品
     */
    public function editGoods($goodsId){
        $arr = M('GoodsVoucher')->field('id,name,price,isSale,rank,pictureUrl')->where('id='.$goodsId)->find();
        $voucherArr = M('VoucherGoodsRelation as go')->join('oto_voucher as v on go.voucher_id = v.id where go.goods_id='.$arr['id'])->field('v.id,v.name,v.validTime')->select();
        $voucherModel = M('voucher');
        $pageSize = 10;
        $count      = $voucherModel->count();// 查询满足要求的总记录数
        $size = ceil($count / $pageSize);
        $start = 0;
        $list = array();
        for($i=1;$i<=$size;$i++)
        {
            $list[] = $voucherModel->field('id,name,validTime')->order('add_time desc')->limit($start.','.$pageSize)->select();
            $start = $start + $pageSize;
        }
        $this->assign('size',$size);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('goodsArr',$arr);
        $this->assign('voucherArr',$voucherArr);
        $this->display();
    }
    /**
     * @author peng
     * @date 2017-01-12
     * @descreption 保存商品动作
     */
    public function saveAction(){
        $data = I('post.');
        $goodsData = array(
            'name'=>$data['goodsName'],
            'rank'=>$data['rank'],
            'price'=>$data['money'],
            'pictureUrl'=>$data['pictureUrl']
        );
        $goodsVoucherRelArr = array();
        foreach ($data['idArr'] as $key => $value)
        {
            $goodsVoucherRelArr[$key]['goods_id'] = $data['id'];
            $goodsVoucherRelArr[$key]['voucher_id'] = $value;
        }
        M()->startTrans();

        M('goodsVoucher')->where('id='.$data['id'])->save($goodsData);          //有可能数据没变化

        $deleteRes = M('voucherGoodsRelation')->where('goods_id='.$data['id'])->delete();
        if($deleteRes)
        {
            $addRes = M('voucherGoodsRelation')->addAll($goodsVoucherRelArr);
            M()->rollback();
            if($addRes)
            {
                $this->ajaxReturn(array(
                    'status'=>0,
                    'msg'=>'修改成功'
                ));
            }else{
                $this->ajaxReturn(array(
                    'status'=>2,
                    'msg'=>'删除失败'
                ));
            }
        }else{
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'修改失败'
            ));
        }
    }

    /**
     * @author peng
     * @date 2017-01-12
     * @descreption 删除商品动作
     */
    public function delAction(){
        #删除商品并且与代金券关系表中的数据
        if(IS_AJAX)
        {
            $goodsId = I('post.goodsId');
            M()->startTrans();
            $res = M('goodsVoucher')->where('id='.$goodsId)->delete();
            if($res) {
                $goodsVoucherRelRes = M('voucherGoodsRelation')->where('goods_id=' . $goodsId)->delete();
                if ($goodsVoucherRelRes) {
                    M()->commit();
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'msg' => '删除成功'
                    ));
                } else {
                    M()->rollback();
                    $this->ajaxReturn(array(
                        'status' => 1,
                        'msg' => '删除失败'
                    ));
                }
            }
        }
    }
    /**
     * @author 魏永就
     * @date 17-1-17
     * @description 商品上架
     */
    public function onSale()
    {
        $goodsId = I('post.goodsId');
        $type = I('post.type');
        if($type == 1)
        {
            $info = M('goodsVoucher')->field('rank')->where('id='.$goodsId)->find();
            $isExit = M('goodsVoucher')->where(array(
                'rank'=>$info['rank'],
                'isSale'=>1
            ))->find();
            if($isExit)
            {
                $this->ajaxReturn(array(
                    'status'=>2,
                    'msg'=>'该等级的商品已经上架'
                ));
            }
        }
        $res = M('goodsVoucher')->where('id='.$goodsId)->setField(array(
           'isSale'=>$type
        ));
        if($res)
        {
            $this->ajaxReturn(array(
                'status'=>0,
                'msg'=>'操作成功'
            ));
        }else{
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'操作失败'
            ));
        }
    }
    // 上传头像
    public function uploadImg()
    {
//        if (! session('oto_userId')) {
//            echo "文件上传失败";
//            return;
//        }
        import('Org.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        $data = date('Y-m', time());
        if ($upload->upload('./Upload/VoucherGoods/' . $data . '/')) {
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if ($info['0']['type'] != 'image/jpeg' && $info['0']['type'] != 'image/jpg' && $info['0']['type'] != 'image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png') {
            $this->ajaxReturn(array(
                'status'=>2,
                'msg'=>'格式不对'
            ));
        }
        if ($info['0']['size'] > $MAX_SIZE)
            $this->ajaxReturn(array(
                'status'=>3,
                'msg'=>'上传的文件大小超过了规定大小'
            ));
        if ($info['0']['size'] == 0)
            $this->ajaxReturn(array(
                'status'=>5,
                'msg'=>'请上传文件'
            ));
        switch ($info['0']['error']) {
            case 0:
                    $this->ajaxReturn(array(
                        'status'=>0,
                        'picUrl'=>'/Upload/VoucherGoods/' . $data . '/' . $file_newname
                    ));
                break;
            case 1:
                $this->ajaxReturn(array(
                    'status'=>7,
                    'msg'=>'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值'
                ));
                break;
            case 2:
                $this->ajaxReturn(array(
                    'status'=>8,
                    'msg'=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值'
                ));
                break;
            case 3:
                $this->ajaxReturn(array(
                    'status'=>9,
                    'msg'=>'文件只有部分被上传'
                ));
                break;
            case 4:
                $this->ajaxReturn(array(
                    'status'=>10,
                    'msg'=>'没有文件被上传'
                ));
                break;
        }
        die;        // 这行代码是二次开发新增的
    }
}