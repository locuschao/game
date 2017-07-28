<?php
namespace Admin\Controller;

/**
 * TT自动充值管理工具
 */
class TTGameController extends BaseController
{
    
    /**
     * @author peng
     * @date 2017-03-17
     * @descreption 添加TT游戏列表到商城数据库
     */
    public function addGameListHandle(){
        preg_match('/<select id="gameId" name="gameId" class="choices-multiple-default form-control"[\s\S]*<\/select>/',D('Api/TcoinBase')->access('http://tcoin.52tt.com/tcoin/admin/dist/single/voucherDist.shtml'),$match);
        preg_match_all('/<option value="(\d+)">(.+?)【(.+?)】<\/option>/',$match[0],$data,PREG_SET_ORDER);
        $ttgame = M('ttgame');
        $update = false;
        foreach($data as $row) {
            if(!$ttgame->where(['gameId'=>$row[1]])->find()) {
                if($ttgame->add([
                    'gameId'=>$row[1],
                    'gameName'=>$row[2],
                    'system'=>$row[3]
                ])){
                    $update = true;
                }
            }
        }
        $this->ajaxReturn([
            'status'=>$update
        ]);
    }   
}
