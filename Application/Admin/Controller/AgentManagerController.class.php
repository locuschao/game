<?php
/**
 * @author peng	
 * @date 2016-12-28
*/
namespace Admin\Controller;
class AgentManagerController extends BaseController{
   
    public function index(){
        
        $shop_version_agent = M('shop_version_agent');
        $page = new \Think\Page($shop_version_agent->count(), 2); // 实例化分页类 传入总记录数和每页显示的记录数
       
        $table_pre = C('DB_PREFIX');
        $this->assign('data',$shop_version_agent
        ->join('sva left join '.$table_pre.'agent a on sva.agentId=a.id left join '.$table_pre.'shops s on sva.shopId=s.shopId')
        ->limit($page->firstRow,$page->listRows)->select());
        foreach(M('versions')->select() as $row){
            $version_arr[$row['id']] = $row['vName'];
        }
        $this->assign('page', $page->show());
        $this->assign('version_arr', $version_arr);
    	$this->display();                
        
    }
    
    public function updateStatus() {
        $get = I('');
        $update_bind = M('update_bind');
        $info = $update_bind->where([
        'id'=>$get['id']
        ])->find();
        if($info['status'] == 1){
            if($update_bind->where([
                'id'=>$get['id']
            ])->save([
            'check_author'=>$_SESSION['oto_mall']['WST_STAFF']['staffId'],
            'status'=>$get['status']
            ])){
                $this->success('处理成功');
            }else{
                $this->error('处理失败');
            }
        }else{
            $this->error('已处理');
        }
    }
    
    
}