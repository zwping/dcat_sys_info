<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;
use Zwping\DcatAdmin\SysInfo\Http\OSInformation;

/** 硬盘信息
 * @date: 2024-03-28 @zwping
 */
class HDInfo extends Card {

    public function __construct(
        private OSInformation $oSInformation, 
            $title = '<i class="fa fa-hdd-o"></i> 硬盘', 
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
        <table class="table default-table table-striped thead-dark table-hover table-sm">
            <thead>
                <tr>
                    <th>地址</th>
                    <th>制作商</th>
                    <th>名称</th>
                    <th>读取</th>
                    <th>写入</th>
                    <th>总容量</th>
                </tr>
            </thead>
            <tbody>
            {$this->getHDTrs()}
            </tbody>
        </table>
Table;
    }

    private function getHDTrs() {
        $hds = $this->oSInformation->getOsInfo()['hd'];
        foreach($hds as $hd) {
            $trs[] = <<<Tr
            <tr>
                <td>{$hd['device']}</td>
                <td>{$hd['vendor']}</td>
                <td>{$hd['name']}</td>
                <td>{$hd['reads']}</td>
                <td>{$hd['writes']}</td>
                <td>{$hd['size']}</td>
            </tr>
            {$this->getPartitions($hd['partitions'])}
Tr;
        }
        return implode('', $trs??[]);
    }

    // 硬盘分区
    private function getPartitions(?array $partitions) {
        if (empty($partitions)) {
            return '';
        }
        $partitions = '└ '. implode('<br/>└ ', $partitions);
        return <<<Tr
        <tr>
            <td colspan="6" style="font-size: 80%;">$partitions</td>
        </tr>
Tr;
    }

}