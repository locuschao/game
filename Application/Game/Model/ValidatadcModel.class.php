<?php
namespace Game\Model;
/**
 * @author peng
 * @date 2017-01
 * @descreption sdk验证接口
 */
class ValidatadcModel extends BaseModel
{
    public function _validataAccount($arr) {
        $versions = $arr['versions'];
        $account = $arr['account'];
        $goodsType = $arr['goodsType'];
        $goodsId = $arr['goodsId'];
        $gameId = $arr['gameId'];
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
            
            //author: peng descreption:如果是来自提交订单调用,则执行
            if($arr['from'] == 'submit_order' && $rs) {
                return array(
                    'status' => - 2 #表示自建账号已经在白名单中了
                );
            }
            
            /**
             * @author peng
             * @date 2017-01
             * @descreption 
             */
            $arr1=[];
            if(!$rs){
                $version_name = M('versions')->find($versions)['vName'];
                $gameName = M('game')->find($gameId)['gameName'];
                $arr1=[
                    'vName'=>$version_name
                ];
                if(in_array($versions,[39,41])){
                    $rs_tmp = true;
                    if($goodsId == '' || !$shopId = M('goods')->where(['goodsId'=>$goodsId])->find()['shopId']) {
                        $rs_tmp = false;
                    }else{
                        
                        $agent_info = D('Api/AutoBase')->getAgentInfo([ #设置并且获得代理信息
                        'shopId'=>$shopId, 
                        'vid'=>$versions
                        ]);
                       
                    }
                }
                
                
                if(strpos($version_name,'手游狗')!==false) {
                    if($agent_id=D('Game/SdkValidate')->checkAccount([
                            'username'=>$account,
                            'app_name'=>$gameName
                     ])){
                        
                        $rs['shopId']=M('shop_agent')
                        ->where(['agent_id'=>$agent_id])
                        ->getField('shop_id');
                        
                     }else{
                        $rs=false;
                        
                     }
                }else if(strpos($version_name,'TT')!==false) {
                    #TT版本的账号验证
     
                    if($rs_tmp !== false){
                        if(D('Api/TcoinRecharge')->findOutGameIdAndName([
                            'gameName'=>$gameName,
                            'accounts'=>$account
                        ])['status'] == 1) {
                            
                        $rs['shopId'] = M('shop_version_agent')->where([
                            'agentId'=>$agent_info['id'],
                            'versionId'=>$versions
                        ])->getField('shopId',true);
                        
                        } else {
                            $rs = false;
                        }
                    }else{
                        $rs = false;
                    }
                }else if(strpos($version_name,'乐8')!==false) {
                    #乐8账号验证
                    if($rs_tmp !== false){
                        if(D('Api/LeRecharge')->checkUsername($account)) {
                        $rs['shopId'] = M('shop_version_agent')->where([
                            'agentId'=>$agent_info['id'],
                            'versionId'=>$versions
                        ])->getField('shopId',true);
                        } else {
                            $rs = false;
                        }
                    }else{
                        $rs = false;
                    }
                    
                    
                }else{
                    return array(
                    'status' => - 1
                    );
                }
                
                
            }
            
            
            if(!$goodsId){
                $goodsId=$rs['goodsId'];
            }
            if($goodsId){
                $goodsType=M('goods')->where(array('goodsId'=>$goodsId))->getField('goodsType');
            }
            
            if ($rs) {
                $arr = array_merge($arr1,array(
                    'goodsId' => $goodsId,
                    'vid' => $versions,
                    'account' => $account,
                    'gameId' => $gameId,
                    'shopId' => $rs['shopId'],
                    'goodsType'=>$goodsType
                ));
                
                session('validateGoods', $arr);
                return array(
                    'status' => 0,
                    'vid' => $versions,
                    'account' => $account
                );
            } else {
                return array(
                    'status' => - 1
                );
            }
        } else {
            return array(
                'status' => - 1
            );
        }
    }
}
