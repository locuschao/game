<?php
namespace ImproveAPI\Model;
use Think\Model;
class GameGiftModel extends Model
{
    /**
     * @des 分页查询列表
     * @date 2017-7-26
     * @author Meke
     */
    public function queryByPage() {
        $post = getData();
        $_GET['p'] = empty($post['p'])?1:$post['p'];
        //上架
        $condition['b.shelves'] = 0;
        //是否当前时间的礼包
        $condition['endTime']  = ['gt',date('Y-m-d H:i:s',time())];

        //热门查询
        if($post['isHot']){
           $condition['b.isHot'] = 1;
        }

        //字母查询
        if($post['letter']){
            $condition['letter'] = $post['letter'];
        }

        //游戏类型查询
        if($post['gameType']){
           $gameTypeAttr = M('game_type_attr')->where(['gameType'=>$post['gameType']])->select();
           foreach ($gameTypeAttr as $key => $value){
               $typeAttr[$key]=$value['gameId'];
           }
        }
        //最新查询
        if($post['newest']){
            $order_str = 'b.beginTime desc';
        }

        $db_prefix = C('DB_PREFIX');
        $join_str = 'b left join '.$db_prefix.'game a on a.id=b.gameId ';
        $page = new \Think\Page(M('game_gift')->join($join_str)->where($condition)->count(),6);
        $data = M('game_gift')
            ->join($join_str)
            ->field('a.gameIco,b.id,b.gameId,b.Integral,b.beginTime,b.endTime,b.totalNumber,b.remainNumber,b.gameName')
            ->where($condition)
            ->order($order_str)
            ->limit($page->firstRow,$page->listRows)
            ->select();
        foreach ($data as $key=>$row){
            $data[$key]['beginTime']=date('Y-m-d',strtotime($row['beginTime']));
        }
        //有类别查询条件
        if(!empty($typeAttr)){
            foreach ($data as $key=>$row){
                if(!in_array($row['gameId'],$typeAttr)) {
                 unset($data[$key]);
                }
            }
        }
        foreach ($data as $key=>$row){
            $typeids[] = $row['id'];
        }
        $map['gameType']  = ['in',$typeids];
        $map['userId'] =session('userId');
        //查询用户是否领取
        foreach(M('game_gift_log')->where($map)->select() as $row){
            $get_assoc[$row['gameType']] = 1;
        }

        foreach($data as $k=>$row){
            $data[$k]['gameIco'] = C('RESOURCE_URL') . $row['gameIco'];
            $data[$k]['percent'] = round(($row['remainNumber'] / $row['totalNumber']) * 100) . '%';
            $data[$k]['is_get'] = $get_assoc[$row['id']] ? 1 : 0;
            unset($data[$k]['totalNumber']);
            unset($data[$k]['remainNumber']);
        }

        foreach ($data as $kk=>$vv){
           if($vv['beginTime']==$data[$kk]['beginTime']){
               $arr[$vv['beginTime']][$kk]=$vv;
           }
        }
        array_multisort($arr,SORT_DESC);//排序  SORT_ASC 按照上升顺序排序， SORT_DESC 按照下降顺序排序
        foreach ($arr as $kkk =>$vvv){
            $return[] = array_merge(array('beginTime'=>$kkk),array('data'=>$vvv));
        }
        return $return;
    }

    /**
     * @des 查询单页详情
     * @date 2017-7-26
     * @author Meke
     */
    public function querybyDetail($condition){
        $db_prefix = C('DB_PREFIX');
        $join_str = 'b left join '.$db_prefix.'game a on a.id=b.gameId ';
        $data = M('game_gift')
            ->join($join_str)
            ->field('a.gameIco,b.id,b.gameId,b.beginTime,b.endTime,b.totalNumber,b.remainNumber,b.gameName,b.content,b.description')
            ->where($condition)
            ->select();
        foreach($data as $k=>$row){
            $data[$k]['gameIco'] = C('RESOURCE_URL').$row['gameIco'];
            $data[$k]['percent'] = round(($row['remainNumber']/$row['totalNumber'])*100).'%';
            unset($data[$k]['totalNumber']);
            unset($data[$k]['remainNumber']);
        }
        return $data;
    }

    /**
     * @des 查询用户的领取的礼包
     * @date 2017-7-27
     * @author Meke
     */
    public function queryByUserGift(){
        $db_prefix = C('DB_PREFIX');
        $condition['b.userId'] = session('userId');
        $join_str = 'b left join '.$db_prefix.'game_gift a on a.id=b.gameType left join '.$db_prefix.'game c on c.id=a.gameId ';
        $order_str = 'b.updateTime desc';
        $page = new \Think\Page(M('game_gift_log')->join($join_str)->where($condition)->count(),6);
        $data = M('game_gift_log')
            ->field('a.endTime,a.gameName,a.gameId,b.giftCode,c.gameIco')
            ->join($join_str)
            ->where($condition)
            ->limit($page->firstRow,$page->listRows)
            ->order($order_str)
            ->select();
        foreach($data as $k=>$row){
            $data[$k]['gameIco'] = C('RESOURCE_URL').$row['gameIco'];
        }
        return $data;
    }
}
