<?php
/* User:lyt123; Date:2016/11/8; QQ:1067081452 */
namespace Common\Model;

class AdminBaseModel extends CURDModel
{
    public function checkLogin($data)
    {
        $result = $this->getData(array('account' => $data['account']));
        if ($result['password'] === encrypt_password($data['password'])) {
            return $result;
        }
        return false;
    }

    public function updatePassword($id, $data)
    {
        $password = current($this->getData(array('id' => $id), array('password')));
        if ($password === encrypt_password($data['pre_password'])) {
            if ($this->update($id, array('password' => encrypt_password($data['new_password'])))) {
                return true;
            }
            return false;
        }
    }

    public function sendEmail($email)
    {
        $security_code = rand(10000, 100000);
        if(send_email($email, '继续教育学院教务管理', '您的验证码是'.$security_code)){
            session('forget_password.security_code', $security_code);
            session('forget_password.send_time', NOW_TIME);
            return true;
        }
        return false;
    }
}