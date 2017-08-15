<?php
/* User:lyt123; Date:2016/11/2; QQ:1067081452 */
namespace Common\Model;

class CourseModel extends CURDModel
{
    public function setCourseExcelCellWidth($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(6);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(6);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(3);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(3);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(11);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(50);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(20);
    }

    public function setCourseExcelTiTle($objPHPExcel, $year_semester_string)
    {
        $objPHPExcel->getActiveSheet()->mergeCells('A1:M1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:M2');
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(35);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(28);
        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(31);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(15);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', "                                          五邑大学继续教育学院{$year_semester_string}学期课程表")
            ->setCellValue('A2', '                                                         开学时间：专科—2月27日晚     专升本—3月4日晚')
            ->setCellValue('A3', '上课院系')
            ->setCellValue('B3', '班级名称')
            ->setCellValue('C3', '学生人数')
            ->setCellValue('D3', '课程代号')
            ->setCellValue('E3', '课程名称')
            ->setCellValue('F3', '考核方式')
            ->setCellValue('G3', '总学时')
            ->setCellValue('H3', '理论')
            ->setCellValue('I3', '实践')
            ->setCellValue('J3', '任课教师')
            ->setCellValue('K3', '上课时间，上课周次，上课地点')
            ->setCellValue('L3', '合班情况')
            ->setCellValue('M3', '备注');
    }

    public function setWorkQualityGatherExcelTitle($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '星期')
            ->setCellValue('B1', '起止周次')
            ->setCellValue('C1', '上课院系')
            ->setCellValue('D1', '班别')
            ->setCellValue('E1', '学生人数')
            ->setCellValue('F1', '论文人数(本)')
            ->setCellValue('G1', '论文人数(专)')
            ->setCellValue('H1', '学期')
            ->setCellValue('I1', '课程名称')
            ->setCellValue('J1', '考核方式')
            ->setCellValue('K1', '总学时')
            ->setCellValue('L1', '理论')
            ->setCellValue('M1', '实践')
            ->setCellValue('N1', '任课教师')
            ->setCellValue('O1', '合班情况')
            ->setCellValue('P1', '学生人数系数')
            ->setCellValue('Q1', '课程系数')
            ->setCellValue('R1', '难度系数')
            ->setCellValue('S1', '备注')
            ->setCellValue('T1', '工作量（刚性）')
            ->setCellValue('U1', '刚性总数')
            ->setCellValue('V1', '核对签字')
            ->setCellValue('W1', '时间');
    }

    public function setWorkQualityGatherExcelCellWidth($objPHPExcel)
    {

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('R')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('S')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('T')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('U')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('V')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('W')->setWidth(15);
    }

    public function getAcademyTeacherCourse($academy_id, $year_and_semester_data)
    {
        return $this->table('course c, teacher t')
            ->field('t.*')
            ->where('c.teacher_id = t.id')
            ->where(array('c.academy_id' => $academy_id))
            ->where(array('c.year' => $year_and_semester_data['year'], 'c.semester' => $year_and_semester_data['semester']))
            ->select();
    }

    public function getTeacherIdByTeacherName($data, $year_and_semester_data)
    {
        $teacher_name = $data['teacher'] ? $data['teacher'] : $data['teacher_name_for_teaching_task'];
        $data['teacher_id'] = current(D('Teacher')->getData(array('name' => $teacher_name), array('id')));
        $data['teacher_id'] = D('Teacher')->dealWithMultipleTeacherCourse(D('Course'), $data['teacher_id'], $year_and_semester_data);

        return $data['teacher_id'];
    }

    public function getTeacherTeachingTaskCourse($course_data)
    {
        $combine_class_course = array();
        $single_class_course = array();
        foreach ($course_data as $item) {
            if ($item['combine_status']) {
                $combine_class_course[] = $item;
            } else {
                $single_class_course[] = $item;
            }
        }
        $combine_class_course = unique_multidim_array($combine_class_course, 'combine_status');
        $course_data = array_values(array_merge($single_class_course, $combine_class_course));

        return $course_data;
    }

    public function getCourseData($srh_data)
    {
        $courses = D('Course')->getData($srh_data, null, true, $srh_data['order'], $srh_data['page'], $srh_data['limit']);

        return $courses;

    }
}