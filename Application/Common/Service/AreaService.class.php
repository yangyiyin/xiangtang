<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class AreaService extends BaseService{



    public function get_tree() {
        $provinceM = D('provinces');
        $cityM = D('cities');
        $areaM = D('areas');
        $provinces = $provinceM->select();
        $cities = $cityM->select();
        $areas = $areaM->select();
        $province_map = result_to_map($provinces, 'provinceid');
        $city_map = result_to_complex_map($cities, 'provinceid');
        $city_cid_map = result_to_map($cities, 'cityid');
        $area_map = result_to_complex_map($areas, 'cityid');
        $tree = [];
        foreach ($provinces as $_province) {
            $tree['provinces'][] = $_province['province'];
        }

        foreach ($city_map as $pid => $_city) {
            if (isset($province_map[$pid])) {
                foreach ($_city as $__city) {
                    $tree['city'][$province_map[$pid]['province']][] = $__city['city'];
                }

            }
        }

        foreach ($area_map as $cid => $_area) {
            if (isset($city_cid_map[$cid])) {
                if (isset($province_map[$city_cid_map[$cid]['provinceid']])) {
                    foreach ($_area as $__area) {
                        $tree['area'][$province_map[$city_cid_map[$cid]['provinceid']]['province'] . $city_cid_map[$cid]['city']][] = $__area['area'];
                    }
                }

            }
        }
        return $tree;

    }


}