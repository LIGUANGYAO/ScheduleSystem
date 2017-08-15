<?php
/* User:lyt123; Date:2016/10/27; QQ:1067081452 */
namespace Common\Model;

class CURDModel extends BaseModel
{
    /**
     * Des :add one data
     * Auth:lyt123
     */
    public function addOne(array $data)
    {
        if($this->create($data)){
            if($result = $this->add())
                return ajax_ok(array('id' => $result));
        }

        $error_msg = $this->getError();

        return ajax_error($error_msg ? $error_msg : 'system error');
    }

    /**
     * Des :add multiple data
     * Auth:lyt123
     */
    public function addData($multi_data)
    {
        $this->patchValidate = true;
        if($this->create($multi_data)){
            if($this->addAll($multi_data))
                return ajax_ok();
        }

        return ajax_error($this->getError());
    }

    /**
     * Des :update data
     * Auth:lyt123
     */
    public function update($id, array $data, $key = 'id')
    {
        if($this->create($data)){
            $result = $this->where(array($key => $id))->save();
            if($result !== false){
                if($result){
                    return ajax_ok();
                }
            }
            return ajax_error('nothing update');
        }

        $error_msg = $this->getError();
        return ajax_error($error_msg ? $error_msg : 'system error');
    }

    /**
     * Des :delete one data
     * Auth:lyt123
     */
    public function destroy($id, $key = 'id')
    {
        if($this->where(array($key => $id))->delete())
            return ajax_ok();
        return ajax_error();
    }

    /**
     * Des :get data
     * Auth:lyt123
     */
    public function getData(array $where = null, array $field = null, $is_multi = false, $order = null, $page = 0, $limit = 0)
    {
        if($where) $this->where($where);

        if($field) $this->field($field);

        if($order) $this->order($order);

        if($page) $this->limit(($page - 1) * $limit, $limit);

        if($is_multi)
            return $this->select();
        return $this->find();
    }
}