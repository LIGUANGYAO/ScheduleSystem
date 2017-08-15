<?php
/* User:lyt123; Date:2016/11/5; QQ:1067081452 */
namespace Common\Model;

class TeacherExistingClassModel extends CURDModel
{
    public function extractTeacherClassFromHtml($html)
    {
        //在html文件中加上字符编码才不会乱码
        $html = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=utf-8">', $html);

        //使用php自带的DOMDocument库来处理得到的html
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        //extract teacher class from the html page by element id
        $i = 0;
        $classes = array();
        while (
        $content = $doc->getElementById('ctl00_ContentPlaceHolder1_ListViewXKQ_ctrl' . $i . '_ctimeLabel')
        ) {
            if ($content->textContent)

                $classes[] = $content->textContent;
            $i++;
        }
        return $classes;
    }

    public function getTeacherClassHtmlFromShoolWebsite($teacher_name)
    {
        //发送到学校网站的数据
        $simulate_data = json_decode(file_get_contents(__PUBLIC__ . '/necessary_infomation/crawl_website_data.json'), 1);
        $post_data = array(
            '__VIEWSTATE' => $simulate_data['value_first'],
            '__EVENTVALIDATION' => $simulate_data['value_second'],
            $simulate_data['value_third'] => 'on',
            $simulate_data['value_fourth'] => $teacher_name,
            $simulate_data['value_fifth'] => '提交'
        );

        //调接口
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://202.192.240.54/kkcx/default.aspx');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function getTeacherExistingClassByTeacherId($teacher_id, $item_year, $item_semester)
    {
        $teacher_data = array();

        if (strpos($teacher_id, ',')) {
            $teacher_ids = explode(',', $teacher_id);
            foreach ($teacher_ids as $teacher_id_key => $teacher_id) {
                $class = current($this->getData(array('teacher_id' => $teacher_id, 'year' => $item_year, 'semester'=> $item_semester), array('existing_class')));
                $teacher_data[] = $class;
            }
            $teacher_data = implode(';', $teacher_data);
        }else{
            $teacher_data = current($this->getData(array('teacher_id' => $teacher_id, 'year' => $item_year, 'semester'=> $item_semester), array('existing_class')));
        }

        return $teacher_data;
    }
}