<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;
use Zwping\DcatAdmin\SysInfo\Http\OSInformation;

/** 系统信息
 * @date: 2024-03-28 @zwping
 */
class SystemInfo extends Card {

    public function __construct(
        private OSInformation $oSInformation, 
        $title = '<i class="fa fa-microchip"></i> 系统', 
        $icon = null
    ){
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
        $osinfo = $this->oSInformation->getOsInfo();
        $rows = [
            ['name' => '服务器名称',    'value' => $osinfo['hostname']],
            ['name' => '服务器型号',    'value' => $osinfo['model']],
            ['name' => '操作系统',      'value' => $osinfo['os']],
            ['name' => '发行版本',      'value' => $osinfo['distro']],
            ['name' => '内核版本',      'value' => $osinfo['kernel']],
            // ['name' => 'Web服务',        'value' => $osinfo['web_service']],
            // ['name' => 'php版本',        'value' => $osinfo['php_version']],
            ['name' => '在线时间',      'value' => str_replace('.', '<br/>', $osinfo['uptime'])],
            ['name' => '系统架构',      'value' => $osinfo['cpu_arch']],
            ['name' => 'CPU使用率',     'value' => $osinfo['cpu_usage']],
            ['name' => 'CPU核心数',     'value' => count($osinfo['cpus'])],
            ['name' => 'CPUs',          'value' => implode('<br/>', $osinfo['cpus'])],
        ];
        return Widgets\Table::make([], $rows, "table-striped thead-dark table-hover table-sm");
    }


}