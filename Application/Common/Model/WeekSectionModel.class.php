<?php
/* User:lyt123; Date:2016/11/2; QQ:1067081452 */
namespace Common\Model;

class WeekSectionModel extends CURDModel 
{
    public function getClassroomTimeByWeekSectionId($week_section_ids)
    {
        $classroom_time_string = array();
        $week_section_ids = explode(',', $week_section_ids);
        foreach ($week_section_ids as $num => $week_section_id) {
            $classroom_time_string[$num] = '';
            $week_section_data = D('WeekSection')->getData(array('id' => $week_section_id));
            $classroom_time_data = D('ClassroomTime')->getData(array('id' => $week_section_data['classroom_time_id']));

            //用于修改课程时使用
            $classroom_time_string[$num]['week_for_update'] = '第' . str_replace(',', '、', $week_section_data['use_weeks']). '周';

            $classroom_time_string[$num]['week'] = '第' . reoganizeData(explode(',', $week_section_data['use_weeks'])) . '周';

            $classroom_time_string[$num]['weekday'] = transferPeriodToDay($classroom_time_data['period']);

            $classroom_time_string[$num]['weekday'] .= ':' . $week_section_data['section_num'] . '节';

            $building = '';
            switch ($classroom_time_data['building']) {
                case 1:
                    $building = '马兰芳教学楼' . $classroom_time_data['number'];
                    break;
                case 2:
                    $building = '南主楼' . $classroom_time_data['number'];
                    break;
                case 3:
                    $building = '黄浩川教学楼' . $classroom_time_data['number'];
                    break;
                case 4:
                    $building = $classroom_time_data['classroom'];
            }
            $classroom_time_string[$num]['classroom'] = $building;
        }

        return $classroom_time_string;
    }
}