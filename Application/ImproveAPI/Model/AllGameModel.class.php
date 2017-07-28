<?php
namespace ImproveAPI\Model;
use Think\Model;
class AllGameModel extends Model
{
    protected $gameids;
    protected $is_keyword_search = false;
    protected $del_key = [
         'is_huobao',
         'hot',
         'shop_recommend'
    ];
    public function queryByPage() {
        $post = getData();
        $_GET['p'] = $post['p'];
        $condition['g.shopPrice'] = 0;
        $condition['g.goodsFlag'] = 1;
        $condition['g.scopeId'] = 1;
        $condition['g.onSale'] = 1;
        $condition['g.shopId'] = getShopId();
        $order_str = '';
        
        if($post['is_tuijian'] == 1){
            $condition['shop_recommend'] = ['gt',0];
        }
        if($post['goodsId']){
            $condition['g.goodsId'] = $post['goodsId'];
        }
        if($post['keyword']){
            $condition['gameName'] = ['like','%'.$post['keyword'].'%'];
            $this->is_keyword_search = true;
        }
        
        if($post['hot']){
            $condition['hot'] = ['gt',0];
        }
        if($post['gameType']){
            $condition['gameType'] = $post['gameType'];
        }
        
        if($post['newest']){
            $order_str = 'g.createTime desc';
        }
        if($zhekou_order = $post['zhekou_order']){
            $order_str .= 'if(heigh_member_price>0,heigh_member_price,attrPrice) '.$zhekou_order;
        }
        if($post['letter']){
            $PY = <<<Eof
    	    ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
            0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
            0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
            0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
            'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
            'Q','R','S','T','W','X','Y','Z') 
Eof;
            $condition['_string'] = "$PY = '$post[letter]'";
        }
        
        $db_prefix = C('DB_PREFIX');
        $join_str = 'g left join '.$db_prefix.'game ga on g.gameId=ga.id left join '.$db_prefix.'goods_versions gv on gv.goodsId=g.goodsId';
        
        $page = new \Think\Page(M('goods')->join($join_str)->where($condition)->count(), 15);
        $data = M('goods')->join($join_str)
        ->field('g.goodsId,g.gameId,ga.gameName,ga.downloadNumber,ga.gameCapacity,ga.gameIco,
        g.applyTo,g.is_huobao,g.hot,g.shop_recommend,if(heigh_member_price,heigh_member_price,attrPrice) zhekou')
        ->where($condition)->order($order_str)
        ->limit($page->firstRow,$page->listRows)
        ->select();
        array_push($this->del_key,'gameId');
        $data = $this->filter($data);
        
        if($this->gameids){
            //被搜索一次，搜索量就加1
            M('game')->where([
            'id'=>['in',array_unique($this->gameids)]
            ])->setInc('searchAmount');
            if(D('Login')->isLogin()['status'] == 1){
                M('search_keyword')->add([
                    'keyword'=>$post['keyword'],
                    'createTime'=>time()
                ]);
            }
        }
        return $data;
        
    }
    public function filter($data) {
        if(!$data) return false;
        foreach($data as $k=>$row){
            if(($row['shop_recommend'] == 2)) $data[$k]['tagName'] = 'recommend';
            else if($row['hot'] == 2) $data[$k]['tagName'] = 'hot';
            else if($row['is_huobao']) $data[$k]['tagName'] = 'huobao';
            else  $data[$k]['tagName'] = '';
            $data[$k]['zhekou'] = (int)$row['zhekou']; 
            $row['applyTo'] = '苹果,安卓';
            $data[$k]['applyTo'] = str_replace(['苹果','安卓',','],['IOS','Android',' '],$row['applyTo']); 
            $data[$k]['gameIco'] = C('RESOURCE_URL').$data[$k]['gameIco'];
            $data[$k]['zhekou'] = number_format($row['zhekou'],'1');
            if($this->is_keyword_search) $this->gameids[] = $row['gameId'];
            foreach($this->del_key as $v){
                unset($data[$k][$v]);
            }
           
        }
        return $data;
    }    
}