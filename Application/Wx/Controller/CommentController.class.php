<?php
namespace Wx\Controller;

use Think\Controller;
use Think\Model;

class CommentController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
    }

    public function evaluate()
    {
        //AND ( date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`) )
        // 签收7天内可评价订单
        $sql = 'SELECT o.orderId, o.orderNo, o.areaId1, o.areaId2, o.areaId3, o.shopId, o.deliverMoney, o.payType, o.isSelf, o.deliverType, o.userName, o.userAddress, o.userPhone, o.needPay, s.shopName, s.shopImg FROM `oto_orders` as o left join oto_shops as s on o.shopId=s.shopId WHERE (`isRefund` = 0) AND (`isClosed` = 0) AND (`orderFlag` = 1) AND (`orderStatus` = 4)  AND orderId NOT IN ( SELECT orderId FROM oto_goods_appraises WHERE orderId = o.orderId AND goodsId  IN ( SELECT goodsId FROM oto_order_goods WHERE orderId = o.orderId ) ) and o.userId='.session('oto_userId').' order by o.orderId desc';
        $evaluate = M()->query($sql);
        $goodsDB = M('order_goods');
        $total=0;
        foreach ($evaluate as $k => $v) {
            $evaluate[$k]['goods'] = $goodsDB->where(array(
                'orderId' => $v['orderId']
            ))->select();
            $total+=count($evaluate[$k]['goods']);
        }
        $this->needEvaluate=$total;
        $this->evaluate = $evaluate;
        //可追加评价的订单
        $addtoSql="SELECT a.goodsId, a.orderId, g.goodsName, g.goodsThums, g.goodsAttrName FROM oto_goods_appraises AS a left JOIN oto_order_goods as g on a.orderId=g.orderId and a.goodsId=g.goodsId WHERE a.goodsId NOT IN ( SELECT goodsId FROM oto_share WHERE goodsId = a.goodsId AND orderId = a.orderId ) and a.userId=".session('oto_userId') .' order by a.id desc';
        $addtoInfo=M()->query($addtoSql);
        $this->addInfo=$addtoInfo;
        //晒单完成
        $finishSql="SELECT a.goodsId, a.orderId, g.goodsName, g.goodsThums,s.isShow, g.goodsAttrName FROM oto_goods_appraises AS a left JOIN oto_order_goods as g on a.orderId=g.orderId and a.goodsId=g.goodsId left JOIN oto_share as s on a.orderId=s.orderId and a.goodsId=s.goodsId  WHERE a.goodsId  IN ( SELECT goodsId FROM oto_share WHERE goodsId = a.goodsId AND orderId = a.orderId ) and a.userId=".session('oto_userId').' order by a.id desc';
        $finishInfo=M()->query($finishSql);
        $this->finish=$finishInfo;
        
        //服务评价
        $map['a.serviceScore']=0;
        $map['a.timeScore']=0;
        $map['a.packScore']=0;
        $map['_string']="date_sub(curdate(), INTERVAL 7 DAY) <= date(a.createTime) ";
        $field="g.goodsName,g.goodsThums,g.goodsAttrName,s.shopName,s.shopImg,g.goodsId,g.orderId";
        $serviceInfo=M('goods_appraises as a')->field($field)->where($map)->join('left join oto_order_goods as g on a.orderId=g.orderId and a.goodsId=g.goodsId left join oto_shops as s on s.shopId=a.shopId')->select();
        $this->serviceInfo=$serviceInfo;
        
        $this->display();
    }
    // 服务评价
    public function serviceEvaluate()
    {
        $gid=I('get.gid');
        $oid=I('get.oid');
        $url = explode('_', I('get.ref'));
        if (! $gid || ! $oid) {
            $this->redirect(U($url[0] . '/' . $url[1], '', '', 0));
            return;
        }
        $this->display();
    }
    
    //服务评价处理
    public function serviceEvaluateHandle(){
            if(!session('oto_userId')){
                $this->ajaxReturn(array('status'=>-3));
                return;
            }
            $oid=I('oid');
            $gid=I('gid');
            $isPJ=M('goods_appraises')->where(array('orderId'=>$oid,'goodsId'=>$gid))->getField('serviceScore');
            if($isPJ){
                $this->ajaxReturn(array('status'=>-2));
                return;
              }
            $data['packScore']=I('pack');
            $data['timeScore']=I('speed');
            $data['serviceScore']=I('service');
            if(!$oid||!$gid||!$data['packScore']||!$data['timeScore']||!$data['serviceScore']){
                $this->ajaxReturn(array('status'=>-1));
                return;
            }
            $res=M('goods_appraises')->where(array('orderId'=>$oid,'goodsId'=>$gid,'userId'=>session('oto_userId')))->data($data)->save();
            if($res){
                $this->ajaxReturn(array('status'=>0));
            }else{
                $this->ajaxReturn(array('status'=>-1));
            }
        }
    
    // 添加评价页面
    public function addEvaluate()
    {
        $gid = I('get.gid');
        $oid = I('get.oid');
        $url = explode('_', I('get.ref'));
        if (! $gid || ! $oid) {
            $this->redirect(U($url[0] . '/' . $url[1], '', '', 0));
            return;
        }
        $this->goodsInfo = M('order_goods')->where(array(
            'goodsId' => $gid
        ))->field('goodsThums,goodsName,goodsAttrName')->find();
        $this->display();
    }
    // 评价处理
    public function addEvaluateHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $goodsId = I('goodsId');
        $orderId = I('orderId');
        $img = I('img');
        $content = I('content');
        $anonymity = I('anonymity');
        $star = I('star');
        $info=M('orders')->where(array('orderId'=>$orderId))->field('shopId,userId')->find();
        
        $data['shopId'] = $info['shopId'];
        $data['userId'] = $info['userId'];
        $data['goodsId'] = $goodsId;
        $data['userId'] = session('oto_userId');
        $data['goodsScore'] = $star;
        $data['orderId'] = $orderId;
        $data['content'] = $content;
        $data['createTime'] = date('Y-m-d H:i:s', time());
        $data['anonymity'] = $anonymity;
        $DB = M('goods_appraises');
        $isExisA=$DB->where(array('orderId'=>$orderId,'goodsId'=>$goodsId))->find();
        if ($img) {
            $sDB = M('share');
            $isExisB=$sDB->where(array('goodsId'=>$goodsId,'orderId'=>$orderId))->find();
            if($isExisB){
                $this->ajaxReturn(array( 'status' => - 2 ));
                return;
            }
            $newImg = explode('|', $img);
            // 有图片晒单和评价2表都是要写数据
            $sdata['userId']=$info['userId'];
            $sdata['shopId']=$info['shopId'];
            $sdata['goodsId']=$goodsId;
            $sdata['orderId']=$orderId;
            $sdata['shareContent']=$content;
            $sdata['shareTitle']=$content;
            $sdata['star']=$star;
            $sdata['shareTime']= date('Y-m-d H:i:s', time());
            $sdata['anonymity']= $anonymity;
            $sdata['shareImg1']= $newImg[0];
            $sdata['shareEnvy']= ' ' ;
            $sdata['shareImg2']= $newImg[1];
            $sdata['shareImg3']= $newImg[2];
            $sdata['shareImg4']= $newImg[3];
            $A=false;
            $B=false;
            if($DB->create($data)){
                    $A=$DB->add();
             }
            if( $sDB->create($sdata)){
                $B=$sDB->add();
            }
            if ($A&&$B) {
                    $this->ajaxReturn(array('status' =>0 ));
                } else {
                    $this->ajaxReturn(array( 'status' => - 1 ));
                }
        } else {
            if($isExisA){
                $this->ajaxReturn(array( 'status' => - 2 ));
                return;
            }
            if ($DB->create($data)) {
                $A = $DB->add();
                if ($A) {
                    $this->ajaxReturn(array('status' =>0 ));
                } else {
                    $this->ajaxReturn(array( 'status' => - 1 ));
                }
            } else {
                $this->ajaxReturn(array('status' => - 1));
            }
            // 没有图片只写评价表
        }
    }
    
    //追加晒图
    public function addToEvaluate(){
        $gid = I('get.gid');
        $oid = I('get.oid');
        $url = explode('_', I('get.ref'));
        if (! $gid || ! $oid) {
            $this->redirect(U($url[0] . '/' . $url[1], '', '', 0));
            return;
        }
        $this->goodsInfo = M('order_goods')->where(array(
            'goodsId' => $gid
        ))->field('goodsThums,goodsName,goodsAttrName')->find();
        $this->comment=M('goods_appraises')->where(array('orderId'=>$oid,'goodsId'=>$gid))->find();
        $this->display();
    }
    //处理追加晒图
    public function addToEvaluateHandle(){
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $goodsId = I('goodsId');
        $orderId = I('orderId');
        $img = I('img');
        $content = I('content');
        $anonymity = I('anonymity');
        $info=M('orders')->where(array('orderId'=>$orderId))->field('shopId,userId')->find();
        if ($img) {
            $sDB = M('share');
            $isExisB=$sDB->where(array('goodsId'=>$goodsId,'orderId'=>$orderId))->find();
            if($isExisB){
                $this->ajaxReturn(array( 'status' => - 2 ));
                return;
            }
            $newImg = explode('|', $img);
            // 有图片晒单和评价2表都是要写数据
            $sdata['userId']=$info['userId'];
            $sdata['shopId']=$info['shopId'];
            $sdata['goodsId']=$goodsId;
            $sdata['orderId']=$orderId;
            $sdata['shareContent']=$content;
            $sdata['shareTitle']=$content;
            $sdata['shareTime']= date('Y-m-d H:i:s', time());
            $sdata['anonymity']= $anonymity;
            $sdata['shareImg1']= $newImg[0];
            $sdata['shareEnvy']= ' ' ;
            $sdata['shareImg2']= $newImg[1];
            $sdata['shareImg3']= $newImg[2];
            $sdata['shareImg4']= $newImg[3];
            $B=false;
            if( $sDB->create($sdata)){
                $B=$sDB->add();
            }
            if ($B) {
                $this->ajaxReturn(array('status' =>0 ));
                M('goods_appraises')->where(array('orderId'=>$orderId,'goodsId'=>$goodsId))->setField('anonymity',$anonymity);
            } else {
                $this->ajaxReturn(array( 'status' => - 1 ));
            }
        }else{
            $this->ajaxReturn(array( 'status' => - 4 ));
        }
    }
    
       //评价详情
       public function commentDetail(){
           $gid = I('get.gid');
           $oid = I('get.oid');
           $url = explode('_', I('get.ref'));
            if (! $gid || ! $oid) {
               $this->redirect(U($url[0] . '/' . $url[1], '', '', 0));
               return;
           } 
           $sql="select a.goodsScore,a.createTime,a.content,a.anonymity,s.shareImg1,s.shareImg2,s.shareImg3,s.shareImg4,u.userName,u.userPhoto from oto_goods_appraises as a left join oto_share as s on a.orderId=s.orderId and a.goodsId=s.goodsId left join oto_users as u on a.userId=u.userId  where  a.orderId={$oid} and a.goodsId={$gid}";
           $info=M()->query($sql);
           if($info[0]['anonymity']==1){
               $name=$info[0]['userName'];
               $lenth=mb_strlen($name,'utf-8');
               if($lenth<=3){
                   $repeat='***';
               }else{
                   $repeat=str_repeat('*', $lenth-2);
               }
               $newName=$this->cut_str($name,1,0).$repeat.$this->cut_str($name, $lenth,$lenth-1);
               $info[0]['userName']=$newName;
           }
           $this->info=$info;
           $this->display();
       }
       public function test(){
           
           $str="我是中国人？";
           echo $this->cut_str($str, 6, 5);
               
       }
       public  function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
           if($code == 'UTF-8')
                {
                    $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
                    preg_match_all($pa, $string, $t_string);
            
                    if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
                    return join('', array_slice($t_string[0], $start, $sublen));
                }
                else
                {
                    $start = $start*2;
                    $sublen = $sublen*2;
                    $strlen = strlen($string);
                    $tmpstr = '';
            
                    for($i=0; $i< $strlen; $i++)
                    {
                        if($i>=$start && $i< ($start+$sublen))
                        {
                            if(ord(substr($string, $i, 1))>129)
                            {
                                $tmpstr.= substr($string, $i, 2);
                            }
                            else
                            {
                                $tmpstr.= substr($string, $i, 1);
                            }
                        }
                        if(ord(substr($string, $i, 1))>129) $i++;
                    }
                    if(strlen($tmpstr)< $strlen ) $tmpstr;
                    return $tmpstr;
            }
        }
    
    
    // 上传评价的三张图片
    public function evaluateUploadImg()
    {
        if (! session('oto_userId')) {
            echo "文件上传失败";
            return;
        }
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        $data = date('Y-m', time());
        if ($upload->upload('./upload/comment/' . $data . '/')) {
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if ($info['0']['type'] != 'image/jpeg' && $info['0']['type'] != 'image/jpg' && $info['0']['type'] != 'image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png') {
            echo "2";
            exit();
        }
        if ($info['0']['size'] > $MAX_SIZE)
            echo "上传的文件大小超过了规定大小";
        
        if ($info['0']['size'] == 0)
            echo "请选择上传的文件";
        switch ($info['0']['error']) {
            case 0:
                echo 'upload/comment/' . $data . '/' . $file_newname;
                break;
            case 1:
                echo "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case 2:
                echo "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 3:
                echo "文件只有部分被上传";
                break;
            case 4:
                echo "没有文件被上传";
                break;
        }
    }
}