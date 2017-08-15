<?php
/* User:lyt123; Date:2016/11/1; QQ:1067081452 */
namespace Home\Controller;

use Common\Controller\BaseController;

class CourseController extends BaseController
{
    /**
     * Des :根据班级类型获取班级
     * Auth:lyt123
     */
    public function getClassByClassType()
    {
        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $data = $this->reqGet(array('type'));
        //这里1-专升本，2-专科，3/4代表村官班
        if ($data['type'] == 1 || $data['type'] == 2) {
            $classes = D('Class')->getData(array('type' => $data['type']), array('name'), true);
        } else {
            $classes = D('Class')->getData(array('type' => array('in', array(3, 4))), array('name'), true);
        }

        $classes_name = [];
        foreach ($classes as $item) {
            //只取出三个年级的数据
            if(substr($item['name'], 2, 2) >= (substr($year_and_semester_data['year'], 2, 2) -2)){
                $classes_name[] = $item['name'];
            }
        }
        $this->ajaxReturn(ajax_ok($classes_name));
    }

    /**
     * Des :根据院系获取班级及课程名称
     * Auth:lyt123
     */
    public function getClassCourseByAcademy()
    {
        $data = $this->reqGet(array('academy_id'));

        //获取当前学期
        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $data = array_merge($data, $year_and_semester_data);
        $courses = D('Course')->getData($data, array('id', 'name', 'class_id'), true);

        foreach ($courses as $key => $item) {
            $courses[$key]['class_name'] = current(D('Class')->getData(array('id' => $item['class_id']), array('name')));
        }

        $this->ajaxReturn(ajax_ok($courses));
    }

    /**
     * Des :查询课程
     * Auth:lyt123
     */
    public function queryCourse()
    {
        $data = $this->reqGet(array('page', 'limit'), array('class_name', 'code', 'teacher', 'academy', 'academy_id', 'name', 'exam_status', 'order', 'other_query', 'teacher_name_for_teaching_task', 'open_class_academy_id'));

        //获取当前学期,以后有更多的需要获取下个学期的数据的情况直接在if里面继续添加
        if(!empty($data['other_query']) || !empty($data['teacher_name_for_teaching_task'])){
            $year_and_semeseter_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        }else{
            $year_and_semeseter_data = D('NecessaryInfomation')->getJsonData('year_semester');

        }
        $data['semester'] = $year_and_semeseter_data['semester'];
        $data['year'] = $year_and_semeseter_data['year'];

        if ($data['teacher'] || $data['teacher_name_for_teaching_task']) {

            $data['teacher_id'] = D('Course')->getTeacherIdByTeacherName($data, $year_and_semeseter_data);
        }
//        dd($data);

        if ($data['academy'])
            $data['academy_id'] = current(D('Academy')->getData(array('name' => $data['academy']), array('id')));
//        unset($data['academy']);

        if ($data['class_name'])
            $data['class_id'] = current(D('Class')->getData(array('name' => $data['class_name']), array('id')));

//        $data['other_query'] == 1 查看未安排教师课程
//        $data['other_query'] == 2 查看未安排教师课程

        if ($data['other_query'] == 1) {
            $data['teacher_id'] = '';
        } elseif ($data['other_query'] == 2) {
            $data['teacher_id'] = array('neq', '');
        }

        $courses = D('Course')->getCourseData($data);

        if ($data['teacher_name_for_teaching_task']) {
            //获取不重复的课程
            $courses = D('Course')->getTeacherTeachingTaskCourse($courses);
        }

        if ($courses) {
            $result['course_data'] = D('SelectBook')->processData($courses);
            $result['sum'] = count(D('Course')->where($data)->select());
            $this->ajaxReturn(ajax_ok($result));
        }

        $this->ajaxReturn(ajax_error('没有找到课程'));
    }

    /**
     * Des :导出课程表
     * Auth:lyt123
     */
    public function outputCourseExcel()
    {
        $data = $this->reqGet(array(), array('teacher_id', 'academy_id', 'name'));
//$data['class_name'] = 'AY150303';
        //获取当前学期
        $search_data = D('NecessaryInfomation')->getJsonData('year_semester');

        if ($data['teacher_id']) {
            $search_data['teacher_id'] = D('Teacher')->dealWithMultipleTeacherCourse(D('Course'), $data['teacher_id'], $search_data);
        } elseif ($data['name']) {
            $search_data['class_id'] = current(D('Class')->getData(array('name' => $data['name']), array('id')));
        } elseif ($data['academy_id']) {
            $search_data['academy_id'] = $data['academy_id'];
        }

        $course_model = D('Course');
        $course_data = $course_model->getData($search_data, array(), true);

        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $course_model->setCourseExcelCellWidth($objPHPExcel);
        $course_model->setCourseExcelTitle($objPHPExcel, D('NecessaryInfomation')->getYearAndSemesterForExcelTitle($search_data));
        $course_data = D('SelectBook')->processData($course_data);
//        ddd($excel_data);
        $excel_data = array();
        $i = 0;
        foreach ($course_data as $item) {
            $excel_data[$i][0] = $item['academy'];
            $excel_data[$i][1] = $item['class_name'];
            $excel_data[$i][2] = $item['student_sum'];
            $excel_data[$i][3] = $item['code'];
            $excel_data[$i][4] = $item['name'];
            $excel_data[$i][5] = $item['examine_way'];
            $excel_data[$i][6] = $item['time_total'];
            $excel_data[$i][7] = $item['time_theory'];
            $excel_data[$i][8] = $item['time_practice'];
            $excel_data[$i][9] = $item['teacher'];
            $excel_data[$i][10] = D('ClassroomTime')->getTimeClassroomDataForOutputExcel($item['classroom_time']);
            $excel_data[$i][11] = $item['combine_status'];
            $excel_data[$i][12] = $item['comment'];
            $i++;
        }

        //显示excel名称
        if ($data['teacher_id']) {
            $excel_name = $excel_data[0][9];
        } elseif ($data['name']) {
            $excel_name = $excel_data[0][1];
        } elseif ($data['academy_id']) {
            $excel_name = $excel_data[0][0];
        }

        automaticWrapText($objPHPExcel, count($course_data) + 5);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A4');

        promptExcelDownload($objPHPExcel, $excel_name . '课程表' . '.xls');
    }

    /**
     * Des :修改课程时间、地点
     * Auth:lyt123
     */
    public function updateCourse()
    {
        $data = $this->reqAdmin()->reqPost(array('id', 'classroom_time_data'));
//debug();
        //解析类似这样的数据
//        $data['classroom_time_data'] = '周五晚上:2节，第18周，学楼203;周日下午:4节，第14-17周，马兰芳教学楼201;';
//        ddd(get_between($data['classroom_time_data'], '周', '晚'));
        dd($data['classroom_time_data']);
        $classroom_time_data = explode(';', rtrim(trimSpace($data['classroom_time_data']), ';'));

        dd($classroom_time_data);
        $week_section_ids = array();
        $classroom_time = array();
        foreach($classroom_time_data as $item){

            $item = trim($item);
            $separate_classroom_time_data = explode(',', $item);dd($separate_classroom_time_data);
            $length = count($separate_classroom_time_data);

            $place = $separate_classroom_time_data[$length-1];dd($place);
            $classroom_time['number'] = substr($place, -3, 3);
            $classroom_name = rtrim($place, $classroom_time['number']);dd($classroom_name);
            $classroom_time['building'] = D('ClassroomTime')->transferClassroomNameToNumber($classroom_name);

            $period_data = explode(':', $separate_classroom_time_data[0]);dd($period_data);
            $classroom_time['period'] = transferPeriodAndDay($period_data[0], 'day_to_period');
dd($classroom_time);

            if($classroom_time['building'] == 4){
                unset($classroom_time['number']);
                $classroom_time['classroom'] = $place;
                $result = D('ClassroomTime')->addOne($classroom_time);
                $classroom_time_id = $result['data']['id'];dd($classroom_time_id);
            }else{
                $classroom_time_id = current(D('ClassroomTime')->getData($classroom_time, array('id')));
            }
            dd($classroom_time_id);

            $week_section_data['section_num'] = substr($period_data[1], 0, 1);dd($item);
            $week_section_data['use_weeks'] = str_replace('、', ',', substr($separate_classroom_time_data[1], 3, -3));dd($week_section_data);
            $week_section_data['classroom_time_id'] = $classroom_time_id;
            $week_section_id = D('WeekSection')->addOne($week_section_data)['data']['id'];
            $week_section_ids[] = $week_section_id;
dd($week_section_ids);
            unset($classroom_time);
            unset($week_section_id);
            unset($week_section_data);
        }
//ddd($week_section_ids);
        D('Course')->update($data['id'], array('week_section_ids' => implode(',', $week_section_ids)));ddd();
        $this->ajaxReturn(ajax_ok());
    }
}
