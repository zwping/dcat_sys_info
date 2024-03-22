<?php

namespace Zwping\DcatAdmin\SysInfo;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class DcatSysInfoServiceProvider extends ServiceProvider
{
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	public function register()
	{
		//
	}

	public function init()
	{
		parent::init();

		//
		
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
