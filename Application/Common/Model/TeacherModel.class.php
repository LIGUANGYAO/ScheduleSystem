<?php
/* User:lyt123; Date:2016/11/1; QQ:1067081452 */
namespace Common\Model;

class TeacherModel extends AdminBaseModel
{

    /**
     * Des :如果一个课程有多个教师，将多个教师id也拼接到单个教师的数组里，形成'in'查询
     * Auth:lyt123
     */
    public function dealWithMultipleTeacherCourse($model, $teacher_id, $search_data)
    {
        $srh_array = array();
        $courses_combine_teachers = $model->getData(array($search_data), array('teacher_id'), true);
        foreach ($courses_combine_teachers as $course_combine_teacher) {
            if (strpos($course_combine_teacher['teacher_id'], ',')) {
                $teacher_ids = explode(',', $course_combine_teacher['teacher_id']);
                if (in_array($teacher_id, $teacher_ids)) {
                    $srh_array[] = $course_combine_teacher['teacher_id'];
                }
            }
        }

        if($srh_array){
            $srh_array[] = $teacher_id;
            return array('in', $srh_array);
        }

        return $teacher_id;
    }

    /**
     * Des :如果有多个教师，将多个教师姓名拼接后返回
     * Auth:lyt123
     */
    public function getMultipleTeacherCourse($teacher_info, $teacher_id)
    {
        $teacher_data = '';
        if (strpos($teacher_id, ',')) {
            $teacher_ids = explode(',', $teacher_id);
            foreach ($teacher_ids as $teacher_id) {
                $teacher_data .= current(D('Teacher')->getData(array('id' => $teacher_id), array('name'))) . '、';
            }
            $teacher_data = rtrim($teacher_data, '、');
        }
        return $teacher_data ? $teacher_data : $teacher_info;
    }

    public function transferMultipleTeacherNameToTeacherIds($teacher_name)
    {
        $teacher_data = '';
        $teacher_names = explode('、', $teacher_name);
        foreach ($teacher_names as $teacher_name) {
            $teacher_data .= current(D('Teacher')->getData(array('name' => $teacher_name), array('id'))) . ',';
        }
        return rtrim($teacher_data, ',');
    }

    public function transferTeacherIdsToTeacherNames($teacher_id)
    {
        $teacher_data = array();
        $teacher_ids = explode(',', $teacher_id);
        foreach ($teacher_ids as $teacher_id) {
            $teacher_data[] = current(D('Teacher')->getData(array('id' => $teacher_id), array('name')));
        }
        $teacher_data = implode('、', $teacher_data);
        return $teacher_data;
    }

    public function getTeacherPositionById($teacher_id)
    {
        if(strpos($teacher_id, ',')){
            $teacher_data = array();
            $teacher_ids = explode(',', $teacher_id);
            foreach ($teacher_ids as $teacher_id_key => $teacher_id) {
                $teacher_datum = $this->getData(array('id' => $teacher_id), array('name', 'position'));
                $teacher_data[] = $teacher_datum['name'].$teacher_datum['position'];
            }
            $teacher_data = implode(';', $teacher_data);
        }else{
            $teacher_data = current($this->getData(array('id' => $teacher_id), array('position')));
        }

        return $teacher_data;
    }

    public function getTeacherData()
    {
        return $this->table('academy a, teacher t')
            ->field('t.account, t.name, a.name as academy_name')
            ->where('t.academy_id = a.id')
            ->select();
    }

    public function setWorkQualityGatherExcelTitle($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'TID')
            ->setCellValue('B1', 'TNAME')
            ->setCellValue('C1', 'DNAME');
    }

    public function setWorkQualityGatherExcelCellWidth($objPHPExcel)
    {

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(30);
    }
}