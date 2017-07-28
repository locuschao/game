<?php
namespace Admin\Controller;

class AutoReceivingController extends BaseController
{
    public  function _initialize(){
        $this->isLogin();
    }
    
    
    public function index(){
        $content = file_get_contents('auto.txt');
        $content=explode('|', $content);
        $this->assign('setting',$content);
       // $this->display();
	   $this->display("/autoReceiving/index");
    }
    //file_put_contents('auto.txt', $content . '|' . date('Y-m-d H:i:s').'|'.$content[2].$content[3]);
    
    
    public function save(){
        $auto=I('auto',0,intval);
        $autoTime=I('autoTime','');
        $content = file_get_contents('auto.txt');
        $content=explode('|', $content);
        $rs=file_put_contents('auto.txt', $auto . '|' . date('Y-m-d H:i:s').'|'.$autoTime.'|'.$content[3]);
        if($rs!=false){
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
    
    public function hand(){
       // fastcgi_finish_request();
       $param['url']=$_SERVER['HTTP_HOST'].'/index.php/Game/AutoExc/autoCommitOrder';
            //MODULE_NAME.'/'.CONTROLLER_NAME."/".ACTION_NAME;
       $info= $this->add_handle($param);
       
    }
    
    
    public function active(){
        // fastcgi_finish_request();
        $param['url']=$_SERVER['HTTP_HOST'].'/index.php/Game/AutoExc/autoRun';
            //MODULE_NAME.'/'.CONTROLLER_NAME."/".ACTION_NAME;
        $info= $this->add_handle($param);
        
        /**
         * @author peng	
         * @date 2016-12
         * @descreption 没定义 fastcgi_finish_request注释掉
         */
        //fastcgi_finish_request();
    }
    
    
    private function add_handle($param)
    {
        
        $handle = curl_multi_init();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $param['url']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        if ($param['method'] == 'POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param['data']);
        }
        curl_multi_add_handle($handle, $curl);
        
        $running=null;
        // 执行批处理句柄
        do {
            curl_multi_exec($handle,$running);
            
        } while($running > 0);
        
        // 关闭全部句柄
        curl_multi_remove_handle($handle, $curl);
        
        curl_multi_close($handle);
        
        return $curl;
    }
    
}

