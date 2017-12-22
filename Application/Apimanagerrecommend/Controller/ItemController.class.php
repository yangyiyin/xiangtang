<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class ItemController extends BaseController {
    public function _empty() {
        if (I('get.user_session')) {
            $this->excute_api('Api\Lib\ItemList');
        } else {
            $this->excute_api('Api\Lib\ItemListDefault');
        }

    }

    public function detail() {
        if (I('get.user_session')) {
            $this->excute_api('Api\Lib\ItemDetail');
        } else {
            $this->excute_api('Api\Lib\ItemDetailDefault');
        }
    }

    public function unreal_list() {
        if (I('get.user_session')) {
            $this->excute_api('Api\Lib\ItemUnrealList');
        } else {
            $this->excute_api('Api\Lib\ItemUnrealListDefault');
        }

    }

    public function unreal_detail() {
        if (I('get.user_session')) {
            $this->excute_api('Api\Lib\ItemUnrealDetail');
        } else {
            $this->excute_api('Api\Lib\ItemUnrealDetailDefault');
        }
    }

    public function skus() {
        $this->excute_api('Api\Lib\ItemSkus');
    }

    public function brands() {
        $this->excute_api('Api\Lib\ItemBrands');
    }
}