<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfProductModel extends Model {
    const STATUS_NORAML = 1;
    const STATUS_DELETE = 99;

    public static $status_map = [1=>'正常',99=>'已下架'];

    protected $_validate = array(
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
    );

    public function get_by_where($where, $order = 'id desc', $limit = '1,20') {
        $where['status'] = isset($where['status']) ? $where['status'] : self::STATUS_NORAML;
        return $this->where($where)->order($order)->limit($limit)->select();
    }

    public function add_by_data($data) {
        $data['status'] = isset($data['status']) ? $data['status'] : self::STATUS_NORAML;
        $data['create_time'] = isset($data['create_time']) ? $data['create_time'] : current_date();
        $data['modify_time'] = isset($data['modify_time']) ? $data['modify_time'] : current_date();
        if (!$this->create($data)) {
            return false;
        }
        return $this->add();
    }

    public function get_by_id($id) {
        return $this->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {
        if (!$id) {
            $this->error = '没有设置id';
            return false;
        }

        $data['modify_time'] = isset($data['modify_time']) ? $data['modify_time'] : current_date();

        if (!$this->create($data)) {
            return false;
        }
        return $this->where('id = ' . $id)->save($data);
    }

    public function del_by_id($id) {
        if (!$id) {
            return false;
        }
        return $this->where('id = ' . $id)->delete();
    }

    public function del_by_ids($ids) {
        if (!check_num_ids($ids)) {
            return false;
        }
        return $this->delete(join(',', $ids));
    }
}