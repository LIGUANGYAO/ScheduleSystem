<?php
/*
 * author:lyt123; create_date:2016/10/27; QQ:1067081452
*/

namespace Common\Controller;

use Think\Controller;

class BaseController extends Controller
{
    /**
     * Des :filter and receive post_data
     * Auth:lyt123
     */
    protected function reqPost(array $require_data = null, array $unnecessary_data = null)
    {
        if (!IS_POST) {
            $this->ajaxReturn(ajax_error('request method error', 405));
        }
        $data = array();
        if ($require_data) {
            foreach ($require_data as $key => $value) {

                $field = is_int($key) ? $value : $key;

                $_k = I('post.' . $field, null); //过滤xss攻击

                if (!$_k)
                    $this->ajaxReturn(ajax_error('lack_parameter : ' . $field));

                $data[$value] = $_k;
            }
        }
        if ($unnecessary_data) {
            foreach ($unnecessary_data as $key => $value) {

                $field = is_int($key) ? $value : $key;

                $_k = I('post.' . $field, null);

                if (!is_null($_k)) $data[$value] = $_k;    //有post该字段则加入
            }
        }
        return $data;
    }

    /**
     * Des :filter and receive get_data
     * Auth:lyt123
     */
    protected function reqGet(array $require_data = null, array $unnecessary_data = null)
    {
        if (!IS_GET) {
            $this->ajaxReturn(ajax_error('request method error', 405));
        }
        $data = array();
        if ($require_data) {
            foreach ($require_data as $key => $value) {

                $field = is_int($key) ? $value : $key;

                $_k = I('get.' . $field, null);//过滤xss攻击

                if (!$_k)
                    $this->ajaxReturn(ajax_error('lack_parameter : ' . $field));

                $data[$value] = $_k;
            }
        }
        if ($unnecessary_data) {
            foreach ($unnecessary_data as $key => $value) {

                $field = is_int($key) ? $value : $key;

                $_k = I('get.' . $field, null);

                if (!is_null($_k))
                    $data[$value] = $_k;      //有get该字段则加入
            }
        }
        return $data;
    }

    /**
     * Des :检查教师登录
     * Auth:lyt123
     */
    protected function reqTeacher()
    {
        if (session("?teacher_id") || session("?admin_academy_id") || session("?admin_id")) {
            return $this;
        }
//        redirect('http://localhost/ss_front/Home/teacherLogin.html');exit();
        $this->ajaxReturn(ajax_error('未登录'));
    }

    /**
     * Des :检查学院教务员登录
     * Auth:lyt123
     */
    protected function reqAdminAcademy()
    {
        if (session("?admin_academy_id") || session("?admin_id")) {
            return $this;
        }
        $this->ajaxReturn(ajax_error('未登录'));
    }

    /**
     * Des :检查管理员登录
     * Auth:lyt123
     */
    protected function reqAdmin()
    {
        if (session("?admin_id")) {
            return $this;
        }
        $this->ajaxReturn(ajax_error('未登录'));
    }

    /**
     * Des :多操作检查结果，操作失败回滚
     * Auth:lyt123
     */
    protected function checkResult($result, $model, $error_msg = null)
    {
        if($error_msg){
            $result['msg'] = $error_msg;
        }

        if ($result['status'] == 422) {
            $model->rollback();
            $this->ajaxReturn($result);
        }
    }

    /**
     * Des :根据管理员的级别，返回相应model
     * Auth:lyt123
     */
    public function getModel($admin_level)
    {
        $model = '';
        switch ($admin_level) {
            case 1 :
                $model = D('Teacher');
                break;
            case 2 :
                $model = D('AdminAcademy');
                break;
            case 3:
                $model = D('Admin');
                break;
            default:
                $this->ajaxReturn(ajax_error('admin_level wrong'));
                break;
        }

        return $model;
    }

    /**
     * Des :session标识登录成功
     * Auth:lyt123
     */
    public function saveSession($admin_level, $admin_data)
    {
        switch ($admin_level) {
            case 1:
                session('teacher_id', $admin_data['id']);
                break;
            case 2:
                session('admin_academy_id', $admin_data['id']);
                session('academy_id', $admin_data['academy_id']);
                break;
            case 3:
                session('admin_id', $admin_data['id']);
                break;
        }
    }

    /**
     * Des :检查是否登录，并返回admin_id
     * Auth:lyt123
     */
    public function reqLogin($admin_level)
    {
        switch ($admin_level) {
            case 3:
                if ($this->reqAdmin())
                    return session('admin_id');
                break;
            case 2:
                if ($this->reqAdminAcademy())
                    return session('admin_academy_id');
                break;
            case 1:
                if ($this->reqTeacher())
                    return session('teacher_id');
                break;
        }
        return false;
    }

    /**
     * Des :检查excel表已读取到最后一行，通过判断单元格是否为空
     * Auth:lyt123
     */
    public function checkLoadToLastLine($cell, $model)
    {
        if(!$cell){
            $model->commit();
            $this->ajaxReturn(ajax_ok('已读取到最后一行'));
        }
    }

    public function transferTypeToFileName($type)
    {
        switch($type){
            case 1:
                $json_file = 'select_book_arrange_status.json';
                break;
            case 2:
                $json_file = 'exam_status.json';
                break;
            default:
                $this->ajaxReturn(ajax_error('type wrong'));
        }
        return $json_file;
    }

    /**
     * Des :统一管理上传excel文件失败的情况
     * Auth:lyt123
     */
    public function loadExcel($file_name, $file_path, $unset_end = 0)
    {
        $excel_result = loadExcel($file_name, $file_path, $unset_end);
        if(!is_array($excel_result)){
            $this->ajaxReturn(ajax_error($excel_result));
        }
        return $excel_result;
    }
}