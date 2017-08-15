<?php
/* User:lyt123; Date:2016/11/5; QQ:1067081452 */
namespace Common\Model;

class ExamModel extends CURDModel
{
    public function transferPeriodToDate($period, $week)
    {
        $date = '';
        $date_data = file_get_contents(__PUBLIC__ . '/necessary_infomation/period_to_date.json');
        $content = json_decode($date_data, 1);dd($content);
        $time_string = strtotime($content['date']);
        switch ($period) {
            case 1:
                $date = date('m月d日', $time_string + (($week - 1) * 7) * 24 * 3600) . '晚上7:30';
                break;
            case 2:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 1) * 24 * 3600) . '晚上7:30';
                break;
            case 3:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 2) * 24 * 3600) . '晚上7:30';dd($time_string + (($week - 1) * 7 + 2) * 24 * 3600);
                break;
            case 4:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 3) * 24 * 3600) . '晚上7:30';
                break;
            case 5:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 4) * 24 * 3600) . '晚上7:30';
                break;
            case 6:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 5) * 24 * 3600) . '上午9:00';
                break;
            case 7:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 5) * 24 * 3600) . '下午3:00';
                break;
            case 8:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 5) * 24 * 3600) . '晚上7:30';
                break;
            case 9:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 6) * 24 * 3600) . '上午9:00';dd($week);dd($time_string + (($week - 1) * 7 + 6) * 24 * 3600);
                break;
            case 10:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 6) * 24 * 3600) . '下午3:00';
                break;
            case 11:
                $date = date('m月d日', $time_string + (($week - 1) * 7 + 6) * 24 * 3600) . '晚上7:30';
                break;
        }
        return $date;
    }

    public function setWorkExamExcelTitle($objPHPExcel, $week)
    {
        $semester_content = file_get_contents(__PUBLIC__ . '/necessary_infomation/year_semester.json');
        $content = json_decode($semester_content, 1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:L1');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '                                 继续教育学院' . $content['year'] . '-' . ($content['year'] + 1) . '-' . $content['semester'] . '第' . $week . '周考试安排')
            ->setCellValue('A2', '开课学院')
            ->setCellValue('B2', '上课院系')
            ->setCellValue('C2', '班级名称')
            ->setCellValue('D2', '学生人数')
            ->setCellValue('E2', '课程代号')
            ->setCellValue('F2', '课程名称')
            ->setCellValue('G2', '考核方式')
            ->setCellValue('H2', '总学时')
            ->setCellValue('I2', '任课教师')
            ->setCellValue('J2', '考试时间')
            ->setCellValue('K2', '考试地点')
            ->setCellValue('L2', '监考老师');
    }

    public function setWorkExamExcelCellWidth($objPHPExcel)
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
    }

    public function findExamClassroom($period, $exam_week)
    {
        //这里的building限定为1是因为一个马兰芳教学楼用于安排考试已经够用了
        $classroom_times = D('ClassroomTime')->getData(array('building' => 1, 'period' => $period), array('id'), true);
//        dump($classroom_times);

        if ($exam_week == 13) {
            //十三周寻找教室需要与普教上课使用教室不一样
            foreach ($classroom_times as $classroom_time) {
//                dump(D('Exam')->getData(array('classroom_time_id' => $classroom_time['id'])));
                if (!D('Exam')->getData(array('classroom_time_id' => $classroom_time['id']))) {
                    $week_section_data = D('WeekSection')->getData(array('classroom_time_id' => $classroom_time['id']), array('use_weeks'), true);
//                    dump($week_section_data);
                    foreach ($week_section_data as $one_week_section_data) {
                        if (strpos($one_week_section_data['use_weeks'], strval($exam_week)) !== false) {
                            continue 2;
                        }
                    }
                    $classroom_time_id = $classroom_time['id'];
                    break;
                }
            }
        } elseif ($exam_week == 19) {
            foreach ($classroom_times as $classroom_time) {
                if (!D('Exam')->getData(array('classroom_time_id' => $classroom_time['id']))) {
                    $classroom_time_id = $classroom_time['id'];
                    break;
                }
            }
        }

        return isset($classroom_time_id) ? $classroom_time_id : 0;
    }

    public function getExamData($search_data, $exam_week, $is_excel_data)
    {
        $data = array();
        $j = 0;
        $json_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $public_course_ids = D('PublicCourse')->getPublicCourseIds($json_data);
//        dump($search_data);exit();
        foreach ($search_data as $item) {
            $course_data = D('Course')->getData(array('code' => $item['code'], 'class_id' => $item['class_id']));

            //是否公共课
            if(in_array($course_data['id'], $public_course_ids)){
                $data[$j]['is_public_course'] = 2;
            }else{
                $data[$j]['is_public_course'] = 1;
            }

            $data[$j]['id'] = $item['id'];
//            $data[$j]['open_class_academy'] = current(D('SelectBook')->getData(array('class_name' => current(D('Class')->getData(array('id' => $item['class_id']), array('name'))), 'name' => $course_data['name']), array('open_class_academy')));
//            $data[$j]['have_class_academy'] = current(D('SelectBook')->getData(array('class_name' => current(D('Class')->getData(array('id' => $item['class_id']), array('name'))), 'name' => $course_data['name']), array('have_class_academy')));
            $data[$j]['open_class_academy'] = $course_data['open_class_academy_id'] ? current(D('Academy')->getData(array('id' => $course_data['open_class_academy_id']), array('name'))) : $course_data['open_class_academy_value'];
            $data[$j]['have_class_academy'] = $course_data['academy_id'] ? current(D('Academy')->getData(array('id' => $course_data['academy_id']), array('name'))) : $course_data['have_class_academy_value'];
            $data[$j]['class_name'] = current(D('Class')->getData(array('id' => $item['class_id']), array('name')));
            $data[$j]['student_sum'] = current(D('Class')->getData(array('id' => $item['class_id']), array('student_sum')));
            $data[$j]['code'] = $item['code'];
            $data[$j]['name'] = $course_data['name'];
            $data[$j]['examine_way'] = transferExamineWay($course_data['examine_way']);
            $data[$j]['time_total'] = $course_data['time_total'];
            $data[$j]['teacher_name'] = current(D('Teacher')->getData(array('id' => $course_data['teacher_id']), array('name')));dd($item['period']);dd($exam_week);
            $data[$j]['time'] = $this->transferPeriodToDate($item['period'], $exam_week);ddd($data[$j]['time']);
            if ($item['classroom_time_id']) {
                $data[$j]['classroom'] = '马兰芳教学楼' . current(D('ClassroomTime')->getData(array('id' => $item['classroom_time_id']), array('number')));
            } elseif ($item['other_classroom']) {
                $data[$j]['classroom'] = $item['other_classroom'];
            }else {
                $data[$j]['classroom'] = '';
            }
            $data[$j]['monitor_teacher_name'] = $item['monitor_teacher_name'] ? $item['monitor_teacher_name'] : '';
            $j++;
        }
        if ($is_excel_data) {
            foreach ($data as $key => $item) {
                $excel_data[$key][0] = $data[$key]['open_class_academy'];
                $excel_data[$key][1] = $data[$key]['have_class_academy'];
                $excel_data[$key][2] = $data[$key]['class_name'];
                $excel_data[$key][3] = $data[$key]['student_sum'];
                $excel_data[$key][4] = $data[$key]['code'];
                $excel_data[$key][5] = $data[$key]['name'];
                $excel_data[$key][6] = $data[$key]['examine_way'];
                $excel_data[$key][7] = $data[$key]['time_total'];
                $excel_data[$key][8] = $data[$key]['teacher_name'];
                $excel_data[$key][9] = $data[$key]['time'];
                $excel_data[$key][10] = $data[$key]['classroom'];
                $excel_data[$key][11] = $data[$key]['monitor_teacher_name'];
            }
        }

        return $is_excel_data ? $excel_data : $data;
    }

    public function getExamDataByCourseData($course_data)
    {
        foreach ($course_data as $item) {
            if ($result = D('Exam')->getData($item))
                $search_data[] = $result;
        }
        return $search_data;
    }

    public function getSearchData($data)
    {
        $search_data = array();

        $exam_status = 0;
        if ($data['week'] == 19) {
            $exam_status = 2;
        } elseif ($data['week'] == 13) {
            $exam_status = 5;
        }

        //排序
        if($data['order'] == 'teacher'){
            $order = 'teacher_id';
        }elseif($data['order'] == 'class'){
            $order = 'class_id';
        }elseif($data['order'] == 'course_name'){
            $order = 'name';
        }

        if ($data['open_class_academy']) {
            //以前的代码
            /*$select_book_data = D('SelectBook')->getData(array('open_class_academy' => $data['open_class_academy']), array('code', 'class_name'), true);
            foreach ($select_book_data as $item) {
                $exam_data['code'] = $item['code'];
                $exam_data['class_id'] = current(D('Class')->getData(array('name' => $item['class_name']), array('id')));
                $exam_data['exam_status'] = $exam_status;
                if ($result = $this->getData($exam_data))
                    $search_data[] = $result;
            }*/
            //现在的代码，不知道会不会出bug
            $academy_id = current(D('Academy')->getData(array('name' => $data['open_class_academy']), array('id')));
            $course_data = D('Course')->getData(array('open_class_academy_id' => $academy_id, 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);

        }elseif($data['open_class_academy_id']){
            $course_data = D('Course')->getData(array('open_class_academy_id' => $data['open_class_academy_id'], 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        }
        elseif ($data['have_class_academy']) {
            $academy_id = current(D('Academy')->getData(array('name' => $data['have_class_academy']), array('id')));
            $course_data = D('Course')->getData(array('academy_id' => $academy_id, 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        } elseif ($data['teacher_name']) {
            $teacher_id = current(D('Teacher')->getData(array('name' => $data['teacher_name']), array('id')));
            $course_data = D('Course')->getData(array('teacher_id' => $teacher_id, 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        } elseif ($data['class_name']) {
            $class_id = current(D('Class')->getData(array('name' => $data['class_name']), array('id')));
            $course_data = D('Course')->getData(array('class_id' => $class_id, 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        } elseif ($data['code']) {
            $course_data = D('Course')->getData(array('code' => $data['code'], 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        } elseif ($data['name']) {
            $course_data = D('Course')->getData(array('name' => $data['name'], 'exam_status' => $exam_status), array('code', 'class_id'), true);
            $search_data = $this->getExamDataByCourseData($course_data);
        } elseif ($data['week']) {
            $search_data = $this->getSortedData($data['week'], $order);
        }

        return $search_data;
    }

    public function getSortedData($week, $order)
    {
        if($order){
            $this->order('co.'.$order);
        }

        return $this
            ->table('course co, exam e')
            ->field('e.*')
            ->where('co.class_id = e.class_id and co.code = e.code')
            ->where(array('e.week' => $week))
            ->select();
    }
}
