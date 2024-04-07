<?php

namespace Zwping\DcatAdmin\SysInfo;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class DcatSysInfoServiceProvider extends ServiceProvider {
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	protected $menu = [
		[
			'title'  => '系统信息',
			'uri'    => '',
			'icon'   => 'fa-server',
		],
		[
			'parent' => '系统信息',
			'title'  => 'phpinfo',
			'uri'    => '/sys_info/phpinfo',
			'icon'   => '',
		],
		[
			'parent' => '系统信息',
			'title'  => 'project',
			'uri'    => '/sys_info/project',
			'icon'   => '',
		],
		[
			'parent' => '系统信息',
			'title'  => 'OS',
			'uri'    => '/sys_info/os',
			'icon'   => 'fa-windows',
		],
		[
			'parent' => '系统信息',
			'title'  => 'env',
			'uri'    => '/sys_info/env',
			'icon'   => 'fa-file-code-o',
		],
		[
			'parent' => '系统信息',
			'title'  => 'route',
			'uri'    => '/sys_info/route',
			'icon'   => 'fa-list-ol',
		],
	];

	public function register() {
	}

	public function init() {
		parent::init();
		
	}

	// public function settingForm() {
	// 	return new Setting($this);
	// }

}
