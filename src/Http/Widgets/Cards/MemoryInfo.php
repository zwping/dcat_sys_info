<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;
use Zwping\DcatAdmin\SysInfo\Http\OSInformation;

/** 内存信息
 * @date: 2024-03-28 @zwping
 */
class MemoryInfo extends Card {

    public function __construct(
        private OSInformation $oSInformation, 
        $title = '<i class="fa fa-ticket"></i> 内存', 
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
        $memory = $this->oSInformation->getOsInfo()['memory'];
        return <<<Table
        <table class="table default-table table-striped thead-dark table-hover table-sm">
            <thead>
                <tr>
                    <th>类型</th>
                    <th>总容量</th>
                    <th>已用</th>
                    <th>可用</th>
                    <th>已用百分比</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{$memory['type']}</td>
                    <td>{$memory['total']}</td>
                    <td>{$memory['used']}</td>
                    <td>{$memory['free']}</td>
                    <td>{$this->getProgressBarHtml($memory['used_percent'])}</td>
                </tr>
                <tr>
                    <td rowspan="2" style="align-content: center;">Swap</td>
                    <td>{$memory['swap_total']}</td>
                    <td>{$memory['swap_used']}</td>
                    <td>{$memory['swap_free']}</td>
                    <td>{$this->getProgressBarHtml($memory['swap_used_percent'])}</td>
                </tr>
                {$this->getSwapChildTable()}
            </tbody>
        </table>
Table;
        $headers = ['类型', '总容量', '已用', '可用', '已用百分比', ];
        $rows = [
            [$memory['type'], $memory['total'], $memory['used'], $memory['free'], $this->getProgressBarHtml($memory['used_percent'])],
            ['Swap', $memory['swap_total'], $memory['swap_used'], $memory['swap_free'], $this->getProgressBarHtml($memory['swap_used_percent'])],
        ];
        return Widgets\Table::make($headers, $rows, "table-striped thead-dark table-hover table-sm");
    }

    private function getSwapChildTable() {
        $swaps = $this->oSInformation->getOsInfo()['memory']['swaps'];
        if (empty($swaps)) {
            return '';
        }
        foreach($swaps as $swap) {
            $trs[] = <<<Tr
            <tr>
                <td>{$swap['device']}</td>
                <td>{$swap['type']}</td>
                <td>{$swap['size']}</td>
                <td>{$swap['used']}</td>
                <td>{$swap['free']}</td>
            </tr>
Tr;
        }
        $trs = implode('', $trs);
        return <<<Table
        <tr>
            <td colspan="4" style="padding: 0px;">
                <table class="table table-sm" style="font-size: 80%;">
                    <tbody>
                        <tr>
                            <th>Device</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Used</th>
                            <th>Free</th>
                        </tr>
                        $trs
                    </tbody>
                </table>
            </td>
        </tr>
Table;
    }

    private function getProgressBarHtml(string $progress) {
        $progress = str_replace('%', '', $progress);
        return <<<HTML
        <div class="progress" style="margin-top: 2px;">
            <div class="progress-bar" role="progressbar" aria-valuenow="$progress" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width:{$progress}%;">
                $progress%
            </div>
        </div>
HTML;
    }



}