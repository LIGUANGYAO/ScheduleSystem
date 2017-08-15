<?php
/* User:lyt123; Date:2016/11/8; QQ:1067081452 */
namespace Admin\Controller;

class AdminController extends ExamController
{
    public function __construct()
    {
        $this->reqAdmin();
        parent::__construct();
    }

    /*----------------------节假日管理---------------------*/

    public function addHoliday()
    {
        $data = $this->reqAdmin()->reqPost(array('week', 'weekday'));
        $data['weekday'] = htmlspecialchars_decode($data['weekday']);
        $data['weekday'] = implode(',', json_decode($data['weekday']));
        $this->ajaxReturn(D('Holiday')->addOne($data));
    }

    public function deleteHoliday()
    {
        $data = $this->reqAdmin()->reqPost(array('id'));
        $this->ajaxReturn(D('Holiday')->destroy($data['id']));
    }

    public function updateHoliday()
    {
        $data = $this->reqAdmin()->reqPost(array('id'), array('week', 'weekday'));
        $data['weekday'] = htmlspecialchars_decode($data['weekday']);
        $data['weekday'] = implode(',', json_decode($data['weekday']));
        $this->ajaxReturn(D('Holiday')->update($data['id'], $data));
    }

    public function showHoliday()
    {
        $this->reqAdmin();
        $data = D('Holiday')->getData(array(), array(), true);
        foreach ($data as $key => &$item) {
            $item['weekday'] = explode(',', $item['weekday']);
            $tmp[$key] = $item['week'];
        }
        unset($item);
        array_multisort($tmp, SORT_ASC, $data);//按二维数组的某个键值对数组进行排序
//        dd($data);
        $this->ajaxReturn(ajax_ok($data));
    }

    /*----------------------学院管理-----------------------*/

    public function addAcademy()
    {
        $data = $this->reqAdmin()->reqPost(array('name'));
        $this->ajaxReturn(D('Academy')->addOne($data));
    }

    public function deleteAcademy()
    {
        $data = $this->reqAdmin()->reqPost(array('id'));
        $this->ajaxReturn(D('Academy')->destroy($data['id']));
    }

    public function updateAcademy()
    {
        $data = $this->reqAdmin()->reqPost(array('id', 'name'));
        $this->ajaxReturn(D('Academy')->update($data['id'], $data));
    }

    public function showAcademy()
    {
        $this->reqAdmin();
        $this->ajaxReturn(ajax_ok(D('Academy')->getData(array(), array(), true)));
    }

    /*----------------------教师或学院教务员管理-----------------------*/

    /**
     * Des :添加教师或学院教务员
     * Auth:lyt123
     */
    public function addAdmin()
    {
        $data = $this->reqAdmin()->reqPost(array('admin_level', 'name', 'account', 'password'), array('position', 'academy_id'));

        $data['password'] = encrypt_password($data['password']);

        $this->ajaxReturn($this->getModel($data['admin_level'])->addOne($data));
    }

    /**
     * Des :修改教师或学院教务员信息
     * Auth:lyt123
     */
    public function updateAdmin()
    {
        $data = $this->reqAdmin()->reqPost(array('admin_level', 'account'), array('name', 'position', 'academy_id', 'email', 'password'));

        $data['password'] = encrypt_password($data['password']);

        $this->ajaxReturn($this->getModel($data['admin_level'])->update($data['account'], $data, 'account'));
    }

    /**
     * Des :删除教师或学院教务员
     * Auth:lyt123
     */
    public function deleteAdmin()
    {
        $data = $this->reqAdmin()->reqPost(array('admin_level', 'account'));
        $this->ajaxReturn($this->getModel($data['admin_level'])->destroy($data['account'], 'account'));
    }

    /**
     * Des :查询教师或学院教务员
     * Auth:lyt123
     */
    public function searchAdmin()
    {
        $data = $this->reqTeacher()->reqPost(array('admin_level', 'page', 'limit'), array('account', 'name', 'academy_id'));

        $result['sum'] = $this->getModel($data['admin_level'])->where($data)->count();
        $result['teacher_data'] = $this->getModel($data['admin_level'])->getData($data, array(), true, '', $data['page'], $data['limit']);
        foreach ($result['teacher_data'] as &$item) {
            $item['academy'] = current(D('Academy')->getData(array('id' => $item['academy_id']), array('name')));
            unset($item['password']);
            unset($item['time_have_class_ids']);
        }

        $this->ajaxReturn(ajax_ok($result));
    }

    /**
     * Des :导入教师或学院教务员账号表
     * Auth:lyt123
     */
    public function importAdminExcel()
    {
        /*$admin_data = $this->loadExcel('teacher-', 'excel_files/admin/teacher_account/', 1);

        $data = array();
        foreach ($admin_data as $item) {

            $data['account'] = $item[0];
            $data['name'] = $item[1];

            if (substr($data['name'], -9, 9) == '教务员') {
                $model = D('AdminAcademy');
            } else {
                $model = D('Teacher');
            }

            if ($result = D('Academy')->getData(array('name' => $item[2]), array('id'))) {
                $data['academy_id'] = $result['id'];
            } else $data['academy_value'] = $item[2];

            $data['password'] = encrypt_password('123456');

            $result = $model->addOne($data);

            $this->checkResult($result, $model);

            unset($data);
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());*/
        //上面的代码是指导入全新的表，下面的表导入使用于修改教师工号
        $admin_data = $this->loadExcel('teacher-', 'excel_files/admin/teacher_account/', 1);
//debug(true);ddd($admin_data);
        foreach ($admin_data as $item) {
            $teacher_id = current(D('Teacher')->getData(array('name' => $item[1]), array('id')));
            D('Teacher')->update($teacher_id, array('account' => $item[0]));
        }

        $this->ajaxReturn(ajax_ok());
    }

    public function outputAdminExcel()
    {
        $this->reqAdmin();
        $model = D('Teacher');
        $excel_data = $model->getTeacherData();
//        debug(true);
//        ddd($excel_data);
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $model->setWorkQualityGatherExcelTitle($objPHPExcel);
        $model->setWorkQualityGatherExcelCellWidth($objPHPExcel);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A2');
        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        promptExcelDownload($objPHPExcel, '教师账号表.xls');
    }

    /**
     * Des :清空教师或学院教务员数据
     * Auth:lyt123
     */
    public function clearAdminData()
    {
        $data = $this->reqPost(array('admin_level'));

        $model = $this->getModel($data['admin_level']);
        $model->startTrans();
        if (
        $model->where(1)->delete()
        ) {
            $model->commit();
            $this->ajaxReturn(ajax_ok(array(), '清空数据成功'));
        }
        $model->rollback();
        $this->ajaxReturn(ajax_ok('清空数据失败'));
    }

    /*----------------------公共课管理------------------------*/

    public function addPublicCourse()
    {
        $data = $this->reqAdmin()->reqPost(array('name', 'semester', 'period'));
        $data['period'] = D("PublicCourse")->transformPeriodNameAndNumber($data['period'], 'name_to_number');
        $this->ajaxReturn(D('PublicCourse')->addOne($data));
    }

    public function deletePublicCourse()
    {
        $data = $this->reqAdmin()->reqPost(array('id'));
        $this->ajaxReturn(D('PublicCourse')->destroy($data['id']));
    }

    public function updatePublicCourse()
    {
        $data = $this->reqAdmin()->reqPost(array('id', 'name', 'semester', 'period'));
        $data['period'] = D("PublicCourse")->transformPeriodNameAndNumber($data['period'], 'name_to_number');
        $this->ajaxReturn(D('PublicCourse')->update($data['id'], $data));
    }

    public function showPublicCourse()
    {
        $this->reqAdmin();
        $public_course_data = D('PublicCourse')->getData(array(), array(), true);
        foreach($public_course_data as &$one_public_course){
            $one_public_course['period'] = D("PublicCourse")->transformPeriodNameAndNumber($one_public_course['period'], 'number_to_name');
        }
        $this->ajaxReturn(ajax_ok($public_course_data));
    }

    /*----------------------学期和年份管理-------------------*/
    public function fillInYearAndSemester()
    {
        $data = $this->reqAdmin()->reqPost(array('year', 'semester'));
        $data = json_encode($data);

        if (file_put_contents(__PUBLIC__ . '/necessary_infomation/year_semester.json', $data)) {
            $this->ajaxReturn(ajax_ok());
        } else {
            $this->ajaxReturn(ajax_error());
        }
    }

    public function showYearAndSemester()
    {
        $this->reqAdmin();
        $year_semester_data = json_decode(file_get_contents(__PUBLIC__ . '\necessary_infomation\year_semester.json'), 1);
        $this->ajaxReturn(ajax_ok($year_semester_data));
    }

    /*----------------------课程管理-----------------------*/

    /**
     * Des :导入可用教室表
     * Auth:lyt123
     */
    public function importClassroomTimeAvailable()
    {
        $classroom_time_info = $this->loadExcel('available_classroom-', 'excel_files/available_classroom/');
        $classroom_time_model = D('ClassroomTime');
        $classroom_time_model->startTrans();
//dd($classroom_time_info);
        foreach ($classroom_time_info as $item) {
            foreach ($item as $key => $value) {
                $data['building'] = $key + 1;
                $data['seat_sum'] = get_between($value, '(', ')');
                $data['number'] = substr($value, 0, strpos($value, '('));

                if (!$data['number'])
                    continue;

                if ($data['building'] == 3)
                    $start = 6;
                else
                    $start = 1;

                for ($i = $start; $i <= 11; $i++) {
                    $data['period'] = $i;
                    $result = D('ClassroomTime')->addOne($data);
                    $this->checkResult($result, $classroom_time_model);
                }
            }
        }
        $classroom_time_model->commit();
        $this->ajaxReturn(ajax_ok(array(), '教室导入成功'));
    }

    /**
     * Des :导入非村官班课程表
     * Auth:lyt123
     */
    public function importCourseExcel()
    {
//        $course_info = $this->loadExcel('curriculum-', 'excel_files/curriculum/normal_class/', 3);
        $course_model = D('Course');
        $course_model->startTrans();
        $course_info = S('course_info');
ddd($course_info);
        //处理课程excel表的数据
        foreach ($course_info as $key => $item) {
            $data['code'] = $item[4];
            $data['class_name'] = $item[2];
            $data['time_total'] = $item[7];
            $data['comment'] = $item[21];//要导入备注

//            如果表格最后有空行，则返回成功
            if (!$data['code']) {
                $course_model->commit();
                $this->ajaxReturn(ajax_ok(array(), '上课时间地点:已读取到最后一行，导入成功'));
            }

            //将毕业设计和毕业环节的课程跳过
            if (substr($data['time_total'], 7, 1) == ')')
                continue;

            //将课程的时间地点插入数据库
            $data['week_section_ids'] = '';
            $week_section_id = '';
            for ($i = 11; $i < 20; $i++) {
                $classroom_time = $item[$i];
                if ($classroom_time) {

                    ddd($classroom_time);
                    //确定period，周一晚-周日晚，十一个区域
                    switch ($i) {
                        case 11:
                            $classroom_time_where['period'] = 1;
                            break;
                        case 12:
                            $classroom_time_where['period'] = 2;
                            break;
                        case 13:
                            $classroom_time_where['period'] = 3;
                            break;
                        case 14:
                            $classroom_time_where['period'] = 4;
                            break;
                        case 15:
                            $classroom_time_where['period'] = 5;
                            break;
                        case 16:
                            $classroom_time_where['period'] = 8;
                            break;
                        case 17:
                            $classroom_time_where['period'] = 9;
                            break;
                        case 18:
                            $classroom_time_where['period'] = 10;
                            break;
                        case 19:
                            $classroom_time_where['period'] = 11;
                            break;
                    }
                    //ddd($classroom_time_where);

                    //如果一个period内有多个教室，则将多个教室分开
//                    if (substr_count($classroom_time, ')') == 2) {
//                        $start = strpos($classroom_time, ')');
//                        $classroom_time_separate[] = substr($classroom_time, 0, $start + 1);
//                        $classroom_time_separate[] = ltrim(substr($classroom_time, $start + 1));
//                    }
//                    else {
//                        $classroom_time_separate[] = $classroom_time;
//                    }
                    //上面代码修改如下
                    $classroom_time_data = explode(')', $classroom_time);
                    foreach($classroom_time_data as $one_classroom_time_data){
                        $classroom_time_separate[] = $one_classroom_time_data.')';
                    }
                    array_pop($classroom_time_separate);

                    ddd('classroom_time_separate');
                    ddd($classroom_time_separate);
                    //将上一个if产生的结果进行foreach
                    //$classroom_time_where['period']保持不变
                    foreach ($classroom_time_separate as $classroom_time) {
                        //确定教学楼和教室
                        $start = "(";
                        $end = ")";
                        $classroom = get_between($classroom_time, $start, $end);
                        //ddd($classroom);
                        $building = substr($classroom, 0, -3);
                        //ddd($building);

//将教学楼名称转化为数据库中的1234
                        $classroom_time_where['building'] = D('ClassroomTime')->transferClassroomNameToNumber($building);

                        //（不去修改其他地方原先的代码，虽然重复）此处为额外增加，针对情况:如果该教室在南主楼/马兰芳教学楼/南主楼，但是教室编号是继续教育学院可用教室表里没有的，这时，$classroom_time_where['building'] = 4
                        $classroom_time_where['number'] = substr($classroom, strlen($building), 3);
                        if (!D('ClassroomTime')->getData($classroom_time_where)) {
                            $classroom_time_where['building'] = 4;
                        }
                        ddd('classroom_time_where:');
                        ddd($classroom_time_where);

                        //获取该教室-时间段的id,如果是123教学楼以外的，就将数据插入到classroom_time表中
                        if ($classroom_time_where['building'] == 4) {
                            $classroom_time_where['classroom'] = $classroom;
                            $classroom_time_where['number'] = 0;
                            $result = D('ClassroomTime')->addOne($classroom_time_where);
                            $this->checkResult($result, D('ClassroomTime'), '插入课程时间地点-添加教室失败');

                            $week_section_data['classroom_time_id'] = $result['data']['id'];
                            //ddd($week_section_data);
                        } else {
                            $classroom_time_where['number'] = substr($classroom, strlen($building), 3);
                            $week_section_data['classroom_time_id'] = current(D('ClassroomTime')->getData($classroom_time_where, array('id')));
                            ddd('week_section_data:');
                            ddd($week_section_data);
                        }

//根据节数不同来确定不同时间
                        ddd($classroom_time);
                        $time_string = (rtrim(substr($classroom_time, 0, strpos($classroom_time, '('))));
                        ddd('time_string');
                        ddd($time_string);
                        $time_data = explode(',', $time_string);
                        ddd($time_data);

//将这一period内的周数和节数插入数据库并将数据的id用','拼接
                        //$week_section_data['classroom_time_id']不变
                        foreach ($time_data as $week_section_combine) {
                            ddd($week_section_combine);
                            //将节数和周数分离
                            $week_section_separate = explode(':', $week_section_combine);
                            ddd($week_section_separate);
                            $week_section_data['section_num'] = substr($week_section_separate[1], 0, 1);
                            $week = substr($week_section_separate[0], 0, -3);
                            //ddd($week_section_data);
                            ddd($week);

                            //获得周数
                            $use_weeks = '';
                            if (strpos($week, '、') !== false) {
                                $weeks = explode('、', $week);
                                //ddd($weeks);
                                foreach ($weeks as $week_part) {

                                    if (strpos($week_part, '-') !== false) {
                                        $week_last = explode('-', $week_part);

                                        for ($j = $week_last[0]; $j <= $week_last[1]; $j++) {

                                            $use_weeks .= $j . ',';
                                        }

                                    } else {
                                        $use_weeks .= $week_part . ',';
                                    }
                                }
                                //ddd($use_weeks);
                            } elseif (strpos($week, '-') !== false) {
                                $week_last = explode('-', $week);
                                //ddd($week_last);
                                for ($j = $week_last[0]; $j <= $week_last[1]; $j++) {

                                    $use_weeks .= $j . ',';
                                }
                            } else {
                                $use_weeks .= $week . ',';
                            }
                            ddd($use_weeks);
                            if (substr($use_weeks, -1, 1) == ',') {
                                $week_section_data['use_weeks'] = substr($use_weeks, 0, -1);
                            }
                            ddd($week_section_data);
                            ddd($item);
                            //将week，section，classroom_time_id数据插入week_section表中
                            $result = D('WeekSection')->addOne($week_section_data);
                            $this->checkResult($result, D('WeekSection'), '插入课程时间地点-添加周数、节数失败');

                            $week_section_id .= $result['data']['id'] . ',';
                            //ddd($week_section_id);
                            unset($week_section_separate);
                            unset($week);
                            unset($use_weeks);
                        }
                        unset($classroom_time_where['number']);
                        unset($classroom_time_where['building']);
                        unset($classroom_time_where['classroom']);
                    }
                    unset($classroom_time_separate);
                }
            }

            if (substr($week_section_id, -1, 1) == ',') {
                $data['week_section_ids'] = substr($week_section_id, 0, -1);
            } else {
                $data['week_section_ids'] = $week_section_id;
            }

            //更新课程信息
            $class_id = current(D('Class')->getData(array('name' => $data['class_name']), array('id')));
            $course_id = current(D('Course')->getData(array('code' => $data['code'], 'class_id' => $class_id), array('id')));
//dd($data);
            $result = D('Course')->update($course_id, $data);
//            ddd($data);
//            ddd($course_id);
//            ddd($class_id);

            $this->checkResult($result, D('Course'), '更新数据失败');
        }

        $course_model->commit();
        $this->ajaxReturn(ajax_ok(array(), '导入课表成功'));
    }

    /**
     * Des :导入村官班课程表
     * Auth:lyt123
     */
    public function importOfficialClassExcel()
    {
        $course_info = $this->loadExcel('curriculum-', 'excel_files/curriculum/official/', 3);
        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');
        $course_model = D('Course');
        $course_model->startTrans();

        //处理课程excel表的数据
        foreach ($course_info as $key => $item) {

            $where['class_name'] = $item[1];
            $where['name'] = $item[3];
            $data['comment'] = $item[11];
            $where['class_id'] = current(D('Class')->getData(array('name' => $where['class_name']), array('id')));

//            如果表格最后有空行，则返回成功
            if (!$where['class_name']) {
                $course_model->commit();
                $this->ajaxReturn(ajax_ok(array(), '导入时间地点:已读取到最后一行，导入成功'));
            }

            //将毕业设计和毕业环节的课程跳过
            if (substr($where['class_name'], 7, 1) == ')')
                continue;

            //将课程的时间地点插入数据库
            $data['week_section_ids'] = '';
            for ($i = 9; $i < 11; $i++) {
                $classroom_time = $item[$i];
                if ($classroom_time) {
                    switch ($i) {
                        case 9:
                            $classroom_time_where['period'] = 6;
                            break;
                        case 10:
                            $classroom_time_where['period'] = 7;
                            break;
                    }

                    $classroom_time_where['number'] = substr($classroom_time, -4, 3);
                    $classroom_time_where['building'] = 1;
                    $week_section_data['classroom_time_id'] = current(D('ClassroomTime')->getData($classroom_time_where, array('id')));

                    $week_section_data['section_num'] = 4;
                    $week_section_data['use_weeks'] = rtrim(explode(':', $classroom_time)[0], '周');
dd($week_section_data);
                    $result = D('WeekSection')->addOne($week_section_data);
                    $this->checkResult($result, D('WeekSection'));
                    $data['week_section_ids'] .= $result['data']['id'] . ',';
                }
            }

            if (substr($data['week_section_ids'], -1, 1) == ',')
                $data['week_section_ids'] = substr($data['week_section_ids'], 0, -1);

            $where['year'] = $year_and_semester_data['year'];
            $where['semester'] = $year_and_semester_data['semester'];

            $course_id = current(D('Course')->getData($where, array('id')));dd($where);
            dd($course_id);
            ddd($data);
            $result = D('Course')->update($course_id, $data);
            $this->checkResult($result, D('Course'));
        }

        $course_model->commit();
        $this->ajaxReturn(ajax_ok(array(), '导入课表成功'));
    }

    /**
     * Des :导出教室使用情况表
     * Auth:lyt123
     */
    public function outputClassroomUseConditionExcel()
    {
        //节假日的处理
        /*        $holidays = D('Holiday')->getData(array('week' => array('ELT', 18)), array('week', 'weekday'), true);
        $holiday = array();
        foreach($holidays as $key => $item){
            $holiday[$key]['periods'] = D('Holiday')->getPeriods($item['weekday']);
            $holiday[$key]['week'] = $item['week'];
        }*/
        $this->reqAdmin();
//debug();
        $model = D('ClassroomTime');
        $periods = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $excel_data_first = $model->getClassroom($periods, 1);
        $excel_data_second = $model->getClassroom($periods, 2);
        $periods = [6, 7, 8, 9, 10, 11];
        $excel_data_third = $model->getClassroom($periods, 3);

//        dump($excel_data_first);
//        dd($excel_data_second);

ddd();
//dd($excel_data);
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $model->setExcelTitle($objPHPExcel);
        $model->setCellWidth($objPHPExcel);


        $excel_data_first = $model->returnDataFitExcel($excel_data_first);
        $objPHPExcel->getActiveSheet()->fromArray(['马兰芳教学楼'], NULL, 'A2');
        $objPHPExcel->getActiveSheet()->fromArray($excel_data_first, NULL, 'A3');
        $length_first = count($excel_data_first);

        $excel_data_second = $model->returnDataFitExcel($excel_data_second);
        $objPHPExcel->getActiveSheet()->fromArray(['南主楼'], NULL, 'A' . ($length_first + 4));
        $objPHPExcel->getActiveSheet()->fromArray($excel_data_second, NULL, 'A' . ($length_first + 5));
        $length_second = count($excel_data_second);

        $excel_data_third = $model->returnThirdDataFitExcel($excel_data_third);
        $objPHPExcel->getActiveSheet()->fromArray(['黄浩川教学楼'], NULL, 'A' . ($length_first + 4 + $length_second + 2));
        $objPHPExcel->getActiveSheet()->fromArray($excel_data_third, NULL, 'A' . ($length_first + 5 + $length_second + 2));
        $length_third = count($excel_data_third);

        automaticWrapText($objPHPExcel, $length_first + $length_second + $length_third + 5);
        promptExcelDownload($objPHPExcel, '教室使用情况表.xls');
    }

    /*----------------------考试管理-----------------------*/

    /**
     * Des :清除考试安排
     * Auth:lyt123
     */
    public function clearExamArrangeData()
    {
        $course_model = D('Course');
        $course_model->startTrans();
        if (//下面where(1)值得注意
            D('Exam')->where(1)->delete() &&
            D('Course')->where(array())->save(array('exam_status' => 1)) !== false
        ) {
            $course_model->commit();
            $this->ajaxReturn(ajax_ok(array(), '清空数据成功'));
        }
        $course_model->rollback();
        $course_model->commit();
        $this->ajaxReturn(ajax_error('清空数据失败'));
    }

    /*------------------- 管理员权限控制 --------------------*/

    /**
     * Des :查看当前权限
     * Auth:lyt123
     */
    public function showStatus()
    {
        $data = $this->reqAdmin()->reqGet(array('type'));
        $json_file = $this->transferTypeToFileName($data['type']);
        $date_data = file_get_contents(__PUBLIC__ . "/necessary_infomation/$json_file");
        $content = json_decode($date_data, 1);
        $this->ajaxReturn(ajax_ok($content));
    }

    /**
     * Des  :修改权限
     * Param:
     *          1-开课一览表权限控制，2-考试安排权限控制
     * 第一种情况（type=1）: 1-不让教师和教务员进行操作，2-让上课院系教务员安排教师和合班，3-让教师填写排课要求。
     * 第二种情况（type=2）: 1-不让开课院系教务员填写监考老师，2-让开课院系教务员填写十三周考试监考老师，3-让开课院系教务员填写十九周考试监考老师。
     * Auth :lyt123
     */
    public function updateStatus()
    {
        $data = $this->reqAdmin()->reqPost(array('type', 'status'));

        $json_file = $this->transferTypeToFileName($data['type']);

        $content = json_encode(array('status' => $data['status']));
        if (file_put_contents(__PUBLIC__ . "/necessary_infomation/$json_file", $content)) {
            $this->ajaxReturn(ajax_ok());
        } else {
            $this->ajaxReturn(ajax_error());
        }
    }

    /*----------------------其他管理-----------------------*/

    /**
     * Des :（应该没用了）导入开课一览表,获取所有班级
     * Auth:lyt123
     */
    public function importCourseSelectBookExcelGetClass()
    {
        $course_data = $this->loadExcel('course_select_book_first-', 'excel_files/course_select_book/', 2);
        $model = D('Class');
        $model->startTrans();
        foreach ($course_data as $item) {
            if (intval(substr($item[0], -1, 1))) {
                $item[0] = substr($item[0], 0, -1);
            }
//            dump($item[0]);continue;
            $data = array('profession' => $item[0], 'name' => $item[3], 'student_sum' => $item[4]);
            if (!$model->getData($data)) {
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
        }
        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :这个函数和对应的表是为了填坑
     * Auth:lyt123
     */
    public function importCourseSelectBookExcelGetOpenClassInfo()
    {
        $course_data = $this->loadExcel('course_select_book_first-', 'excel_files/course_select_book/', 2);
        $model = D('SelectBook');
        $model->startTrans();
        foreach ($course_data as $item) {
            $data = array();
            if (intval(substr($item[0], -1, 1))) {
                $item[0] = substr($item[0], 0, -1);
            }

            //将毕业设计和毕业环节的课程跳过
            if (substr($item[8], 7, 1) == ')')
                continue;

            $data['prefession'] = $item[0];
            $data['open_class_academy'] = $item[1];
            $data['have_class_academy'] = $item[2];
            $data['class_name'] = $item[3];
            $data['code'] = $item[5];
            $data['name'] = $item[6];
            $result = $model->addOne($data);
            $this->checkResult($result, $model);
        }
        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :导出工作量汇总表
     * Auth:lyt123
     */
    public function outputWorkQualityGatherExcel()
    {
        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');

        $excel_data = array();
        $model = D('Course');
        $course_data = $model->getData(array($year_and_semester_data), array(), true, 'teacher_id');
//dd($course_data);
        $i = 0;
        foreach ($course_data as $one_course) {

            //根据课程week_section_ids去获得相应的周数/节数/时间
            $week_section_ids = explode(',', $one_course['week_section_ids']);
            $week_section_data = array();
            foreach ($week_section_ids as $key => $week_section_id) {
                $week_section_data[$key] = D('WeekSection')->getData(array('id' => $week_section_id), array());
                $week_section_data[$key]['period'] = current(D('ClassroomTime')->getData(array('id' => $week_section_data[$key]['classroom_time_id']), array('period')));
            }

//            dump($week_section_data);continue;
            //将周数/节数/时间格式转化后，放到数组temp_array里面
            $periods = array_values(array_unique(array_column_equal($week_section_data, 'period')));
            $temp_array = array();
            foreach ($periods as $key => $period) {
                $temp_array[$key]['period'] = $period;
                $temp_array[$key]['week_section'] = '';
                $value['time'] = 0;
            }
//                dump($temp_array);continue;
            foreach ($week_section_data as $item) {
                foreach ($temp_array as &$value) {
                    if ($item['period'] == $value['period']) {
                        $use_weeks = explode(',', $item['use_weeks']);
                        $value['week_section'] .= reoganizeData($use_weeks) . '周 : ' . $item['section_num'] . '节' . '; ';
                        $value['time'] += count($use_weeks) * $item['section_num'];//学时
                    }
                }
            }

//            dump($temp_array);continue;
            foreach ($temp_array as $item) {

                $excel_data[$i][0] = transferPeriodToDay($item['period']);
                $excel_data[$i][1] = substr($item['week_section'], 0, -2);//如果末尾是';',将它剔除
                $excel_data[$i][2] = current(D('Academy')->getData(array('id' => $one_course['academy_id']), array('name')));
                $excel_data[$i][3] = current(D('Class')->getData(array('id' => $one_course['class_id']), array('name')));
                $excel_data[$i][4] = current(D('Class')->getData(array('id' => $one_course['class_id']), array('student_sum')));
                $excel_data[$i][5] = '';//这里没有置空，excel表导出会错误
                $excel_data[$i][6] = '';
                $excel_data[$i][7] = D('NecessaryInfomation')->getYearAndSemesterForExcelTitle($year_and_semester_data, true);
                $excel_data[$i][8] = $one_course['name'];
                $excel_data[$i][9] = transferExamineWay($one_course['examine_way']);

                //如果一个课程拆分成多行，处理学时
                if ($item['time'] == $one_course['time_total']) {
                    $excel_data[$i][10] = $one_course['time_total'];
                    $excel_data[$i][11] = $one_course['time_theory'];
                    $excel_data[$i][12] = $one_course['time_practice'];
                } else {
                    $excel_data[$i][10] = $item['time'];
                    $excel_data[$i][11] = '';
                    $excel_data[$i][12] = '';
                }
                $excel_data[$i][13] = current(D('Teacher')->getData(array('id' => $one_course['teacher_id']), array('name')));

                //处理合班
                if ($one_course['combine_status']) {
                    $class_ids = explode(',', $one_course['combine_status']);
                    $classes = array();
                    foreach ($class_ids as $class_id) {
                        $classes[] = current(D('Class')->getData(array('id' => $class_id), array('name')));
                    }
                    $excel_data[$i][14] = implode(',', $classes);
                } else {
                    $excel_data[$i][14] = '';
                }
                $excel_data[$i][21] = $excel_data[$i][20] = $excel_data[$i][19] = $excel_data[$i][18] = $excel_data[$i][17] = $excel_data[$i][16] = $excel_data[$i][15] = '';
                if ($year_and_semester_data['semester'] == 1) {
                    $excel_data[$i][22] = $year_and_semester_data['year'] . '年上';
                } else {
                    $excel_data[$i][22] = $year_and_semester_data['year'] . '年下';
                }

                $i++;//继续excel表的下一行
            }
        }

//        dd($excel_data);
        //导出课表
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $model->setWorkQualityGatherExcelTitle($objPHPExcel);
        $model->setWorkQualityGatherExcelCellWidth($objPHPExcel);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A2');
        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        promptExcelDownload($objPHPExcel, '工作量汇总表.xls');
    }

    public function help()
    {
        debug();
//        D('Course')->startTrans();
        $course_data = D('Course')->select();

        foreach ($course_data as $item) {
            $result = D('Course')
                ->table('course co, class cl, select_book sb, academy a')
                ->field('sb.code, sb.open_class_academy, sb.have_class_academy')
                ->where('cl.name = sb.class_name and a.name = sb.open_class_academy')
                ->where(array('cl.id' => $item['class_id'], 'sb.name' => $item['name']))
                ->find();
//            dd($item);
//            dd($result);
            $update_data['open_class_academy_id'] = current(D('Academy')->getData(array('name' => $result['open_class_academy']), array('id')));
            $update_data['academy_id'] = current(D('Academy')->getData(array('name' => $result['have_class_academy']), array('id')));
            $update_data['code'] = $result['code'];
//            dd($update_data);
            if(empty($update_data['open_class_academy_id']) || empty($update_data['academy_id'])|| empty($update_data['code'])){
                ddd($item);
            }
            D('Course')->update($item['id'], $update_data);
        }
    }

    public function helpFilterData()
    {
        $data = D('PublicCourse')->select();
        foreach($data as $item){
dd($item);
            foreach($item as $key => $value){
                $tmp_data[$key] = filter($value);
                dd($value);dd($key);
            }
            D('PublicCourse')->update($tmp_data['id'], $tmp_data);
            $tmp_data = array();
        }
    }

    public function clearThisSemesterData()
    {
        if (
            D('Holiday')->where(1)->delete() !== false &&
            D('TeacherExistingClass')->where(1)->delete() !== false &&
            D('WeekSection')->where(1)->delete() !== false
        ) {
            $this->ajaxReturn(ajax_ok());
        }

        $this->ajaxReturn(ajax_error());
    }
}