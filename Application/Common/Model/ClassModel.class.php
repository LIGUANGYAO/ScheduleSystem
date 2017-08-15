<?php
/* User:lyt123; Date:2016/11/1; QQ:1067081452 */
namespace Common\Model;

class ClassModel extends CURDModel 
{
    public function transferClassIdsToClassNames($class_ids)
    {
        $class_ids = explode(',', $class_ids);

        $class_names = array();
        foreach ($class_ids as $class_id) {
            $class_names[] = current(D('Class')->getData(array('id' => $class_id), array('name')));
        }

        $class_names = implode('/', $class_names);
        return $class_names;
    }
}