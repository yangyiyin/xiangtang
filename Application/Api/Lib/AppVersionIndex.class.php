<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class AppVersionIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $AdService;
    public function init() {
        $this->AppVersionService = Service\AppVersionService::get_instance();
    }

    public function excute() {

        $data = $this->AppVersionService->get_current();
        if ($data) {
            $data['apk_url'] = 'http://' . $_SERVER['HTTP_HOST'] . $data['apk_url'];
        }
        $data['force_upgrade'] = boolval(intval($data['force_upgrade'])));
        $data = convert_obj($data, 'app_name=appName,version_code=versionCode,version_name=versionName,apk_url=apkUrl,change_log=changeLog,update_tips=updateTips,force_upgrade=forceUpgrade');

        echo json_encode($data);
        exit();
    }

}