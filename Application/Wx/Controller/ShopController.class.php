<?php
namespace Wx\Controller;
use Think\Controller;
use Think\Model;
class ShopController extends BaseController
{
    
    public function shop(){
        $id=I('get.id',0,intval);
        $shopInfo=M('shops')->where(array('shopId'=>$id,'shopFlag'=>1,'shopAtive'=>1,'shopStatus'=>1))->find();
        $this->shopInfo=$shopInfo;
        
        $shopCate=M('shops_cats')->where(array('shopId'=>$id,'catFlag'=>1))->select();
        $cate=$this->unlimitedForLayer($shopCate,0);
        $this->cate=$cate;
//         p($cate);return;
        $cid=I('get.cid');
        $map['shopId']=$id;
        $map['goodsStatus']=1;
        $map['goodsFlag']=1;
        if(!empty($cid)){
            $map['shopCatId2']=$cid;
        }
        $goods=M('goods')->where($map)->select();
        $this->goods=$goods;
       $this->display();
    }
    
    
     public function unlimitedForLayer ($cate, $name = 'child', $pid = 0) {
        $arr = array();
        $name = 'child';
        foreach ($cate as $v) {
            if ($v['parentId'] == $pid) {
                $v[$name] = self::unlimitedForLayer($cate, $name, $v['catId']);
                $arr[] = $v;
            }
        }
        return $arr;
    }
}