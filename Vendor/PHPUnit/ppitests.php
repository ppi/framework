<?php

set_include_path(implode(PATH_SEPARATOR, array(
       dirname(__FILE__).'/dbunit/',
       dirname(__FILE__).'/php-code-coverage/',
       dirname(__FILE__).'/php-file-iterator/',
       dirname(__FILE__).'/php-text-template/',
       dirname(__FILE__).'/php-timer/',
       dirname(__FILE__).'/php-token-stream/',
       dirname(__FILE__).'/phpunit-mock-objects/',
       dirname(__FILE__).'/phpunit-selenium/',
       get_include_path(),
)));

include 'phpunit.php';
