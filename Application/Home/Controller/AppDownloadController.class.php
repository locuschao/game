<?php
namespace Home\Controller;
/**
 * @author peng
 * @date 2017-04
 * @descreption 下载app管理
 */
class AppDownloadController extends BaseController
{
   public function lists(){
        
        $this->display("shops/AppDownload/lists");
   }
   public function addList() {
        $this->assign('game', M('game')->order('CONVERT( gameName USING gbk ) COLLATE gbk_chinese_ci ASC')->select());
        $this->assign('editInfo',M('game_version_download')->where(['id'=>I('id')])->find());
        $this->display("shops/AppDownload/addList");
   }
   public function addSaveListHandle() {
        $this->isShopLogin();
        $post = I('');
        
        $game_version_dl = M('game_version_download');
        if($post['id']){#修改
            if($game_version_dl->where([
                'id'=>$post['id']
            ])->save([
                'downloadLink'=>$post['downloadLink']
            ])){
                $this->redirect('AppDownload/lists'); 
            }else{
                $this->error('修改失败');
            }
        }else{#添加
            if(!$post['game']){
                $this->error('游戏不能空');
            }
            if(!$post['versions']){
                $this->error('游戏版本不能空');
            }
            if(!$post['downloadLink']){
                $this->error('下载链接不能空');
            }
            if($game_version_dl->where([
                'shopId'=>session('WST_USER')['shopId'],
                'gameId'=>$post['game'],
                'versionId'=>$post['versions']
            ])->find()){
                $this->error('相应下载链接已经存在');
            }
            if($game_version_dl->add([
                'shopId'=>session('WST_USER')['shopId'],
                'gameId'=>$post['game'],
                'versionId'=>$post['versions'],
                'downloadLink'=>$post['downloadLink']
            ])){
               $this->redirect('AppDownload/lists'); 
            }else{
                $this->error('添加失败');
            }
        }
        
   }
   public function delRow() {
    
        if(M('game_version_download')->delete(I('id'))){
            $this->redirect('AppDownload/lists'); 
        }else{
            $this->error('删除失败');
        }
   
   }
}