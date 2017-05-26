<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ProviderService extends BaseService{
    public static $page_size = 20;
    public function add_one($data) {
        $NfProvider = D('NfProvider');
        if ($NfProvider->add($data)) {
            return result(TRUE, '', $NfProvider->getLastInsID());
        } else {
            return result(FALSE, $NfProvider->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfProvider = D('NfProvider');
        return $NfProvider->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfProvider = D('NfProvider');

        if ($NfProvider->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProvider->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfProvider = D('NfProvider');
        $ret = $NfProvider->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProvider->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfProvider = D('NfProvider');
        $ret = $NfProvider->where('id=' . $id)->save(['status'=>\Common\Model\NfProviderModel::STATUS_FORBID]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProvider->getError());
        }
    }

    public function add_batch($data) {
        $NfProvider = D('NfProvider');
        if ($NfProvider->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function get_all_provider_option($selected_id = '') {
        $NfProvider = D('NfProvider');
        $where = [];
        $where['status'] = ['EQ', \Common\Model\NfProviderModel::STATUS_NORAML];

        $all = $NfProvider->where($where)->select();
        $options = '';
        if ($all) {
            foreach ($all as $_provider) {
                if ($selected_id && $selected_id == $_provider['id']) {
                    $options .= '<option selected="selected" value="'.$_provider['id'].'">'.$_provider['name'].'</option>';
                } else {
                    $options .= '<option value="'.$_provider['id'].'">'.$_provider['name'].'</option>';
                }
            }
        }
        return $options;
    }

    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfProvider = D('NfProvider');
        $data = [];
        $where['status'] = isset($where['status']) ? $where['status'] : $where['status'] = ['EQ', \Common\Model\NfProviderModel::STATUS_NORAML];
        $count = $NfProvider->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfProvider->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }

}