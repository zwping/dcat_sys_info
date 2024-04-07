<?php

use Zwping\DcatAdmin\SysInfo\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('sys_info/{type}', Controllers\DcatSysInfoController::class.'@index');
