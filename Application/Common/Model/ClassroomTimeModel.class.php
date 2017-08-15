<?php
/* User:lyt123; Date:2016/11/2; QQ:1067081452 */
namespace Common\Model;

class ClassroomTimeModel extends CURDModel
{
    public function getDataByString($where = '', array $field = null, $is_multi = false)
    {
        if ($where) $this->where($where);

        if ($field) $this->field($field);

        if ($is_multi)
            return $this->select();
        return $this->find();
    }

    public function setExcelTitle($objPHPExcel)
    {
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(28);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '教室')
            ->setCellValue('B1', '星期一')
            ->setCellValue('C1', '星期二')
            ->setCellValue('D1', '星期三')
            ->setCellValue('E1', '星期四')
            ->setCellValue('F1', '星期五')
            ->setCellValue('G1', '星期六')
            ->setCellValue('H1', '星期日');
    }

    public function setCellWidth($objPHPExcel)
    {
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(18);

    }

    /**
     * Des :获取教室使用情况
     * Auth:lyt123
     */
    public function getClassroom($periods, $building)
    {
        $excel_data = array();

        foreach($periods as $period){
            $classrooms = $this->getData(array('building' => $building, 'period' => $period), array(), true);
//ddd($classrooms);

            //修bug,week_section表里的id需在course表里的week_section_id
            $week_section_ids = array_column_equal(D('Course')->field('week_section_ids')->select(), 'week_section_ids');
            $week_section_array = array();
            foreach($week_section_ids as $one_week_section_id){
                if(strpos($one_week_section_id, ',')){
                    $week_section_id_separate  = explode(',', $one_week_section_id);
                    $week_section_array = array_merge($week_section_array, $week_section_id_separate);
                }else{
                    $week_section_array[] = $one_week_section_id;
                }
            }

            foreach($classrooms as $key => $classroom){
                if(!$excel_data[$key][0]){
                    $excel_data[$key][0] = $classroom['number'];
                }

                $use_weeks = array();
                $week_data = D('WeekSection')->getData(array('classroom_time_id' => $classroom['id'], 'id' => array('in', $week_section_array)), array('use_weeks'), true);

                foreach($week_data as $value){
                    $use_weeks[] = $value['use_weeks'];
                }

                //补上节假日的信息
                /*                foreach($holiday as $value){
                    if(in_array($period, $value['periods'])){
                        $use_weeks[] = $value['week'];
                    }
                }*/
                dd($use_weeks);
                $use_weeks = explode(',', implode(',', array_unique($use_weeks)));
                sort($use_weeks);
                $use_weeks = reoganizeData($use_weeks);
dd($use_weeks);
                if($use_weeks){
                    switch($period){
                        case 1:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 2:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 3:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 4:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 5:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 6:
                            $use_weeks = '上午:'.$use_weeks.'周';
                            break;
                        case 7:
                            $use_weeks = '下午:'.$use_weeks.'周';
                            break;
                        case 8:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                        case 9:
                            $use_weeks = '上午:'.$use_weeks.'周';
                            break;
                        case 10:
                            $use_weeks = '下午:'.$use_weeks.'周';
                            break;
                        case 11:
                            $use_weeks = '晚:'.$use_weeks.'周';
                            break;
                    }
                }


                $excel_data[$key][$period] = $use_weeks;
            }
        }

        return $excel_data;
    }

    public function returnDataFitExcel($excel_data)
    {
        foreach ($excel_data as &$data) {
            $data[6] = $data[6] ? "{$data[6]}; " : '';
            $data[6] .= $data[7] ? "{$data[7]}; " : '';
            $data[6] .= $data[8] ? "{$data[8]} " : '';
            $data[9] = $data[9] ? "{$data[9]}; " : '';
            $data[9] .= $data[10] ? "{$data[10]}; " : '';
            $data[9] .= $data[11] ? "{$data[11]} " : '';
            $data[7] = $data[9];
            unset($data[8]);
            unset($data[9]);
            unset($data[10]);
            unset($data[11]);
        }

        return $excel_data;
    }

    public function returnThirdDataFitExcel($excel_data)
    {
//        dd($excel_data);
        $data = array();
        foreach ($excel_data as $key => $single_data) {
            $data[$key][0] = $single_data[0];
            $data[$key][1] = ' ';
            $data[$key][2] = ' ';
            $data[$key][3] = ' ';
            $data[$key][4] = ' ';
            $data[$key][5] = ' ';
            $data[$key][6] = $single_data[6] ? "{$single_data[6]}; \n" : '';
            $data[$key][6] .= $single_data[7] ? "{$single_data[7]}; \n" : '';
            $data[$key][6] .= $single_data[8] ? "{$single_data[8]} \n" : '';
            $data[$key][7] = $single_data[9] ? "{$single_data[9]}; \n" : '';
            $data[$key][7] .= $single_data[10] ? "{$single_data[10]}; \n" : '';
            $data[$key][7] .= $single_data[11] ? "{$single_data[11]} \n" : '';
        }

        return $data;
    }

    public function transferClassroomNameToNumber($building)
    {
        switch ($building) {
            case '马兰芳教学楼':
                $number = 1;
                break;
            case '南主楼':
                $number = 2;
                break;
            case '黄浩川教学楼':
                $number = 3;
                break;
            default :
                $number = 4;
        }

        return $number;
    }

    public function getTimeClassroomDataForOutputExcel($classroom_time)
    {
        $classroom_time_string = array();
        foreach ($classroom_time as $one_classroom_time) {
            $classroom_time_string[] = "{$one_classroom_time['weekday']}，{$one_classroom_time['week']}，{$one_classroom_time['classroom']}";
        }
        $classroom_time = implode("\n", $classroom_time_string);
        return $classroom_time;
    }
}
