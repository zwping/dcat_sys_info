<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;

/** 项目依赖
 * @date: 2024-03-28 @zwping
 */
class DependenciesInfo extends Card {

    public function __construct($title = '<i class="fa fa-list-alt"></i> 项目依赖', $icon = null){
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
        $dependencies = json_decode(file_get_contents(base_path('composer.json')), true)['require'];
        $labelColor = Admin::color()->success();
        foreach($dependencies as $name => $version) {
            $deps["<a href='https://packagist.org/packages/$name' target='_bank'>$name</a>"] = 
                "<span class='label' style='background:$labelColor'>$version</span>";
        }
        return Widgets\Table::make([], $deps, "table-striped thead-dark table-hover table-sm");
    }


}