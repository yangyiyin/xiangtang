<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class IndexIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ItemService = Service\ItemService::get_instance();
    }

    public function excute() {

        $result = new \stdClass();
        $ItemBlockService = \Common\Service\ItemBlockService::get_instance();

        //分类
        $CategoryService = \Common\Service\CategoryService::get_instance();
        $tree = $CategoryService->get_all_tree();
        $cats = $this->make_tree($tree, '1', '');
        $result->list[] = ['items' => $cats, 'title'=>'商城', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'cats'];

        //促销商品
        //获取促销商品iids
        $where_block = [];
        $where_block['type'] = \Common\Model\NfItemBlockModel::TYPE_PROMOTION;
        list($item_blocks, $count) = $ItemBlockService->get_by_where($where_block);
        if ($item_blocks) {
            $where = [];
            $where['is_real'] = 1;
            $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
            $where['id'] = ['in', result_to_array($item_blocks, 'iid')];
            list($data, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
            $result->list[] = ['items' => $this->convert_data($data), 'title'=>'促销商品', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'mall'];
        }

        //推荐商品
        $where_block = [];
        $where_block['type'] = \Common\Model\NfItemBlockModel::TYPE_RECOMMEND;
        list($item_blocks, $count) = $ItemBlockService->get_by_where($where_block);
        if ($item_blocks) {
            $where = [];
            $where['is_real'] = 1;
            $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
            $where['id'] = ['in', result_to_array($item_blocks, 'iid')];
            list($data, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
            $result->list[] = ['items' => $this->convert_data($data), 'title' => '推荐商品', 'icon' => item_img('/Uploads/Picture/12.png'), 'type' => 'mall'];
        }

        //农家乐
        $where = [];
        $where['is_real'] = 0;
        $where['cid'] = 19;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
        $result->list[] = ['items' => $this->convert_data($data), 'title'=>'农家乐', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'farm_happy'];
        //旅游
        $where = [];
        $where['is_real'] = 0;
        $where['cid'] = 2;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
        $result->list[] = ['items' => $this->convert_data($data), 'title'=>'旅游', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'travel'];

        //婚庆
        $result->list[] = ['items' => [], 'title'=>'婚庆', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'wedding'];

        //农资产品
        $result->list[] = ['items' => [], 'title'=>'农资产品', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'farm_goods'];

        //生活服务
        $result->list[] = ['items' => [], 'title'=>'生活服务', 'icon'=>item_img('/Uploads/Picture/12.png'),'type'=>'needs'];

        return result_json(TRUE, '', $result);
    }

    private function convert_data($data) {
        $list = [];
        if ($data) {

            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉
                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_normal_price'];
                }
                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                $_item['sold_num'] = (int) $_item['sold_num'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,sold_num');
            }

        }
        return $list;
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