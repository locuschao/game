<?php
namespace Api\Model;
/**
 * @author peng
 * @date 2017-03
 * @descreption 工具类
 */
class UtilModel{
    public function insertKey() {
        foreach(file('TTkey.txt') as $row){
            
            preg_match_all('/gameId = (.+) gameKey = (.+)/',$row,$data,PREG_SET_ORDER);
            
            M('ttgame')->where(['gameId'=>$data[0][1]])->save(['gameKey'=>$data[0][2]]);
        }
    }
}