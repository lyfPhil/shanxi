<?php
namespace app\common\controller;

use think\Controller;
class CliBase extends Controller{
    public function __construct()
    {
        if (!(php_sapi_name() === 'cli'))
        {
            die("Must run in cli mode");
        }
    }
}

