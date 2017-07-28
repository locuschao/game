<?php
namespace Admin\Controller;

use Lib\Exp\DataExp;

class GameController extends BaseController
{
    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        // 获取地区信息
        $m = D('Admin/Game');
        $object = array();
        if (I('id', 0) > 0) {
            $object = $m->get();
        } else {
            $object['allVersions'] = M('versions')->select();
            $object['allGameType'] = M('game_type')->select();
        }
        $this->assign('object', $object);
        $this->view->display('/Game/edit');
    }

    /**
     * 跳到新增/编辑页面
     */
    public function versionsToEdit()
    {
        $this->isLogin();
        // 获取地区信息
        $m = D('Admin/Game');
        $object = array();
        if (I('id', 0) > 0) {
            // $this->checkPrivelege('gggl_02');
            $object = $m->getVersions();
        }
        $this->assign('object', $object);
        $this->view->display('/Game/versionsEdit');
    }

    public function ajaxEdit()
    {
        $m = D('Admin/Game');
        $rs = $m->ajaxEdit();
        $this->ajaxReturn($rs);
    }

    public function versionsAjaxEdit()
    {
        $m = D('Admin/Game');
        $rs = $m->versionsAjaxEdit();
        $this->ajaxReturn($rs);
    }
    
    // 热门设置
    public function changeHotStatus()
    {
        $m = D('Admin/Game');
        $rs = $m->changeHotStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Ads');
        $rs = array();
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('gggl_02');
            $rs = $m->edit();
        } else {
            $this->checkAjaxPrivelege('gggl_01');
            $rs = $m->insert();
        }
        $this->ajaxReturn($rs);
    }

    /**
     * 删除操作
     */
    public function del()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('gggl_03');
        $m = D('Admin/Game');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 删除游戏版本
     */
    public function delVersions()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('gggl_03');
        $m = D('Admin/Game');
        $rs = $m->delVersions();
        $this->ajaxReturn($rs);
    }

    /**
     * 分页查询
     * @des 增加游戏种类
     * @date 修改日期 2017-7-18
     * @author Meke
     */
    public function gameList()
    {
        $this->isLogin();
        // $this->checkAjaxPrivelege('gggl_00');
        self::WSTAssigns();
        // 获取商品分类
        $m = D('Admin/Game');
        $page = $m->queryByPage();
        $gameTypeAttr=M('game_type_attr')->select();
        foreach ($gameTypeAttr as $key=>$value){
            $typeAttr[$value['gameId']][$key]=$value['gameType'];
        }
        foreach ($page['root'] as $key=>$value){
            $page['root'][$key]['gameType']=$typeAttr[$value['id']];
        }
        $gameType=M('game_type')->select();
        if(version_compare(PHP_VERSION, '5.5.0') >= 0){
            $arr=array_column($gameType,'typeName','typeId');
        }else{
            foreach ($gameType as $k=>$row){
                $arr[$row['typeId']] = $row['typeName'];
            }
        }
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('arr', $arr);
        $this->assign('Page', $page);
        $this->display("/Game/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出数据
     */
    public function dataExp()
    {
        $this->isLogin();
        $type = I('post.type');
        switch ($type)
        {
            case 1:
                $where = ' where isHot = 1';
                break;
            case 2:
                $where = 'where isHot = 0';
                break;
            default:
                $where = '';
        }

        $xlsModel = M('Game');
        $xlsName = 'GameList';
        $xlsData = $xlsModel->query("select id,gameName,isHot from __PREFIX__game $where order by id DESC");
        $xlsCell = array(
            array('id','分类ID'),
            array('gameName','游戏名'),
            array('isHot','是否热门')
        );
        if($type=='1')
        {
            foreach ($xlsData as $key => $value)
            {
                $xlsData[$key]['isHot'] = '是';
            }
        }else if($type == '2')
        {
           foreach ($xlsData as $key =>$value )
           {
               $xlsData[$key]['isHot'] = '否';
           }
        }else{
            foreach ($xlsData as $key => $value)
            {
                if($value['isHot'] == 1)
                {
                    $xlsData[$key]['isHot'] = '是';
                }else{
                    $xlsData[$key]['isHot'] = '否';
                }
            }
        }
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    /**
     * 版本列表
     */
    public function versionsList()
    {
        $this->isLogin();
        // $this->checkAjaxPrivelege('gggl_00');
        self::WSTAssigns();
        // 获取商品分类
        $m = D('Admin/Game');
        $page = $m->queryByPageVersionsList();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/Game/versions");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 游戏版本数据导出
     */
    public function versionsListExp()
    {
        $this->isLogin();
        $xlsModel = M('game');
        $xlsData = $xlsModel->query("select * from __PREFIX__versions order by id desc");
        $xlsCell = array(
            array('id','版本ID'),
            array('vName','版本名称'),
        );
        $xlsName = 'versionList';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    /**
     * 列表查询
     */
    public function queryByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Ads');
        $list = $m->queryByList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }


    /**
     * @des 查询/显示游戏类型
     * @date 2017-7-17
     * @author Meke
     */
    public function listType(){
        $this->isLogin();
        if(IS_POST){
            $gameModel = M('game_type');
            if(empty(I('post.gameName'))){
                $gameType =  $gameModel->order('typeId asc')->select();
            }else{
                $gameType =  $gameModel->where(array('typeName'=>I('post.gameName')))->select();
            }
        }else{
            $gameModel = M('game_type');
            $gameType =  $gameModel->order('typeId asc')->select();
        }
        $this->assign('gameType', $gameType);
        $this->display("/Game/list_type");

    }

    /**
     * @des 添加游戏类型
     * @date 2017-7-18
     * @author Meke
     */
    public function addGameType()
    {
        $this->isLogin();
        if (IS_POST) {
            $id = I('id', 0);
            $typeName = I('typeName');
            if ($id > 0) {
                $rs = M('game_type')->where(array('typeId' => $id))->save(array('typeName' => $typeName));
            } else {
                //查询是否存在该种类
                $typeId = M('game_type')->where(array('typeName' => $typeName))->find();
                if (!empty($typeId)) {
                    $rs = M('game_type')->where(array('typeId' => $typeId))->save(array('typeName' => $typeName));
                } else {
                    $rs = M('game_type')->add(array('typeName' => $typeName,));
                }
            }
            if ($rs !== false) {
                $rs = array(
                    'status' => 0
                );
            } else {
                $rs = array(
                    'status' => -1
                );
            }
            $this->ajaxReturn($rs);
        } else {
            if (!empty(I('id'))) {
                $gameType = M('game_type')->where(array('typeId' => I('id')))->find();
                $this->assign('gameType', $gameType);
                $this->display('/Game/add_game_type');
            } else {
                $this->display('/Game/add_game_type');
            }

        }
    }

    /**
     * @des 删除游戏类型
     * @date 2017-7-18
     * @author Meke
     */
    public function delGameType(){
        $this->isAjaxLogin();
        $id = intval(I('id'));
        if(empty($id)){
            $rs['status'] = 0;
        }else{
            //游戏类型下面是否存在绑定的游戏种类
            $where['gameType']=array('like','%'.$id.'%');
            $result=M('game')->where($where)->select();
            if(!empty($result)){
                $rs['status'] = 2;
            }else{
                $typeId=M('game_type')->where(array('typeId'=>$id))->delete();
                if(empty($typeId)){
                    $rs['status'] = 0;
                }else{
                    $rs['status'] = 1;
                }
            }

        }
        $this->ajaxReturn($rs);
    }

    /**
     * @des 显示游戏礼包
     * @date 2017-7-19
     * @author Meke
     */
    public function listGameGift(){
        $this->isLogin();
        self::WSTAssigns();
        // 获取商品分类
        $m = D('Admin/Game');
        $page = $m->queryGiftByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display('/Game/list_game_gift');
    }

    /**
     * @des 导入Excel
     * @date 2017-7-20
     * @author Meke
     */
    public function importGameData(){
        $this->isLogin();
        self::WSTAssigns();
        $config = array(
            'maxSize' => 0, // 上传的文件大小限制 (0-不做限制)
            'exts' => array(
                'xls',
                'xlsx',
                'xlsm'
            ), // 允许上传的文件后缀
            'rootPath' => './Upload/', // 保存根路径
            'driver' => 'LOCAL', // 文件上传驱动
            'subName' => array(
                'date',
                'Y-m'
            ),
            'savePath' => I('dir', 'uploads') . "/"
        );
        if(I('typeId',0)>0){
            //修改
            $data['Integral'] = empty(intval($_POST['Integral'])) ? 0 : intval($_POST['Integral']);
            $data['beginTime'] = $_POST['beginTime'];
            $data['endTime'] = $_POST['endTime'];
            $data['shelves'] = intval($_POST['shelves']);
            $data['isHot'] = intval($_POST['isHot']);
            $data['content'] = trim($_POST['content']);
            $data['description'] = trim($_POST['description']);
            if($_FILES['file']['size']>0) {
                $upload = new \Think\Upload($config);
                $rs = $upload->upload($_FILES);
                M()->startTrans();
                $res=M('game_gift')->where(array('id'=>I('typeId',0)))->save($data);
                if($res!== false){
                    $m = D('Admin/Game');
                    $rv = $m->importGameGift(I('typeId', 0), $rs);
                }
                if ($rv) {
                    M()->commit();
                    $this->success('更新成功', '/Admin/Game/listGameGift');
                } else {
                    M()->rollback();
                    $this->error('更新失败，请参考示例Excel');
                }
            }else{
                $res=M('game_gift')->where(array('id'=>I('typeId',0)))->save($data);
                if($res!== false){
                    $this->success('更新成功', '/Admin/Game/listGameGift');
                }else{
                    $this->error('更新失败');
                }
            }
        }else {
            $data['gameName'] = trim($_POST['gameName']);
            $data['gameId'] = intval($_POST['gameId']);
            $data['Integral'] = empty(intval($_POST['Integral'])) ? 0 : intval($_POST['Integral']);
            $data['beginTime'] = $_POST['beginTime'];
            $data['endTime'] = $_POST['endTime'];
            $data['shelves'] = intval($_POST['shelves']);
            $data['isHot'] = intval($_POST['isHot']);
            $data['content'] = trim($_POST['content']);
            $data['description'] = trim($_POST['description']);
            $data['totalNumber'] = 0;
            $data['remainNumber'] = 0;
            if($_FILES['file']['size']>0) {
            $upload = new \Think\Upload($config);
            $rs = $upload->upload($_FILES);
            }
            if (!$rs) {
                $rv=M('game_gift')->add($data);
                if(!$rv){
                    $this->error('导入失败');
                }else{
                    $this->success('导入成功', '/Admin/Game/listGameGift');
                }
            } else {
                M()->startTrans();
                $ggId = M('game_gift')->add($data);
                if ($ggId) {
                    $m = D('Admin/Game');
                    $rv = $m->importGameGift($ggId, $rs);
                }
                if ($rv) {
                    M()->commit();
                    $this->success('导入成功', '/Admin/Game/listGameGift');
                } else {
                    M()->rollback();
                    $this->error('导入失败,请参考示例Excel');
                }

            }
        }

    }

    /**
     * @des 导入显示
     * @date 2017-7-20
     * @author Meke
     */
    public function listImportGameData(){
        $this->isLogin();
        $object = array();
        if (I('id', 0) > 0) {
            $object = M('game_gift')->where(array('id'=>I('id', 0)))->select();
        }
        $this->assign('object', $object[0]);
        $this->display('/Game/import_data');
    }

    /**
     * @des 获取对应游戏的id ajax
     * @date 2017-7-20
     * @author Meke
     */
    public function selectGame(){
        $this->isLogin();
        self::WSTAssigns();
        $gameName=trim(I('post.gameName'));
        $map['gameName']=array('like', $gameName.'%');
        $data = M('game')->where($map)->select();
        echo json_encode($data);
    }

    /**
     * @des 游戏礼包热门设置
     * @date 2017-7-21
     * @author Meke
     */
    public function changeHotGiftStatus(){
        $m = D('Admin/Game');
        $rs = $m->changeHotGiftStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * @des 游戏礼包是否下架
     * @date 2017-7-21
     * @author Meke
     */
    public function changeShelvesGiftStatus(){
        $m = D('Admin/Game');
        $rs = $m->changeShelvesGiftStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * @des 分页查询游戏礼包
     * @date 2017-7-21
     * @author Meke
     */
    public function gameGiftList()
    {
        $this->isLogin();
        self::WSTAssigns();
        // 获取商品分类
        $m = D('Admin/Game');
        $page = $m->queryGiftByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/Game/list_game_gift");
    }

    /**
     * @des Excel示例导出
     * @date 2017-7-21
     * @author Meke
     */
    public function importExcel(){
        //$xlsData=M('game_code')->where(array('ggId'=>I('id',0)))->select();
        $xlsData=array(
            array('gameCode' =>'7VB6RZ6VV3NBGF3WFG'),
            array('gameCode' =>'RK2XW8M3278EDY2CKA'),
            array('gameCode' =>'V2KCMHZN7BUC3DY5FF'),
            array('gameCode' =>'9YSZ85NTTBF99CXABE'),
            array('gameCode' =>'39T9TY3NXTQ2HTWF2E'),
            array('gameCode' =>'RK2XW8M3278EDY2CKA'),
            array('gameCode' =>'9YSZ85NTTBF99CXABE'),
            array('gameCode' =>'39T9TY3NXTQ2HTWF2E'),
            array('gameCode' =>'9YSZ85NTTBF99CXABE'),
            array('gameCode' =>'V2KCMHZN7BUC3DY5FF'),
            array('gameCode' =>'RK2XW8M3278EDY2CKA'),
            array('gameCode' =>'39T9TY3NXTQ2HTWF2E'),
        );
        $xlsName='导入示例';
        $xlsCell  = array(
            array('ID','商品编号'),
            array('GameCode','游戏充换码'),
        );
        $dataExp = new DataExp();
        $dataExp->exportShiLiExcel($xlsName,$xlsCell,$xlsData);
    }

    /**
     * @des大批量字母插入
     * @date 2017-7-26
     * @auhor Meke
     */
    public function test(){
        foreach(M('game')->select() as $row){
            $a=strtoupper(substr( encode($row['gameName'], $sRetFormat='head'), 0, 1 ));
            $res=M('game')->where(['id'=>$row['id']])->setField('letter',$a);
        }
        echo $res;
    }



}
;
?>