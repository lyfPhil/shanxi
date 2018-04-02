<?php
namespace app\tasks\controller;

use app\common\controller\CliBase;

class Index extends CliBase
{
    public function index()
    {
        $res = ['jobs index'];
        return json($res);
    }
    public function start()
    {
        $res = ['jobs index start'];
        return json($res);
    }
}
