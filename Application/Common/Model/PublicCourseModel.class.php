<?php
/* User:lyt123; Date:2016/11/6; QQ:1067081452 */
namespace Common\Model;

class PublicCourseModel extends CURDModel
{
    public function getPublicCourses($course_name, $json_data)
    {
        $courses_data = $this
            ->table('course co, class cl')
            ->field('co.*')
            ->where('co.class_id = cl.id and cl.type <> 3')
            ->where(array('co.name' => $course_name))
            ->where(array('year' => $json_data['year'], 'semester' => $json_data['semester']))
            ->select();

        return $courses_data;
    }

    public function getPublicCourseIds($json_data)
    {
        $public_course_ids = array();

        $public_courses = D('PublicCourse')->getData(array(), array(), true);
        foreach($public_courses as $public_course){
            $public_courses_data = $this->getPublicCourses($public_course['name'], $json_data);
            $public_course_ids = array_merge(array_column_equal($public_courses_data, 'id'), $public_course_ids);
        }

        return $public_course_ids;
    }

    public function transformPeriodNameAndNumber($period, $transfer_way)
    {
        $period_info = array(
            array(
                'name' => "星期一晚上",
                'period' => 1
            ),
            array(
                'name' => "星期二晚上",
                'period' => 2
            ),
            array(
                'name' => "星期三晚上",
                'period' => 3
            ),
            array(
                'name' => "星期四晚上",
                'period' => 4
            ),
            array(
                'name' => "星期五晚上",
                'period' => 5
            ),
            array(
                'name' => "星期六上午",
                'period' => 6
            ),
            array(
                'name' => "星期六下午",
                'period' => 7
            ),
            array(
                'name' => "星期六晚上",
                'period' => 8
            ),
            array(
                'name' => "星期日上午",
                'period' => 9
            ),
            array(
                'name' => "星期日下午",
                'period' => 10
            ),
            array(
                'name' => "星期日晚上",
                'period' => 11
            )
        );

        foreach($period_info as $one_period_info)
        {
            if($transfer_way == 'name_to_number'){
                if($one_period_info['name'] == $period){
                    return $one_period_info['period'];
                }
            }
            if($transfer_way == 'number_to_name'){
                if($one_period_info['period'] == $period){
                    return $one_period_info['name'];
                }
            }
        }
    }
}