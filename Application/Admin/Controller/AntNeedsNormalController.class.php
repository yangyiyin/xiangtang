<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 12:18:12
 */
namespace Admin\Controller;

class AntNeedsNormalController extends AntNeedsController {
    protected $types_all = [];


    protected function _initialize() {
        parent::_initialize();
        $NeedsTypesService = \Common\Service\NeedsTypesService::get_instance();
        $types_all = $NeedsTypesService->get_normal_types();
        $this->types_all = result_to_array($types_all);
    }
}