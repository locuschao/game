<?php

namespace Wx\Controller;

use Think\Controller;
use Think\Model;
class ServiceController extends BaseController {
    public function index(){
        $kf=M('sys_configs')->where(array('configId'=>array(in,'30,31')))->field('fieldCode,fieldValue')->select();
        $this->kf=$kf['0']['fieldCode']=='phoneNo'?$kf['0']['fieldValue']:'';
        $this->qq=$kf['1']['fieldCode']=='qqNo'?$kf['1']['fieldValue']:'';
        $this->list=M('article_cats')->field('catId,catName')->where(array('catFlag'=>1,'parentId'=>0))->select();
        $this->display();
    }
    public function articleCate(){
        $cid=I('get.cid');//分类ID
        $this->list=M('articles')->field('articleId,articleTitle')->where(array('isShow'=>1,'catId'=>$cid))->select();
        $this->display();
    }
    public function article(){
        $id=I('get.id');
        $info=M('articles')->where(array('articleId'=>$id))->field('articleTitle,articleContent')->find();
        $info['articleContent']=html_entity_decode($info['articleContent']);
        $this->info=$info;
        $this->display();
    }
    public  function suggest(){
        
        $this->display();
    }
    
    public function suggestHandle(){
        $phone=I('phone');
        $content=I('content');
        $data['userId']=session('oto_userId');
        $data['phone']=$phone;
        $data['content']=$content;
        $db=M('suggest');
        if($db->create($data)){
            $r=$db->add();
            if($r){
                $this->ajaxReturn(array('status'=>0));
            }else{
                $this->ajaxReturn(array('status'=>-1));
            }
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
}