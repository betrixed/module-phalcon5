<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        // dispatcher controller name is used 
        // as the folder containing the automatic view
        // currently it is App\Controllers\Index, 
        // which is not a useful file path.
        $this->dispatcher->setControllerName('index');
    }
}
