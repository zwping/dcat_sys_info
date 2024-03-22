<?php

use Zwping\DcatAdmin\SysInfo\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('dcat-sys-info', Controllers\DcatSysInfoController::class.'@index');