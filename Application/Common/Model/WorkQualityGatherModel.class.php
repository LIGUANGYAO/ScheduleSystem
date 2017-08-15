<?php
/* User:lyt123; Date:2016/12/5; QQ:1067081452 */
namespace Common\Model;

class WorkQualityGatherModel extends CURDModel
{
    public function processData($data)
    {
        foreach ($data as &$item) {
            $item['academy'] = $item['academy_value'] ? $item['academy_value'] : current(D('Academy')->getData(array('id' => $item['academy_id']), array('name')));
            $item['year_and_semester'] = substr($item['year'], 2, 2) . '-' . substr(($item['year'] + 1), 2, 2) . '-' . $item['semester'];

            if ($item['semester'] == 1) {

                $item['time'] = $item['year'] . '年下';

            } elseif ($item['semester'] == 2) {

                $item['time'] = 1 + $item['year'] . '年上';

            }
        }

        return $data;
    }

    public function setWorkQualityGatherExcelTitle($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '上课院系')
            ->setCellValue('B1', '班级开课院系')
            ->setCellValue('C1', '人数')
            ->setCellValue('D1', '论文人数(本)')
            ->setCellValue('E1', '论文人数(专)')
            ->setCellValue('F1', '学期')
            ->setCellValue('G1', '课程名称')
            ->setCellValue('H1', '考核方式')
            ->setCellValue('I1', '总学时')
            ->setCellValue('J1', '理论')
            ->setCellValue('K1', '实践')
            ->setCellValue('L1', '任课教师')
            ->setCellValue('M1', '教师职称')
            ->setCellValue('N1', '备注')
            ->setCellValue('O1', '工作量（刚性）')
            ->setCellValue('P1', '刚性总数')
            ->setCellValue('Q1', '时间');
    }

    public function setWorkQualityGatherExcelCellWidth($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(11);
    }

}