<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsOutCashIndexInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
//    private $OutCashService;
    public function init() {
//        $this->OutCashService = Service\OutCashService::get_instance();
    }

    public function excute() {
        $data = '<p style="color: red">重要提醒：<br/>
                    你的购物金额少于你的佣金的时候，可以使用佣金在线支付。<br/>
                    如：购物金额380元，你的帐户佣金余额超过380元，支付的时候可以选择佣金支付。<br/></p>
                    <p>提现须知<br/>
                    1.佣金满100元才可以申请提现。<br/>
                    2.每次提现申请，扣除提现金额1%手续费，如提现100元，实际到帐为99元。<br/>
                    3.提现申请通过后，5个工作日内打款到提现帐户。<br/>
                    4.联系电话：05782206607</p>';
        return result_json(TRUE, '',$data);
    }

}