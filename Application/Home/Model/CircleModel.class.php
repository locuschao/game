<?php
namespace Home\Model;

/**
 * 圈子
 */
class CircleModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
        
        $this->getUserInfo();
        $this->shopId = 40;
    }

    public function look()
    {
        $id = I('get.circleId');
        if (! $id) {
            
            return false;
        }
        
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

    public function getUserInfo()
    {
        $this->userId = $this->shopId = $_SESSION['oto_mall']['oto_userInfo']['userId'];
        $this->shopId = M('shops')->where(array(
            'userId' => $this->userId
        ))
            ->field('shopId')
            ->find();
    }

    public function index()
    {
        $shopId = 40;
        
        $data = M('circle')->where(array(
            'shopId' => $shopId
        ))->select();
        $this->checkIndexData($data);
        
        return $data;
    }

    public function checkIndexData(&$data)
    {
        foreach ($data as $key => $value) {
            $value['imgThums'] = isset($value['imgThums']) ? "<img class='order_img' src=/" . $value['imgThums'] . ">" : '未上传';
            $value['title'] = isset($value['title']) ? $value['title'] : '未填写';
            $value['action'] = $this->circleCheckAction($value['id']);
            $value['time'] = date("Y-m-d h:i:s", $value['time']);
            $value['like'] = isset($value['like']) ? $value['like'] : 0;
            $value['isShow'] = $this->checkStatus($value['isShow']);
            $data[$key] = $value;
        }
    }

    public function circleCheckAction($id)
    {
        $action = "<a class='btn btn-success btn-sm' href='/Home/Circle/look/circleId/$id'>" . '查看' . "</a>" . '&nbsp;&nbsp;&nbsp;' . "<a class='btn btn-danger btn-sm' href='/Home/Circle/modify/circleId/$id' >" . '编辑' . "</a>";
        
        return $action;
    }

    public function checkStatus($status)
    {
        switch ($status) {
            case 0:
                $status = '审核中';
                break;
            case 1:
                $status = '通过';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function action()
    {
        $data = I('post.data');
        $status = $data['status'];
        
        switch ($status) {
            case 'add':
                $re = $this->add();
                break;
            case 'delete':
                $re = $this->delete();
                break;
            case 'edit':
                $re = $this->edit();
                break;
            default:
                return false;
                break;
        }
        
        return $re;
    }

    public function add()
    {
        $tmp = I('post.data');
        $data['time'] = time();
        $data['title'] = $tmp['title'];
        $data['content'] = $_POST['data']['content'];
        
        $data['shopId'] = $tmp['shopId'];
        $data['imgThums'] = $tmp['imgThums'];
        
        $data['goodsId'] = $tmp['goodsId'];
        $res = M('circle')->data($data)->add();
        
        return $res;
    }

    public function edit()
    {
        $tmp = I('post.data');
        $id = $tmp['circleId'];
        $data['title'] = $tmp['title'];
        $data['content'] = $_POST['data']['content'];
        $data['shopId'] = $tmp['shopId'];
        $data['goodsId'] = $tmp['goodsId'];
        $data['imgThums'] = $tmp['imgThums'];
        
        $res = M('circle')->where(array(
            'id' => $id
        ))
            ->data($data)
            ->save();
        
        return $res;
    }

    public function delete()
    {
        $id = I('post.circleId');
        
        $res = M('circle')->where(array(
            'id' => $id
        ))->delete();
        
        return $res;
    }

    public function getList()
    {
        $shopId = $this->shopId;
        
        $res = M('circle')->where(array(
            'shopId' => $shopId
        ))->select();
        
        return $res;
    }

    public function getGoods()
    {
        
        // $shopId = $this->shopId;
        $shopId = 41;
        
        $res['goods'] = M('goods')->where(array(
            'shopId' => $shopId
        ))->select();
        if ((int) I('get.circleId') > 0) {
            $id = (int) I('get.circleId');
            $res['circle'] = M('circle')->where(array(
                'id' => $id
            ))->find();
        }
        $res['userId'] = $this->userId;
        $res['shopId'] = $this->shopId;
        
        return $res;
    }
}