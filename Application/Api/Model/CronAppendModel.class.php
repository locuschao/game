<?php
namespace Api\Model;
/**
 * @author peng
 * @date 2017-03
 * @descreption 计划任务的拓展模型
 */
class CronAppendModel{
    /**
     * @descreption 以后的计划任务可以通过此方法来拓展
     */
    private $cronList = [
        'fenchengBatch'=>['model_name'=>'Admin/Orders','function'=>'fenchengBatch'],#批量进行分成
        'update_le8_token'=>['model_name'=>'Api/LeRecharge','function'=>'updateToken'],#更新会话
        'update_le8_yzm_token'=>['model_name'=>'Api/LeRegister','function'=>'updateToken'],#更新验证码会话
    ];
    public function excute() {
        
        foreach($this->cronList as $row){
            D($row['model_name'])->$row['function']();
        }
            
    }
}