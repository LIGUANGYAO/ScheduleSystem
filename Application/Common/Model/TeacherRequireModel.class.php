<?php
/* User:lyt123; Date:2016/12/10; QQ:1067081452 */
namespace Common\Model;

class TeacherRequireModel extends CURDModel 
{
    public function getTeacherRequireData($id)
    {
        $teacher_require_data = $this->getData(array('id' => $id));

        return $teacher_require_data;
    }

    /**
     * Des :将排课要求的数据整理成字符串
     * Auth:lyt123
     */
    public function reorganizeData($teacher_require_data)
    {
        return $teacher_require_data ? "考核方式：{$teacher_require_data['check_way']};\n多媒体：{$teacher_require_data['time_media']}节;\n课内：{$teacher_require_data['time_in_class']}节;\n要求：{$teacher_require_data['require']}" : '';
    }
}