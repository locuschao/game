<?php
namespace Wx\Model;
use Think\Model;
/**
 * 圈子模块类
 */
class CircleModel extends Model {

    public function __construct(){

        parent::__construct();
        $this->uid=isset($_SESSION['oto_mall']['oto_userId'])?$_SESSION['oto_mall']['oto_userId']:'';
     //   unset($_SESSION['oto_mall']);

    }



    public function listData(){
    
        $data = array();

        $uid=$this->uid;

        $sql = "SELECT circle.looks,circle.id,circle.imgThums,circle.title,shop.shopId,shop.shopName,circle.likes,shop.shopImg,FROM_UNIXTIME(circle.time,'%Y年%m月%d') as time  from __PREFIX__circle as circle,__PREFIX__shops as shop  WHERE (circle.isShow=1) AND (shop.shopId = circle.shopId) order by `likes` desc ";

        $data['hot'] = M()->query($sql);
        foreach($data['hot'] as $key=>$value){
                    $temp = M('circle_likes')->where(array('userId'=>$uid,'shopId'=>$value['shopId'],'circleId'=>$value['id']))->find();
            $value['uid'] = $uid;
                if($temp){

                    $value['likeStatus'] = true;
                  
                }else{
                    $value['likeStatus'] = false;
                }

            
            $data['hot'][$key] = $value;
        }



//
//        dump($data['hot']);





        $sql  = "SELECT circle.looks,circle.id,circle.imgThums,circle.shopId,circle.title,shop.shopId,shop.shopName,circle.likes,shop.shopImg,FROM_UNIXTIME(circle.time,'%Y年%m月%d') as time from __PREFIX__circle as circle,__PREFIX__shops as shop  WHERE (circle.isShow=1) AND (shop.shopId = circle.shopId) order by `time` desc";

        $data['new']   =  M()->query($sql);
        foreach($data['new'] as $key=>$value){
            $temp = M('circle_likes')->where(array('userId'=>$uid,'shopId'=>$value['shopId'],'circleId'=>$value['id']))->find();
            $value['uid'] = $uid;
            if($temp){

                $value['likeStatus'] = true;
                
            }else{
                $value['likeStatus'] = false;
            }


            $data['new'][$key] = $value;
        }









//        dump($data);


        return $data;
    }


    public function likes(){
        
        $data = I('post.data');
        $uid = (int)$data['uid']?$data['uid']:0;
        $status['status']=false;
        $circleId = (int)$data['circleId']?$data['circleId']:0;

        $shopId = (int)$data['shopId']?$data['shopId']:0;

        if($shopId && $uid && $circleId ){
            $add_data['userId'] = $uid;
            $add_data['circleId'] = $circleId;
            $add_data['shopId'] = $shopId;
            $add_data['time'] = time();

            switch($data['action']){
                    
                case 'add':
                  $status['status'] = M('circle_likes')->data($add_data)->add();
                     $s=       M('circle')->where(array('id'=>$circleId,'shopId'=>$shopId))->setInc('likes');
                    break;
                    
                case 'delete':
                  $status['status'] =  M('circle_likes')->where(array('userId'=>$uid,'circleId'=>$circleId,'shopId'=>$shopId))->delete();
                   $s =  M('circle')->where(array('id'=>$circleId,'shopId'=>$shopId))->setDec('likes');
                    break;
                default:
                    
                    break;
            
            }
            





        }
        
        
        
        return $status;
    }


    public function Detail(){
        $action = I('get.action');
        switch($action){
            case 'like':
                return   $this->likes();
                break;
            default:
                break;
        }
        $uid =$this->uid;
        $id = I('get.circleId');


        M('circle')->where(array('id'=>$id))->setInc('looks');
//        M('circle')->where(array('id'=>$id))
        $sql = "SELECT *,FROM_UNIXTIME(circle.time) as time from `__PREFIX__circle` as circle, __PREFIX__shops as shop WHERE (circle.isShow=1) AND   (shop.shopId = circle.shopId) AND circle.id='{$id}' LIMIT  1 ";
        $temp = M()->query($sql);
//        __PREFIX__shops as shop  WHERE (circle.isShow=1) AND (shop.shopId = circle.shopId)
        $data['circleInfo']  = $temp[0];
//        echo M()->getLastSql();
        if($data['circleInfo'] && $uid){
            $sql = "SELECT * from `__PREFIX__circle_likes`  WHERE `shopId`='{$data['circleInfo']['shopId']}' AND `circleId`='{$data['circleInfo']['id']}' AND `userId`='{$uid}' ";
            $temp = M()->query($sql);
            $data['likeInfo'] = $temp[0];

            if($data['likeInfo']){

                $data['likeInfo']['likeStatus']=true;
            }else{
                $data['likeInfo']['userId'] = $uid;
                $data['likeInfo']['shopId'] = $data['circleInfo']['shopId'];
                $data['likeInfo']['circleId'] = $data['circleInfo']['id'];
                $data['likeInfo']['likeStatus']=false;
            }

        }

        $sql  = "SELECT * from `__PREFIX__goods` WHERE `goodsId`='{$data['circleInfo']['goodsId']}'";
        $temp = M()->query($sql);
        $data['goodsInfo'] = $temp[0];
//        echo M()->getLastSql();
//        dump($data);

        return $data;
    }









	
}