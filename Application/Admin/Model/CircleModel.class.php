<?php
namespace Admin\Model;

/**
 * 分销类
 */
class CircleModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
    }

    public function look()
    {
        $id = I('get.circleId');
        
        $data['circle'] = M('circle')->where(array(
            'id' => $id
        ))->find();
        $data['goods'] = M('goods')->where(array(
            'goodsId' => $data['circle']['goodsId']
        ))->find();
        $data['shops'] = M('shops')->where(array(
            'shopId' => $data['circle']['shopId']
        ))->find();
        
        return $data;
    }

    public function index()
    {
        $data = M('circle')->select();
        $this->checkIndexData($data);
        
        return $data;
    }

    public function checkIndexData(&$data)
    {
        foreach ($data as $key => $value) {
            $value['imgThums'] = isset($value['imgThums']) ? "<img class='order_img' src=/" . $value['imgThums'] . ">" : '未上传';
            $value['title'] = isset($value['title']) ? $value['title'] : '未填写';
            $value['action'] = $this->circleCheckAction($value['id']);
            $value['shopInfo'] = $this->shopInfo($value['shopId']);
            $value['time'] = date("Y-m-d h:i:s", $value['time']);
            $value['like'] = isset($value['like']) ? $value['like'] : 0;
            $value['isShow'] = $this->checkStatus($value['isShow']);
            $data[$key] = $value;
        }
    }

    public function shopInfo($shopId)
    {
        $data = M('shops')->where(array(
            'shopId' => $shopId
        ))->find();
        
        return $data['shopName'];
    }

    public function circleCheckAction($id)
    {
        $action = "<a class='btn btn-success btn-sm' href='/Admin/Circle/look/circleId/$id'>" . '查看' . "</a>&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick=changeStatus($id,1)>" . '显示' . "</button>" . '&nbsp;&nbsp;&nbsp;' . "<button class='btn btn-danger btn-sm' onclick='changeStatus($id,0)'>" . '隐藏' . "</button>" . '&nbsp;&nbsp;&nbsp;' . "<button class='btn btn-danger btn-danger' onclick='changeStatus($id,-1)'>" . '删除' . "</button>";
        
        return $action;
    }

    public function checkStatus($status)
    {
        switch ($status) {
            case 0:
                $status = '审核中';
                break;
            case 1:
                $status = '发布中';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function change()
    {
        $id = I('post.id');
        $isShow = I('post.status');
        if ($isShow == - 1) {
            $re = M('circle')->where(array(
                'id' => $id
            ))->delete();
        } else {
            $re = M('circle')->where(array(
                'id' => $id
            ))->setField('isShow', $isShow);
        }
        
        return $re;
    }
}
;
?>