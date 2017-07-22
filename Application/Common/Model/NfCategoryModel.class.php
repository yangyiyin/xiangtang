<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfCategoryModel extends Model {
    const STATUS_NORAML = 1;
    const STATUS_DELETE = 99;

    protected $_validate = array(
        array('name', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH)
    );

    public function get_all() {//最多3级
        $where = [];
        $where[] = ['status'=>['eq', 1]];
        $where[] = ['id'=>['not in', C('SERVER_CIDS')]];
        return $this->where($where)->select();
    }

    public function get_server_cats() {//最多3级
        $where = [];
        $where[] = ['status'=>['eq', 1]];
        $where[] = ['id'=>['in', C('SERVER_CIDS')]];
        return $this->where($where)->select();
    }

    public function add_by_data($data) {
        $data['status'] = isset($data['status']) ? $data['status'] : self::STATUS_NORAML;
        return $this->add($data);
    }

    public function get_by_id($id) {
        return $this->where('id = ' . $id)->find();
    }

    public function get_by_pid($pid) {
        return $this->where('parent_id = ' . $pid)->select();
    }

    public function get_by_pids($pids) {
        return $this->where('parent_id in ( ' . join(',', $pids) . ')')->select();
    }

    public function update_by_id($id, $data) {
        return $this->where('id = ' . $id)->save($data);
    }

    public function del_by_id($id) {
        return $this->where('id = ' . $id)->delete();
    }

    public function del_by_ids($ids) {
        return $this->delete(join(',', $ids));
    }
}