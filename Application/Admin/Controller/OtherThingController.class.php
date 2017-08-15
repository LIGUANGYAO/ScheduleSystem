<?php
/* User:lyt123; Date:2016/12/4; QQ:1067081452 */
namespace Admin\Controller;

use Common\Controller\BaseController;

class OtherThingController extends BaseController
{
    /**
     * Des :上传教学周次表
     * Auth:lyt123
     */
    public function uploadTeachingWeekExcel()
    {
        $info = upload_file('teaching_week', 'excel_files/teaching_week/', 'certain_name_excel');
        if (!is_array($info)) $this->ajaxReturn(ajax_error($info));

        $this->ajaxReturn(ajax_ok());
    }

    public function downloadTeachingWeekExcel()
    {
        $file_name = __PUBLIC__ . "/upload/excel_files/teaching_week/teaching_week.xls";
        if (!file_exists($file_name)) return ajax_error("文件不存在");
        $fp = fopen($file_name, "r");
        $file_size = filesize($file_name);
        //返回的文件
        header("Content-type: application/octet-stream");
        //按字节大小返回
        header("Accept-Ranges: bytes");
        //返回文件大小
        header("Accept-Length: $file_size");
        //这里客户端的弹出对话框，对应的文件名
        header("Content-Disposition: attachment; filename=" . "成教教学周次时间表.xls");

        //向客户端回送数据
        $buffer = 1024;
        $file_count = 0;
        while (!feof($fp) && $file_size - $file_count > 0) {
            $file_data = fread($fp, $buffer);
            //统计读了多少个字节
            $file_count += $buffer;
            //把部分数据回送给浏览器
            echo $file_data;
        }

        //关闭文件
        fclose($fp);
    }

    public function help()
    {
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();
        $excel_data = \PHPExcel_IOFactory::load(__PUBLIC__ . '/upload/excel_files/work_quality_gather/ww.xls');
        ddd($excel_data);

        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A4');
        promptExcelDownload($objPHPExcel, 'ceshi.xls');
    }

    public function importWorkQualityGatherExcel()
    {
        $post_data = $this->reqAdmin()->reqPost(array('admin_confirmed'));

        $admin_confirmed = $post_data['admin_confirmed'] == 2 ? true : false;
        ini_set('memory_limit', '-1');
        $excel_data = $this->loadExcel('work_quality_gather-', 'excel_files/work_quality_gather/', 1);
//        dump(count($excel_data)); output 9657
//dump($excel_data);exit();
        $model = D('WorkQualityGather');
        $model->startTrans();
//        dd($excel_data);
        $no_match_box = array();
        foreach ($excel_data as $key => $item) {

            //这段代码不能用，因为在excel中间有一行空的
            /*if (!$item[1] && !$item[6]) {
                if ($no_match_box && !$admin_confirmed) {
                    $model->rollback();
                    $this->ajaxReturn($no_match_box);
                }else{
                    $model->commit();
                    $this->ajaxReturn(ajax_ok(array(), '已读取到最后一行'));
                }
            }*/

            if ($academy = current(D('Academy')->getData(array('name' => $item[0]), array('id')))) {
                $data['academy_id'] = $academy;
            } else {
                $data['academy_id'] = 0;
                $data['academy_value'] = $item[0];
            }
            $data['class'] = $item[1];
            $data['student_sum'] = $item[2];
            $data['paper_undergraduate'] = $item[3];
            $data['paper_special'] = $item[4];
            $data['semester'] = substr($item[5], -1, 1);
            $data['year'] = '20' . substr($item[5], 0, 2);
            $data['name'] = $item[6];
            $data['examine_way'] = $item[7];
            $data['time_total'] = $item[8];
            $data['time_theory'] = $item[9];
            $data['time_practice'] = $item[10];
            $data['teacher_name'] = $item[11];
            $data['teacher_position'] = $item[12];
            $data['comment'] = $item[13];
            $data['work_quality'] = $item[14];
            $data['work_quality_sum'] = $item[15];
            if (!$data['academy_id']) {
                $item['index'] = $key;
                $no_match_box[] = $no_match_course_box[] = "学院找不到:第{$key}个课程（{$data['name']}-{$data['teacher_name']}）";;
            }
            $this->checkResult($model->addOne($data), $model);
        }

        if ($no_match_box && !$admin_confirmed) {
            $model->rollback();
            $this->ajaxReturn($no_match_box);
        }

        $model->commit();
        $this->ajaxReturn(ajax_ok());
    }

    public function queryWorkQualityGather()
    {
        $data = $this->reqTeacher()->reqGet(array(), array('teacher_name', 'academy_id'));

        $data = D('WorkQualityGather')->getData($data, array(), true);

        $data = D('WorkQualityGather')->processData($data);

        $this->ajaxReturn(ajax_ok($data));
    }

    public function outputWorkQualityGather()
    {
        $data = $this/*->reqTeacher()*/
        ->reqGet(array(), array('teacher_name', 'academy_id'));

        $model = D('WorkQualityGather');

        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';
        $objPHPExcel = new \PHPExcel();

        $model->setWorkQualityGatherExcelTitle($objPHPExcel);
        $model->setWorkQualityGatherExcelCellWidth($objPHPExcel);

        $data = D('WorkQualityGather')->getData($data, array(), true);
        $data = D('WorkQualityGather')->processData($data);

        $excel_data = array();
        $i = 0;
        foreach ($data as $item) {
            $excel_data[$i][0] = $item['academy'];
            $excel_data[$i][1] = $item['class'];
            $excel_data[$i][2] = $item['student_sum'];
            $excel_data[$i][3] = $item['paper_undergraduate'];
            $excel_data[$i][4] = $item['paper_special'];
            $excel_data[$i][5] = $item['year_and_semester'];
            $excel_data[$i][6] = $item['name'];
            $excel_data[$i][7] = $item['examine_way'];
            $excel_data[$i][8] = $item['time_total'];
            $excel_data[$i][9] = $item['time_theory'];
            $excel_data[$i][10] = $item['time_practice'];
            $excel_data[$i][11] = $item['teacher_name'];
            $excel_data[$i][12] = $item['teacher_position'];
            $excel_data[$i][13] = $item['comment'];
            $excel_data[$i][14] = $item['work_quality'];
            $excel_data[$i][15] = $item['work_quality_sum'];
            $excel_data[$i][16] = $item['time'];
            $i++;
        }

//        dd($excel_data);
        automaticWrapText($objPHPExcel, count($excel_data) + 5);
        $objPHPExcel->getActiveSheet()->fromArray($excel_data, NULL, 'A2');
        promptExcelDownload($objPHPExcel, '工作量汇总表.xls');
    }

    public function uploadTeachingTaskExcel()
    {
        $info = upload_file('teaching_task', 'excel_files/teaching_task/', 'certain_name_excel');
        if (!is_array($info)) $this->ajaxReturn(ajax_error($info));
        $this->ajaxReturn(ajax_ok());
    }

    /**
     * Des :教师或教务员下载教学任务书
     * Auth:lyt123
     */
    public function downloadTeachingTaskExcel()
    {
        $data = $this->reqTeacher()->reqGet(array(), array('teacher_name'));
//        $data['teacher_name'] = 'liu';
//        session('academy_id', 9);
        $year_and_semester_data = D('NecessaryInfomation')->getNextSemesterAndYear();

        $academy_id = session('academy_id');
        if ($data['teacher_name']) {
            $teachers = D('Teacher')->getData(array('name' => $data['teacher_name']), array(), true);
        } elseif ($academy_id) {
            //多表操作获取教师课程
            $teachers = D('Course')->getAcademyTeacherCourse($academy_id, $year_and_semester_data);
        } else {
            $this->ajaxReturn(ajax_error('没有输入教师姓名'));
        }

        //将'name'字段一样的一维数组元素剔除，得到不重复的所有教师姓名
        $teachers = unique_multidim_array($teachers, 'name');
//ddd($teachers);
        include_once __PUBLIC__ . '/plugins/excel/PHPexcel.php';

        //循环教师姓名，生成所有教师的课程表
        //如果是单个教师，则直接提示下载
//        ddd($teachers);

        //下面注意，如果学院没有课程会导致程序卡死
        foreach ($teachers as $key => $teacher) {
            $academy_teaching_task_excel_dir = '';

            //处理课程数据，合班的课程只显示在一行里
            $teacher['id'] = D('Course')->getTeacherIdByTeacherName(array('teacher' => $teacher['name']), $year_and_semester_data);
            $course_data = D('Course')->getCourseData(array('teacher_id' => $teacher['id'], 'page' => 1, 'limit' => 20, 'semester' => $year_and_semester_data['semester'], 'year' => $year_and_semester_data['year']));
            $course_data = D('Course')->getTeacherTeachingTaskCourse($course_data);
            //加载表并将课程和教师数据放进去
            $objPHPExcel = \PHPExcel_IOFactory::load(__PUBLIC__ . '/upload/excel_files/teaching_task/teaching_task.xls');
//dump($course_data);
//            dump($teachers);
            D('SelectBook')->putTeacherAndCourseDataIntoExcel($objPHPExcel, $teacher, $course_data);

            //文件名带中文保存不了
            $excel_name = urlencode($teacher['name'] . '老师教学任务书.xls');
//ddd($teacher);
            if ($data['teacher_name']) {
                promptExcelDownload($objPHPExcel, $teacher['name'] . '教学任务书.xls');
                exit();
            } else {
                $academy_teaching_task_excel_dir = 'Public/system_generate_file/' . $academy_id;
                if (!is_dir($academy_teaching_task_excel_dir)) {
                    mkdir($academy_teaching_task_excel_dir);
                } else {
                    //每次下载之前都先把之前表格清空，再重新生成
                    if ($key == 0) {
                        deleteFiles($academy_teaching_task_excel_dir);
                    }
                }
                dd($excel_name);
                $save_path = $academy_teaching_task_excel_dir . '/' . $excel_name;
//                ddd($save_path);

                save_excel($objPHPExcel, $save_path, $excel_name);
            }
        }

        $academy_name = current(D('Academy')->getData(array('id' => $academy_id), array('name'))) . '教学任务书';
        if ($academy_id) {
            //文件的路径不能有中文哇，好坑
            compress_directory_to_zip_file($academy_teaching_task_excel_dir . '/', $academy_teaching_task_excel_dir . '/' . 'haha.zip');
            zip_file_prompt_download($academy_teaching_task_excel_dir . '/' . 'haha.zip', $academy_name);
        }
    }

    public function clearCourseDataOfOneSemester()
    {
        $this->reqAdmin();

        $year_and_semester_data = D('NecessaryInfomation')->getJsonData('year_semester');

        $course_model = D('Course');
        $course_model->startTrans();

        if (
            is_int(D('Course')->where($year_and_semester_data)->delete()) &&
            is_int(D('Exam')->where(1)->delete()) &&
            is_int(D('TeacherExistingClass')->where($year_and_semester_data)->delete()) &&
            is_int(D('WeekSection')->where(1)->delete())
        ) {
            $course_model->commit();
            $this->ajaxReturn(ajax_ok());
        } else {
            $course_model->rollback();
            $this->ajaxReturn(ajax_error());
        }

    }
}