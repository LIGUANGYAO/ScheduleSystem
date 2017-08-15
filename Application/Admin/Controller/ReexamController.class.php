<?php
/* User:lyt123; Date:2016/11/26; QQ:1067081452 */
namespace Admin\Controller;

use Common\Controller\BaseController;

class ReexamController extends BaseController
{
    public function __construct()
    {
        $this->reqAdmin();
        parent::__construct();
    }

    public function importReexamExcel()
    {
        $excel_data = $this->loadExcel('exam_make_up-', 'excel_files/exam/exam_make_up/', 2);

        $model = D('Reexam');
        $model->startTrans();

        foreach($excel_data as $item){

            if(!$item){
                $this->ajaxReturn(ajax_ok(array(), '已读取到最后一行'));
            }
            $data['open_class_academy'] = current(D('Academy')->getData(array('name' => $item[0]), array('id')));
            $data['have_class_academy'] = current(D('Academy')->getData(array('name' => $item[1]), array('id')));
            $data['class'] = $item[2];
            $data['student_id'] = $item[3];
            $data['student_name'] = $item[4];
            $data['code'] = $item[5];
            $data['name'] = $item[6];
            $data['semester'] = $item[7];
            $data['teacher_account'] = $item[9];dump($item);
            $this->checkResult($model->addOne($data), $model);
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    public function arrangeExam()
    {
        $model = D('Reexam');
        $data = $model->select();

        dd($data);
    }

    public function teacherCourseSum()
    {
        $teacher_account = '002000023';
        $courses = D('Reexam')->getData(array('teacher_account' => $teacher_account), array('student_id', 'semester', 'code'), true);

        $course_tmp = array();
        $exam_tmp = array();
        foreach($courses as $course){
            $needle_course_sum = $course['semester'].$course['code'];
            if(!in_array($needle_course_sum, $course_tmp)){
                $course_tmp[] = $needle_course_sum;
            }

            $needle_exam_sum = $course['student_id'].$course['code'];
            if(!in_array($needle_exam_sum, $exam_tmp)){
                $exam_tmp[] = $needle_exam_sum;
            }
        }

        dd(count($tmp));
    }

    public function clearExcelData()
    {
        $post_data = $this->reqAdmin()->reqPost(array('excel_name'));
        $model = '';
        if($post_data['excel_name'] == 'reexam'){
            $model = D('Reexam');
            $model->where(1)->delete();
        }
        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }
}