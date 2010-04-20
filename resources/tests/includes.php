<?php
set_include_path(
    dirname(__FILE__) . '/../../library' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../../zend/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../modules/utils/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../modules/core/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../modules/file/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../modules/search/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../modules/services/sources' .  PATH_SEPARATOR . 
    get_include_path());