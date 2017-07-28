<?php
namespace Home\Model;

/**
 * 银行服务类
 */
use Think\Model;

class BanksModel extends BaseModel
{

    /**
     * 分页列表
     */
    public function queryByPage()
    {
        $m = M('banks');
        $sql = "select * from __PREFIX__banks where bankFlag=1 order by bankId desc";
        $rs = $m->pageQuery($sql);
        return $rs;
    }

    /**
     * 获取列表
     */
    public function queryByList()
    {
        $m = M('banks');
        $rs = $m->where('bankFlag=1')->select();
        return $rs;
    }
}
;
?>