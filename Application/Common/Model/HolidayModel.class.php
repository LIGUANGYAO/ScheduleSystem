<?php
/* User:lyt123; Date:2016/11/20; QQ:1067081452 */
namespace Common\Model;

class HolidayModel extends CURDModel 
{
    /**
     * Des :如weekday = '6, 7',返回periods = '6,7,8,9,10,11'
     * Auth:lyt123
     */
    public function getPeriods($weekdays)
    {
        $periods = array();
        $weekdays = explode(',', $weekdays);
        foreach($weekdays as $weekday){
            switch($weekday){
                case 1:
                    $periods[] = 1;
                    break;
                case 2:
                    $periods[] = 2;
                    break;
                case 3:
                    $periods[] = 3;
                    break;
                case 4:
                    $periods[] = 4;
                    break;
                case 5:
                    $periods[] = 5;
                    break;
                case 6:
                    $periods[] = 6;
                    $periods[] = 7;
                    $periods[] = 8;
                    break;
                case 7:
                    $periods[] = 9;
                    $periods[] = 10;
                    $periods[] = 11;
                    break;

            }
        }
        return $periods;
    }
}