<?php
namespace Admin\Model;

/**
 * 公告类
 */
class GameComplainModel extends BaseModel
{

    

    /**
     * 分页列表[获取已审核列表]
     */
    public function queryByPage()
    {
        $m = M('game_complain');
        $sql = "select c.*,u.userPhone from __PREFIX__game_complain as c left join oto_users as u on u.userId = c.uid order by id desc";
        $info = $m->pageQuery($sql);
        
        return $info;
    }
}
;
?>