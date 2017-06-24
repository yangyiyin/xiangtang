<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntItemController extends AdminController {
    private $ItemService;
    protected function _initialize() {
        parent::_initialize();
        $this->ItemService = \Common\Service\ItemService::get_instance();
    }

    public function index() {
        $categoryService = \Common\Service\CategoryService::get_instance();
        $catetree = $categoryService->get_all_tree_option(I('get.cid'));
        $this->assign('catetree', $catetree);

        $ServicesService = \Common\Service\ServicesService::get_instance();
        list($services, $count) = $ServicesService->get_by_where_all([]);
        $this->assign('services', $services);
        $where = [];
        if (I('get.cid')) {
            $where['cid'] = ['EQ', I('get.cid')];
        }
        if (I('get.status')) {
            $where['status'] = ['EQ', I('get.status')];
        }
        if (I('get.create_begin')) {
            $where['create_time'][] = ['EGT', I('get.create_begin')];
        }
        if (I('get.create_end')) {
            $where['create_time'][] = ['ELT', I('get.create_end')];
        }

        if (I('get.title')) {
            $where['title'] = ['LIKE', '%'.I('get.title').'%'];
        }


        $page = I('get.p', 1);
        list($data, $count) = $this->ItemService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ItemService::$page_size);
        if($total>\Common\Service\ItemService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    public function update_prices() {
        if (IS_POST) {
            $id = I('post.id');
            $data = I('post.');
            if ($id) {
                $ItemServicePricesServicee = \Common\Service\ItemServicePricesService::get_instance();
                $ret = $ItemServicePricesServicee->update_by_iid_prices($id, $data);

                if ($ret->success) {
                    action_user_log('修改商品价格');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $this->error('没有id');
            }

        }
    }

    private function convert_data(&$data) {
        if ($data) {

            $categorySkuService = \Common\Service\CategoryService::get_instance();
            $cids = result_to_array($data, 'cid');
            $cates = $categorySkuService->get_by_ids($cids);
            $cates_map = result_to_map($cates);

            $itemServicePricesService = \Common\Service\ItemServicePricesService::get_instance();
            $iids = result_to_array($data);
            $prices = $itemServicePricesService->get_by_iids($iids);
            $prices_map = result_to_complex_map($prices, 'iid');

            foreach ($data as $key => $_product) {
                if (isset($cates_map[$_product['cid']])) {
                    $data[$key]['cate'] = $cates_map[$_product['cid']];
                }

                if (isset($prices_map[$_product['id']])) {

                    $data[$key]['prices'] = $itemServicePricesService->get_prices_map($prices_map[$_product['id']]);
                }

                $data[$key]['status_text'] = $this->ItemService->get_status_txt($_product['status']);
            }
        }
    }

    public function off_shelf() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->ItemService->off_shelf([$id]);
        }

        if ($ids) {
            $ret = $this->ItemService->off_shelf($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('下架商品');
        $this->success('下架成功！');
    }

    public function on_shelf() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->ItemService->on_shelf([$id]);
        }

        if ($ids) {
            $ret = $this->ItemService->on_shelf($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('上架商品');
        $this->success('上架成功！');
    }


    public function add() {
        $id = I('get.id');
        $categoryService = \Common\Service\CategoryService::get_instance();
        if (!$id) {
            $catetree = $categoryService->get_all_tree_option('');
            $this->assign('catetree', $catetree);
            $this->display();
            exit;
        }
        $item = $this->ItemService->get_info_by_id($id);
        if (!$item) {
            $this->error('没有找到商品信息');
        }
        $catetree = $categoryService->get_all_tree_option($item['cid']);
        $this->assign('catetree', $catetree);
        $this->assign('item', $item);
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            $data['pid'] = 0;
            if ($id) {
                $ret = $this->ItemService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改商品信息');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {

                $ret = $this->ItemService->add_one($data);

                if ($ret->success) {
                    action_user_log('添加商品信息');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }
        }
        $this->error('非法请求');
    }


    public function modify_sort() {
        $id = I('post.id');
        $sort = I('post.sort');
        if ($id) {
            $data = [];
            $data['sort'] = (int) $sort;
            $ret = $this->ItemService->update_by_id($id, $data);
            if ($ret->success) {
                action_user_log('修改商品排序,id:'.$id);
                $this->success('修改排序成功！', U('index'));
            } else {
                $this->error($ret->message);
            }
        } else {

            $this->error('没有id~');
        }
    }


}