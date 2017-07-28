<?php
namespace Admin\Controller;

/**
 * 秒杀商品控制器
 */
class SeckillController extends BaseController
{

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        $m = D('Admin/Seckill');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        } else {
            die("商品不存在!");
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->view->display('/Seckill/view');
    }

    /**
     * 查看
     */
    public function toPenddingView()
    {
        $this->isLogin();
        $m = D('Admin/Seckill');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
            // 获取商品分类信息
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            // 获取商家商品分类
            $m = D('Admin/ShopsCats');
            $this->assign('shopCatsList', $m->queryByList($object['shopId'], 0));
        } else {
            die("商品不存在!");
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->assign('seckillStartTime', date('Y-m-d 08:00:00', time()));
        $this->assign('seckillEndTime', date('Y-m-d 12:00:00', time()));
        $this->view->display('/seckill/view_pendding');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('splb_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取商品分类信息
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatsList', $m->queryByList());
        $m = D('Admin/Seckill');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        // echo $page['total'].'--'.$page['pageSize'];
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('goodsCatId1', I('goodsCatId1', 0));
        $this->assign('goodsCatId2', I('goodsCatId2', 0));
        $this->assign('goodsCatId3', I('goodsCatId3', 0));
        $this->assign('isAdminBest', I('isAdminBest', - 1));
        $this->assign('isAdminRecom', I('isAdminRecom', - 1));
        $this->display("/seckill/list");
    }

    /**
     * 分页查询
     */
    public function queryPenddingByPage()
    {
        $this->isLogin();
        $this->checkPrivelege('spsh_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取商品分类信息
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatsList', $m->queryByList());
        $m = D('Admin/Seckill');
        $page = $m->queryPenddingByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $pager->setConfig('header', '个会员');
        $page['pager'] = $pager->show();
        // echo $page['total'].'--'.$page['pageSize'];
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('goodsCatId1', I('goodsCatId1', 0));
        $this->assign('goodsCatId2', I('goodsCatId2', 0));
        $this->assign('goodsCatId3', I('goodsCatId3', 0));
        $this->assign('seckillStartTime', date('Y-m-d 08:00:00', time()));
        $this->assign('seckillEndTime', date('Y-m-d 12:00:00', time()));
        $this->display("/seckill/list_pendding");
    }
    // 刷新通过审核的秒杀商品的 状态
    public function refreshStatus()
    {
        $m = M('GoodsSeckill');
        $waitChangeDate = $m->where('(seckillStatus=0 OR seckillStatus=1) AND goodsSeckillStatus=1')
            ->field('id,seckillStartTime,seckillEndTime')
            ->select();
        
        foreach ($waitChangeDate as $k => $v) {
            $data = $m->where('id=' . $v['id'])
                ->field('seckillStartTime,seckillEndTime,seckillStatus')
                ->find();
            
            // 0表示未开始，1表示开始中，2表示结束
            if ($data['seckillStartTime'] < time()) {
                if ($data['seckillEndTime'] < time()) {
                    $data['seckillStatus'] = 2;
                } else {
                    $data['seckillStatus'] = 1;
                }
            } else {
                $data['seckillStatus'] = 0;
            }
            if ($v['seckillStatus'] != $data['seckillStatus']) {
                $m->where('id=' . $v['id'])->save(array(
                    'seckillStatus' => $data['seckillStatus']
                ));
            }
        }
        $this->index();
    }

    /**
     * 修改待审核秒杀商品状态
     */
    public function changePenddingGoodsStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('spsh_04');
        $m = D('Admin/Seckill');
        $rs = $m->changeGoodsStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 秒杀商品禁售
     */
    public function changeGoodsStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Seckill');
        $rs = $m->changeGoodsStatus();
        $this->ajaxReturn($rs);
    }
    // 秒杀审核 不通过
    public function seckillNotPass()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Seckill');
        $rs = $m->seckillNotPass();
        $this->ajaxReturn($rs);
    }
    // 卖家放弃申请秒杀商品
    public function giveUpSeckill()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Seckill');
        $rs = $m->giveUpSeckill();
        $this->ajaxReturn($rs);
    }
}
;
?>