<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;
// 首充号代充验证
class ValidatadcController extends BaseController
{

    public function _initialize()
    {
        // 验证时初始化数据
        session('validateGoods', null);
        //peng 实现代充验证页面重新登录跳回原地址
        
        _setCookie('ref','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        
        parent::isLogin();
    }

    public function yanzhen()
    {
        
        $goodsId = I('id');
        $getGameId = I('gameId', 0, 'intval');
        
        if ($getGameId) {
            $gameId = $getGameId;
        } else {
            $gameId = M('goods')->where(array(
                'goodsId' => $goodsId
            ))->getField('gameId');
        }
        $this->versions = M('versions')->where(array(
            'isDel' => 0
        ))->select();
        $this->account = M('white_list')->where(array(
            'userId' => session('oto_userId'),
            'g.gameId' => $gameId,
            'w.is_del' => 0,//不能是删除的
        ))
            ->field('w.id,w.vid,w.account,v.vName')
            ->join(' as w left join oto_versions as v on v.id=w.vid')
            ->join('oto_goods as g on w.goodsId=g.goodsId')
            ->select();
       
        $this->display();
    }

    public function validataAccount()
    {
        $this->ajaxReturn(D('Game/Validatadc')->_validataAccount([
            'versions' => I('versions'),
            'account' => I('account'),
            'goodsType' => I('goodsType',0,'intval'),
            'goodsId' => I('id'),
            'gameId' => I('gameId', 0, 'intval'),
            'from' =>I('from','')
        ]));
    }
    
    //author: peng descreption:删除以往充值过的账号
    public function delRecordAccount() {
        if(M('white_list')->where(array(
            'userId' => session('oto_userId'),
            'id' => I('id')
        ))->setField([
            'is_del'=>1
        ])){
            $this->success('删除成功');
        }else{
            
            $this->error('删除失败');
        }
    }
    
     
}