<?php
/* User:lyt123; Date:2016/11/4; QQ:1067081452 */
namespace Admin\Controller;

class ExamController extends AdminBaseController
{
    /* 标记考试状态为已考试 */
    public function showMarkExamedCourse()
    {
        $this->reqAdmin();
        $json_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $courses = D('Course')->getData(array('exam_status' => 7, 'semester' => $json_data['semester'], 'year' => $json_data['year']), array(), true);
        $this->ajaxReturn(ajax_ok(D('SelectBook')->processData($courses)));
    }

    public function markCourseIsExamed()
    {
        $post_data = $this->reqAdmin()->reqPost(array('id'));
        $this->ajaxReturn(D('Course')->update($post_data['id'], array('exam_status' => 7)));
    }

    public function markCourseNotExamed()
    {
        $post_data = $this->reqAdmin()->reqPost(array('id'));
        $this->ajaxReturn(D('Course')->update($post_data['id'], array('exam_status' => 1)));
    }

    public function automaticArrangeExam()
    {
        $post_data = $this->reqAdmin()->reqPost(array('week'));

        $course_model = D('Course');

        $json_data = D('NecessaryInfomation')->getJsonData('year_semester');

        if ($post_data['week'] == 13) {
            //将十二周前结束上课的课程的exam_status标记为4
            $courses = $course_model->getData(array('exam_status' => array('NEQ', 7), 'examine_way' => 1, 'semester' => $json_data['semester'], 'year' => $json_data['year']), array(), true);
            foreach ($courses as $course) {
                $week_section_ids = explode(',', $course['week_section_ids']);
                foreach ($week_section_ids as $week_section_id) {
                    $use_weeks = current(D('WeekSection')->getData(array('id' => $week_section_id), array('use_weeks')));
                    if (substr($use_weeks, -2, 2) > 12) {
                        continue 2;
                    }
                }
                //这里在十三周安排考试的时候，会先判断这门课是否在十二周前结束考试，如果是-》exam_status = 4,这样设计不会导致错误
                $course_model->update($course['id'], array('exam_status' => 4));
            }
            if ($this->automaticArrangeExamInWeek($course_model, 13, $json_data)['status'] == 200) {
                $course_model->commit();
                $this->ajaxReturn(ajax_ok());
            }
        }

        if ($post_data['week'] == 19) {
            if (
                $this->automaticArrangeExamForPublicCourseInNineteenthWeek($course_model, $json_data)['status'] == 200 &&
                $this->automaticArrangeExamInWeek($course_model, 19, $json_data)['status'] == 200
            ) {
//                $course_model->commit();
                $this->ajaxReturn(ajax_ok());
            }
        }
        $course_model->rollback();
        $this->ajaxReturn(ajax_error());

    }

    public function clearExamArrangeData()
    {
        $post_data = $this->reqAdmin()->reqPost(array('week'));

        $course_model = D('Course');
        $course_model->startTrans();

        $delete_exam_result = '';
        $delete_course_result = '';

        if ($post_data['week'] == 13) {
            $delete_exam_result = D('Exam')->where(array('week' => 13))->delete();
            $delete_course_result = D('Course')->where(array('exam_status' => array('in', [5, 6])))->save(array('exam_status' => 4));
        }
        if ($post_data['week'] == 19) {
            $delete_exam_result = D('Exam')->where(array('week' => 19))->delete();
            $delete_course_result = D('Course')->where(array('exam_status' => array('in', [2, 3])))->save(array('exam_status' => 1));
        }

        if (
            $delete_exam_result &&
            $delete_course_result !== false
        ) {
            $course_model->commit();
            $this->ajaxReturn(ajax_ok(array(), '清空数据成功'));
        }
        $course_model->rollback();
        $course_model->commit();
        $this->ajaxReturn(ajax_error('清空数据失败'));
    }

    /**
     * Des :查询考试信息
     * Auth:lyt123
     */
    public function showExam()
    {
        $data = $this->reqPost(array('order'), array('week', 'open_class_academy', 'have_class_academy', 'teacher_name', 'class_name', 'code', 'name','open_class_academy_id'));
        $model = D('Exam');

        //填坑，week字段本来为必须的，但在填写监考教师那里不能判断到当前周数，只能这样了
        if($data['open_class_academy_id']){
            $json_data_exam_status = D('NecessaryInfomation')->getJsonData('exam_status')['status'];
            if($json_data_exam_status == 2){
                $data['week'] = 13;
            }elseif($json_data_exam_status == 3){
                $data['week'] = 19;
            }elseif($json_data_exam_status == 1){
                $this->ajaxReturn(ajax_error('等待管理员开启权限'));
            }
        }

        $search_data = $model->getSearchData($data);
        $exam_data = $model->getExamData($search_data, $data['week'], false);

        $this->ajaxReturn(ajax_ok($exam_data));
    }

    public function getAvailableClassroom()
    {
        $data = $this->reqAdmin()->reqPost(array('id'));
        $exam_data = D('Exam')->getData($data);
        $classroom_times = D('ClassroomTime')->getData(array('building' => 1, 'period' => $exam_data['period']), array('id', 'number'), true);
//        dump($classroom_times);

        //十三周寻找教室需要与普教上课使用教室不一样
        $classroom_time_data = array();
        $i = 0;
        if ($exam_data['week'] == 13) {
            foreach ($classroom_times as $classroom_time) {
//                dump(D('Exam')->getData(array('classroom_time_id' => $classroom_time['id'])));
                if (!D('Exam')->getData(array('classroom_time_id' => $classroom_time['id'], 'code' => array('neq', $exam_data['code'])))) {
                    $week_section_data = D('WeekSection')->getData(array('classroom_time_id' => $classroom_time['id']), array('use_weeks'), true);
//                    dump($week_section_data);
                    foreach ($week_section_data as $one_week_section_data) {
                        if (strpos($one_week_section_data['use_weeks'], strval($exam_data['week'])) === false) {
                            $classroom_time_data[$i]['classroom_id'] = $classroom_time['id'];
                            $classroom_time_data[$i]['classroom'] = '马兰芳教学楼' . $classroom_time['number'];
                            $i++;
                            continue 2;
                        }
                    }
                }
            }
        } elseif ($exam_data['week'] == 19) {
            foreach ($classroom_times as $classroom_time) {
                if (!D('Exam')->getData(array('classroom_time_id' => $classroom_time['id'], 'code' => array('neq', $exam_data['code'])))) {
                    $classroom_time_data[$i]['classroom_id'] = $classroom_time['id'];
                    $classroom_time_data[$i]['classroom'] = '马兰芳教学楼' . $classroom_time['number'];
                    $i++;
                }
            }
        }

        $this->ajaxReturn(ajax_ok($classroom_time_data));
    }

    /**
     * Des :导出考试安排表
     * Auth:lyt123
     */
    public function outputExamExcel()
    {
        $data = $this->reqGet(array('week'), array('open_class_academy', 'have_class_academy', 'teacher_name', 'class_name'));

//        $data['week'] = 19;
//        $data['teacher_name'] = '吴淑娟';
        $model = D('Exam');
        $search_data = $model->getSearchData($data);        ddd($search_data);

        $excel_data = $model->getExamData($search_data, $data['week'], true);
ddd($excel_data);
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $model->setWorkExamExcelTitle($objPHPExcel, $data['week']);
        $model->setWorkExamExcelCellWidth($objPHPExcel);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A3');
        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        promptExcelDownload($objPHPExcel, "继续教育学院第{$data['week']}周考试安排表".'.xls');
    }

    public
    function updateExamClassroom()
    {
        $data = $this->reqAdmin()->reqPost(array('id'), array('classroom_id', 'other_classroom'));
        if ($data['classroom_id']) {
            $this->ajaxReturn(D('Exam')->update($data['id'], array('classroom_time_id' => $data['classroom_id'], 'other_classroom' => ' ')));
        } else {
            $this->ajaxReturn(D('Exam')->update($data['id'], array('other_classroom' => $data['other_classroom'], 'classroom_time_id' => 0)));
        }
    }

    public function getAvailableTime()
    {
        $data = $this->reqAdmin()->reqPost(array('id'));
        $json_data = D('NecessaryInfomation')->getJsonData('year_semester');

        $model = D('Exam');
        $model->startTrans();
        $exam_data = $model->getData(array('id' => $data['id']));
        $model->where(array('classroom_time_id' => $exam_data['classroom_time_id'], 'week' => $exam_data['week'], 'period' => $exam_data['period']))->delete();
        $course_data = D('Course')->getData(array('code' => $exam_data['code'], 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('combine_status', 'name', 'class_id'));
        $combine_classes = array();
        if ($course_data['combine_status']) {
            $combine_classes = explode(',', $course_data['combine_status']);
        } else {
            $combine_classes[] = $course_data['class_id'];
        }

        $course_id = 0;
        foreach ($combine_classes as $combine_class) {
            $course_id = current(D('Course')->getData(array('name' => $course_data['name'], 'class_id' => $combine_class, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('id')));
            D('Course')->where(array('id' => $course_id))->setDec('exam_status');
        }

        $course_info = D('Course')->getData(array('id' => $course_id), array('id', 'teacher_id', 'class_id', 'combine_status', 'name', 'code'));
        $remain_period = $this->getRemainPeriod($course_info, $exam_data['week'], $json_data);

        $available_time = array();
        $available_time['course_id'] = $course_id;
        $available_time['week'] = $exam_data['week'];
        foreach ($remain_period as $key => $period) {
            $available_time['time'][$key]['period'] = $period;
            $available_time['time'][$key]['date'] = D('Exam')->transferPeriodToDate($period, $exam_data['week']);
        }
        $this->ajaxReturn(ajax_ok($available_time));
    }

    public function updateExamTime()
    {
        $data = $this->reqAdmin()->reqPost(array('course_id', 'week', 'period'));
        $json_data = D('NecessaryInfomation')->getJsonData('year_semester');

        $course_model = D('Course');
        $course_info = $course_model->getData(array('id' => $data['course_id']));
        if ($course_info['combine_status']) {
            $combine_classes = explode(',', $course_info['combine_status']);

            foreach ($combine_classes as $combine_class) {
                $code = current($course_model->getData(array('name' => $course_info['name'], 'class_id' => $combine_class, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('code')));

                //这里可能不同课程代号但课程名称相同的班级合班
                $exam_id = current(D('Exam')->getData(array('class_id' => $combine_class, 'code' => $code), array('id')));
                D('Exam')->update($exam_id, array('period' => $data['period'], 'classroom_time_id' => '', 'other_classroom' => ''));
            }
        } else {
            $code = current($course_model->getData(array('name' => $course_info['name'], 'class_id' => $course_info['class_id'], 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('code')));

            $exam_id = current(D('Exam')->getData(array('class_id' => $course_info['class_id'], 'code' => $code), array('id')));

            D('Exam')->update($exam_id, array('period' => $data['period'], 'classroom_time_id' => '', 'other_classroom' => ''));
        }

        $this->ajaxReturn(ajax_ok());
    }

    public function automaticArrangeExamInWeek($course_model, $exam_week, $json_data)
    {
        //获取未考试课程信息(exam_status == 1)
//        $course_model = D('Course');
//        $course_model->startTrans();

        if ($exam_week == 19) {
            $exam_status = 1;
        } elseif ($exam_week == 13) {
            $exam_status = 4;
        }

        while ($course_info = $course_model->getData(array('exam_status' => $exam_status, 'examine_way' => 1, 'semester' => $json_data['semester'], 'year' => $json_data['year']), array('id', 'teacher_id', 'class_id', 'combine_status', 'name', 'code'))) {
//        $course_info = $course_model->getData(array('exam_status' => $exam_status, 'examine_way' => 1, 'code' => '003L3250'), array('id', 'teacher_id', 'class_id', 'combine_status', 'name', 'code'));dump($course_info);
//        $course_info = $course_model->getData(array('exam_status' => $exam_status, 'examine_way' => 1, 'code' => '003L3290'), array('id', 'teacher_id', 'class_id', 'combine_status', 'name', 'code'));dump($course_info);

            dd($course_info);
            $class_name = current(D('Class')->getData(array('id' => $course_info['class_id']), array('name')));

            $remain_period = $this->getRemainPeriod($course_info, $exam_week, $json_data);

            dd($remain_period);
            //循环剩余的period，寻找合适的教室
            if (count($remain_period) == 0) {
                //一般$remain_period都不会为空
                $course_model->update($course_info['id'], array('exam_status' => $exam_status + 2));
            } else {

                $fit_period = array();
                $class_type = substr($class_name, 0, 1);
                if ($class_type == 'A') {
                    foreach ($remain_period as $one_period) {
                        if ($one_period >= 8) {
                            $fit_period[] = $one_period;
                        }
                    }
                } else {
                    foreach ($remain_period as $one_period) {
                        if ($one_period <= 7) {
                            $fit_period[] = $one_period;
                        }
                    }
                }

                if (count($fit_period) >= 1)
                    $remain_period = $fit_period;

                //count($remain_period) >= 1
                //随机选择一个时间
                $period_sum = count($remain_period);
                $rand = rand(0, $period_sum - 1);
                $remain_period = $remain_period[$rand];
//                dd($remain_period);
                $student_sum = 0;
                $classroom_time_id = 0;
                if ($course_info['combine_status']) {

                    $combine_classes = explode(',', $course_info['combine_status']);dd($combine_classes);
                    foreach ($combine_classes as $combine_class) {
                        $student_sum += current(D('Class')->getData(array('id' => $combine_class), array('student_sum')));
                        $course_id = current($course_model->getData(array('class_id' => $combine_class, 'name' => $course_info['name'], 'semester' => $json_data['semester'], 'year' => $json_data['year']), array('id')));dd($course_id);
                        $course_model->update($course_id, array('exam_status' => $exam_status + 1));
                    }

                    if ($student_sum <= 80) {
                        //寻找教室，找到classroom_time_id
                        $classroom_time_id = D('Exam')->findExamClassroom($remain_period, $exam_week);

                        foreach ($combine_classes as $combine_class) {
                            $code = current($course_model->getData(array('name' => $course_info['name'], 'class_id' => $combine_class, 'semester' => $json_data['semester'], 'year' => $json_data['year']), array('code')));
                            D('Exam')->addOne(array('classroom_time_id' => $classroom_time_id, 'class_id' => $combine_class, 'code' => $code, 'week' => $exam_week, 'period' => $remain_period));
                        }
                    } else {
//                        dd($combine_classes);
                        foreach ($combine_classes as $combine_class) {
                            $code = current($course_model->getData(array('name' => $course_info['name'], 'class_id' => $combine_class, 'semester' => $json_data['semester'], 'year' => $json_data['year']), array('code')));
//                            dump($code);
//                            dump($course_info);
                            D('Exam')->addOne(array('class_id' => $combine_class, 'code' => $code, 'week' => $exam_week, 'period' => $remain_period));
                        }
                    }
                } else {
                    $student_sum = current(D('Class')->getData(array('id' => $course_info['class_id']), array('student_sum')));

                    if ($student_sum <= 80) {
                        //寻找教室，找到classroom_time_id
                        $classroom_time_id = D('Exam')->findExamClassroom($remain_period, $exam_week);

                        D('Exam')->addOne(array('class_id' => $course_info['class_id'], 'classroom_time_id' => $classroom_time_id, 'code' => $course_info['code'], 'week' => $exam_week, 'period' => $remain_period));
                    } else {
                        D('Exam')->addOne(array('class_id' => $course_info['class_id'], 'code' => $course_info['code'], 'period' => $remain_period, 'week' => $exam_week));
                    }
                    $course_model->update($course_info['id'], array('exam_status' => $exam_status + 1));
                }
            }
//            dd();
        }
//        $course_model->commit();
        return ajax_ok();
    }

    public function getRemainPeriod($course_info, $exam_week, $json_data)
    {
        if ($exam_week == 19) {
            $exam_status = 1;
        } elseif ($exam_week == 13) {
            $exam_status = 4;
        }

        $class_name = current(D('Class')->getData(array('id' => $course_info['class_id']), array('name')));


        if (preg_match('/[C]/', $class_name))
            $origin_periods = array_diff(array(6, 7), D('Holiday')->getPeriods(current(D('Holiday')->getData(array('week' => $exam_week), array('weekday')))));//非村官班的班级里有'C'
        else
            $origin_periods = array_diff(array(1, 2, 3, 4, 5, 8, 9, 10, 11), D('Holiday')->getPeriods(current(D('Holiday')->getData(array('week' => $exam_week), array('weekday')))));
//dump($origin_periods);
        //排除教师普教上课时间
        $teacher_existing_class_period = self::filterTeacherExistingClass($course_info['teacher_id'], $exam_week, $json_data);

        //排除教师其他课程考试时间
        $teacher_other_course_exam_period = self::filterTeacherOtherCourseExam($course_info['teacher_id'], $exam_week, $exam_status + 1, $json_data);

//                    dump($course_info['name']);
//        dump('teacher_other_course_exam_period:');
//            dump($teacher_other_course_exam_period)

        //排除教师在成教上课时间
        $teacher_course_period = array();
        if ($exam_week == 13) {
            $teacher_course_period = self::filterTeacherCourseInthirteenthWeek($course_info['teacher_id'], $json_data);
//                dump('teacher_course_period:');
//            dump($course_info['name']);
//                dump($teacher_course_period);
        }

        //排除班级（或合班）其他课程考试时间
        $class_course_period = array();
        if ($course_info['combine_status']) {
            $class_other_course_period = self::filterClassOtherCourseExam($course_info['combine_status'], $exam_week, $exam_status + 1, $json_data);
            if ($exam_week == 13) {
                $class_course_period = self::filterClassCourseInthirteenthWeek($course_info['combine_status'], $json_data);
            }
        } else {
            $class_other_course_period = self::filterClassOtherCourseExam($course_info['class_id'], $exam_week, $exam_status + 1, $json_data);
            if ($exam_week == 13) {
                $class_course_period = self::filterClassCourseInthirteenthWeek($course_info['class_id'], $json_data);
            }
        }
//dd();
//            dump('class_other_course_period:');
//            dump($class_other_course_period);

        //剩余的period
        $remain_period = array_values(array_diff($origin_periods, $teacher_existing_class_period, $teacher_other_course_exam_period, $class_other_course_period, $teacher_course_period, $class_course_period));

        return $remain_period;
    }

    /**
     * Des :更新监考教师
     * Auth:lyt123
     */
    public
    function updateMonitorTeacher()
    {
        $post_data = $this->reqAdminAcademy()->reqPost(array('id', 'monitor_teacher_name'));

        $week = current(D('Exam')->getData(array('id' => $post_data['id']), array('week')));
        //状态控制
        $content = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/exam_status.json'), 1);
//        dd($week);ddd($content);

        $power = 0;
        if($content['status'] == 2 && $week == 13){
            $power = 1;
        }
        if($content['status'] == 3 && $week == 19){
            $power = 1;
        }

        if ($power == 0) {
            $this->ajaxReturn(ajax_error('等待管理员开启权限'));
        }

//        $data['monitor_teacher_id'] = D('Teacher')->transferMultipleTeacherNameToTeacherIds($post_data['teacher_name']);//凌德全老师在全校教师账号里面没有，所以监考教师暂时不存id了
        $this->ajaxReturn(D('Exam')->update($post_data['id'], $post_data));
    }

    /* 十九周安排考试需另外考虑的 */
    /**
     * Des :十九周安排考试，为公共课安排考试
     * Auth:lyt123
     */
    public
    function automaticArrangeExamForPublicCourseInNineteenthWeek($course_model, $json_data)
    {
//        $course_model = D('Course');
//        $course_model->startTrans();

        $public_courses = D('PublicCourse')->getData(array(), null, true);
//        $public_courses = array(
//            array('id' => 8,
//                'name' => '毛泽东思想和中国特色社会主义理论体系概论',
//                'semester' => 2,
//                'period' => 10)
//        );
        //        dump($public_courses);
        $previous_period = array();
        //将非村官班的考试可排时间跟节假日错开
        dd($public_courses);
        $available_period = array_diff(array(1, 2, 3, 4, 5, 8, 9, 10, 11), D('Holiday')->getPeriods(current(D('Holiday')->getData(array('week' => 19), array('weekday')))));
//debug();
        foreach ($public_courses as $one_type_public_course) {

            $public_course_teachers = array();
            $public_course_classes = array();
            $teacher_existing_class_period = array();
            $teacher_other_course_exam_period = array();
            $class_other_course_period = array();

            //公共课，如"毛泽东思想和中国特色社会主义理论体系概论"是需要考试的，但是考核方式为考查
//            $one_type_public_courses = D('Course')->getData(array('name' => $one_type_public_course['name'], 'academy_id' => array('NEQ', 0)), null, true);
            $one_type_public_courses = D('PublicCourse')->getPublicCourses($one_type_public_course['name'], $json_data);dd($one_type_public_courses);

            //马克思那门课在2017-1是没课程的
            if(empty($one_type_public_courses)){
                continue;
            }

//            dump($one_type_public_courses);
//            continue;
            foreach ($one_type_public_courses as $single_course) {
                $public_course_teachers[] = $single_course['teacher_id'];
                $public_course_classes[] = $single_course['combine_status'];
            }
            $public_course_teachers = array_values(array_unique($public_course_teachers));
            $public_course_classes = array_values(array_unique($public_course_classes));

            //将几门公共课的考试时间隔开
            $origin_periods = array_diff($available_period, $previous_period);
            foreach ($public_course_teachers as $single_teacher) {
                //排除教师普教上课时间
                if ($result = self::filterTeacherExistingClass($single_teacher, 19, $json_data))
                    $teacher_existing_class_period = array_merge($teacher_existing_class_period, $result);

                //排除教师其他课程考试时间
                if ($result = self::filterTeacherOtherCourseExam($single_teacher, 19, 1, $json_data))
                    $teacher_other_course_exam_period = array_merge($teacher_other_course_exam_period, $result);
            }

            foreach ($public_course_classes as $single_combine_classes) {
                dd($public_course_classes);dd($single_combine_classes);
                //排除合班其他课程考试时间
                if ($result = self::filterClassOtherCourseExam($single_combine_classes, 19, 1, $json_data))
                    $class_other_course_period = array_merge($class_other_course_period, $result);
                $tmp = explode(',', $single_combine_classes);
                foreach ($tmp as $combine_class) {
                    $course_id = current($course_model->getData(array('class_id' => $combine_class, 'code' => $one_type_public_courses[0]['code'], 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('id')));dd($course_id);
                        dd($combine_class);
                    $course_model->update($course_id, array('exam_status' => 2));
                }
            }
            //剩余的period
//            dump($teacher_existing_class_period);
//            dump($teacher_other_course_exam_period);
//            dump($class_other_course_period);
            $remain_period = array_diff($origin_periods, $teacher_existing_class_period, $teacher_other_course_exam_period, $class_other_course_period);

            //下面rand()，要保证数组的键是连续的
            $remain_period = array_values($remain_period);
//            dump($remain_period);

            //优先选择指定的period
            if (in_array($one_type_public_course['period'], $remain_period)) {
                $remain_period = $one_type_public_course['period'];
            } else {
                $class_type = substr(current(D('Class')->getData(array('id' => $one_type_public_courses[0]['class_id']), array('name'))), 0, 1);
                if ($class_type == 'A') {
                    //A-本科考试排在周六晚+周日
                    $remain_period = array_reverse($remain_period);
                    $remain_period = $remain_period[0];
                } else {
                    $remain_period = $remain_period[0];
                }
            }

//            dd($remain_period);
dd($remain_period);
            foreach ($one_type_public_courses as $course) {
                $result = D('Exam')->addOne(array('code' => $course['code'], 'class_id' => $course['class_id'], 'week' => 19, 'period' => $remain_period));

                $this->checkResult($result, $course_model);
            }
            $previous_period[] = $remain_period;
        }
//        $course_model->commit();
        return ajax_ok();
    }

    /* 十三周安排考试需另外考虑的 */
    public
    function filterTeacherCourseInthirteenthWeek($teacher_id, $json_data)
    {
        $teacher_ids = explode(',', $teacher_id);
        $period = array();

        foreach($teacher_ids as $teacher_id){
            $courses = D('Course')->getData(array('teacher_id' => $teacher_id, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('week_section_ids'), true);
//        dump($courses);
            foreach ($courses as $course) {
                $week_section_ids = explode(',', $course['week_section_ids']);
//            dump($week_section_ids);
                foreach ($week_section_ids as $week_section_id) {
                    $week_section_data = D('WeekSection')->getData(array('id' => $week_section_id));
                    $use_weeks = explode(',', $week_section_data['use_weeks']);
                    if (in_array(12, $use_weeks)) {
                        $period[] = current(D('ClassroomTime')->getData(array('id' => $week_section_data['classroom_time_id']), array('period')));
                    }
                }
            }
        }

        return $period;
    }

    public
    function filterClassCourseInthirteenthWeek($class_ids, $json_data)
    {
        $class_ids = explode(',', $class_ids);

        $period = array();
        foreach ($class_ids as $class_id) {
            $courses = D('Course')->getData(array('class_id' => $class_id, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('week_section_ids'), true);
            foreach ($courses as $course) {
                $week_section_ids = explode(',', $course['week_section_ids']);
//                dump($week_section_ids);
                foreach ($week_section_ids as $week_section_id) {
                    $week_section_data = D('WeekSection')->getData(array('id' => $week_section_id));
                    $use_weeks = explode(',', $week_section_data['use_weeks']);
                    if (in_array(12, $use_weeks)) {
                        $period[] = current(D('ClassroomTime')->getData(array('id' => $week_section_data['classroom_time_id']), array('period')));
                    }
                }
            }
        }
        return $period;
    }

    /**
     * Des :各种情况不冲突
     * Auth:lyt123
     */
    public
    function filterTeacherExistingClass($teacher_id, $exam_week, $json_data)
    {
        $existing_class_period = array();

        $teacher_ids = explode(',', $teacher_id);
        foreach ($teacher_ids as $teacher_id) {
            $existing_class = current(D('TeacherExistingClass')->getData(array('teacher_id' => $teacher_id, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('existing_class')));

            //如果没有上课信息，直接返回空
            if (!$existing_class)
                continue;

            if (strpos($existing_class, '\n') !== false)
                $classes_time = explode('\n', $existing_class);
            else
                $classes_time[] = $existing_class;

            foreach ($classes_time as $class_time) {
                if (substr($class_time, -6, 6) == '晚上') {
                    $week_combine = '';
                    if (strpos($class_time, ',') !== false) {
                        $different_weeks = explode(',', $class_time);
                        foreach ($different_weeks as $separate_week) {
                            $weeks = get_between($separate_week, '第', '周');

                            if (strpos($weeks, '-')) {
                                $weeks = explode('-', $weeks);
                                for ($i = $weeks[0]; $i <= $weeks[1]; $i++) {

                                    $week_combine .= $i . ',';
                                }
                            } else {
                                $week_combine .= $weeks . ',';
                            }
                        }
                    } elseif (strpos($class_time, '-')) {
                        $weeks = get_between($class_time, '第', '周');
                        $weeks = explode('-', $weeks);
                        for ($i = $weeks[0]; $i <= $weeks[1]; $i++) {

                            $week_combine .= $i . ',';
                        }
                    } else {
                        $weeks = get_between($class_time, '第', '周');
                        $week_combine .= $weeks;
                    }
//                dump($week_combine);
                    //解决‘第19周星期三晚上’
                    if (strpos($week_combine, ',') !== false) {
                        $weeks = explode(',', substr($week_combine, 0, -1));
                    } else $weeks = array($week_combine);

                    if (in_array($exam_week, $weeks)) {
                        $weekday = substr($class_time, strlen($class_time) - 9, 3);

                        switch ($weekday) {
                            case '一':
                                $existing_class_period[] = 1;
                                break;
                            case '二':
                                $existing_class_period[] = 2;
                                break;
                            case '三':
                                $existing_class_period[] = 3;
                                break;
                            case '四':
                                $existing_class_period[] = 4;
                                break;
                            case '五':
                                $existing_class_period[] = 5;
                                break;
                        }
                    }
                }
            }
        }
//        dd($existing_class_period);
        return $existing_class_period;
    }

    public
    function filterTeacherOtherCourseExam($teacher_id, $exam_week, $exam_status, $json_data)
    {
        //考试时间地点
        $period = array();

        $teacher_ids = explode(',', $teacher_id);
        foreach($teacher_ids as $teacher_id){

            //教师其他有考试的课程的考试时间
            $classes = D('Course')->getData(array('teacher_id' => $teacher_id, 'exam_status' => $exam_status, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('code'), true);

            if ($classes) {
                foreach ($classes as $class) {
                    $period[] = current(D('Exam')->getData(array('code' => $class['code'], 'week' => $exam_week), array('period')));
                }
            }
        }

        return $period;
    }

    public
    function filterClassOtherCourseExam($class, $exam_week, $exam_status, $json_data)
    {
        $period = array();
        if (strpos($class, ',') !== false) {
            $classes = explode(',', $class);
        } else {
            $classes[] = $class;
        }
//        dump($classes);
        foreach ($classes as $one_class) {
            $class_courses = D('Course')->getData(array('class_id' => $one_class, 'exam_status' => $exam_status, 'semester'=> $json_data['semester'], 'year' => $json_data['year']), array('code'), true);

            if ($class_courses) {
                foreach ($class_courses as $class_course) {
                    $period[] = current(D('Exam')->getData(array('code' => $class_course['code'], 'week' => $exam_week), array('period')));
                }
            }
        }
        return $period;
    }
}
