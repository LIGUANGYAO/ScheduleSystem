<?php
/* User:lyt123; Date:2016/10/27; QQ:1067081452 */

function debug()
{
    define('DEBUG_MODE', true);
}

function dd($data = 'hehe', $name = null)
{
    if(DEBUG_MODE === true){
        if($name){
            dump("{$name}:{$data}");
        }else{
            dump($data);
        }
    }
}
/**
 * Des : 断点测试
 * Auth: lyt123
 */
function ddd($data = 'hahaa', $name = null)
{
    if(DEBUG_MODE === true){
        if($name){
            dump("{$name}:{$data}");
        }else{
            dump($data);
        }
        exit();
    }
}

/**
 * Des : ajax_ok
 * Auth: lyt123
 */
function ajax_ok(array $data = null, $msg = null, $status = 200)
{
    if (!$msg) $msg = 'ok';

    return array(
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    );
}

/**
 * Des : ajax_error
 * Auth: lyt123
 */
function ajax_error($msg = null, $status = 422, $detail = null)
{
    if (!$msg) $msg = 'error';

    return array(
        'status' => $status,
        'msg' => $msg,
        'detail' => $detail
    );
}

function encrypt_password($password)
{
    return md5(sha1($password));
}

/**
 * Des :此函数跟array_column()一样，为了兼容php5.4版本
 * Auth:lyt123
 */
function array_column_equal($input, $column_key, $index_key = NULL) {
    if (!is_array($input)) {
        trigger_error(__FUNCTION__ . '() expects parameter 1 to be array, ' . gettype($input) . ' given', E_USER_WARNING);
        return FALSE;
    }

    $ret = array();
    foreach ($input as $k => $v) {
        $value = NULL;
        if ($column_key === NULL) {
            $value = $v;
        }
        else {
            $value = $v[$column_key];
        }

        if ($index_key === NULL || !isset($v[$index_key])) {
            $ret[] = $value;
        }
        else {
            $ret[$v[$index_key]] = $value;
        }
    }

    return $ret;
}

/**
 * Des :upload file
 * Auth:lyt123
 */
function upload_file($file_name, $save_path, $type, $multiple = false)
{
    $upload = new \Think\Upload();                 // 实例化上传类
    $upload->rootPath = './Public/upload/';        // 根目录
    $upload->replace = true;                       // 覆盖同名文件
    $upload->autoSub = false;                      // 不自动子目录保存文件
    $upload->hash = false;                         // 不生成hash编码，提速

    switch ($type) {                                // 定制配置

        case 'excel':
            $upload->exts = array('xls', 'xlsx');
            $upload->maxSize = 8388608;            //4M
            $upload->saveName = $file_name . date("Y-m-d");
            $upload->savePath = $save_path;
            break;
        case 'certain_name_excel':
            $upload->exts = array('xls', 'xlsx');
            $upload->maxSize = 4194304;            //4M
            $upload->saveName = $file_name;
            $upload->savePath = $save_path;
            break;
        default:
            dd('wrong_type');
    }
    $upload_info = $upload->upload();

    if (!$upload_info) return $upload->getError();          //上传失败

    return array(current($upload_info)['savepath'] . current($upload_info)['savename']);
}

/**
 * Des :get the string between $start and $end
 * Auth:lyt123
 */
function get_between($input, $start, $end)
{
    $substr = substr($input, strlen($start) + strpos($input, $start),
        (strlen($input) - strpos($input, $end)) * (-1));
    return $substr;
}

/**
 * Des :载入excel表, $unset_end表示unset几行
 * Auth:lyt123
 */
function loadExcel($file_name, $file_path, $unset_end)
{
    //载入excel表
    $upload_file = upload_file($file_name, $file_path, 'excel');

    //处理上传失败的情况
    if(!is_array($upload_file)){
        return $upload_file;
    }

    include_once(__PUBLIC__ . '/plugins/excel/PHPExcel.php');
    $objPHPExcel = \PHPExcel_IOFactory::load(__PUBLIC__ . '/upload/' . $upload_file[0]);
    $info = $objPHPExcel->getActiveSheet()->toArray(null, true, true, false);

    //将excel表前几行去除
    for ($i = 0; $i < $unset_end; $i++) {
        unset($info[$i]);
    }

    trimSpaceForExcel($info);

    return $info;
}

/**
 * Des :剪除数据前后的空格并将为null的数据置为''
 * Auth:lyt123
 */
function trimSpaceForExcel(&$data)
{
    foreach ($data as &$item) {
        foreach ($item as &$value) {
            if ($value) {
                $match = array(' ', '（', '）', '：');
                $replace = array('', '(', ')', ':');
                $value = str_replace($match, $replace, $value);
            } else {
                $value = '';
            }
        }
    }
    unset($item);
    unset($value);
}

function filter($string_to_filt){
    $match = array(" ", '（', '）', '：', '；','，', "\n");
    $replace = array("", '(', ')', ':', ';', ',', "");
    return str_replace($match, $replace, $string_to_filt);
}
/**
 * Des :剪除数据前后的空格并将为null的数据置为''
 * Auth:lyt123
 */
function trimSpace($data)
{
    $data = trim($data, "\n");
    $match = array(" ", '（', '）', '：', '；','，', "\n");
    $replace = array("", '(', ')', ':', ';', ',', "");
    return str_replace($match, $replace, $data);
}

/**
 * Des :下载excel表
 * Auth:lyt123
 */
function promptExcelDownload($objPHPExcel, $excel_name)
{
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename='{$excel_name}'");
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}

/**
 * Description : 发送邮件
 * Auth : Shelter
 *
 * @param $to
 * @param $title
 * @param $content
 * @return bool
 */
function send_email($to,$title,$content)
{
    $Smtp = new \Common\Model\SmtpModel(
        'smtp.qq.com', 25, true,   //使用身份验证
        '这里填邮箱前面的qq号', '这里填邮箱密码'
    );

    $Smtp->debug = false;       //是否显示发送的调试信息

    $state = $Smtp->sendmail(
        $to, '2581318149@qq.com', $title,
        $content, 'HTML'
    );

    if($state=="") return false;
    else return true;
}

/**
 * Des :整理weeks数据
 * Auth:lyt123
 */
function reoganizeData($weeks)
{
    $length = count($weeks);
    $j = 0;
    $arrays = array();
    for ($i = 0; $i < $length; $i++) {
        if (($weeks[$i + 1] - $weeks[$i]) == 1) {
            $arrays[$j][] = $weeks[$i];
        } else {
            $arrays[$j][] = $weeks[$i];
            $j++;
        }
    }

    $week_string = '';
    foreach ($arrays as $array) {
        $length = count($array);
        if ($length > 1) {
            $week_string .= "{$array[0]}-{$array[$length-1]},";
        } else {
            $week_string .= "{$array[0]},";
        }
    }
    $week_string = rtrim($week_string, ',');

    return $week_string;
}

/**
 * Des :自动换行
 * Auth:lyt123
 */
function automaticWrapText($objPHPExcel, $row_sum)
{
    $sum = $row_sum + 5;
    //getStype('A1:Z5')表示将A1到Z5的所有单元格设为自动换行
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z' . $sum)->getAlignment()->setWrapText(TRUE);
}

function transferPeriodToDay($period)
{
    $day = '';
    switch ($period) {
        case 1:
            $day .= '周一晚上';
            break;
        case 2:
            $day .= '周二晚上';
            break;
        case 3:
            $day .= '周三晚上';
            break;
        case 4:
            $day .= '周四晚上';
            break;
        case 5:
            $day .= '周五晚上';
            break;
        case 6:
            $day .= '周六上午';
            break;
        case 7:
            $day .= '周六下午';
            break;
        case 8:
            $day .= '周六晚上';
            break;
        case 9:
            $day .= '周日上午';
            break;
        case 10:
            $day .= '周日下午';
            break;
        case 11:
            $day .= '周日晚上';
            break;
    }
    return $day;
}

function transferPeriodAndDay($value, $transfer_way)
{
    $period_day_array = array(
        1 => '周一晚上',
        2 => '周二晚上',
        3 => '周三晚上',
        4 => '周四晚上',
        5 => '周五晚上',
        6 => '周六上午',
        7 => '周六下午',
        8 => '周六晚上',
        9 => '周日上午',
        10 => '周日下午',
        11 => '周日晚上',
    );
    switch($transfer_way){
        case 'period_to_day':
            break;
        case 'day_to_period':
            return array_keys($period_day_array, $value)[0];
            break;
    }
}

function transferExamineWay($examine_way)
{
    switch ($examine_way) {
        case 1:
            return '考试';
        case 2:
            return '考查';
        case 3:
            return '其他';
    }
}

function transferExamineWayNameToNumber($name)
{
    if ($name == '考试')
        $number = 1;
    elseif ($name == '考查')
        $number = 2;
    elseif ($name == '其他' || $name == '其它')
        $number = 3;
    return $number;
}

//Create multidimensional array unique for any single key index.
function unique_multidim_array($array, $key)
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}

function list_dir($dir)
{
    $result = array();
    if (is_dir($dir)) {
        $file_dir = scandir($dir);
        foreach ($file_dir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            } elseif (is_dir($dir . $file)) {
                $result = array_merge($result, list_dir($dir . $file . '/'));
            } else {
                array_push($result, $dir . $file);
            }
        }
    }
    return $result;
}

function compress_directory_to_zip_file($dir, $filename)
{

    $datalist = list_dir($dir);

    $zip = new ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
    if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
        exit('无法打开文件，或者文件创建失败');
    }
    foreach( $datalist as $val){
        if(file_exists($val)){
            //这里我加了urldecode()函数，将字符编码改正过来
            $zip->addFile( $val, iconv('utf-8', 'gbk', urldecode(basename($val))));//第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
        }
    }
    $zip->close();//关闭
//    ddd(mb_detect_encoding('鍒樹匠鑰佸笀鏁欏浠诲姟涔?xls'));
}

function zip_file_prompt_download($file_path, $file_name)
{
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename=' . $file_name.'.zip'); //文件名
    header("Content-Type: application/zip"); //zip格式的
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
    header('Content-Length: ' . filesize($file_path)); //告诉浏览器，文件大小
    @readfile($file_path);
}

/**
 * Des :delete the files in the folder, cannot delete the directories in the folder
 * Auth:lyt123
 */
function deleteFiles($dirPath)
{
    //add a slash to the end of path if not exist
    if(substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }

    //gets all the path in $dirPath
    $files = glob($dirPath . '*');

    foreach($files as $file) {
        if(is_file($file))
            unlink($file);
    }
}

/**
 * Des :保存excel
 * Auth:lyt123
 */
function save_excel($objPHPExcel, $save_path, $excel_name){
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename='{$excel_name}'");
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save($save_path);
}






