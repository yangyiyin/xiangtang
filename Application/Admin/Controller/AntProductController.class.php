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

    public function set_sku_prop_vids() {
        $NfSkuProperty = D('NfSkuProperty');
//        $NfProperty = D('NfProperty');
        $NfPropertyValue = D('NfPropertyValue');

        $all = $NfSkuProperty->where(['id'=>1907])->select();
//        $props = $NfProperty->select();
        $prop_values = $NfPropertyValue->select();
        $prop_values_map = result_to_complex_map($prop_values, 'property_id');

        foreach ($all as $item) {
            if (isset($prop_values_map[$item['property_id']])) {
                foreach ($prop_values_map[$item['property_id']] as $value) {
                    if ($item['property_value_name'] == $value['name']) {
                        //$NfSkuProperty->update_by_id($item['id'],['property_value_id'=>$value['id']]);
                        echo $value['id'];
                        break;
                    }
                }
            }
        }
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

        $where['is_real'] = 1;

        //获取加盟商的uids
        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
            $where['uid'] = UID;//加盟商,只筛选自己的产品
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

    public function unreal() {
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

        $where['is_real'] = 0;

        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
            $where['uid'] = UID;//加盟商,只筛选自己的产品
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
        $provider_id = $cate_id = $brand_id = 0;

        if ($id = I('get.id')) {
            $product = $this->ProductService->get_info_by_id($id);
            if ($product) {
                $cate_id = $product['cid'];
                $provider_id = $product['provider_id'];
                $brand_id = $product['brand_id'];
                $this->assign('product',$product);
            } else {
                $this->error('没有找到对应的产品信息~');
            }

            //获取所有sku sku属性
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $skus = $ProductSkuService->get_by_pids([$id]);
            $sku_ids = result_to_array($skus);
            $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
            $sku_properties = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_properties_str = join(',',result_to_array($sku_properties, 'property_value_id'));
            $sku_properties_map = result_to_complex_map($sku_properties, 'sku_id');
            foreach ($skus as &$sku) {
                if (isset($sku_properties_map[$sku['id']])) {
                    $props_arr = [];
                    $prop_vals = '';
                    foreach ($sku_properties_map[$sku['id']] as $prop) {
                        $props_arr[] = '['.$prop['property_value_name'].']';
                        $prop_vals .= $prop['property_id'] . '_' . $prop['property_name'] . '_' . $prop['property_value_id'] . '_' . $prop['property_value_name'] . '|+|';
                    }
                    $sku['props'] = join('', $props_arr);
                    $sku['prop_vals'] = $prop_vals;
                }
            }

            $this->assign('skus',$skus);
            $this->assign('sku_properties_str',$sku_properties_str);

        } else {
            $this->assign('skus','');
        }
        $providerService = \Common\Service\ProviderService::get_instance();
        $providers = $providerService->get_all_provider_option($provider_id);
        $categoryService = \Common\Service\CategoryService::get_instance();
        $catetree = $categoryService->get_all_tree_option($cate_id);

        $BrandService = \Common\Service\BrandService::get_instance();
        $brand_options = $BrandService->get_all_options($brand_id);
        $this->assign('brand_options', $brand_options);

        $this->assign('product_no',$product_no);
        $this->assign('providers',$providers);
        $this->assign('catetree',$catetree);
        $this->display();

    }


    public function add_unreal() {
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

            //获取所有sku sku属性
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $skus = $ProductSkuService->get_by_pids([$id]);
            $sku_ids = result_to_array($skus);
            $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
            $sku_properties = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_properties_str = join(',',result_to_array($sku_properties, 'property_value_id'));
            $sku_properties_map = result_to_complex_map($sku_properties, 'sku_id');
            foreach ($skus as &$sku) {
                if (isset($sku_properties_map[$sku['id']])) {
                    $props_arr = [];
                    $prop_vals = '';
                    foreach ($sku_properties_map[$sku['id']] as $prop) {
                        $props_arr[] = '['.$prop['property_value_name'].']';
                        $prop_vals .= $prop['property_id'] . '_' . $prop['property_name'] . '_' . $prop['property_value_id'] . '_' . $prop['property_value_name'] . '|+|';
                    }
                    $sku['props'] = join('', $props_arr);
                    $sku['prop_vals'] = $prop_vals;
                }
            }

            $this->assign('skus',$skus);
            $this->assign('sku_properties_str',$sku_properties_str);

        } else {
            $this->assign('skus','');
        }
        $providerService = \Common\Service\ProviderService::get_instance();
        $providers = $providerService->get_all_provider_option($provider_id);
        $categoryService = \Common\Service\CategoryService::get_instance();
        $catetree = $categoryService->get_server_cats_tree_option($cate_id);
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
            $data['min_normal_price'] = intval(min($data['normal_prices']) * 100);
            $data['min_dealer_price'] = intval(min($data['dealer_prices']) * 100);
            //获取加盟商的uids
            $MemberService = \Common\Service\MemberService::get_instance();
            $franchisee_uids = $MemberService->get_franchisee_uids();
            if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
                $data['uid'] = UID;
            } else {
                $data['uid'] = 1;//1为平台
            }
            if ($id) {
                $ret = $this->ProductService->update_by_id($id, $data);
                if ($ret->success) {
                    //删除
                    $productSkuService = \Common\Service\ProductSkuService::get_instance();
                    $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
                    $skus = $productSkuService->get_by_pids([$id]);
                    //$productSkuService->del_by_pid($id);
                    $sku_ids = result_to_array($skus);

                    $del_sku_ids = array_diff($sku_ids, $data['ids']);
                    if ($del_sku_ids) {
                        $productSkuService->del_by_ids($del_sku_ids);
                    }

                    $SkuPropertyService->del_by_sku_ids($sku_ids);

                    $this->set_skus($data, $id, $sku_ids);

                    //修改item的显示价
                    $ItemService = \Common\Service\ItemService::get_instance();
                    $items = $ItemService->get_by_pids([$id]);
                    $items_map = result_to_complex_map($items, 'pid');
                    if (isset($items_map[$id]) && $items_map[$id]) {
                        foreach ($items_map[$id] as $item) {
                            $ItemService->update_by_id($item['id'], ['min_normal_price'=>$data['min_normal_price'], 'min_dealer_price'=>$data['min_dealer_price']]);
                        }
                    }

                    action_user_log('修改产品信息');
                    $this->success('修改成功！','javascript:self.location=document.referrer;');

                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ProductService->add_one($data);
                if ($ret->success) {
                    $this->set_skus($data, $ret->data);
                    action_user_log('添加产品');
                    $this->success('添加成功！','javascript:self.location=document.referrer;');
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

    private function set_skus($data, $producy_id, $sku_ids = '') {
        //新增sku
        $productSkuService = \Common\Service\ProductSkuService::get_instance();
        $data_sku = [];
        $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
        for($i=0; $i<count($data['stocks']); $i++) {
            if ($sku_ids && in_array($data['ids'][$i], $sku_ids)) {
                //更新
                $data_sku = ['price' => ceil($data['normal_prices'][$i] * 100), 'dealer_price' => ceil($data['dealer_prices'][$i] * 100), 'num'=>$data['stocks'][$i],'code'=>$data['codes'][$i]];
                $ret_sku = $productSkuService->update_by_id($data['ids'][$i], $data_sku);
//                if (!$ret_sku->success) {
//                    $this->error($ret_sku->message);
//                }
                $sku_id = $data['ids'][$i];
            } else {
                //新增
                $data_sku = ['pid'=>$producy_id, 'price' => ceil($data['normal_prices'][$i] * 100), 'dealer_price' => ceil($data['dealer_prices'][$i] * 100), 'num'=>$data['stocks'][$i],'code'=>$data['codes'][$i]];
                $ret_sku = $productSkuService->add_one($data_sku);
                if (!$ret_sku->success) {
                    $this->error($ret_sku->message);
                }

                //新增sku属性
                $sku_id = $ret_sku->data;

            }

            $prop_vals = explode('|+|', $data['prop_vals'][$i]);
            foreach ($prop_vals as $vals) {
                if ($vals) {
                    list($pid, $p_name, $vid, $v_name) = explode('_', $vals);
                    $data_values = [
                        'sku_id' => $sku_id,
                        'property_id' => $pid,
                        'property_name' => $p_name,
                        'property_value_id' => $vid,
                        'property_value_name' => $v_name,
                    ];

                    $SkuPropertyService->add_one($data_values);
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

        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();

        $is_franchisee = ($franchisee_uids && in_array(UID, $franchisee_uids))? TRUE : FALSE;


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
            $data['code'] = $product['code'];
            $data['uid'] = $product['uid'];
            $data['title'] = $product['title'];
            $data['cid'] = $product['cid'];
            $data['brand_id'] = $product['brand_id'];
            $data['img'] = $product['img'];
            $data['desc'] = $product['desc'];
            $data['price'] = $product['price'];
            $data['unit_desc'] = $product['unit_desc'];
            $data['is_real'] = $product['is_real'];
            $data['min_normal_price'] = $product['min_normal_price'];
            $data['min_dealer_price'] = $product['min_dealer_price'];
            if ($is_franchisee) {
                $data['status'] = \Common\Model\NfItemModel::STATUS_READY;
            }
            $ret = $itemService->add_one($data);

            if (!$ret->success) {
                $this->error($ret->message);
            }

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
                $data['code'] = $product['code'];
                $data['uid'] = $product['uid'];
                $data['title'] = $product['title'];
                $data['cid'] = $product['cid'];
                $data['img'] = $product['img'];
                $data['desc'] = $product['desc'];
                $data['price'] = $product['price'];
                $data['unit_desc'] = $product['unit_desc'];
                $data['is_real'] = $product['is_real'];
                $data['create_time'] = current_date();
                $data['min_normal_price'] = $product['min_normal_price'];
                $data['min_dealer_price'] = $product['min_dealer_price'];
                if ($is_franchisee) {
                    $data['status'] = \Common\Model\NfItemModel::STATUS_READY;
                }
                $insert_data[] = $data;
            }
            $ret = $itemService->add_batch($insert_data);
            if (!$ret->success) {
                $this->error($ret->message);
            }

        } else {
            $this->error('id没有');
        }
        action_user_log('创建商品item');

        $this->success('创建成功！');
    }

}