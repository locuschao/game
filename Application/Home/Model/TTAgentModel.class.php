<?php
namespace Home\Model;
/**
 * @author peng
 * @date 2017-03
 */
class TTAgentModel
{
    public function applyUpdate() {
        
        $update_reason = str_replace("\n",'<br/>',$_POST['update_reason']);
        $post = I('');
        if(!$post['update_reason']){
            return [
                'status'=>0,
                'info'=>'理由不能是空'
            ];
        }
        $update_bind = M('update_bind');
        $id = (int)$post['id'];
        if($id){
            if(!$apply=$update_bind->where([
            'id'=>$id
            ])->find()){
                return [
                    'status'=>0,
                    'info'=>'记录不存在'
                ];
            }
            if($apply['status'] != 1) {
                return [
                    'status'=>0,
                    'info'=>'已处理修改原因'
                ];
            }
            if($update_bind->where([
            'id'=>$id
            ])->save([
                'update_reason'=>$update_reason
            ])){
                return [
                    'status'=>1,
                    'info'=>'更改成功'
                ];
            }else{
                
                return [
                    'status'=>0,
                    'info'=>'更改失败'
                ];
            }
        }
        
        
        if($update_bind->where([
            'shopId'=>session('WST_USER.shopId'),
            'versionId' =>$post['versionId'],
            'status'=>1
        ])->find()){
            return [
                'status'=>0,
                'info'=>'已经申请'
            ];
        }
        if($update_bind->add([
            'shopId'=>session('WST_USER.shopId'),
            'agentId'=>$post['agentId'],
            'versionId' =>$post['versionId'],
            'update_reason' =>$update_reason,
            'apply_time' =>time(),
            'status'=>1,
        ])){
            return [
                'status'=>1,
                'info'=>'提交成功'
            ];
        }else{
            return [
                'status'=>1,
                'info'=>'提交失败'
            ];
        }
    }
    
}