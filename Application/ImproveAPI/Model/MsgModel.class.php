<?php
namespace ImproveAPI\Model;
use Think\Model;
class MsgModel extends Model
{   
    /**
     * @author peng
     * @date 2017-07
     * @descreption [
     *   'moduleId'=>2, 模块id
        'info'=>$orderInfo,
        'changeType'=>-7 改变订单状态
     * ]
     */
    public function addMsgHook($arr) {
        $info = $arr['info'];
        $normal_order_text = '您好,您有一个编号为:';
        $content = '';
        $title = '';
        
        switch($arr['moduleId']){
            case 1:
                break;
            case 2:
                if($arr['changeType'] == -7) {
                    $title = '商家取消订单';
                    $content = $normal_order_text.$info['orderNo'].'的订单被商家取消。';
                }else if($arr['changeType']== -5){
                    $title = '退款成功';
                    $content = $normal_order_text.$info['orderNo'].'的订单退款成功。';
                }else if($arr['changeType'] == -6){
                    $title = '拒绝退款';
                    $content = $normal_order_text.$info['orderNo'].'的订单拒绝退款。';
                }else if($arr['changeType'] == 2){
                    $title = '订单发货';
                    $content = $normal_order_text.$info['orderNo'].'的订单发货了。';
                }
                $firstLevelId = $info['orderNo'];
                break;
            case 3:
                $content = '恭喜您,您有一个提现订单已通过。';
                $title = '提现成功';
                break;
            case 4:
                $content = '恭喜您,您的狗粮发送成功。';
                $title = '狗粮发送成功';
                break;
        }
        
        M('msg')->add([
            'moduleId'=>$arr['moduleId'],
            'msgContent'=>$content.'点击查看详情按钮,查看详情订单。',
            'title'=>$title,
            'createTime'=>time(),
            'firstLevelId'=>$firstLevelId?:'',
            'isRead'=>0,
            'userId'=>$info['userId']
        ]);
        
    }
}