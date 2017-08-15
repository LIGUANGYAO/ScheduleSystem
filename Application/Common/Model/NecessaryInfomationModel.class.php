<?php
/* User:lyt123; Date:2016/12/15; QQ:1067081452 */
namespace Common\Model;

class NecessaryInfomationModel
{
    public function getJsonData($file_name)
    {
        $json_file_content = file_get_contents(__PUBLIC__ . "/necessary_infomation/{$file_name}.json");
        return json_decode($json_file_content, 1);
    }

    public function getNextSemesterAndYear()
    {
        $now_semester_and_year = $this->getJsonData('year_semester');
        if($now_semester_and_year['semester'] == 2){
            return array(
                'year' => $now_semester_and_year['year']+1,
                'semester' => 1
            );
        }elseif($now_semester_and_year['semester'] == 1){
            return array(
                'year' => $now_semester_and_year['year'],
                'semester' => 2
            );
        }
    }

    public function getYearAndSemesterForExcelTitle($year_and_semester_data, $cut_year = false)
    {
        if($cut_year){
            $year_and_semester_data['year'] = substr($year_and_semester_data['year'], 2, 2);
        }
        if($year_and_semester_data['semester'] == 1){
            return ($year_and_semester_data['year']-1).'-'.$year_and_semester_data['year'].'-2';
        }else{
            return ($year_and_semester_data['year']).'-'.($year_and_semester_data['year']+1).'-1';
        }
    }
}