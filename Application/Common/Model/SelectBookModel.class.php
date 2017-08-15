<?php
/* User:lyt123; Date:2016/11/23; QQ:1067081452 */
namespace Common\Model;

class SelectBookModel extends CURDModel
{
    public function setSelectBookOfOneSemesterExcelTitle($objPHPExcel, $year_semester_string)
    {
        $objPHPExcel->getActiveSheet()->mergeCells('A1:X1');
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(34);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', "                                                     {$year_semester_string}学期开课一览表")
            ->setCellValue('A2', '专业')
            ->setCellValue('B2', '开课院系')
            ->setCellValue('C2', '上课院系')
            ->setCellValue('D2', '班级')
            ->setCellValue('E2', '人数')
            ->setCellValue('F2', '课程代号')
            ->setCellValue('G2', '课程名称')
            ->setCellValue('H2', '考核方式')
            ->setCellValue('I2', '总学时')
            ->setCellValue('J2', '理论')
            ->setCellValue('K2', '实践')
            ->setCellValue('L2', '任课教师')
            ->setCellValue('M2', '教师职称')
            ->setCellValue('N2', '备注')
            ->setCellValue('O2', '排课要求')
            ->setCellValue('P2', '合班情况')
            ->setCellValue('Q2', '不能排时间');
    }

    public function setSelectBookOfOneSemesterExcelCellWidth($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(12.63);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(12.63);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(12.63);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(4);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(16.5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(3);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(3);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(3);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(10.5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(10.5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(10.5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(25);
    }

    public function processData($course_data)
    {
        foreach ($course_data as &$item) {
            $class_data = D('Class')->getData(array('id' => $item['class_id']), array('name', 'student_sum', 'profession'));
            $item['profession'] = $class_data['profession'];
            $item['teacher_require_data'] = D('TeacherRequire')->getTeacherRequireData($item['teacher_require_id']);
            $item['open_class_academy'] =
                $item['open_class_academy_value'] ? $item['open_class_academy_value'] :
                current(D('Academy')->getData(array('id' => $item['open_class_academy_id']), array('name')));
            $item['academy'] =
                $item['have_class_academy_value'] ? $item['have_class_academy_value'] :
                current(D('Academy')->getData(array('id' => $item['academy_id']), array('name')));
            $item['class_name'] = $class_data['name'];
            $item['student_sum'] = $class_data['student_sum'];
            $item['examine_way'] = transferExamineWay($item['examine_way']);
            $item['teacher'] = D('Teacher')->transferTeacherIdsToTeacherNames($item['teacher_id']);
            $item['teacher_position'] = D('Teacher')->getTeacherPositionById($item['teacher_id']);
            $item['teacher_existing_class'] = D('TeacherExistingClass')->getTeacherExistingClassByTeacherId($item['teacher_id'], $item['year'], $item['semester']);
            //处理合班
            if ($item['combine_status']) {
                $item['combine_status'] = D('Class')->transferClassIdsToClassNames($item['combine_status']);
            }
            $item['classroom_time'] = D('WeekSection')->getClassroomTimeByWeekSectionId($item['week_section_ids']);
        }

        return $course_data;
    }

    public function setCourseOfOneSemesterExcelTitle($objPHPExcel, $course_type, $year_semester_string)
    {
        $objPHPExcel->getActiveSheet()->mergeCells('A1:V1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:V2');
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(38);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(31);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(15);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', "                                                                           五邑大学继续教育学院{$year_semester_string}学期课程表")
            ->setCellValue('A2', '                                                                                                   开学时间:专科—8月29日晚专升本—9月3日晚')
            ->setCellValue('A3', '序号')
            ->setCellValue('B3', '上课院系')
            ->setCellValue('C3', '班级名称')
            ->setCellValue('D3', '学生人数')
            ->setCellValue('E3', '课程代号')
            ->setCellValue('F3', '课程名称')
            ->setCellValue('G3', '考核方式')
            ->setCellValue('H3', '总学时')
            ->setCellValue('I3', '理论')
            ->setCellValue('J3', '实践')
            ->setCellValue('K3', '任课教师');
        if ($course_type == 1) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('L3', '星期一晚上')
                ->setCellValue('M3', '星期二晚上')
                ->setCellValue('N3', '星期三晚上')
                ->setCellValue('O3', '星期四晚上')
                ->setCellValue('P3', '星期五晚上')
                ->setCellValue('Q3', '星期六晚上')
                ->setCellValue('R3', '星期日上午')
                ->setCellValue('S3', '星期日下午')
                ->setCellValue('T3', '星期日晚上')
                ->setCellValue('U3', '合班情况')
                ->setCellValue('V3', '备注');
        } elseif ($course_type == 2) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('L3', '星期六上午')
                ->setCellValue('M3', '星期六下午')
                ->setCellValue('N3', '备注');
        }
    }

    public function putTeacherAndCourseDataIntoExcel($objPHPExcel, $teacher_data, $course_data)
    {
        $objPHPExcel->setActiveSheetIndex()
            ->setCellValue('B3', $teacher_data['name'])
            ->setCellValue('E3', $teacher_data['position'])
            ->setCellValue('I3', current(D('Academy')->getData(array('id' => $teacher_data['academy_id']), array('name'))));

        foreach ($course_data as $key => $item) {
            $student_sum = 0;
            if ($item['combine_status']) {
                $class = D('Class')->transferClassIdsToClassNames($item['combine_status']);
                $class_ids = explode(',', $item['combine_status']);
                foreach ($class_ids as $class_id) {
                    $student_sum += current(D('Class')->getData(array('id' => $class_id), array('student_sum')));
                }
            } else {
                $class = current(D('Class')->getData(array('id' => $item['class_id']), array('name')));
                $student_sum = current(D('Class')->getData(array('id' => $item['class_id']), array('student_sum')));
            }
            automaticWrapText($objPHPExcel, 15);
            $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('B' . ($key + 5), $class)
                ->setCellValue('C' . ($key + 5), $item['name'])
                ->setCellValue('D' . ($key + 5), $student_sum)
                ->setCellValue('F' . ($key + 5), $item['time_total']);
            $teacher_require_data = D('TeacherRequire')->getTeacherRequireData( $item['teacher_require_id']);
//            ddd($teacher_require_data);
            if($teacher_require_data){
                $objPHPExcel->setActiveSheetIndex()
                    ->setCellValue('E' . ($key + 5), $teacher_require_data['check_way'])
                    ->setCellValue('G' . ($key + 5), $teacher_require_data['time_media'])
                    ->setCellValue('H' . ($key + 5), $teacher_require_data['time_in_class'])
                    ->setCellValue('I' . ($key + 5), $teacher_require_data['require']);
            }
        }
    }
}
