<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/20
 * Time: 19:48
 */
namespace Admin\Model;

class ComplainModel extends BaseModel
{

    /**
     * ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½Ð¹ï¿½ï¿½ï¿½ï¿½Ã»ï¿½ï¿½ï¿½Í¶ï¿½ï¿½ï¿½ï¿½Ï?
     * 
     * @param $id int
     *            ï¿½Ì¼ï¿½ID
     * @return mixed array
     */
    public function selectAll()
    {
        // ï¿½Ð¶Ï²ï¿½Ñ¯ï¿½ï¿½ï¿½ï¿½
        $orderNo = I('get.orderNo');
        $starttime = I('get.starttime');
        $endtime = I('get.endtime');
        $isHandle = I('get.isHandle');
        
        $sql = '';
        if ($orderNo != '') {
            $sql .= " and o.orderNo = {$orderNo}";
        }
        if ($starttime && $endtime) {
            $sql .= " and c.time between '$starttime' and '$endtime' ";
        } else 
            if (! $starttime && $endtime) {
                $sql .= " and c.time <='$endtime' ";
            } else 
                if ($starttime && ! $endtime) {
                    $sql .= " and c.time>='$starttime' ";
                }
        if ($isHandle == - 1) {
            $sql .= '';
        } else 
            if ($isHandle == 0) {
                $sql .= ' and c.isHandle=0 ';
            } else 
                if ($isHandle == 1) {
                    $sql .= ' and c.isHandle=1  ';
                }
        $data['count'] = $this->join("as c left join oto_orders as o on c.orderid=o.orderId")
            ->where("1 {$sql}")
            ->count();
        $Page = new \Think\Page($data['count'], 20);
        $data['show'] = $Page->show();
        $data['list'] = $this->join("as c left join oto_orders as o on c.orderid=o.orderId")
            ->join('oto_users as u on u.userId=c.userId')
            ->where("1 {$sql}")
            ->field('c.*,u.userName,u.userPhone,o.orderNo')
            ->limit($Page->firstRow, $Page->listRows)
            ->order('time desc')
            ->select();
        // 不分页取出所有查询内容
        $arr = $this->field('id')
            ->join("as c left join oto_orders as o on c.orderid=o.orderId")
            ->where("1 {$sql}")
            ->order('id desc')
            ->select();
        $map = array();
        foreach ($arr as $v) {
            $map[] = $v['id'];
        }
        // 将查询到的id存储到cookie中，方便导出数据
        cookie('searchesCondition', serialize($map));
        if ($isHandle == '') {
            $data['isHandle'] = - 1;
        } else {
            $data['isHandle'] = $isHandle;
        }
        
        return $data;
    }

    /**
     * ï¿½ï¿½Ê¾ï¿½ï¿½ï¿½ï¿½ï¿½Ç·ï¿½ï¿½ï¿½Ê¾/ï¿½ï¿½ï¿½ï¿½
     */
    public function editiIsShow()
    {
        $rd = array(
            'status' => - 1
        );
        if (I('id', 0) == 0)
            return $rd;
        $m = M('Complain');
        $m->isHandle = ((int) I('isHandle') == 1) ? 1 : 0;
        $rs = $m->where("id =" . (int) I('id', 0))->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * *批量删除
     */
    public function BatchDelete($id)
    {
        $map['id'] = array(
            'in',
            $id
        );
        return $this->where($map)->delete();
    }

    /**
     * 根据投诉id查询
     * 
     * @return mixed array
     */
    public function selectBySelecId($id)
    {
        $map['id'] = array(
            'in',
            $id
        );
        return $this->where($map)
            ->join("as c left join oto_orders as o on c.ordersn=o.orderId")
            ->field("c.username,o.orderNo,c.type,c.content,c.time,c.isHandle")
            ->select();
    }

    /**
     * 获取待处理的投诉数量
     */
    public function queryPenddingComplainNum()
    {
        $rd = array(
            'status' => - 1
        );
        $sql = "select count(*) counts from __PREFIX__complain where isHandle = 0";
        $rs = $this->query($sql);
        $rd['num'] = $rs[0]['counts'];
        return $rd;
    }
}