<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/20
 * Time: 19:48
 */
namespace Admin\Controller;

use Lib\Exp\DataExp;

class ComplainController extends BaseController
{

    /**
     * 查询记录
     */
    public function index()
    {
        $this->isLogin();
        $complain = D('Complain');
        $data = $complain->selectAll();
        if ($data) {
            $this->assign('data', $data);
        } else {
            $data['error'] = '没有记录';
            $this->assign('data', $data);
        }
        // dump($data);die;
        $this->view->display('complain/index');
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出订单投诉数据
     */
    public function comDataExp()
    {
        $this->isLogin();
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''||$end == ''||$start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同</font>');
            $this->index();
            die;
        }
        $xlsCell = array(
            array('num','序号'),
            array('user','用户名'),
            array('orderNo','订单号'),
            array('type','投诉类型'),
            array('time','投诉时间'),
            array('isHandle','处理'),
        );
        $where = " UNIX_TIMESTAMP(c.time) >= $start and UNIX_TIMESTAMP(c.time) <= $end ";
        $xlsModel = M('Complain');
        $xlsData = $xlsModel->join("as c left join oto_orders as o on c.orderid=o.orderId")
            ->join('oto_users as u on u.userId=c.userId')
            ->where($where)
            ->field('c.type,c.time,c.isHandle,c.content,u.userName,u.userPhone,o.orderNo')
            ->order('time desc')
            ->select();
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['num'] = $key + 1;
            if($value['userName'])
            {
                $xlsData[$key]['user'] = $value['userName'];
            }else{
                $xlsData[$key]['user'] = $value['userPhone'];
            }
            if($value['type'] == 0)
            {
                $xlsData[$key]['type'] = '其他投诉'.$value['content'];
            }else{
                $xlsData[$key]['type'] = '商家已确定，但没有送达';
            }
            if($value['isHandle'] == 0)
            {
                $xlsData[$key]['isHandle'] = '未处理';
            }else{
                $xlsData[$key]['isHandle'] = '已处理';
            }
        }
        $xlsName = 'complain';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    /**
     * 根据订单id修改处理状态
     */
    public function editiIsShow()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('wzlb_02');
        $m = D('Admin/Complain');
        $rs = $m->editiIsShow();
        $this->ajaxReturn($rs);
    }

    /**
     * 批量删除
     */
    public function deletes()
    {
        $this->isLogin();
        $disposable = D("Complain");
        $id = trim(I('post.id', '', 'htmlspecialchars'), ',');
        $info = array();
        $disposable->BatchDelete($id) ? $info['status'] = 1 : $info['status'] = 0;
        $this->ajaxReturn($info);
        exit();
    }

    /**
     * 根据id删除
     */
    public function del()
    {
        $this->isLogin();
        $disposable = D("Complain");
        $id = trim(I('post.id', '', 'htmlspecialchars'), ',');
        $info = array();
        $disposable->where("id = {$id}")->delete() ? $info['status'] = 1 : $info['status'] = 0;
        $this->ajaxReturn($info);
        exit();
    }

    /**
     * 导出数据
     */
    public function outExcel()
    {
        $this->isLogin();
        $complainModel = D('Complain');
        $data = json_decode($_GET['id'], true);
        $exportStatus = $data['exportStatus'];
        if ($exportStatus == 0) {
            // 所选
            $id = trim($data['id'], ',');
        }
        if ($exportStatus == 1) {
            // 全部
            $arr = $complainModel->field('id')->select();
            $map = array();
            foreach ($arr as $v) {
                $map[] = $v['id'];
            }
            $id = implode(',', $map);
        }
        if ($exportStatus == 2) {
            // 查询
            $id = implode(',', unserialize(cookie('searchesCondition')));
        }
        $array = array(
            '用户名',
            '订单号',
            '投诉类型',
            '投诉内容',
            '投诉时间',
            '处理状态'
        );
        $list = array();
        $list = $complainModel->selectBySelecId($id);
        $list = $this->handle($list);
        $this->out($list, $array);
    }

    /**
     * 把数据表里的数据处理并返回
     * 
     * @param $arr 从数据表里取得数据            
     * @return array
     */
    public function handle($arr)
    {
        $list = array();
        foreach ($arr as $key => $val) {
            $list[$key] = $val;
            $list[$key]['time'] = date('Y-m-d H:i:s', $val['time']);
            if ($list[$key]['type'] == 0) {
                $list[$key]['type'] = '其它投诉';
            } else {
                $list[$key]['type'] = '商家已确定，但没有送达';
            }
            if ($list[$key]['isHandle'] == 0) {
                $list[$key]['isHandle'] = '未处理';
            } else {
                $list[$key]['isHandle'] = '已处理';
            }
        }
        
        return $list;
    }

    /**
     * 生成A-Z之间的所有英文字母
     * 
     * @return array
     */
    public function merge()
    {
        $arr = range('A', 'Z');
        return $arr;
    }

    /**
     * 生成Excel导出数据
     * 
     * @param $list 字段内容            
     * @param $array 字段名            
     * @param $txstatus 提现状态            
     */
    public function out($list, $array)
    {
        vendor('PHPExcel');
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle('Sheet1');
        $objActSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 设置excel文件默认水平垂直方向居中
        $objActSheet->getDefaultStyle()
            ->getFont()
            ->setSize(14)
            ->setName("微软雅黑"); // 设置默认字体大小和格式
        $objActSheet->getDefaultRowDimension()->setRowHeight(30); // 设置默认行高
        $objActSheet->getColumnDimension('C')->setWidth(20); // 设置宽度
        $objActSheet->getColumnDimension('K')->setWidth(20);
        $arr = $this->merge();
        for ($i = 1; $i <= count($arr); $i ++) {
            
            $objActSheet->setCellValue($arr[$i - 1] . '1', $array[$i - 1]);
        }
        
        $count = 1;
        $num = 0;
        
        foreach ($list as $key => $val) {
            foreach ($val as $k => $v) {
                $num ++;
                $number = $arr[$num - 1];
                $font = $count + 1;
                $objActSheet->setCellValue($number . $font, $v);
            }
            $num = 0;
            $count ++;
        }
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // ob_end_clean();
        $filename = '订单投诉';
        
        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Disposition:attachment;filename={$filename}" . date('Y-m-d', mktime()) . ".xls");
        header("Pragma:no-cache");
        header("Expires:0");
        $objWriter->save('php://output');
    }
}