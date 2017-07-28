<?php
namespace Admin\Model;

/**
 * 统计模块的模型
 */
class StatisticModel extends \Think\Model 
{   
    
    public function exportExcel($arr){
        ob_clean();
        $file_name=$arr['file_name'];
        header("Content-type:application/vnd.ms-excel"); 
        header("Content-Disposition:attachment; filename=\"{$file_name}.xls\"");
        foreach($arr['header'] as $v){
            echo iconv('utf-8','GB2312//IGNORE',$v).chr(9);
        }
        
        echo chr(13);
        
        foreach($arr['data'] as $row){
            foreach($arr['header'] as $k=>$v){
                echo $row[$k].chr(9);
            }
            
            echo chr(13);
        }
        
    }
}