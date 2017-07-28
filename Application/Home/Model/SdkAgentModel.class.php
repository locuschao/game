<?php
namespace Home\Model;
class SdkAgentModel extends BaseModel
{
    /**
    * @author peng
    * @copyright 2016
    * @remark 验证sdk agent用户名和密码
    */
    public function checkAgent($post_data){
    	
        $url=C('BIND_SDKAGENT_URL');
        $util_model=D('Common/Util');
        $first_data=$util_model->curl_post($url);
        $data=json_decode($first_data,true);
        
        if ( $data['auth_id'] ) {
            $timestamp=time();
            $re = $util_model->curl_post( $url,array_merge([
                //验证参数
                'sign'=>$util_model->createSignture($data['auth_id'],$timestamp),
                'timestamp'=>$timestamp,
                'auth_id'=>$data['auth_id'],
                
            ],$post_data) );
            
            return json_decode($re,true);
            
        }else{
            return [
                'error'=>1,
                'msg'=>'握手失败'
            ];
        }

    }
    /**
     * @author peng
     * @date 2017-01
     * @descreption 自动发货
     */
    public function autoFahuo($orderId){
        $orderInfoParam=M('orders')->find($orderId);
        if($orderInfoParam['userId'] == 1105) return;//测试
        $re=$this->chooseFahuoWay($orderInfoParam);
        if ($re) {
            M('autofahuo_log')->add([
                'userid'=>$orderInfoParam['userId'],
                'order_id'=>$orderInfoParam['orderId'],
                'time'=>time(),
                'status'=>$re['status']==0?1:0,
                'remark'=>$re['msg']
            ]);
           
        }
        return $re;
           
    }
    /**
     * @author peng
     * @date 2017-02
     * @descreption 选择自动发货的方式
     */
    public function chooseFahuoWay($orderInfoParam){
        $orderId=$orderInfoParam["orderId"];
        
        $obj["userId"] = (int) $orderInfoParam['userId'];
        $obj["shopId"] = (int) $orderInfoParam['shopId'];
        $obj["orderId"] = $orderId;
        $order_info = D('Home/Orders')->getOrderDetails($obj)['order'];
        
        if($order_info['isMiao']!=1){
            return;
        }
        
        if ($order_info['orderStatus'] !=1 || empty($order_info) ){
            return array(
                'status' => - 5,
                'msg'=>'订单状态已改变'
            );
            
        }
        $order_info['shopId'] = $obj["shopId"];
        
        if(strpos($order_info['vName'],'手游狗')!==false){
            return $this->getGameIdentity($orderId,$obj['shopId'],$order_info);
        }else if(strpos($order_info['vName'],'TT')!==false) {
            #返回TT自动发货的方法
            return D('Api/TcoinRecharge')->TTautoFahuo($orderId,$order_info);
        }else if(strpos($order_info['vName'],'乐8')!==false) {
            
            return D('Api/LeRecharge')->LeAutoFahuo($orderId,$order_info);
        }else{
            return array(
                'status' => - 2,
                'msg'=>'该订单不能自动发货'
            );
        }
    
    }
    

    
    /**
     * @author peng
     * @date 2017-01
     * @descreption 通过sdk发货接口获取游戏账号或充值
     */
    public function getGameIdentity($orderId,$shopId,$order_info){
        
        //查出代理信息
        $agent_info=M('shop_agent')->where([
            'shop_id'=>$shopId
        ])->find();
        if(!$agent_info){
            return array(
                'status' => - 1,'msg'=>'还没绑定代理'
            );
        }
       
        $result_info=D('Home/AutoFahuo')->autoHandle([
            
                //业务参数
                //'app_id'=>60020,
                'app_name'=>$order_info['gameName'],
                //'mem_id'=>I('mem_id'),
                'amount'=>$order_info['totalMoney'],
                //'agent_id'=>122,
                'agent_id'=>$agent_info['agent_id'],
                //'agentgame'=>'yaowan001'
                'agentgame'=>$agent_info['agentgame'],
                'username'=>$order_info['account']?:'',
                //'username'=>103368
                'order_type'=>$order_info['orderType'],
                
                'selfBuildAccount'=>$order_info['selfBuildAccount']
                
        ]);
        
        if($result_info['error']==0) {
            if ($result_info['info']['username']){
                $info=join('|',[
                    $order_info['userAddress'],
                    $result_info['info']['username'],
                    $result_info['info']['passwd']
                ]).';';
            }else{
                $info='';
            }
            
            return $this->_fahuoHandle($orderId,$info);
        }else{
            //首充失败
            return array(
                'status' => - 1,'msg'=>$result_info['info']
            );
            
        }
      
    }
    
    public function _fahuoHandle($orderId,$info){
        
        $info = trim($info, ';');
        $data = array();
        $A = true;
        $B = true;
        M()->startTrans();
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        
        if ($info) {
            $arr = explode(';', $info);
            
            foreach ($arr as $k => $v) {
                $temp = explode('|', $v);
                
                $data['orderId'] = $orderId;
                $data['account'] = $temp[1];
                $data['password'] = $temp[2];
                $data['area'] = $temp[0];
                $data['ftime'] = date('Y-m-d H:i:s');
                $tempA = M('fahuo')->add($data);
                    if (! $tempA) {
                        $A = false;
                    }
            }
        }
           
        // 更改订单状态
        $update_data = array(
            'orderStatus'=>2,
            'fahuoTime'=>date('Y-m-d H:i:s'),
            'isRead'=>0,                 //魏永就    发货动作，订单消息设置为未读
            'lastMessTime'=>time(),
            /**
            * @author peng
            * @date 2017-02
            * @descreption 发货的类型
            */
            'fahuoType'=> 2,//秒充类型
        );
        $B = M('orders')->where(array(
            'orderId' => $orderId
        ))->setField($update_data);
        // 添加 订单日志
        $log = array();
        $log['orderId'] = $orderId;
        $log['logContent'] = '订单已经发货';
        $log['logUserId'] = $orderInfo['userId'];
        $log['orderId'] = $orderId;
        $log['logType'] = 0;
        $log['logTime'] = date('Y-m-d H:i:s');
        
        M('log_orders')->add($log);
        
        // 添加消息推送给客户
        $mes = array();
        $mes['type'] = 1;
        $mes['orderId'] = $orderId;
        $mes['content'] = $orderId . '订单已经发货';
        $mes['time'] = date('Y-m-d H:i:s');
        $mes['isRead'] = 0;
        $mes['userId'] = $orderInfo['userId'];
        M('mess')->add($mes);
        
        $C = A('Home/orders')->whiteList($orderInfo, $data['account']);
        
        if ($A && $B && $C ) {
            
            M()->commit();
            return array(
                'status' => 0,'msg'=>'发货成功 !'
            );
        } else {
            M()->rollback();
            return array(
                'status' => - 1,'msg'=>'发货失败!'
            );
        }
    }
    
    
    
    
    
}