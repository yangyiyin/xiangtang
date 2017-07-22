<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class CategoryService extends BaseService{
    public function get_all_tree() {
        $NfCategory = D('NfCategory');
        $cat_all = $NfCategory->get_all();
        return $this->make_tree($cat_all);
    }

    public function get_server_tree() {
        $NfCategory = D('NfCategory');
        $cat_all = $NfCategory->get_server_cats();
        return $this->make_tree($cat_all);
    }

    private function make_tree($cats) {
        if (!$cats) return [];
        $tree1 = $tree2 = [];
        foreach ($cats as $_cat) {
            $tree[$_cat['id']]['content'] = $_cat;
            if ($_cat['parent_id']) { // 1级类目
                $tree[$_cat['parent_id']]['child'][$_cat['id']] = $_cat;
            }
        }

        $tree = $this->_make_tree($tree);
        return $tree;
    }

    private function _make_tree($tree) {
        $del_id = [];
        foreach ($tree as $_key => $_cat) {
            if (isset($_cat['child']) && $_cat['child']) {
                foreach ($_cat['child'] as $key => $__cat) {
                    if (isset($tree[$key])) {
                        $_cat['child'][$key] = &$tree[$__cat['id']];
                        $del_id[] = $key;
                    }
                }
            }
            $tree[$_key] = $_cat;
        }
        foreach ($del_id as $key) {
            unset($tree[$key]);
        }
        return $tree;
    }


    public function add_one($data) {
        if (!$data['name']) {
            return false;
        }
        $NfCategory = D('NfCategory');
        return $NfCategory->add_by_data($data);
    }

    public function get_info_by_id($id) {
        $NfCategory = D('NfCategory');
        return $NfCategory->get_by_id($id);
    }

    public function update_info_by_id($id, $data) {
        $NfCategory = D('NfCategory');
        return $NfCategory->update_by_id($id, $data);
    }

    public function del_by_id($id, $is_del_child = TRUE) {
        $NfCategory = D('NfCategory');
        if (!$is_del_child) {
            return $NfCategory->del_by_id($id);
        } else {
            //采用循环删除的方式,最多删除3级
            $info = $this->get_info_by_id($id);
            $NfCategory->del_by_id($id);
            $level2 = $NfCategory->get_by_pid($id);
            $ids2 = [];
            if ($level2) {
                foreach ($level2 as $_cat) {
                    $ids2[] = $_cat['id'];
                }
                $NfCategory->del_by_ids($ids2);
                $level3 = $NfCategory->get_by_pids($ids2);
                $ids3 = [];
                if ($level3) {
                    foreach ($level3 as $_cat) {
                        $ids3[] = $_cat['id'];
                    }
                    $NfCategory->del_by_ids($ids3);
                }
            }
            return TRUE;
        }

    }

    public function get_all_tree_option($id = '') {
        $all_tree = $this->get_all_tree();
        $options = '';
        //取3级
        foreach ($all_tree as $_cat) {
            if ($id && $id == $_cat['content']['id']) {
                $selected = ' selected="selected" ';
            } else {
                $selected = '';
            }
            $options .= '<option value="' . $_cat['content']['id']  . '"' .$selected. '>'. $_cat['content']['name'] .'</option>';

            if (isset($_cat['child']) && $_cat['child']) {
                foreach ($_cat['child'] as $__cat) {
                    if ($id && $id == $__cat['content']['id']) {
                        $selected = ' selected ="selected" ';
                    } else {
                        $selected = '';
                    }
                    $options .= '<option value="' . $__cat['content']['id']  . '"' .$selected. '>---'. $__cat['content']['name'] .'</option>';

                    if (isset($__cat['child']) && $__cat['child']) {
                        foreach ($__cat['child'] as $___cat) {
                            if ($id && $id == $___cat['content']['id']) {
                                $selected = ' selected ="selected" ';
                            } else {
                                $selected = '';
                            }
                            $options .= '<option value="' . $___cat['content']['id'] . '"' .$selected. '>------'. $___cat['content']['name'] .'</option>';
                        }
                    }

                }
            }
        }
        return $options;
    }

    public function get_server_cats_tree_option($id = '') {
        $all_tree = $this->get_server_tree();
        $options = '';
        //取3级
        foreach ($all_tree as $_cat) {
            if ($id && $id == $_cat['content']['id']) {
                $selected = ' selected="selected" ';
            } else {
                $selected = '';
            }
            $options .= '<option value="' . $_cat['content']['id']  . '"' .$selected. '>'. $_cat['content']['name'] .'</option>';

            if (isset($_cat['child']) && $_cat['child']) {
                foreach ($_cat['child'] as $__cat) {
                    if ($id && $id == $__cat['content']['id']) {
                        $selected = ' selected ="selected" ';
                    } else {
                        $selected = '';
                    }
                    $options .= '<option value="' . $__cat['content']['id']  . '"' .$selected. '>---'. $__cat['content']['name'] .'</option>';

                    if (isset($__cat['child']) && $__cat['child']) {
                        foreach ($__cat['child'] as $___cat) {
                            if ($id && $id == $___cat['content']['id']) {
                                $selected = ' selected ="selected" ';
                            } else {
                                $selected = '';
                            }
                            $options .= '<option value="' . $___cat['content']['id'] . '"' .$selected. '>------'. $___cat['content']['name'] .'</option>';
                        }
                    }

                }
            }
        }
        return $options;
    }

    public function get_by_ids($ids) {
        if (!check_num_ids($ids)) {
            return false;
        }
        $NfCategory = D('NfCategory');
        return $NfCategory->where('id in (' . join(',', $ids) . ')')->select();
    }

    public function get_cids_by_cid($cid) {//最多3级
        $NfCategory = D('NfCategory');
        $cate_2 = $NfCategory->where('parent_id = ' . $cid)->select();
        $cids = [$cid];
        if (!$cate_2) {
            return $cids;
        }
        $cate_2_ids = result_to_array($cate_2);
        $cids = array_merge($cids, $cate_2_ids);

        $cate_3 = $NfCategory->where('parent_id in (' . join(',', $cate_2_ids) . ')')->select();
        if (!$cate_3) {
            return $cids;
        }
        $cate_3_ids = result_to_array($cate_3);
        $cids = array_merge($cids, $cate_3_ids);

        return $cids;
    }
}