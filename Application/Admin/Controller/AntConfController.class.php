<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 12:18:12
 */
namespace Admin\Controller;

class AntConfController extends AdminController {
    protected $ConfService;
    protected function _initialize() {
        parent::_initialize();
        $this->ConfService = \Common\Service\ConfService::get_instance();
    }

    public function index() {

        if (IS_POST) {
            $data = I('post.');

            foreach ($data as $key => $value) {
                $conf = $this->ConfService->get_by_key_name($key);
                if ($conf) {
                    $ret = $this->ConfService->update_by_key_name($key, ['content'=>$value]);
                } else {
                    $ret = $this->ConfService->add_one(['key_name'=>$key, 'content'=>$value]);
                }


            }
            $this->success('保存成功!');
        }
        $conf = $this->ConfService->get_by_key_name('franchisee_fee_rate');
        $franchisee_fee_rate = isset($conf['content']) ? $conf['content'] : '';
        $conf = $this->ConfService->get_by_key_name('volunteer_pay_sum');
        $volunteer_pay_sum = isset($conf['content']) ? $conf['content'] : '';

        $this->assign('franchisee_fee_rate',$franchisee_fee_rate);
        $this->assign('volunteer_pay_sum',$volunteer_pay_sum);
        $this->display();
    }




}