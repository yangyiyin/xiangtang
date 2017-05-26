<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntProductController extends AdminController {
    private $ProductService;
    protected function _initialize() {
        parent::_initialize();
        $this->ProductService = \Common\Service\ProductService::get_instance();
    }

    public function index() {
        $categoryService = \Common\Service\CategoryService::get_instance();
        $catetree = $categoryService->get_all_tree_option(I('get.cid'));
        $this->assign('catetree', $catetree);

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
        list($data, $count) = $this->ProductService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ProductService::$page_size);
        if($total>\Common\Service\ProductService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    public function add() {
        $product_no = time();//默认
        $provider_id = $cate_id = 0;
        if ($id = I('get.id')) {
            $product = $this->ProductService->get_info_by_id($id);
            if ($product) {
                $cate_id = $product['cid'];
                $provider_id = $product['provider_id'];
                $this->assign('product',$product);
            } else {
                $this->error('没有找到对应的产品信息~');
            }
        }
        $providerService = \Common\Service\ProviderService::get_instance();
        $providers = $providerService->get_all_provider_option($provider_id);
        $categoryService = \Common\Service\CategoryService::get_instance();
        $catetree = $categoryService->get_all_tree_option($cate_id);
        $this->assign('product_no',$product_no);
        $this->assign('providers',$providers);
        $this->assign('catetree',$catetree);
        $this->display();

    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            $data['price'] = intval($data['price'] * 100);
            if ($id) {
                $ret = $this->ProductService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改产品信息');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ProductService->add_one($data);
                if ($ret->success) {
                    //新增库存
                    $productSkuService = \Common\Service\ProductSkuService::get_instance();
                    $data_sku = [];
                    $data_sku['pid'] = $ret->data;
                    $data_sku['price'] = $data['price'];
                    $data_sku['num'] = $data['sku_num'];
                    $ret_sku = $productSkuService->add_one($data_sku);
                    if (!$ret_sku->success) {
                        $this->error($ret_sku->message);
                    }
                    //新增批号库存
                    $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
                    $data_no_sku = [];
                    $data_no_sku['pid'] = $ret->data;
                    $data_no_sku['product_no'] = $data['no'];
                    $data_no_sku['num'] = $data['sku_num'];
                    $data_no_sku['create_time'] = current_date();
                    $ret_no_sku = $productNoSkuService->add_one($data_no_sku);
                    if (!$ret_no_sku->success) {
                        $this->error($ret_no_sku->message);
                    }
                    action_user_log('添加产品');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

    private function convert_data(&$data) {
        if ($data) {

            $productSkuService = \Common\Service\ProductSkuService::get_instance();
            $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
            $categorySkuService = \Common\Service\CategoryService::get_instance();
            $ItemService = \Common\Service\ItemService::get_instance();
            $pids = result_to_array($data);
            $cids = result_to_array($data, 'cid');
            $skus = $productSkuService->get_by_pids($pids);
            $sku_pid_map = result_to_map($skus, 'pid');
            $cates = $categorySkuService->get_by_ids($cids);
            $cates_map = result_to_map($cates);
            $items = $ItemService->get_by_pids($pids);
            $item_pid_map = result_to_map($items, 'pid');
            $no_skus = $productNoSkuService->get_by_pids($pids);
            $no_skus_map = result_to_complex_map($no_skus, 'pid');
            foreach ($data as $key => $_product) {
                if (isset($sku_pid_map[$_product['id']])) {
                    $data[$key]['sku'] = $sku_pid_map[$_product['id']];
                }

                if (isset($cates_map[$_product['cid']])) {
                    $data[$key]['cate'] = $cates_map[$_product['cid']];
                }

                if (isset($no_skus_map[$_product['id']])) {
                    $data[$key]['no_sku'] = $no_skus_map[$_product['id']];
                }

                if (isset($item_pid_map[$_product['id']])) {
                    $data[$key]['has_item'] = TRUE;
                } else {
                    $data[$key]['has_item'] = FALSE;
                }

                $data[$key]['status_text'] = $this->ProductService->get_status_txt($_product['status']);
            }
        }
    }

    public function off_shelf() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->ProductService->off_shelf([$id]);
        }

        if ($ids) {
            $ret = $this->ProductService->off_shelf($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }

        $this->success('下架成功！');
    }

    public function on_shelf() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->ProductService->on_shelf([$id]);
        }

        if ($ids) {
            $ret = $this->ProductService->on_shelf($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        $this->success('上架成功！');
    }

    public function stock_add() {
        $no = I('post.no');
        $modify_num = I('post.modify_num');

        $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
        $info = $productNoSkuService->get_info_by_no($no);
        if (!$info) {
            $this->error('没有找到对应的批号');
        }
        $ret = $productNoSkuService->add_stock_no($no, $modify_num);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        //增加总库存
        $productSkuService = \Common\Service\ProductSkuService::get_instance();
        $ret = $productSkuService->add_stock_by_pid($info['pid'], $modify_num);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        //增加出入库记录
        $stockInOutLogService = \Common\Service\StockInOutLogService::get_instance();
        $stockInOutLogService->add_in(['pid'=>$info['pid'], 'product_no'=>$no, 'info'=>'pid:'. $info['pid'] .',批号:'. $no.',入库数量:'.$modify_num]);
        $this->success('加库成功！');
    }
    public function stock_out() {

        $no = I('post.no');
        $modify_num = I('post.modify_num');
        $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
        $info = $productNoSkuService->get_info_by_no($no);
        if (!$info) {
            $this->error('没有找到对应的批号');
        }
        $ret = $productNoSkuService->minus_stock_no($no, $modify_num);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        //减总库存
        $productSkuService = \Common\Service\ProductSkuService::get_instance();
        $ret = $productSkuService->minus_stock_by_pid($info['pid'], $modify_num);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        //增加出入库记录
        $stockInOutLogService = \Common\Service\StockInOutLogService::get_instance();
        $stockInOutLogService->add_out(['pid'=>$info['pid'], 'product_no'=>$no, 'info'=>'pid:'. $info['pid'] .',批号:'. $no.',出库数量:'.$modify_num]);
        $this->success('出库成功！');
    }
    public function stock_in() {
        $data_no_sku = [];
        $data_no_sku['pid'] = I('post.pid');
        $data_no_sku['product_no'] = I('post.no');
        $data_no_sku['num'] = I('post.num', 0);
        $data_no_sku['create_time'] = current_date();
        $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
        $ret = $productNoSkuService->add_one($data_no_sku);
        if (!$ret->success) {
            $this->error($ret->message);
        }

        //增加总库存
        $productSkuService = \Common\Service\ProductSkuService::get_instance();
        $ret = $productSkuService->add_stock_by_pid($data_no_sku['pid'], $data_no_sku['num']);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        //增加出入库记录
        $stockInOutLogService = \Common\Service\StockInOutLogService::get_instance();
        $stockInOutLogService->add_in(['pid'=>$data_no_sku['pid'], 'product_no'=>$data_no_sku['product_no'], 'info'=>'pid:'. $data_no_sku['pid'] .',批号:'. $data_no_sku['product_no'].',入库数量:'.$data_no_sku['num']]);

        $this->success('入库成功！');
    }

    public function create_item() {
        $ids = I('post.ids');
        $id = I('get.id');
        $itemService = \Common\Service\ItemService::get_instance();
        if ($id) {
            if ($itemService->get_by_pids([$id])) {
                $this->error('已经创建了商品了');
            }
            $product = $this->ProductService->get_info_by_id($id);
            if (!$product) {
                $this->error('没有找到相应的产品信息');
            }
            $data = [];
            $data['pid'] = $product['id'];
            $data['title'] = $product['title'];
            $data['cid'] = $product['cid'];
            $data['img'] = $product['img'];
            $data['desc'] = $product['desc'];
            $data['price'] = $product['price'];
            $data['unit_desc'] = $product['unit_desc'];

            $ret = $itemService->add_one($data);

            if (!$ret->success) {
                $this->error($ret->message);
            }
            //插入个price
            $ItemUsertypePricesService = \Common\Service\ItemUsertypePricesService::get_instance();
            $ret = $ItemUsertypePricesService->add_by_iid_price($ret->data, $data['price']);

        } elseif ($ids) {//批量
            if ($exists_items = $itemService->get_by_pids($ids)) {
                $exists_pids = result_to_array($exists_items, 'pid');
                $ids = array_diff($ids, $exists_pids);
                if (!$ids) {
                    $this->error('商品都已经创建过了~');
                }
            }
            $products = $this->ProductService->get_by_ids($ids);
            if (!$products) {
                $this->error('没有找到相应的产品信息');
            }
            $insert_data = [];
            foreach ($products as $product) {
                $data = [];
                $data['pid'] = $product['id'];
                $data['title'] = $product['title'];
                $data['cid'] = $product['cid'];
                $data['img'] = $product['img'];
                $data['desc'] = $product['desc'];
                $data['price'] = $product['price'];
                $data['unit_desc'] = $product['unit_desc'];
                $data['create_time'] = current_date();
                $insert_data[] = $data;
            }
            $ret = $itemService->add_batch($insert_data);
            if (!$ret->success) {
                $this->error($ret->message);
            }

            $ItemUsertypePricesService = \Common\Service\ItemUsertypePricesService::get_instance();
            $ret = $ItemUsertypePricesService->add_by_pids($ids);
        } else {
            $this->error('id没有');
        }
        action_user_log('创建商品item');

        $this->success('创建成功！');
    }

}