<?php
namespace Home\Model;

/**
 * 商品类型服务类
 */
class ExpressModel extends BaseModel
{

    /**
     * 新增
     */
    public function insert()
    {
        $m = M('Express');
        $rd = array(
            'status' => - 1
        );
        $data = array();
        $data["expressCompany"] = I("expressCompany");
        $data['telephone'] = I('telephone');
        $data['website'] = I('website');
        $data["shopId"] = (int) session('WST_USER.shopId');
        $data['isShow'] = (int) I('isShow', 1);
        if ($this->checkEmpty($data)) {
            $rs = $m->add($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 修改
     */
    public function edit()
    {
        $m = M('Express');
        $rd = array(
            'status' => - 1
        );
        $shopId = (int) session('WST_USER.shopId');
        $data = array();
        $data["expressCompany"] = I("expressCompany");
        $data['telephone'] = I('telephone');
        $data['website'] = I('website');
        $data["shopId"] = (int) session('WST_USER.shopId');
        $data['isShow'] = (int) I('isShow', 1);
        $id = (int) I('id');
        if ($this->checkEmpty($data)) {
            $rs = $m->where("shopId=" . $shopId . " and id=" . $id)->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 获取商户配送物流
     */
    public function get()
    {
        $m = M('Express');
        $shopId = (int) session('WST_USER.shopId');
        return $m->where("shopId=" . $shopId . " and isShow = 1 and expressFlag = 1")->select();
    }

    /**
     * 获取单个配送物流
     */
    public function getOne()
    {
        $m = M('Express');
        $id = (int) I('id', 0);
        $shopId = (int) session('WST_USER.shopId');
        return $m->where("shopId=" . $shopId . " and id = " . $id . " and isShow = 1 and expressFlag = 1")->find();
    }

    /**
     * 分页列表
     */
    public function queryByList()
    {
        $m = M('Express');
        $shopId = (int) session('WST_USER.shopId');
        $sql = "select * from __PREFIX__express where (shopId = 0 or  shopId = " . $shopId . ") and expressFlag = 1 and isEnable = 1 order by shopId desc,id desc";
        return $this->pageQuery($sql);
    }

    /**
     * 删除
     */
    public function del()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I('id');
        if ($id == 0)
            return $rd;
        $m = M('Express');
        $shopId = (int) session('WST_USER.shopId');
        $rs = $m->execute("delete from __PREFIX__express where shopId=" . $shopId . " and id=" . $id);
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 显示状态
     */
    public function changeExpressStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('Express');
        $shopId = (int) session('WST_USER.shopId');
        $id = (int) I("id", 0);
        $isShow = (int) I("isShow", 0);
        $parentId = (int) I("pid", 0);
        $data = array();
        if ($parentId > 0) {
            $rs = $m->where('shopId = ' . $shopId . ' and parentId = ' . $parentId)->find();
            $data["isShow"] = (int) I("isShow");
            if ($rs) {
                if ($this->checkEmpty($data, true)) {
                    $m->where("id=" . I('id') . " and shopId=" . $shopId)->save($data);
                    $rd['status'] = 1;
                }
            }
        } else {
            $rs = $m->where('id=' . $id)->find();
            $data['shopId'] = $shopId;
            $data['expressCompany'] = $rs['expressCompany'];
            $data['telephone'] = $rs['telephone'];
            $data['website'] = $rs['website'];
            $data['isShow'] = 1;
            $data['expressFlag'] = $rs['expressFlag'];
            $data['pinyin'] = $rs['pinyin'];
            $data['parentId'] = $id;
            $shopExpress = $m->where('parentId = ' . $id)->find();
            if (! $shopExpress) {
                $m->add($data);
                $rd['status'] = 1;
            } else {
                $rd['status'] = 0;
            }
        }
        return $rd;
    }
}
;
?>