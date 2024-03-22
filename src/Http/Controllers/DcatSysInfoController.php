<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Routing\Controller;

class DcatSysInfoController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Title')
            ->description('Description')
            ->body(Admin::view('zwping.dcat_sys_info::index'));
    }
}