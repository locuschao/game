<?php
namespace Home\Controller;

/**
 * 晒单分享
 */
class ShareController extends BaseController
{

    /**
     * 晒单分享列表
     */
    public function index()
    {
        $shareModel = D('Home/Share');
        $counts = $shareModel->count();
        // 分页显示
        $page = new \Think\Page($counts, 30);
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $show = $page->show();
        $limit = $page->firstRow . ',' . $page->listRows;
        $sql = "select a.*,b.loginName,b.userPhoto from __PREFIX__share a inner join  __PREFIX__users b on a.userId = b.userId order by id desc limit $limit";
        $sharedata = $shareModel->query($sql);
        $this->assign('show', $show);
        $this->assign('counts', $counts);
        // 显示羡慕和评论数
        $envyModel = M('Share_envy');
        $commentsModel = M('Share_comments');
        foreach ($sharedata as $k => $v) {
            $id = $v['id'];
            $sharedata["$k"]["envy"] = $envyModel->where("shareId=$id")->count();
            $sharedata["$k"]["comments"] = $commentsModel->where("shareId=$id")->count();
            $sql = "select a.*,b.userPhoto from __PREFIX__share_comments a left join __PREFIX__users b on a.userId=b.userId where shareId=$id";
            $sharedata["$k"]["comments_content"] = $commentsModel->query($sql);
        }
        // dump($sharedata['1']);die;
        $this->assign("sharedata", $sharedata);
        $this->display("Share/listshare");
        exit(0);
    }

    /**
     * 晒单分享详情
     */
    public function detailshare()
    {
        $id = intval(I('get.id'));
        if (! $id) {
            $this->error('非法操作！');
        }
        // 晒单id
        $USER = session('WST_USER'); // 用户登陆id
        $this->assign("USER", $USER);
        // dump($USER);die;
        $shareModel = M('Share');
        $commentsModel = M('Share_comments');
        // 晒单详情
        $sql = "select a.*,b.loginName,b.userPhoto from __PREFIX__share a left join  __PREFIX__users b on a.userId = b.userId  where a.id=$id";
        $sharedata = $shareModel->query($sql);
        // 晒单评论数
        $counts = $commentsModel->where("shareId=$id")->count();
        $this->assign("sharedata", $sharedata);
        $this->assign("counts", $counts);
        
        // 最新晒单
        $sql = "select a.*,b.loginName,b.userPhoto from __PREFIX__share a left join  __PREFIX__users b on a.userId = b.userId  order by id desc limit 4";
        $sharelist = $shareModel->query($sql);
        $this->assign("sharelist", $sharelist);
        // 羡慕嫉妒恨数量
        $envyModel = M('Share_envy');
        $count = $envyModel->where("shareId=$id")->count();
        $this->assign("count", $count);
        // 显示晒单评论
        $sql = "select a.*,b.userPhoto from __PREFIX__share_comments a left join __PREFIX__users b on a.userId=b.userId where shareId=$id";
        $commentsModel = M('Share_comments');
        $commentsdata = $commentsModel->query($sql);
        $this->assign('commentsdata', $commentsdata);
        // 如果用户登陆则显示回复框，否则提示用户登陆或者注册
        $this->display('Share/detailshare');
        exit(0);
    }

    /**
     * 添加晒单羡慕嫉妒恨
     */
    public function addEnvy()
    {
        if (IS_AJAX) {
            $envyModel = M('Share_envy');
            $userId = intval(I('post.userId'));
            $shareId = intval(I('post.shareId'));
            // 判断用户是否登陆，没有登陆跳转到登陆页
            if ($userId == 0) {
                echo json_encode('请登录！');
            } else {
                $data = array();
                $data['shareId'] = $shareId;
                $data['userId'] = $userId;
                $data['is_envy'] = 1;
                $is_envy = $envyModel->where("userId=$userId and shareId=$shareId")->find();
                if (! $is_envy) {
                    $rs = $envyModel->add($data);
                    echo json_encode('ok');
                } else {
                    echo json_encode('no');
                }
            }
        }
    }

    /**
     * 添加晒单评论
     */
    public function addComments()
    {
        $commentsModel = M('Share_comments');
        // check验证码
        $code = $_POST['verify'];
        $verify = new \Think\Verify();
        $code = $verify->check($code);
        if (! $code) {
            $status = - 1;
        } else {
            $data = array();
            $data['userId'] = (int) session('WST_USER')['userId'];
            $data['shareId'] = (int) I('shareId');
            $data['commentsContents'] = I('commentsContents');
            $data['commentsTime'] = date('Y-m-d H:i:s');
            $rs = $commentsModel->add($data);
            if ($rs) {
                $status = 1;
            }
        }
        $this->ajaxReturn($status);
    }

    /**
     * 增加晒单分享前上传图片
     */
    public function addshareimg()
    {
        // 上传路径和图片命名
        $time = date('Ymd');
        $path = "./Upload/share/$time/";
        if (! is_dir($path)) {
            mkdir(iconv('UTF-8', 'GBK', $path), 0777, true);
        }
        $name1 = rand() . rand() . '.jpg';
        $path1 = $path . $name1;
        $name2 = rand() . rand() . '.jpg';
        $path2 = $path . $name2;
        $name3 = rand() . rand() . '.jpg';
        $path3 = $path . $name3;
        if (move_uploaded_file($_FILES['fileToUpload1']['tmp_name'], $path1)) {
            $res = '图片1上传成功';
            $_SESSION['path1'] = substr($path1, 1);
        } elseif (move_uploaded_file($_FILES['fileToUpload2']['tmp_name'], $path2)) {
            $res = '图片2上传成功';
            $_SESSION['path2'] = substr($path2, 1);
        } elseif (move_uploaded_file($_FILES['fileToUpload3']['tmp_name'], $path3)) {
            $res = '图片3上传成功';
            $_SESSION['path3'] = substr($path3, 1);
        } else {
            $res = '有图片上传失败';
        }
        echo json_encode($res);
    }

    /**
     * 增加晒单分享
     */
    public function addshare()
    {
        $this->isUserAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Share');
        $obj['userId'] = $USER['userId'];
        $obj['shopId'] = intval(I('shopId'));
        $obj['orderId'] = intval(I('orderId'));
        $obj['goodsId'] = intval(I('goodsId'));
        $obj['path1'] = $_SESSION['path1'];
        $obj['path2'] = $_SESSION['path2'];
        $obj['path3'] = $_SESSION['path3'];
        if (empty($obj['path1']) || empty($obj['path1']) || empty($obj['path1'])) {
            $data['status'] = '请上传三张图片';
            $this->ajaxReturn($data);
        }
        $rs = $morders->addShare($obj);
        $data['status'] = $rs;
        $this->ajaxReturn($data);
    }

    /**
     * ****************卖家对晒单操作**********************
     */
    /*
     * 卖家查看晒单
     */
    public function shopShare()
    {
        $this->isShopLogin();
        $USER = session("WST_USER");
        // 获取商家商品分类
        $m = D('Home/ShopsCats');
        $this->assign('shopCatsList', $m->queryByList($USER['shopId'], 0));
        $m = D('Home/share');
        $page = $m->queryByPage($USER['shopId']);
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign("shopCatId2", I('shopCatId2'));
        $this->assign("shopCatId1", I('shopCatId1'));
        $this->assign("goodsName", I('goodsName'));
        $this->assign("umark", "goodsShare");
        $this->display("Shops/goodsShare/list");
    }

    /*
     * 卖家审核晒单
     */
    public function changeShareShow()
    {
        $this->isShopLogin();
        $USER = session("WST_USER");
        $m = D('Share');
        $rs = $m->changeShareShow();
        $this->ajaxReturn($rs);
    }
}