<?php
/* User:lyt123; Date:2016/11/13; QQ:1067081452 */
namespace Admin\Controller;

use Common\Controller\BaseController;

class AdminBaseController extends BaseController
{
    public function forgetPasswordSendEmail()
    {
        $data = $this->reqPost(array('account', 'admin_level'));

        $model = $this->getModel($data['admin_level']);

        if (!$model->getData($data))
            $this->ajaxReturn(ajax_error('账号不存在, 请联系管理员'));

        $email = current($model->getData($data, array('email')));
        $result = $model->sendEmail($email);

        if ($result) {
            session('forget_password.account', $data['account']);
            $this->ajaxReturn(ajax_ok(array('email' => $email), '验证码已发送到您的邮箱'));
        }

        $this->ajaxReturn(ajax_error('发送邮件失败'));
    }

    public function forgetPasswordCheckCode()
    {
        $data = $this->reqPost(array('security_code'));
        if (session('forget_password.security_code') == $data['security_code'] && (NOW_TIME - session('forget_password.send_time') < 300)) {
            session('forget_password.check_code_success', true);
            $this->ajaxReturn(ajax_ok(array(), '验证码正确'));
        }
        $this->ajaxReturn(ajax_error('验证码错误或已过期'));
    }

    public function forgetPasswordNewPassword()
    {
        $data = $this->reqPost(array('password', 'admin_level'));

        $model = $this->getModel($data['admin_level']);

        if (session('?forget_password.account') && session('?forget_password.check_code_success')) {
            $data['password'] = encrypt_password($data['password']);

            if ($model->update(session('forget_password.account'), $data, 'account')) {
                session('forget_password', null);
                $this->ajaxReturn(ajax_ok(array(), '修改密码成功'));
            }

            $this->ajaxReturn(ajax_error('修改密码失败，系统错误'));
        }

        $this->ajaxReturn(ajax_error('请先进行邮箱验证'));
    }

    public function login()
    {
        $post_data = $this->reqPost(array('account', 'password', 'admin_level'));

        $model = $this->getModel($post_data['admin_level']);

        $result = $model->checkLogin($post_data);
        if ($result) {
            $this->saveSession($post_data['admin_level'], $result);

            if ($result['first_login'] == 1)
                $result['first_login'] = 1;
            else
                $result['first_login'] = 2;

            $result['academy_name'] = current(D('Academy')->getData(array('id' => $result['academy_id']), array('name')));

            unset($result['password']);
            unset($result['email']);
            $this->ajaxReturn(ajax_ok($result));
        }
        $this->ajaxReturn(ajax_error('账号或密码错误'));
    }

    public function updatePassword()
    {
        $post_data = $this->reqPost(array('pre_password', 'new_password', 'admin_level'));

        if ($id = $this->reqLogin($post_data['admin_level'])) {

            $model = $this->getModel($post_data['admin_level']);
            if ($model->updatePassword($id, $post_data))
                $this->ajaxReturn(ajax_ok(array(), '修改密码成功'));
            $this->ajaxReturn(ajax_error('修改密码失败,请检验原密码是否正确'));

        } else $this->ajaxReturn(ajax_error('未登录'));
    }

    public function fillInEmail()
    {
        $post_data = $this->reqPost(array('email', 'admin_level'));

        if ($id = $this->reqLogin($post_data['admin_level'])) {
            $model = $this->getModel($post_data['admin_level']);

            $post_data['first_login'] = 2;
            $result = $model->update($id, $post_data);
            if ($result['code'] == 200){
                $this->ajaxReturn(ajax_ok('填写邮箱成功'));
            }
            $this->ajaxReturn($result);
        } else $this->ajaxReturn(ajax_error('未登录'));

    }

    public function logout()
    {
        switch (current($this->reqGet(array('admin_level')))) {
            case 1:
                session('teacher_id', null);
                break;
            case 2:
                session('admin_academy_id', null);
                break;
            case 3:
                session('admin_id', null);
                break;
        }
        $this->ajaxReturn(ajax_ok('退出成功'));
    }
}