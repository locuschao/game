<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;

class SearchController extends BaseController
{   
    /**
     * @api {post} Search/mySearch 我的搜索记录
     * @apiParam {string} token
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": obj,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function mySearch() {
        parent::isLogin();
        if($data = M('search_keyword')->where(['userId'=>$_SESSION['userId']])
        ->order('id desc')
        ->getField('keyword',true)){
            $this->ajaxReturn($data);
        }else{
            $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} Search/hotGame  热门游戏
     * @apiParam nuparam 不用带token
     * @apiSuccess {object} result
     *       {
     *        "status": 1,
     *        "info": ,
     *      }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 没有数据,
     *      }
     */
    public function hotGame() {
        $data = M('game')->field('gameName,gameIco')
        ->order('searchAmount desc')
        ->limit(10)
        ->select();
        if($data){
            foreach($data as $k=>$row){
                $data[$k]['gameIco'] = C('RESOURCE_URL').$row['gameIco'];
            }
            $this->ajaxReturn($data);
        }else{
            $this->error('没有数据');
        }
    }

}