<?php
namespace Game\Controller;
use Think\Controller;

class SqlController extends Controller
{
    private $model;
    function __construct()
    {
        parent::__construct();
        $this->model = M('platfrom_account');
    }

    public function delete()
    {
        $res = $this->model->where('orderId=614')->delete();
        dd($res);           //6192
    }
    public function delete2()
    {
        $res = $this->model->where('orderId<664')->delete();
        dd($res);       //89
    }
    public function dataInsert()
    {
        $arr = $this->model->field('orderId')->select();
        $addArr = array();
        foreach ($arr as $value)
        {
            $count = $this->model->where('orderId='.$value['orderId'])->count();
            if($count!=2)
            {
                $info = $this->model->where('orderId='.$value['orderId'])->field('income,orderNo')->find();
                $addArr[] = array(
                    'orderId'=>$value['orderId'],
                    'time'=>0,
                    'income'=>-$info['income'],
                    'remark'=>'买家购物支付成功',
                    'orderNo'=>$info['orderNo']
                );
            }
        }
        dd($addArr);
//        $res = $this->model->addAll($addArr);
//        dd($res);
    }
    public function sum()
    {
        $sum = $this->model->sum('income');
        dd(M()->getLastSql());
        dd($sum);
    }
    public function findBug()
    {
        $arr = $this->model->field('orderId')->select();
        $Arr = array();
        foreach ($arr as $value)
        {
            $res = $this->model->where('orderId='.$value['orderId'])->sum('income');
            if($res != 0)
            {
                $info = $this->model->where('orderId='.$value['orderId'])->select();
                $Arr[] = $info;
            }
        }
        dd($Arr);
    }
    public function test()
    {
        $this->model->where('id=7558')->delete();
        $this->model->add(array(
            'orderId'=>1005,
            'time'=>0,
            'income'=>67,
            'orderNo'=>2017206311087
        ));
        $this->model->where('id=7762')->delete();
        $this->model->add(array(
            'orderId'=>1246,
            'time'=>0,
            'income'=>260,
            'orderNo'=>2017362041328
        ));
    }
}