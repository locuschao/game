<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 代金券管理
 */
class VoucherController extends BaseController
{
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 代金券列表
     */
    public function index(){
        $where = '';
        if(IS_POST)
        {
            $name = trim(I('post.voucherName'));
            $where .= "name = '$name' ";
        }
        $voucherModel = M('voucher as v');
        $count = $voucherModel->join('oto_staffs as s on v.create_userid = s.staffId')->where($where)->count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $list = $voucherModel->join('oto_staffs as s on v.create_userid = s.staffId')->field('v.validTime,v.id,v.name,v.add_time,s.loginName,v.remark')->where($where)->order('add_time desc')->limit($page->firstRow,$page->listRows)->select();
        $this->assign('voucherArr',$list);
        $this->assign('page',$show);
    	$this->display();
    }
    
    
    /**
     * @author 魏永就
     * @date 2017-01-12
     * @descreption 添加列表
     */
    public function addVoucher(){
     
        $gameArr = M('game')->field('id,gameName')->order('CONVERT( gameName USING gbk ) COLLATE gbk_chinese_ci ASC')->select();
        $versionArr = M('versions')->field('id,vName')->select();
        $this->assign('versions',$versionArr);
        $this->assign('game',$gameArr);
        $this->display();
    }
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 添加代金券动作
     */
    public function addVoucherAction(){
        if(IS_AJAX)
        {
            $data = I('post.');
            $res = M('voucher')->where($data)->find();
            if($res)                                //如果已经有一模一样的代金券，则不需要添加
            {
                $this->ajaxReturn(array(
                    'status'=>2,
                    'msg'=>'该形式的代金券已存在'
                ));
            }
            $data['add_time'] = time();
            $data['create_userid'] = $_SESSION['oto_mall']['WST_STAFF']['staffId'];
            if(M('voucher')->add($data))
            {
                $this->ajaxReturn(array(
                    'status'=>0,
                    'msg'=>'添加成功'
                ));
            }else{
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
     * @descreption 修改代金券
     */
    public function editVoucher($voucherId){
//        $voucherGoodsRelRes = M('voucherGoodsRelation')->where('voucher_id='.$voucherId)->find();
//        if($voucherGoodsRelRes)
//        {
//            echo '<script>alert("代金券已添加到商品中，不能修改");location.href="/index.php/Admin/Voucher/index"</script>';
//            die;
//        }
        $voucherArr = M('voucher')->field('id,name,gameId,is_global,versionId,consume,validTime,money,discount_amount')->where('id='.$voucherId)->find();
        $gameArr = M('game')->field('id,gameName')->order('CONVERT( gameName USING gbk ) COLLATE gbk_chinese_ci ASC')->select();
        $versionArr = M('versions')->field('id,vName')->select();
        $this->assign('voucherArr',$voucherArr);
        $this->assign('gameArr',$gameArr);
        $this->assign('versionArr',$versionArr);
        $this->display();
    }
     /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 保存代金券动作
     */
    public function saveVoucherAction(){
        $data = I('post.');
        $voucherRes = M('voucher')->save($data);
        if($voucherRes)
        {
            $this->ajaxReturn(array(
                'status'=>0,
                'msg'=>'更新成功'
            ));
        }else{
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'更新失败'
            ));
        }
    }
    
     /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 删除代金券动作
     */
    public function delVoucherAction(){
        #如果有代金券id存在于商品与代金券关系表中,则不允许删除，或者被用户所购买了
        $voucherId = I('post.voucherId');
        $goodsVoucherRetRes = M('voucherGoodsRelation')->where('voucher_id='.$voucherId)->find();
        if($goodsVoucherRetRes)
        {
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'该代金券已添加到商品中，不能删除'
            ));
        }
        $voucherRes = M('voucher')->where('id='.$voucherId)->delete();
        if($voucherRes)
        {
            $this->ajaxReturn(array(
                'status'=>0,
                'msg'=>'删除成功'
            ));
        }else{
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'删除失败'
            ));
        }
        
    }
    
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 平台订单
     */
    public function orders(){
        $where = '';
        if(IS_POST)
        {
            $orderNo = I('post.orderNo');
            $where .=" o.orderNo = $orderNo and ";
        }
        $count      = M('orders as o')->join('oto_order_goods as og on o.orderId = og.orderId')->where($where.'o.shopId=0 ')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list = M('orders as o')->join('left join oto_order_goods as og on o.orderId = og.orderId left join '.C('DB_PREFIX').'percentage_log p on o.orderId=p.orderId')
        ->order('o.createTime desc')
        ->where($where.'o.shopId=0 ')->field('o.orderNo,og.goodsName,og.goodsThums as picture,o.createTime,o.orderId,o.needPay,o.orderStatus,p.gain_price')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }

    /**
     * @author 魏永就
     * @date 2017-1-20
     * @description 订单详情
     */
    public function orderDetail($orderId)
    {
        $orderLogArr = M('log_orders')->where('orderId='.$orderId)->field('logTime,logContent')->select();
        $orderArr = M('orders')->field('orderNo,payType,createTime')->where('orderId='.$orderId)->find();
        $goodsArr = M('orderGoods')->where('orderId='.$orderId)->field('goodsName,goodsNums,goodsPrice')->find();
        $voucherGoodsId = M('orderGoods as og')->join('oto_goods_voucher as gv on gv.id=og.goodsId')->where('og.orderId ='.$orderId)->field('gv.id')->find();
        $voucherIds = M('voucherGoodsRelation')->where('goods_id='.$voucherGoodsId['id'])->field('voucher_id')->select();
        $voucherArr = array();
        foreach ($voucherIds as $key => $value)
        {
            $voucherArr[] = M('voucher')->where('id='.$value['voucher_id'])->field('name,validTime')->find();
        }
        $this->assign('voucherArr',$voucherArr);
        $this->assign('orderLogArr',$orderLogArr);
        $this->assign('orderArr',$orderArr);
        $this->assign('goodsArr',$goodsArr);
        $this->display();
    }
    /**
     * @author 魏永就
     * @date 17-1-22
     * @description 游戏搜索
     */
    function gameSearch()
    {
        $gameName = I('post.gameName');
        $gameId = M('game')->field('id')->where("gameName='$gameName'")->find();
        if($gameId)
            $this->ajaxReturn(array(
                'status'=>0,
                'gameId'=>$gameId['id']
            ));
        else
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>"“{$gameName}”游戏不存在"
            ));
        
    }
  
}