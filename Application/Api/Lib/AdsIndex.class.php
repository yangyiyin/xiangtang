<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class AdsIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $AdService;
    public function init() {
        $this->AdService = Service\AdService::get_instance();
    }

    public function excute() {
        $ids = I('get.names');
        if (!$ids) {
            result_json(FALSE, '没有广告ids');
        }
        $ids_arr = explode(',', $ids);

        $ads = $this->AdService->get_by_names($ids_arr, $this->from);
        if (!$ads) {
            result_json(FALSE, '没有任何广告');
        }

        $data = [];
        foreach ($ads as $ad) {
            $ad['id'] = (int) $ad['id'];
            $ad['type'] = (int) $ad['type'];
            $ad['width'] = (int) $ad['width'];
            $ad['height'] = (int) $ad['height'];
            $data[$ad['name']] = convert_obj($ad, 'id,type,height,width,link=url');

            $imgs = explode(',', $ad['imgs']);
            foreach ($imgs as $img_id) {
                $data[$ad['name']]->imgs[] = item_img(get_cover($img_id, 'path'));
            }
        }

        return result_json(TRUE, '', $data);


    }

}