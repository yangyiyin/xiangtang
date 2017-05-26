<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntStockInOutLogController extends AdminController {
    private $StockInOutLogService;
    protected function _initialize() {
        parent::_initialize();
        $this->StockInOutLogService = \Common\Service\StockInOutLogService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.type')) {
            $where['type'] = ['EQ', I('get.type')];
        }
        if (I('get.create_begin')) {
            $where['create_time'][] = ['EGT', I('get.create_begin')];
        }
        if (I('get.create_end')) {
            $where['create_time'][] = ['ELT', I('get.create_end')];
        }

        if (I('get.pid')) {
            $where['pid'] = ['EQ', I('get.pid')];
        }
        if (I('get.product_no')) {
            $where['product_no'] = ['EQ', I('get.product_no')];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->StockInOutLogService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\StockInOutLogService::$page_size);
        if($total>\Common\Service\StockInOutLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    private function convert_data(&$data) {
        if ($data) {
            foreach ($data as $key => $_product) {

                $data[$key]['type_desc'] = $this->StockInOutLogService->get_type_txt($_product['type']);
            }
        }
    }

}