<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/6/1
 * Time: 下午3:37
 */

class newModule {
    private $name = 'test';
    private $desc = 'test';
    private $service_path = '../../Application/Common/Service/';
    private $model_path = '../../Application/Common/Model/';
    private $controller_path = '../../Application/Admin/Controller/';
    public function __construct($name, $desc = ''){
        $this->name = ucfirst($name);
        $this->desc = ucfirst($desc);
    }

    public function gain_model() {
        $content = file_get_contents('model');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->model_path . 'Nf' . $this->name . 'Model.class.php', $content);
    }

    public function gain_service() {
        $content = file_get_contents('service');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->service_path . $this->name . 'Service.class.php', $content);
    }

    public function gain_controller() {
        $content = file_get_contents('controller');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___desc___', $this->desc, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->controller_path . 'Ant' . $this->name . 'Controller.class.php', $content);
    }

    public function gain($names) {
        if (isset($names['service'])) {
            $this->gain_service();


        }
        if (isset($names['model'])) {
            $this->gain_model();

        }
        if (isset($names['controller'])) {
            $this->gain_controller();

        }
        echo 'success';
        exit();
    }

    public function gain_power($names) {
        if (isset($names['service'])) {

            chmod($this->service_path . $this->name . 'Service.class.php', 777);

        }
        if (isset($names['model'])) {

            chmod($this->model_path . 'Nf' . $this->name . 'Model.class.php', 777);
        }
        if (isset($names['controller'])) {

            chmod($this->controller_path . 'Ant' . $this->name . 'Controller.class.php', 777);
        }
        echo 'success';
        exit();
    }
}


$newModule = new newModule('article', '文章');
//$newModule->gain(['model'=>1, 'controller'=>1, 'service'=>1]);
$newModule->gain_power(['model'=>1, 'controller'=>1, 'service'=>1]);