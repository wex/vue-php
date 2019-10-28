<?php

use Phi\VuePHP\Layout;
use Phi\VuePHP\Page;
use Phi\VuePHP\Page\SCSS;
use Phi\VuePHP\Page\CSS;
use Phi\VuePHP\Page\JS;

require_once 'vendor/autoload.php';

$index = new Page('index.html', 'index');
$index->with(function() {
    $this->add( new CSS('app.css') );
    $this->add( new SCSS('app.scss') );
    $this->add( new JS('app.js') );
});

$layout = new Layout('public', 'test');
$layout->add( $index );
$layout->build();