<?php
namespace Admin\Model;

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
        $data['pinyin'] = I('pinyin');
        $data['telephone'] = I('telephone');
        $data['website'] = I('website');
        $data["shopId"] = 0;
        $data['isShow'] = (int) I('isShow', 0);
        $data['isEnable'] = (int) I('isEnable', 1);
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
        $data = array();
        $data["expressCompany"] = I("expressCompany");
        $data['pinyin'] = I('pinyin');
        $data['telephone'] = I('telephone');
        $data['website'] = I('website');
        $data['isShow'] = (int) I('isShow', 0);
        $data['isEnable'] = (int) I('isEnable', 1);
        $id = (int) I('id');
        if ($this->checkEmpty($data)) {
            $rs = $m->where("shopId=0 and id=" . $id)->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 获取指定物流信息
     */
    public function get()
    {
        $m = M('Express');
        return $m->where("shopId=0 and id = " . I('id'))->find();
    }

    /**
     * 获取物流信息
     */
    public function getAll()
    {
        $m = M('Express');
        return $m->where("shopId=0 and expressFlag = 1 and isShow = 1")->select();
    }

    /**
     * 分页列表
     */
    public function queryByList()
    {
        $m = M('Express');
        $sql = "select * from __PREFIX__express where shopId = 0 and expressFlag = 1 order by id asc";
        $express = $m->pageQuery($sql);
        
        return $express;
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
        $rs = $m->execute("update __PREFIX__express set expressFlag=-1 where shopId=0 and id=" . $id);
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 启用状态
     */
    public function editiIsShow()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I("id", 0);
        $isShow = (int) I("isShow", 0);
        $data = array();
        $data["isShow"] = (int) I("isShow");
        if ($this->checkEmpty($data, true)) {
            $m = M('Express');
            $m->where("id=" . I('id') . " and shopId=0")->save($data);
        }
        return $rd;
    }

    /**
     * 显示状态
     */
    public function editiIsEnable()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I("id", 0);
        $isEnable = (int) I("isEnable", 0);
        $data = array();
        $data["isEnable"] = (int) I("isEnable");
        if ($this->checkEmpty($data, true)) {
            $m = M('Express');
            $m->where("id=" . I('id') . " and shopId=0")->save($data);
        }
        return $rd;
    }
}
;
?>