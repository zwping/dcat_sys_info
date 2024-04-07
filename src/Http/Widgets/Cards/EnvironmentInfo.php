<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;
use Illuminate\Support\Arr;

/** 项目环境
 * @date: 2024-03-28 @zwping
 */
class EnvironmentInfo extends Card {

    public function __construct($title = '<i class="fa fa-file-code-o"></i> Environment', $icon = null){
        parent::__construct($title, $icon);
    }

    protected function init() {
        parent::init();

        Admin::style(<<<CSS
        .table td { height: auto; }
CSS);
        $this->content($this->getTable());
    }

    private function getTable() {
        $debugStateLabel = config('app.debug') ? '是' : '否';
        $debugStateColor = config('app.debug') ? Admin::color()->danger() : Admin::color()->success();
        $envs = [
            ['name' => 'Name',              'value' => config('app.name')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'Debug',             'value' => "<span class='label' style='background:$debugStateColor;'>$debugStateLabel</span>"],
            ['name' => '',                  'value' => ''],
            ['name' => 'PHP version',       'value' => 'PHP/'.PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'Dcat version',      'value' => Admin::VERSION],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            // ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],
            ['name' => '',                  'value' => ''],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'URL',               'value' => config('app.url')],
            ['name' => 'AssetURL',          'value' => config('app.asset_url')],
            ['name' => 'ROOT',              'value' => base_path()],
        ];
        return Widgets\Table::make([], $envs, "table-striped thead-dark table-hover table-sm");
    }


}