<?php
namespace Admin\Controller;

/**
 * 优惠券控制器
 */
class YouhuiController extends BaseController
{
    // 优惠券首页视图
    public function index()
    {
        $this->isLogin();
        $model = D('Youhui');
        D('Home/Youhui')->overdue();
        $search = I('youhuiName');
        $list_data = $model->index_list(25, $search);
        $status = session('status');
        switch ($status) {
            case '1':
                $this->assign('return', $status);
                session('status', null);
                break;
            case '2':
                $this->assign('return', $status);
                session('status', null);
                break;
            case '3':
                $this->assign('return', $status);
                session('status', null);
                break;
        }
        $this->checkPrivelege('cmgl_00');
        // $this->checkPrivelege('cm_00');
        $this->assign('list', $list_data['list']);
        $this->assign('show', $list_data['show']);
        $this->display();
    }
    // 增加页
    public function add()
    {
        $this->isLogin();
        // 获取地区分类
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        $this->assign('formurl', U('Admin/Youhui/upload'));
        $this->display();
    }
    
    // 处理增加内容
    public function upload()
    {
        $this->isLogin();
        $youhui_data = $_POST;
        // 上传路径和图片命名
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
        // 错误返回数据重新添加
        if ($return_data['error'] > 0) {
            $this->assign('return_data', $return_data);
            $m = D('Admin/Areas');
            $this->assign('areaList', $m->queryShowByList(0));
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            $this->display('add');
            return;
        }
        
        $insert = M('youhui')->data($return_data)->add();
        $return_data['admin_check_status'] = 1;
        $return_data['biz_apply_status'] = 1;
        $return_data['youhui_id'] = M('youhui')->where('create_time=' . $return_data['create_time'])
            ->field('id')
            ->find();
        $return_data['youhui_id'] = $return_data['youhui_id']['id'];
        $biz_insert = M('youhui_biz_submit')->data($return_data)->add();
        if ($insert && $biz_insert) {
            session('status', '1');
            $this->redirect('Admin/Youhui/index');
        } else {
            session('status', '3');
            $this->redirect('Admin/Youhui/index');
        }
    }
    // 删除
    public function del()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('cmgl_02');
        if ($id = (int) I('id')) {
            $del[] = M('Youhui')->delete($id);
            $del[] = M('youhui_biz_submit')->where('youhui_id=' . $id)->delete();
            $del[] = M('youhui_use_record')->where('youhui_id=' . $youhui_id['youhui_id'])->delete();
            $del[] = M('youhui_user_link')->where('youhui_id=' . $youhui_id['youhui_id'])->delete();
            for ($i = 0; $i < 3; $i ++) {
                if (! $del[$i]) {
                    $dels ++;
                }
            }
            if ($save !== false && $dels > 0) {
                echo json_encode(array(
                    'status' => 1
                ));
            } else {
                echo json_encode(array(
                    'status' => 0
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 0
            ));
        }
    }
    // 修改页
    public function updata()
    {
        $this->isLogin();
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
            $data = M('Youhui')->field($field)->find($id);
            $m = D('Admin/Areas');
            $this->assign('areaList', $m->queryShowByList(0));
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            $m = D('Home/Youhui');
            $data = $m->runreleasedata($data);
            $this->assign('return_data', $data);
            $this->assign('formurl', U('Admin/Youhui/updatarun'));
            $this->checkPrivelege('cmgl_01');
            $this->display('add');
        } else {
            session('status', '3');
            $this->redirect('Admin/Youhui/index');
        }
    }
    // 内容修改表单处理
    public function updatarun()
    {
        $this->isLogin();
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
            $this->checkPrivelege('cmgl_01');
            $this->display('add');
        }
        unset($return_data['xg']);
        $save = M('youhui')->save($return_data);
        $biz_save = M('youhui_biz_submit')->where('youhui_id=' . $return_data['id'])
            ->data($return_data)
            ->save();
        if ((int) $return_data['is_effect'] == 0) {
            $user_data['u_is_effect'] = 0;
        } else {
            $user_data['u_is_effect'] = 1;
        }
        $user_save = M('youhui_user_link')->where('youhui_id=' . $return_data['id'])
            ->data($user_data)
            ->save();
        if ($save != false && $biz_save !== false && $user_save !== false) {
            session('status', '2');
            $this->redirect('Admin/Youhui/index');
        } else {
            session('status', '3');
            $this->redirect('Admin/Youhui/index');
        }
    }
    // 审核页内容
    
    // 审核页
    public function shopup_log()
    {
        $this->isLogin();
        $m = D('Youhui');
        $search = I('admin_check');
        D('Home/Youhui')->overdue();
        $list_data = $m->getshopup_log(15, $search);
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
        $this->assign('list', $list_data['list']);
        $this->assign('show', $list_data['show']);
        $this->checkPrivelege('cmgl_03');
        $this->display();
    }
    // 商户删除
    public function down_youhui()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('cmgl_06');
        $id = (int) I('id');
        $data['id'] = $id;
        $data['biz_apply_status'] = 3;
        $data['admin_check_status'] = 1;
        $data['create_time'] = time();
        $m = M('youhui_biz_submit');
        $youhui_id = $m->where('id=' . $data['id'])
            ->field('youhui_id')
            ->find();
        $save = $m->save($data);
        $del[] = M('youhui')->delete($youhui_id['youhui_id']);
        $del[] = M('youhui_use_record')->where('youhui_id=' . $youhui_id['youhui_id'])->delete();
        $del[] = M('youhui_user_link')->where('youhui_id=' . $youhui_id['youhui_id'])->delete();
        $dels = 0;
        for ($i = 0; $i < 2; $i ++) {
            if (! $del[$i]) {
                $dels ++;
            }
        }
        if ($save !== false && $dels > 0) {
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
    // 拒绝
    public function refuse()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('cmgl_07');
        $id = (int) I('id');
        $m = M('youhui_biz_submit');
        $updata['id'] = $id;
        $updata['admin_check_status'] = 2;
        $m->save($updata);
        if ($save !== false) {
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
    
    // 发布页and 修改页
    public function release()
    {
        $this->isLogin();
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
            $data = M('youhui_biz_submit')->field($field)->find($id);
            $m = D('Admin/Areas');
            $this->assign('areaList', $m->queryShowByList(0));
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            $m = D('Home/Youhui');
            $release = 'release';
            $data = $m->runreleasedata($data, $release);
            $this->assign('return_data', $data);
            $this->assign('formurl', U('Admin/Youhui/releaserun'));
            $this->assign('xg', (int) I('xg'));
            $this->checkPrivelege('cmgl_04');
            $this->display('add');
        } else {
            session('status', '3');
            $this->redirect('Admin/Youhui/shopup_log');
        }
    }
    
    // 发布内容表单处理
    public function releaserun()
    {
        $this->isLogin();
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
            $this->checkPrivelege('cmgl_04');
            $this->display('add');
        }
        
        if ($youhui_data['xg'] == 1) {
            unset($return_data['xg']);
            $zhu_tb = M('youhui')->save($return_data);
            $return_data['youhui_id'] = M('youhui')->order('id desc')
                ->limit(1)
                ->select();
        } else {
            unset($return_data['xg']);
            $zhu_tb = M('youhui')->data($return_data)->add();
            $return_data['youhui_id'] = $zhu_tb;
        }
        $m = M('youhui_biz_submit');
        $return_data['admin_check_status'] = 1;
        
        if ((int) $return_data['is_effect'] == 0) {
            $user_data['u_is_effect'] = 0;
        } else {
            $user_data['u_is_effect'] = 1;
        }
        $user_save = M('youhui_user_link')->where('youhui_id=' . $return_data['id'])
            ->data($user_data)
            ->save();
        $save = $m->save($return_data);
        if ($save !== false && $zhu_tb && $user_save !== false) {
            session('status', '1');
            $this->redirect('Admin/Youhui/shopup_log');
        } else {
            session('status', '3');
            $this->redirect('Admin/Youhui/shopup_log');
        }
    }
    // 用户领取列表
    public function userlist()
    {
        $this->isLogin();
        $model = D('Youhui');
        D('Home/Youhui')->overdue();
        $data = $model->getuserlist();
        $this->assign('list', $data['list']);
        $this->assign('show', $data['show']);
        $this->checkPrivelege('cmgl_08');
        $this->display();
    }
    
    // 用户优惠券修改页面
    public function userlistupdata()
    {
        $this->isLogin();
        $youhui_id = I('youhui_id');
        $user_id = I('user_id');
        $data = D('Youhui')->getuserlist($youhui_id, $user_id);
        $data = $data['list']['0'];
        $this->checkPrivelege('cmgl_09');
        $this->assign('data', $data);
        $this->display();
    }
    
    // 用户优惠券删除
    public function deluseryouhui()
    {
        $this->isLogin();
        $data = $_POST;
        if ($data) {
            $this->checkAjaxPrivelege('cmgl_10');
            $rslt = M('youhui_user_link')->where('youhui_id=' . $data['youhui_id'], 'user_id=' . $data['user_id'])->delete();
            if ($rslt != false) {
                echo json_encode(array(
                    'status' => 1
                ));
            } else {
                echo json_encode(array(
                    'status' => 0
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 0
            ));
        }
    }
    
    // 处理用户优惠券修改内容
    public function runuseryouhui()
    {
        $this->isLogin();
        $data = $_POST;
        $rel = M('youhui_user_link')->where('youhui_id=' . $data['youhui_id'], 'user_id=' . $data['user_id'])->save($data);
        if ($rel !== false) {
            echo json_encode(array(
                'status' => 1
            ));
        } else {
            echo json_encode(array(
                'status' => 0
            ));
        }
    }
}
//
?>