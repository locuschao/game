<?php
namespace Home\Model;

/**
 * 会员等级服务类
 */
class UserRanksModel extends BaseModel
{

    /**
     * 获取列表
     */
    public function checkUserRank($score)
    {
        $m = M('user_ranks');
        return $rs = $m->where($score . ' between  startScore and endScore ')->find();
    }
}
;
?>