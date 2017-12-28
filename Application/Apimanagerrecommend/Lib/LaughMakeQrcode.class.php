<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMakeQrcode extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $url = $this->post_data['url'];

        $file_name = 'pages/qrcode/'.md5($url).'.png';
        $link = 'https://www.88plus.net/public/'.$file_name;
        if (file_exists($file_name)) {
            return result_json(TRUE, '发布成功', $link);
        }

        require APP_PATH . '/Common/Lib/phpqrcode/qrlib.php';

        $value = $url; //二维码内容

        $errorCorrectionLevel = 'L';//容错级别

        $matrixPointSize = 8;//生成图片大小

        $tmp_png = time() . 'qrcode.png';

        $logo = 'logo.jpeg';//准备好的logo图片

        //生成二维码图片

        \QRcode::png($value, $tmp_png, $errorCorrectionLevel, $matrixPointSize, 2);


        $QR = $tmp_png;//已经生成的原始二维码图



        if ($logo !== FALSE) {

            $QR = imagecreatefromstring(file_get_contents($QR));

            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);//二维码图片宽度

            $QR_height = imagesy($QR);//二维码图片高度

            $logo_width = imagesx($logo);//logo图片宽度

            $logo_height = imagesy($logo);//logo图片高度

            $logo_qr_width = $QR_width / 5;

            $scale = $logo_width/$logo_qr_width;

            $logo_qr_height = $logo_height/$scale;

            $from_width = ($QR_width - $logo_qr_width) / 2;

            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,

                $logo_qr_height, $logo_width, $logo_height);

        }

        imagepng($QR, $file_name);
        unlink($tmp_png);
        return result_json(TRUE, '发布成功',$link);
    }


}