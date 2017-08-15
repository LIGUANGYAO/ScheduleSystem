<?php
/* User:lyt123; Date:2016/12/3; QQ:1067081452 */
namespace Admin\Controller;

use Common\Controller\BaseController;

class SelectBookController extends BaseController
{
    /**
     * Des :导入班级表
     * Auth:lyt123
     */
    public function importClassExcel()
    {
        $excel_data = $this->loadExcel('class-', 'excel_files/class/', 2);

        $model = D('Class');
        $model->startTrans();

        foreach ($excel_data as $item) {

            $this->checkLoadToLastLine($item[0], $model);

            //用于在class表中增加profession字段
            /*$class_id = current(D('Class')->getData(array('name' => $item[3]), array('id')));
            D('Class')->update($class_id, array('profession' => $item[2]));
            continue;*/

            $data['profession'] = $item[2];
            $data['name'] = $item[3];
            $data['student_sum'] = $item[7];

            if (substr($item[3], 0, 1) == 'A') {

                if (substr($item[3], -2, 1) == 'C') {
                    $data['type'] = 3;
                } else {
                    $data['type'] = 1;
                }

            } elseif (substr($item[3], 0, 1) == 'B') {

                if (substr($item[3], -2, 1) == 'C') {
                    $data['type'] = 4;
                } else {
                    $data['type'] = 2;
                }

            }
            $result = $model->addOne($data);
            $this->checkResult($result, $model);
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :导入开课一览表
     * Auth:lyt123
     */
    public function importSelectBook()
    {
        $post_data = $this->reqAdmin()->reqPost(array('admin_confirmed'));
debug();
        $admin_confirmed = $post_data['admin_confirmed'] == 2 ? true : false;

        //缓存处理可以加快速度
        if(S('excel_data')){
            $excel_data = S('excel_data');
        }
        else{
            $excel_data = $this->loadExcel('select_book-', 'excel_files/select_book/', 1);
            S('excel_data', $excel_data);
        }
//        $excel_data = $this->loadExcel('select_book-', 'excel_files/select_book/', 1);


//        $excel_data = array_reverse($excel_data);
//        ddd($excel_data);
        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $classes = D('Class')->select();

        //将class表中的四个年级中的最高年级去除
        foreach($classes as $single_class_key => $single_class){
            if(substr($single_class['name'], 2, 2) < (substr($year_and_semester_data['year'], 2, 2)-1)){
                unset($classes[$single_class_key]);
            }
        }
        sort($classes);//重建索引

        $course_model = D('Course');
        $course_model->startTrans();

        $no_match_course_box = array();
        foreach ($excel_data as $key => $item) {

            if (!$item[0]) {
                if (!$no_match_course_box || $admin_confirmed) {
                    $course_model->commit();
                    $this->ajaxReturn(ajax_ok('已读取到最后一行'));
                } else {
                    $course_model->rollback();
                    $this->ajaxReturn(ajax_error('第n个课程没有匹配到班级', 422, $no_match_course_box));
                }
            }
//提取excel表的值
            $data['profession'] = $item[0];
            if ($result = current(D('Academy')->getData(array('name' => $item[1]), array('id')))) {
                $data['open_class_academy_id'] = $result;
            } else {
                $data['open_class_academy_id'] = 0;//侨乡文化研究中心，先填坑
//                $data['open_class_academy_value'] = $admin_confirmed ? $item[1] : '';
                $data['open_class_academy_value'] = $item[1];
            }
            if ($result = current(D('Academy')->getData(array('name' => $item[2]), array('id')))) {
                $data['academy_id'] = $result;
            } else {
                $data['academy_id'] = 0;//侨乡文化研究中心，先填坑
//                $data['have_class_academy_value'] = $admin_confirmed ? $item[2] : '';
                $data['have_class_academy_value'] = $item[2];
            }

            $data['grade_and_type'] = $item[3];
            if ($item[4] == 1 || $item[4] == 3 || $item[4] == 5) {
                continue;
                $data['semester'] = 1;
            } else {
                $data['semester'] = 2;
            }
            $data['year'] = D('NecessaryInfomation')->getJsonData('year_semester')['year']+1;

            $data['code'] = $item[6] ? $item[6] : '';//老师数据不对
            $data['name'] = $item[7];
            $data['examine_way'] = transferExamineWayNameToNumber($item[8]);
            $data['time_total'] = $item[9];
            $data['time_theory'] = $item[10] ? $item[10] : 0;
            $data['time_practice'] = $item[11] ? $item[11] : 0;
            $data['comment'] = $item[14] ? $item[14] : '';

//年级
            $grade = 0;
            switch (substr($data['grade_and_type'], 0, 3)) {
                case '一':
                    $grade = 1;
                    break;
                case '二':
                    $grade = 2;
                    break;
                case  '三':
                    $grade = 3;
                    break;
            }

            $profession_match = 0;//如果匹配到专业就+1
            $class_match = 0;//如果匹配到班级就+1
            $is_match = 0;//表示已经匹配到了
//            $other_error = 0;//如果不属于以上两种错误就+1
            foreach ($classes as $class) {

                //本科-1，专科-2
                $type = substr($data['grade_and_type'], 9, 3) == '本' ? array(1, 3) : array(2, 4);

                if (
                    $class['profession'] == $data['profession']
                ) {
//                    dump($class);
//                    dd($item);
                    $profession_match++;
                    if (
                        in_array($class['type'], $type) &&
                        substr($class['name'], 2, 2) ==
                        (
                        substr($year_and_semester_data['year'] - $grade + 2, -2, 2)
                        )
                    ) {
                        $class_match++;
                        //插入课程表
                        $data['class_id'] = $class['id'];
//                        ddd($data);
                        $result = $course_model->addOne($data);
                        $this->checkResult($result, $course_model);
                        $is_match++;
                    }
                }
            }

            if ($profession_match == 0) {
                $no_match_course_box[] = "专业找不到:第{$key}个课程（{$data['profession']}-{$data['grade_and_type']}-{$data['name']}）";
            } elseif ($class_match == 0) {
                $no_match_course_box[] = "班级找不到:第{$key}个课程（{$data['profession']}-{$data['grade_and_type']}-{$data['name']}）";
            } elseif ($is_match == 0 || $data['academy_id'] == 0 || $data['open_class_academy_id'] == 0) {
                $no_match_course_box[] = "其他错误:第{$key}个课程（{$data['profession']}-{$data['grade_and_type']}-{$data['name']}）";
            }

            unset($data);
        }

        if ($no_match_course_box && !$admin_confirmed) {
            $course_model->rollback();
            $this->ajaxReturn(ajax_error('', 422, $no_match_course_box));
        }

        $course_model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :安排合班
     * Auth:lyt123
     */
    public function academyAdminArrangeCombineClass()
    {
        $this->reqAdminAcademy();
        //状态控制
        $content = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/select_book_arrange_status.json'), 1);

        if ($content['status'] != 2) {
            $this->ajaxReturn(ajax_error('等待管理员开启权限'));
        }

        $data = I('post.', '', '');//将默认的过滤方法去除，否则json字符串不能成功接收
        dd($data);
        $model = D('Teacher');
        $model->startTrans();

        $teacher_data = json_decode($data['teacher'], 1);
        $teacher_ids = array();
        dd($teacher_data);
        foreach ($teacher_data as $item) {
            $teacher_id = current(D('Teacher')->getData(array('name' => $item['name']), array('id')));
            if(empty($teacher_id)){
                $this->ajaxReturn(ajax_error("教师'{$item['name']}'不存在，请联系管理员添加该教师"));
            }
            D('Teacher')->update($teacher_id, array('position' => $item['position']));
            $teacher_ids[] = $teacher_id;
        }
        $teacher_id = implode(',', $teacher_ids);
        dd($teacher_id);
        $class_ids = array();
        $course_ids = json_decode($data['course_ids'], 1);
        dd($course_ids);
        foreach ($course_ids as $course_id) {
            $result = D('Course')->update($course_id, array('teacher_id' => $teacher_id));
//            $this->checkResult($result, $model);

            $class_ids[] = current(D('Course')->getData(array('id' => $course_id), array('class_id')));
        }
        dd($course_ids);

        if(count($course_ids) > 1){
            foreach ($course_ids as $course_id) {
                D('Course')->update($course_id, array('combine_status' => implode(',', $class_ids)));
            }
        }


        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :取消合班安排
     * Auth:lyt123
     */
    public function academyAdminCancelCombineClass()
    {
        $post_data = $this->reqAdminAcademy()->reqPost(array('id'));
        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        //状态控制
        $content = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/select_book_arrange_status.json'), 1);

        if ($content['status'] != 2) {
            $this->ajaxReturn(ajax_error('等待管理员导入开课一览表'));
        }

        $course_data = D('Course')->getData(array('id' => $post_data['id']), array('combine_status', 'name', 'class_id'));
dd($course_data);
        if(!$course_data['combine_status']){
            $course_classes[0] = $course_data['class_id'];
        }else{
            $course_classes = explode(',', $course_data['combine_status']);
        }

        dd($course_data);
        foreach($course_classes as $class_id){
            $course_id = current(D('Course')->getData(array('class_id' => $class_id, 'name' => $course_data['name'], 'semester'=> $year_and_semester_data['semester'], 'year' => $year_and_semester_data['year'])));dd($course_id);
            D('Course')->update($course_id, array('combine_status' => '', 'teacher_id' => ''));
        }

        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :教师填写排课要求
     * Auth:lyt123
     */
    public function teacherFillInCourseRequire()
    {
        $data = $this->reqTeacher()->reqPost(array('course_id', 'check_way', 'time_media', 'time_in_class', 'require'));
        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        //状态控制(这个功能不要了，直接教务员填写了教师之后教师就可以查到并填写了)
       /* $content = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/select_book_arrange_status.json'), 1);
        if ($content['status'] != 3) {
            $this->ajaxReturn(ajax_error('等待教务员为课程安排教师'));
        }*/

        $model = D('TeacherRequire');
        $model->startTrans();

        if($teacher_require_id = current(D('Course')->getData(array('id' => $data['course_id']), array('teacher_require_id')))){
            $this->checkResult(D('TeacherRequire')->update($teacher_require_id, $data), $model);
        }else{
            $result = $model->addOne($data);
            $this->checkResult($result, $model);
            $course_data = D('Course')->getData(array('id' => $data['course_id']), array('combine_status', 'name'));
//        ddd($course_data);
            if ($course_data['combine_status']) {
                $class_ids = explode(',', $course_data['combine_status']);
                foreach ($class_ids as $class_id) {
                    $course_id = current(D('Course')->getData(array('name' => $course_data['name'], 'class_id' => $class_id, 'semester'=> $year_and_semester_data['semester'], 'year' => $year_and_semester_data['year']), array('id')));
                    $this->checkResult(D('Course')->update($course_id, array('teacher_require_id' => $result['data']['id'])), $model);
                }
            }
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :教师修改排课要求
     * Auth:lyt123
     */
    public function teacherUpdateCourseRequire()
    {
        $this->reqTeacher();

        //状态控制
        $content = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/select_book_arrange_status.json'), 1);
        if ($content['status'] != 3) {
            $this->ajaxReturn(ajax_error('等待教务员为课程安排教师'));
        }

        $data = I('teacher_require', '', '');
        $this->ajaxReturn(D('TeacherRequire')->update($data['course_id'], $data, 'course_id'));
    }

    /**
     * Des :获取教师普教上课的时间
     * Auth:lyt123
     */
    public function getTeacherExistingClass()
    {
        ini_set('max_execution_time', 3000);
        $year_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();
        $finished_teachers = D('TeacherExistingClass')->getData(array('semester' => $year_semester_data['semester'], 'year' => $year_semester_data['year']), array('teacher_id'), true);
        $finished_teachers = array_column_equal($finished_teachers, 'teacher_id');
dd($finished_teachers);
        //这里如果$finished_teachers为空数组，会报错
        if($finished_teachers){
            $not_finished_teachers = D('Course')->getData(array('teacher_id' => array('NOTIN', $finished_teachers), 'semester' => $year_semester_data['semester'], 'year' => $year_semester_data['year']), array('teacher_id'), true);
        } else {
            $not_finished_teachers = D('Course')->getData(array('semester' => $year_semester_data['semester'], 'year' => $year_semester_data['year']), array('teacher_id'), true);
        }
dd($finished_teachers);
//dd($course_data);
        $model = D('TeacherExistingClass');
        $model->startTrans();

//        $time = time();
        foreach ($not_finished_teachers as $item) {
//            if ((time() - $time) > 10) {
//                $model->commit();
//                $this->ajaxReturn(ajax_ok());
//            }

            $all_teachers = array();
            if (strpos($item['teacher_id'], ',')) {
                $all_teachers = explode(',', $item['teacher_id']);
            } else {
                $all_teachers[] = $item['teacher_id'];
            }

            foreach ($all_teachers as $one_teacher) {
                if ($one_teacher && !D('TeacherExistingClass')->getData(array('teacher_id' => $one_teacher, 'semester' => $year_semester_data['semester'], 'year' => $year_semester_data['year']))) {

                    $teacher_name = current(D('Teacher')->getData(array('id' => $item['teacher_id']), array('name')));
                    dd($teacher_name);
                    $class_html = $model->getTeacherClassHtmlFromShoolWebsite($teacher_name);

                    $classes = $model->extractTeacherClassFromHtml($class_html);
//dd($classes);
                    dd($classes);
                    //这里，老师希望留着上下午的数据，如果一个老师上下午的课比较多，她就不排晚上的课了。
                    /*$existing_class = array();
                    foreach ($classes as $class) {
                        if (substr($class, -6, 6) == '晚上') {
                            $existing_class[] = $class;
                        }
                    }*/
                    $result = $model->addOne(array('teacher_id' => $one_teacher, 'existing_class' => implode('\\', $classes), 'semester' => $year_semester_data['semester'], 'year' => $year_semester_data['year']));
                    $this->checkResult($result, $model);
                }
            }
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :查询教师普教上课时间
     * Auth:lyt123
     */
    public function showTeacherExistingClass()
    {
        $data = $this->reqGet(array(), array('teacher_name'));

        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        if ($data['teacher_name']) {
            $result = D('TeacherExistingClass')->table('teacher_existing_class tec, teacher t')
                ->field('tec.*, t.name')
                ->where('tec.teacher_id = t.id')
                ->where(array('t.name' => $data['teacher_name'], 'semester'=> $year_and_semester_data['semester'], 'year' => $year_and_semester_data['year']))
                ->select();
        } else {
            $result = D('TeacherExistingClass')->table('teacher_existing_class tec, teacher t')
                ->field('tec.*, t.name')
                ->where('tec.teacher_id = t.id')
                ->where($year_and_semester_data)
                ->select();
        }

        $this->ajaxReturn(ajax_ok($result));
    }

    /**
     * Des :导出一个学期的开课一览表
     * Auth:lyt123
     */
    public function outputSelectBookOfOneSemester()
    {
//        $post_data = $this->reqPost(array('semester'));
        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        $model = D('SelectBook');

        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();

        $model->setSelectBookOfOneSemesterExcelTitle($objPHPExcel, D('NecessaryInfomation')->getYearAndSemesterForExcelTitle($year_and_semester_data));
        $model->setSelectBookOfOneSemesterExcelCellWidth($objPHPExcel);

        $course_data = D('Course')->where(array('semester' => $year_and_semester_data['semester'], 'year' => $year_and_semester_data['year']))->select();
        $course_data = $model->processData($course_data);

        $excel_data = array();
        $i = 0;
        foreach ($course_data as $item) {
            $excel_data[$i][0] = $item['profession'];
            $excel_data[$i][1] = $item['open_class_academy'];
            $excel_data[$i][2] = $item['academy'];
            $excel_data[$i][3] = $item['class_name'];
            $excel_data[$i][4] = $item['student_sum'];
            $excel_data[$i][5] = $item['code'];
            $excel_data[$i][6] = $item['name'];
            $excel_data[$i][7] = $item['examine_way'];
            $excel_data[$i][8] = $item['time_total'];
            $excel_data[$i][9] = $item['time_theory'];
            $excel_data[$i][10] = $item['time_practice'];
            $excel_data[$i][11] = $item['teacher'];
            $excel_data[$i][12] = $item['teacher_position'];
            $excel_data[$i][13] = $item['comment'];
            $excel_data[$i][14] = D('TeacherRequire')->reorganizeData($item['teacher_require_data']);
            $excel_data[$i][15] = $item['combine_status'];
            $excel_data[$i][16] = str_replace('\\', "\n", $item['teacher_existing_class']);
            $i++;
        }

//        dd($excel_data);
        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A3');
        promptExcelDownload($objPHPExcel, '开课一览表.xls');
    }

    /**
     * Des :导出课程表模板
     * Auth:lyt123
     */
    public function outputCourseOfOneSemester()
    {
        $data = $this->reqGet(array('course_type'));
        $model = D('SelectBook');
        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();

        $model->setCourseOfOneSemesterExcelTitle($objPHPExcel, $data['course_type'], D('NecessaryInfomation')->getYearAndSemesterForExcelTitle($year_and_semester_data));

        if ($data['course_type'] == 1) {
            $type_array = array(1, 2);
        } else {
            $type_array = array(3, 4);
        }


        $course_data = D('Course')
            ->table('course co, class cl')
            ->field('co.*')
            ->where('co.class_id = cl.id')
            ->where(array('cl.type' => array('in', $type_array)))
            ->where(array('co.semester' => $year_and_semester_data['semester'], 'co.year' => $year_and_semester_data['year']))
            ->order('class_id')
            ->select();
        $course_data = $model->processData($course_data);
//ddd($course_data);
        $excel_data = array();
        $i = 0;
        $j = 0;
        $class_ids = array();
        foreach ($course_data as $item) {
            //序号要跟随班级递增
            if (in_array($item['class_id'], $class_ids)) {
                $excel_data[$i][0] = $j;
            } else {
                $excel_data[$i][0] = ++$j;
                $class_ids[] = $item['class_id'];
            }
            $excel_data[$i][1] = $item['academy'];
            $excel_data[$i][2] = $item['class_name'];
            $excel_data[$i][3] = $item['student_sum'];
            $excel_data[$i][4] = $item['code'];
            $excel_data[$i][5] = $item['name'];
            $excel_data[$i][6] = $item['examine_way'];
            $excel_data[$i][7] = $item['time_total'];
            $excel_data[$i][8] = $item['time_theory'];
            $excel_data[$i][9] = $item['time_practice'];
            $excel_data[$i][10] = $item['teacher'];
            if ($data['course_type'] == 1) {
                $excel_name = '学期课程表.xls';
                $excel_data[$i][11] = '';
                $excel_data[$i][12] = '';
                $excel_data[$i][13] = '';
                $excel_data[$i][14] = '';
                $excel_data[$i][15] = '';
                $excel_data[$i][16] = '';
                $excel_data[$i][17] = '';
                $excel_data[$i][18] = '';
                $excel_data[$i][19] = '';
                $excel_data[$i][20] = $item['combine_status'];
                $excel_data[$i][21] = $item['comment'];
            } else {
                $excel_name = '村官班课程表.xls';
                $excel_data[$i][11] = '';
                $excel_data[$i][12] = '';
                $excel_data[$i][13] = $item['comment'];
            }
            $i++;
        }

        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A4');
        promptExcelDownload($objPHPExcel, $excel_name);
    }
}