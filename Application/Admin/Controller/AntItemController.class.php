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
    protected $tpl = '';
    protected $attr = \Common\Model\NfProductModel::ATTR_NORMAL;
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
        if ($cid = I('get.cid')) {

            //获取cid下的所有cids
            $CategoryService = \Common\Service\CategoryService::get_instance();
            $cids = $CategoryService->get_cids_by_cid($cid);

            $where['cid'] = ['IN', $cids];

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

        if (I('get.platform')) {
            $where['platform'] = ['eq', I('get.platform')];
        }

        if (I('get.is_self') == 1) {
            $where['uid'] = ['eq', 1];
        } elseif(I('get.is_self') == 2) {
            $where['uid'] = ['neq', 1];
        }

        $where['attr'] = $this->attr;
        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
            $where['uid'] = UID;//加盟商,只筛选自己的产品
            $this->is_franchisee = 1;
        }

        $where['is_real'] = 1;
        $page = I('get.p', 1);
        list($data, $count) = $this->ItemService->get_by_where($where, 'status asc, id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ItemService::$page_size);
        if($total>\Common\Service\ItemService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();
        $this->assign('page_html', $page_html);

        //如果是限时抢购页面
        if (I('activity') == 'timelimit') {
            $this->tpl = 'index_activity';
            $aid = I('aid');
            $TimelimitActivityService = \Common\Service\TimelimitActivityService::get_instance();
            $info = $TimelimitActivityService->get_info_by_id($aid);
            if (!$info) {
                $this->error('没有对应的信息');
            }
            $this->assign('activity_info', $info);

            //获取sku信息
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $skus = $ProductSkuService->get_skus_by_items($data);
            $this->convert_data_skus($data, $skus);
        }

        $this->assign('list', $data);
        //var_dump($data[0]);die();
        $this->display($this->tpl);
    }

    private function convert_data_skus(&$data, $skus) {
        $skus_pid_map = result_to_complex_map($skus, 'pid');


        if ($data) {

            foreach ($data as $key => $_item) {
                if (isset($skus_pid_map[$_item['pid']])) {
                    $data[$key]['skus'] = $skus_pid_map[$_item['pid']];
                }
            }
        }

        //print_r($data[0]);die();
    }
    public function unreal() {
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

        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
            $where['uid'] = UID;//加盟商,只筛选自己的产品
        }

        $where['is_real'] = 0;
        $page = I('get.p', 1);
        list($data, $count) = $this->ItemService->get_by_where($where, 'status asc, id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ItemService::$page_size);
        if($total>\Common\Service\ItemService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);


        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids && in_array(UID, $franchisee_uids)) {
            $is_manager = 0;
        } else {
            $is_manager = 1;
        }
        $this->assign('is_manager', $is_manager);
        $this->display();
    }

    public function index_love() {
        $this->tpl = 'index';
        $this->attr = \Common\Model\NfProductModel::ATTR_LOVE;
        $this->assign('is_love',true);
        $this->index();
    }
    public function add_love() {
        $this->tpl = 'add';
        $this->attr = \Common\Model\NfProductModel::ATTR_LOVE;
        $this->assign('is_love',true);
        $this->add();
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

            $ItemBlockService = \Common\Service\ItemBlockService::get_instance();
            $item_promotion = $ItemBlockService->get_by_iids_type($iids, \Common\Model\NfItemBlockModel::TYPE_PROMOTION);
            $item_recommend = $ItemBlockService->get_by_iids_type($iids, \Common\Model\NfItemBlockModel::TYPE_RECOMMEND);
            $item_promotion_iids = result_to_array($item_promotion, 'iid');
            $item_recommend_iids = result_to_array($item_recommend, 'iid');

            $uids = result_to_array($data, 'uid');
            $MemberService = \Common\Service\MemberService::get_instance();
            $franchisees = $MemberService->get_franchisees($uids);
            $franchisees_map = result_to_map($franchisees, 'uid');

            //获取活动信息
            $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
            $itemtimelimit = $ItemTimelimitActivityService->get_by_iids($iids);
            $itemtimelimit_map = result_to_complex_map($itemtimelimit, 'iid');
            foreach ($itemtimelimit_map as $key => $_itemtimelimit) {
                $itemtimelimit_map[$key] = result_to_map($_itemtimelimit, 'sku_id');
                $itemtimelimit_map[$key]['start_time'] = $_itemtimelimit[0]['start_time'];
                $itemtimelimit_map[$key]['end_time'] = $_itemtimelimit[0]['end_time'];

            }
            foreach ($data as $key => $_product) {
                if (isset($cates_map[$_product['cid']])) {
                    $data[$key]['cate'] = $cates_map[$_product['cid']];
                }

                if (isset($prices_map[$_product['id']])) {

                    $data[$key]['prices'] = $itemServicePricesService->get_prices_map($prices_map[$_product['id']]);
                }

                $data[$key]['status_text'] = $this->ItemService->get_status_txt($_product['status']);

                if (in_array($_product['id'], $item_promotion_iids)) {
                    $data[$key]['is_promotion'] = TRUE;
                }
                if (in_array($_product['id'], $item_recommend_iids)) {
                    $data[$key]['is_recommend'] = TRUE;
                }

                if (!$this->is_franchisee) {
                    $data[$key]['franchisee_info'] = isset($franchisees_map[$_product['uid']]) ? $franchisees_map[$_product['uid']] : [];
                } else {
                    $data[$key]['franchisee_info'] = [];
                }

                if (isset($itemtimelimit_map[$_product['id']])) {

                    $data[$key]['timelimit_activity_info'] = $itemtimelimit_map[$_product['id']];
                }
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

    public function submit() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->ItemService->submit([$id]);
        }

        if ($ids) {
            $ret = $this->ItemService->submit($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('提交审核商品');
        $this->success('提交审核成功！');
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
        $this->assign('attr', $this->attr);
        $this->display($this->tpl);
    }

    public function add_unreal() {
        $id = I('get.id');
        $categoryService = \Common\Service\CategoryService::get_instance();
        if (!$id) {
            $catetree = $categoryService->get_server_cats_tree_option('');
            $this->assign('catetree', $catetree);
            $this->display();
            exit;
        }
        $item = $this->ItemService->get_info_by_id($id);
        if (!$item) {
            $this->error('没有找到商品信息');
        }
        $catetree = $categoryService->get_server_cats_tree_option($item['cid']);
        $this->assign('catetree', $catetree);
        $this->assign('item', $item);
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            $data['platform'] = array_sum($data['platform']);
            if ($id) {
                $ret = $this->ItemService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改商品信息');
                    $this->success('修改成功！','javascript:self.location=document.referrer;');
                } else {
                    $this->error($ret->message);
                }
            } else {

                $ret = $this->ItemService->add_one($data);

                if ($ret->success) {
                    action_user_log('添加商品信息');
                    $this->success('添加成功！','javascript:self.location=document.referrer;');
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


    public function set_block() {
        $ids = I('post.ids');
        $id = I('get.id');
        $type = I('get.type');
        $ItemBlockService = \Common\Service\ItemBlockService::get_instance();

        if ($id) {
            $ret = $ItemBlockService->set_block([$id], $type);
        }

        if ($ids) {
            $ret = $ItemBlockService->set_block($ids, $type);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('批量设置活动商品');
        $this->success('设置成功！');
    }

    public function cancel_block() {
        $ids = I('post.ids');
        $id = I('get.id');
        $type = I('get.type');
        $ItemBlockService = \Common\Service\ItemBlockService::get_instance();

        if ($id) {
            $ret = $ItemBlockService->cancel_block([$id], $type);
        }

        if ($ids) {
            $ret = $ItemBlockService->cancel_block($ids, $type);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('批量取消活动商品');
        $this->success('取消成功！');
    }

    public function set_timelimit_activity() {
        $iids = I('post.iids') ? explode(',', I('post.iids')) : [];
        $price = intval(strval(I('post.price') * 100));
        $start_time = I('post.start_time');
        $end_time = I('post.end_time');

        $skus = I('post.skus');

        if (!$iids || !$skus || !$start_time || !$end_time) {
            $this->error('参数错误');
        }

        $skus_arr = explode('|', $skus);
        $skus_map = [];
        foreach ($skus_arr as $sku) {
            $_sku_arr = explode('-', $sku);
            if(!$_sku_arr[1]) {
                $this->error('请填写完整价格');
            }
            $skus_map[$_sku_arr[0]] = $_sku_arr[1];
        }
        $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
        //获得老的
        $exits = $ItemTimelimitActivityService->get_by_iids($iids);

        if ($exits) {
            //更新老的
            //$ids = result_to_array($exits);
            $data_update = [];
            $data_update['start_time'] = $start_time;
            $data_update['end_time'] = $end_time;
            foreach ($exits as $_value) {
                if (isset($skus_map[$_value['sku_id']])) {
                    $data_update['price'] = intval(strval($skus_map[$_value['sku_id']] * 100));
                    $ItemTimelimitActivityService->update_by_id($_value['id'],$data_update);
                }
            }
            $exit_iids = result_to_array($exits, 'iid');
            $new_iids = array_diff($iids, $exit_iids);

        } else {
            $new_iids = $iids;
        }

        if ($new_iids) {
          //  $data_add = [];
            foreach ($new_iids as $_iid) {
                $temp = [];
                $temp['iid'] = $_iid;
                $temp['start_time'] = $start_time;
                $temp['end_time'] = $end_time;
//                $temp['price'] = $price;
                foreach ($skus_map as $sku_id => $price) {
                    $temp['price'] = intval(strval($price * 100));
                    $temp['sku_id'] = $sku_id;
                    $ItemTimelimitActivityService->add_one($temp);
                }
             //  $data_add[] = $temp;
            }
           // $ItemTimelimitActivityService->add_batch($data_add);
        }
        action_user_log('批量设置限时抢购商品');
        $this->success('设置成功！');
    }

    public function cancel_timelimit_activity() {
        $ids = I('post.ids');
        $id = I('get.id');

        $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();

        if ($id) {
            $ret = $ItemTimelimitActivityService->cancel_timelimit_activity([$id]);
        }

        if ($ids) {
            $ret = $ItemTimelimitActivityService->cancel_timelimit_activity($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('批量取消限时抢购活动商品');
        $this->success('取消成功！');
    }




}