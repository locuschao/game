<?php
namespace Admin\Model;

/**
 * 店铺服务类
 */
class GameModel extends BaseModel
{

    /**
     * 获取指定对象
     * @des 添加游戏类型
     * @date 修改日期 2017-7-19
     * @author Meke
     */
    public function get()
    {
        $m = M('game');
        $id = I('id', 0);
        $rs = $m->where("id=" . $id)->find();
        $versions = M('game_versions')->where(array(
            'gid' => $id
        ))->select();
        $allVersions = M('versions')->select();
        foreach ($allVersions as $k => $v) {
            foreach ($versions as $kk => $vv) {
                if ($v['id'] == $vv['vid']) {
                    $allVersions[$k]['checked'] = 1;
                }
            }
        }
        //2017-7-19 by Meke
        $where['typeId'] = array('in',$rs['gameType']);
        $gameType=M('game_type')->where($where)->select();
        if(version_compare(PHP_VERSION, '5.5.0') >= 0){
            $gameType=array_column($gameType,'typeName','typeId');
        }else{
            foreach ($gameType as $kk => $vv){
                $gameType[$vv['typeId']] = $vv['typeName'];
            }
        }
        $allGameType=M('game_type')->select();
        foreach ($allGameType as $k => $v) {
            foreach ($gameType as $kk => $vv) {
                if ($v['typeId'] == $kk) {
                    $allGameType[$k]['checked'] = 1;
                }
            }
        }
        $rs['versions'] = $versions;
        $rs['allVersions'] = $allVersions;
        $rs['allGameType'] = $allGameType;
        return $rs;
    }

    /**
     * 获取指定对象
     */
    public function getVersions()
    {
        $m = M('versions');
        $id = I('id', 0);
        $rs = $m->where("id=" . $id)->find();
        return $rs;
    }

    public function versionsAjaxEdit()
    {
        $id = I('id', 0);
        $vName = I('vName');
        if ($id > 0) {
            $rs = M('versions')->where(array(
                'id' => $id
            ))->save(array(
                'vName' => $vName
            ));
        } else {
            $rs = M('versions')->add(array(
                'vName' => $vName,
                'createTime' => date('Y-m-d H:i:s')
            ));
        }
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }

    /**
     * 修改游戏
     */
    public function ajaxEdit()
    {
        $id = I('id', 0);
        $gameName = I('gameName');
        $version = I('version');
        $version = trim($version, ',');
        $gameIco = I('gameIco');
        $downLoadUrl = I('downLoadUrl');
        $isHot = I('isHot', 0);
        $gameType =trim(I('gameType'), ',');
        $A = true;
        $B = true;
        $data = array();
        $data['gameName'] = $gameName;
        $data['gameIco'] = $gameIco;
        $data['downLoadUrl'] = $downLoadUrl;
        $data['isHot'] = $isHot;
        //拼音首字母转化为大写 2017-7-26 by Meke
        $data['letter'] =strtoupper(substr( encode($gameName, $sRetFormat='head'), 0, 1 ));
        M()->startTrans();
        if ($id) {
            $res = M('game')->where(array(
                'id' => $id
            ))->save($data);
            if ($res === false) {
                $A = false;
            }
            foreach (explode(',',$gameType) as $k => $v){
                $gameAttr[$k]['gameId']=$id;
                $gameAttr[$k]['gameType']=$v;
            }
            if(M('game_type_attr')->where(['gameId'=>$id])->delete()!==false){
                M('game_type_attr')->addALL($gameAttr);
            }
        } else {
            $id = M('game')->add($data);
            if($id){
                foreach (explode(',',$gameType) as $k => $v){
                    $gameAttr[$k]['gameId']=$id;
                    $gameAttr[$k]['gameType']=$v;
                }
                M('game_type_attr')->addALL($gameAttr);
            }
        }
        $del = M('game_versions')->where(array(
            'gid' => $id
        ))->delete();
        $gv = explode(',', $version);
        $gvData['gid'] = $id;
        foreach ($gv as $v) {
            $gvData['vid'] = $v;
            $res = M('game_versions')->add($gvData);
            if ($res == false) {
                $B = false;
            }
        }
        if ($A && $B) {
            M()->commit();
            return array(
                'status' => 0
            );
        } else {
            M()->rollback();
            return array(
                'status' => - 1
            );
        }
    }

    /**
     * 分页列表
     */
    public function queryByPage()
    {
        $m = M('game');
        $key = I('gameName');
        $where = '';
        if ($key) {
            $where = " where gameName like '%$key%' ";
        }
        $sql = "select * from __PREFIX__game  $where ";
        $sql .= " order by id desc";
        $info = $m->pageQuery($sql);
        return $info;
    }

    /**
     * 分页列表
     */
    public function queryByPageVersionsList()
    {
        $m = M('game');
        $key = I('gameName');
        $where = '';
        if ($key) {
            $where = " where vName like '%$key%' ";
        }
        $sql = "select * from __PREFIX__versions $where ";
        $sql .= " order by id desc";
        $info = $m->pageQuery($sql);
        return $info;
    }

    /**
     * 分页列表[待审核列表]
     */
    public function queryPeddingByPage()
    {
        $m = M('shops');
        $sql = "select serviceStartTime,scope,serviceEndTime,shopStatus,shopId,shopSn,shopName,u.userName,shopAtive,shopStatus from __PREFIX__shops s,__PREFIX__users u 
	 	     where s.userId=u.userId and shopStatus<=0 and shopFlag=1";
        if (I('shopName') != '')
            $sql .= " and shopName like '%" . I('shopName') . "%'";
        if (I('shopStatus', - 999) != - 999)
            $sql .= " and shopStatus =" . (int) I('shopStatus');
        $sql .= " order by shopId desc";
        $info = $m->pageQuery($sql);
        foreach ($info['root'] as $k => $v) {
            $temp = explode(',', $v['scope']);
            $str = '';
            foreach ($temp as $vv) {
                if ($vv == 1) {
                    $str .= '首充号' . ',';
                }
                if ($vv == 2) {
                    $str .= '首充号代充' . ',';
                }
                if ($vv == 3) {
                    $str .= '首充号分销' . ',';
                }
                if ($vv == 4) {
                    $str .= '自主充值' . ',';
                }
            }
            $str = rtrim($str, ',');
            $info['root'][$k]['scopeName'] = $str;
        }
        return $info;
    }

    /**
     * 获取列表
     */
    public function queryByList()
    {
        $m = M('shops');
        $sql = "select * from __PREFIX__shops order by shopId desc";
        $rs = $m->find($sql);
    }
    
    // 热门游戏设置
    public function changeHotStatus()
    {
        $id = I('id', 0);
        $rd['status'] = - 1;
        $status = I('status', 0);
        if (! $id) {
            return $rd;
        }
        $rs = M('game')->where(array(
            'id' => $id
        ))->setField('isHot', $status);
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => 1
            );
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = (int) I('id');
        $rd = array(
            'status' => - 1
        );
        $m = M('game');
        // 下架所有商品
        $rs = $m->where(array(
            'id' => $id
        ))->delete();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 删除游戏版本
     */
    public function delVersions()
    {
        $id = (int) I('id');
        $rd = array(
            'status' => - 1
        );
        ;
        // 下架所有商品
        M()->startTrans();
        $A = M('versions')->where(array(
            'id' => $id
        ))->delete();
        $B = M('game_versions')->where(array(
            'vid' => $id
        ))->delete();
        if (false !== $A && false !== $B) {
            $rd['status'] = 1;
            M()->commit();
        } else {
            M()->rollback();
        }
        return $rd;
    }

    /**
     * 获取待审核的店铺数量
     */
    public function queryPenddingShopsNum()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $sql = "select count(*) counts from __PREFIX__shops where shopStatus=0 and shopFlag=1";
        $rs = $this->query($sql);
        $rd['num'] = $rs[0]['counts'];
        return $rd;
    }

    /**
     * @des 游戏礼包导入Excel
     * @date 2017-7-20
     * @author Meke
     */
    public function importGameGift($id,$data){
        $objReader = WSTReadExcel($data['file']['savepath'].$data['file']['savename']);
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        //数据集合
        $readData = array();
        $gameCode = M('game_code');
        //$importNum = 0;
        //判断该游戏礼包是否存在以前的充换码
        M('game_code')->where(array('ggId'=>$id))->delete();
        //循环读取每个单元格的数据
        for ($row = 2; $row <= $rows; $row++){//行数是以第3行开始
            $gameCodes=array();
            $gameCodes['ggId']=intval($id);
            $gameCodes['gameCode']=trim($sheet->getCell("B".$row)->getValue());
            if(empty($gameCodes['gameCode'])){
               continue;
            }
            $readData[]=$gameCodes;
            //$importNum++;
        }
        //检查数组是否存在重复
        $Data=unique_2d_array_by_key($readData,'gameCode');
        if(count($Data)>0){
            M()->startTrans();
            $result=$gameCode->addAll($Data);
            if(!empty($result)){
                $res=M('game_gift')
                    ->where(array('id'=>$gameCodes['ggId']))
                    ->save(array('totalNumber'=>count($Data),'remainNumber'=>count($Data),));
            }
            if(!empty($result) && !empty($res)){
                M()->commit();
            }else{
                M()->rollback();
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * @des 查询礼包分页
     * @date 2017-7-20
     * @author Meke
     */
    public function queryGiftByPage()
    {
        $key = I('gameName');
        $isHot =I('isHot');
        $where = '';
        if($key && !empty($isHot) ){
            $where = " where gameName like '%$key%' and isHot ={$isHot} ";
        }elseif($key){
            $where .= " where gameName like '%$key%' ";
        }elseif(!empty($isHot)){
            $where =" where isHot ={$isHot} ";
        }else{
            $where = '';
        }
        $m = M('game_gift');
        $sql = "select * from __PREFIX__game_gift $where ";
        $sql .= " order by id asc";
        $info = $m->pageQuery($sql);
        return $info;
    }

    /**
     * @des 礼包热门设置
     * @date 2017-7-21
     * @author Meke
     */
    public function changeHotGiftStatus()
    {
        $id = I('id', 0);
        $rd['status'] = - 1;
        $status = I('status', 0);
        if (! $id) {
            return $rd;
        }
        $rs = M('game_gift')->where(array(
            'id' => $id
        ))->setField('isHot', $status);
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => 1
            );
        }
    }

    /**
     * @des 礼包热门设置
     * @date 2017-7-21
     * @author Meke
     */
    public function changeShelvesGiftStatus()
    {
        $id = I('id', 0);
        $rd['status'] = - 1;
        $status = I('status', 0);
        if (! $id) {
            return $rd;
        }
        $rs = M('game_gift')->where(array(
            'id' => $id
        ))->setField('shelves', $status);
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => 1
            );
        }
    }







}
;
?>