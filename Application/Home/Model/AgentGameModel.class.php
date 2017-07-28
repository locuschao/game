<?php
/**
 * @author peng
 * @date 2017-01
 * @descreption 
 */
namespace Home\Model;
class AgentGameModel extends BaseModel
{
    public function checkGame($app_name,$chooseVersion){
        if(strpos($chooseVersion,'手游狗')!==false) return $this->shouyougouGameCheck($app_name);
        else if (strpos($chooseVersion,'TT')!==false) return $this->TTgameCheck($app_name);
        else if (strpos($chooseVersion,'乐8')!==false) return json_encode([
                'status'=>1
        ]);
        
        else return json_encode([
                'status'=>-1
            ]);
    }
    
    /**
     * @author peng
     * @date 2017-02
     * @descreption 验证是否是代理游戏列表,手游狗版本
     */
     public function shouyougouGameCheck($app_name){
        if(!M('shop_agent')->where(['shop_id'=>session('WST_USER.shopId')])->find()){
            return json_encode([
                'status'=>-2
            ]);
        }
        $url=C('CHECK_GAME_URL');
        $util_model=D('Common/Util');
        $timestamp=time();
        $nonce=mt_rand(6,10);
        return $util_model->curl_post( $url,[
            //验证参数
            'sign'=>$util_model->createSignture($nonce,$timestamp),
            'timestamp'=>$timestamp,
            'nonce'=>$nonce,
            //发送数据
            'agent_id'=>M('shop_agent')->find(['shop_id'=>session('WST_USER.shopId')])['agent_id'],
            'app_name'=>$app_name
            
        ] );
     }
     
     /**
      * @author peng
      * @date 2017-02
      * @descreption 验证TT版本游戏
      */
      public function TTgameCheck($app_name){
         if(M()->query('select * from __PREFIX__ttgame where gameName like "'.$app_name.'%" limit 1')) {
            return json_encode([
                'status'=>1
            ]);
            
         }else{
            return json_encode([
                'status'=>-1
            ]);
         }
              
      }
      /**
       * @author peng
       * @date 2017-02
       * @descreption 商品编辑页的秒充是否可以设置
       */
       public function isCanSetMiaoChong($gameId){
        
            $gameName=M('game')->find($gameId)['gameName'];
            foreach(M('game_versions')
            ->field('vName')
            ->join('gv left join '.C('DB_PREFIX').'versions v on gv.vid=v.id ')
            ->where(['gid'=>$gameId])
            ->select() as $row) {
                if(json_decode($this->checkGame($gameName,$row['vName']),true)['status']==1) {
                    return true;
                    
                }
            }
            return false;
       }
}