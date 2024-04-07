<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;
use Zwping\DcatAdmin\SysInfo\Http\OSInformation;

/** 挂载的文件信息
 * @date: 2024-03-28 @zwping
 */
class MountsInfo extends Card {

    public function __construct(
        private OSInformation $oSInformation, 
        $title = '<i class="fa fa-cloud"></i> 文件系统', 
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
        return <<<Table
        <table class="table default-table thead-dark table-hover table-sm">
            <thead>
                <tr>
                    <th>设备</th>
                    <th>挂载点</th>
                    <th>文件系统</th>
                    <th>总容量</th>
                    <th>已用</th>
                    <th>可用</th>
                    <th>已用百分比</th>
                </tr>
            </thead>
            <tbody>
            {$this->getMountsTrs()}
            {$this->getTotalTr()}
            </tbody>
        </table>
Table;
    }

    private function getMountsTrs() {
        $mounts = $this->oSInformation->getOsInfo()['mounts']['mounts'];
        foreach($mounts as $mount) {
            $trs[] = <<<Tr
            <tr>
                <td>{$mount['device']}</td>
                <td style="max-width: 180px;">{$mount['mount']}</td>
                <td>{$mount['type']}</td>
                <td>{$mount['size']}</td>
                <td>{$mount['used']}</td>
                <td>{$mount['free']}</td>
                <td>{$this->getProgressBarHtml($mount['used_percent'])}</td>
            </tr>
Tr;
        }
        return implode('', $trs??[]);
    }

    private function getTotalTr() {
        $total = $this->oSInformation->getOsInfo()['mounts']['total'];
        return <<<Tr
        <tr style="background-color: rgba(34,41,47,.05);">
            <td colspan="3">Totals:</td>
            <td>{$total['size']}</td>
            <td>{$total['used']}</td>
            <td>{$total['free']}</td>
            <td>{$this->getProgressBarHtml($total   ['used_percent'])}</td>
        </tr>
Tr;
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