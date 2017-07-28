<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;
// 首充号代充验证
class ValidatadcController extends BaseController
{

    public function _initialize()
    {
        // 验证时初始化数据
    }

    public function test()
    {
        echo authCode(92, 'ENCODE');
    }

    public function yanzhen()
    {
        $goodsId = I('id');
        $gameId = I('gameId', 0, 'intval');
        $userId = authCode(I('userId'));
        if (! $gameId) {
            $gameId = M('goods')->where(array(
                'goodsId' => $goodsId
            ))->getField('gameId');
        }
        $versions = M('versions')->where(array(
            'isDel' => 0
        ))->select();
        $account = M('white_list')->where(array(
            'userId' => $userId,
            'g.gameId' => $gameId
        ))
            ->field('w.vid,w.account,v.vName')
            ->join(' as w left join oto_versions as v on v.id=w.vid')
            ->join('oto_goods as g on w.goodsId=g.goodsId')
            ->select();
        $arr = array();
        $arr['versions'] = $versions;
        $arr['account'] = $account;
        $this->returnJson($arr);
    }

    public function validataAccount()
    {
        $versions = I('versions');
        $account = I('account');
        $goodsId = I('id');
        $gameId = I('gameId', 0, 'intval');
        // 查找游戏ID
        if ($versions && $account) {
            if (! $gameId) {
                $gameId = M('goods')->where(array(
                    'goodsId' => $goodsId
                ))->getField('gameId');
            }
            $rs = M('white_list')->where(array(
                'vid' => $versions,
                'account' => $account,
                'gid' => $gameId
            ))->find();
            if ($rs) {
                $arr = array(
                    'goodsId' => $goodsId,
                    'vid' => $versions,
                    'account' => $account,
                    'gameId' => $gameId
                );
                $this->returnJson(array(
                    'status' => 0,
                    'vid' => $versions,
                    'account' => $account,
                    'goodsId' => $goodsId,
                    'gameId' => $gameId,
                    'shopId' => $rs['shopId']
                ));
            } else {
                $this->returnJson(array(
                    'status' => - 1
                ));
            }
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '验证失败'
            ));
        }
    }
}