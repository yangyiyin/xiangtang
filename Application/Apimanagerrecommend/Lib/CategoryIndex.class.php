<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class CategoryIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $CategoryService;
    public function init() {
        $this->CategoryService = Service\CategoryService::get_instance();
    }

    public function excute() {
        $cid = I('get.cid');
        $level = I('get.level');
        $tree = $this->CategoryService->get_all_tree();
        $result = $this->make_tree($tree, $level, $cid);
        return result_json(TRUE, '', ['categories' => $result, 'level' => intval($level)]);
    }

    private function make_tree($tree_old, $level, $cid) {

        $tree = [];
        foreach ($tree_old as $_tree1) {
            $tmp_tree = [];

            $tmp_tree['cid'] = (int) $_tree1['content']['id'];
            $tmp_tree['name'] = $_tree1['content']['name'];
            $tmp_tree['img'] = item_img(get_cover($_tree1['content']['icon'], 'path'));
            $tmp_tree['has_child'] = isset($_tree1['child']);
            if ($tmp_tree['has_child'] && (($level && $level > 1) || !$level)) {
                foreach ($_tree1['child'] as $_child) {
                    $tmp_tree_2 = [];

                    $tmp_tree_2['cid'] = (int) $_child['content']['id'];
                    $tmp_tree_2['name'] = $_child['content']['name'];
                    $tmp_tree_2['img'] = item_img(get_cover($_child['content']['icon'], 'path'));
                    $tmp_tree_2['has_child'] = isset($_child['child']);
                    if ($tmp_tree_2['has_child'] && (($level && $level > 2) || !$level)) {
                        foreach ($_child['child'] as $__child) {
                            $tmp_tree_3 = [];

                            $tmp_tree_3['cid'] = (int) $__child['content']['id'];
                            $tmp_tree_3['name'] = $__child['content']['name'];
                            $tmp_tree_3['img'] = item_img(get_cover($__child['content']['icon'], 'path'));
                            $tmp_tree_3['has_child'] = FALSE;
                            if ($level && $level < 3) {
                                continue;
                            }
                            if ($cid && $cid == $tmp_tree_3['cid']) {
                                if ($level && $level != 3) {
                                    $tree = [];
                                } else {
                                    $tree[] = $tmp_tree_3;
                                }

                                break 3;
                            } else {
                                $tmp_tree_2['child'][] = $tmp_tree_3;
                            }
                        }
                    }
                    if ($level && $level < 2) {
                        continue;
                    }
                    if ($cid && $cid == $tmp_tree_2['cid']) {
                        if ($level && $level != 2) {
                            $tree = isset($tmp_tree_2['child']) ? $tmp_tree_2['child'] : [];
                        } else {
                            $tree[] = $tmp_tree_2;
                        }

                        break 2;
                    } else {
                        $tmp_tree['child'][] = $tmp_tree_2;
                    }
                }
            }
            if ($level && $level < 1) {
                continue;
            }
            if ($cid && $cid == $tmp_tree['cid']) {

                if ($level && $level != 1) {
                    $tree = isset($tmp_tree['child']) ? $tmp_tree['child'] : [];
                } else {
                    $tree[] = $tmp_tree;
                }
                break;
            } else {
                $tree[] = $tmp_tree;
            }

            if ($cid) {
                $tree = [];
            }
        }
        return $tree;
    }



}