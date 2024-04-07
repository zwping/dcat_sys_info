<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Column;
use Illuminate\Routing\Controller;
use Dcat\Admin\Repositories\Repository;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Tooltip;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Zwping\DcatAdmin\SysInfo\Http\OSInformation;
use Zwping\DcatAdmin\SysInfo\Http\SystemInfo;
use Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;
use Illuminate\Support\Str;

class DcatSysInfoController extends Controller {

    public function index(Content $content, string $type) {
        return $content
            ->title($type ?? 'SysInfo')
            // ->description('Description')
            // ->body(Admin::view('zwping.dcat_sys_info::index'));
            ->body(function(Row $row) use($type) {
                switch($type) {
                    case 'phpinfo':
                        $row->column(1, '');
                        $row->column(10, new Cards\PhpInfo());
                        break;
                    case 'os':
                        $osInformation = new OSInformation();
                        $row->column(6, new Cards\SystemInfo($osInformation));
                        $row->column(6, function(Column $column) use($osInformation) {
                            $column->row(new Cards\MemoryInfo($osInformation));
                            $column->row(new Cards\HDInfo($osInformation));
                        });
                        $row->column(12, new Cards\MountsInfo($osInformation));
                        break;
                    case 'project':
                        $row->column(4, new Cards\EnvironmentInfo());
                        $row->column(4, new Cards\DependenciesInfo());
                        break;
                    case 'env':
                        $row->column(12, $this->getEnvGrid());
                        break;
                    case 'route':
                        $row->column(12, $this->getRouteGrid());
                        break;
                    default:
                        abort(404);
                }
            })
            ;
    }

    /** env 表格 */
    private function getEnvGrid() {
        return Grid::make(null, function(Grid $grid){
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disablePagination();
            $grid->number();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            // $grid->addTableClass(['table-striped']);

            // $grid->column('ignore');
            $grid->column('key')->display(function() {
                $ignore = $this->get('ignore');
                $_index = $this->get('_index');
                $key = $this->get('key');
                if ($ignore) {
                    $color = Admin::color()->gray();
                    Tooltip::make("._key_{$_index}")->background($color)->title("已注释");
                    return "<span style='color: $color;' class='_key_{$_index}'><i class='feather icon-help-circle'></i> {$key}</span>";
                } else {
                    $color = Admin::color()->font();
                    return "<span style='color: $color;'>{$key}</span>";
                }
            });
            $grid->column('value')->copyable();
            $grid->column('remarks')->display(fn($remarks) => implode('<br/>', $remarks));

            $grid->model()->setData($this->getEnvData());
        });
    }

    /** env数据 */
    private function getEnvData() {
        $content = file_get_contents(base_path('.env'));
        $lines = preg_split('/\n+/', $content);
        $pattern = '/^(?<ignore>#\s*){0,1}\s*(?<key>[^\s=]+){1}\s*=\s*(?<value>[.\S]+){0,1}\s*[#\s]*(?<remarks>.+)?$/';
        foreach($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                // dd($matches);
                $envs[] = [
                    'ignore'    => $matches['ignore'] ?? false, # key value不可用
                    'key'       => $matches['key'],
                    'value'     => $matches['value'] ?? '',
                    'remarks'   => array_filter(array_merge([$matches['remarks'] ?? ''], $remarks ?? []), fn($it) => $it),
                ];
                $remarks = []; // 支持多行注释 & 尾部注释
            } else {
                $remarks[] = trim(preg_replace('/^#/', '', trim($line)));   // 去除#及两端空格
            }
        }
        return $envs;
    }

    private const Colors = [
        'GET'    => 'green',
        'HEAD'   => 'gray',
        'POST'   => 'blue',
        'PUT'    => 'yellow',
        'DELETE' => 'red',
        'PATCH'  => 'aqua',
        'OPTIONS'=> 'light-blue',
    ];
    /** route 表格 */
    private function getRouteGrid() {
        $c = Admin::color()->danger();
        $c1 = Admin::color()->alpha('danger', 0.2);
        Admin::style(<<<CSS
        td { height: auto !important; }
        .label { font-size: 70% !important; }
        code { color: $c; background: $c1; }
CSS);
        return Grid::make($this->getRouteRepository($this->getRouteData()), function(Grid $grid){
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disablePagination();
            $grid->number();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            // $grid->addTableClass(['table-striped']);

            $grid->method()->map(function ($method) {
                $colors = static::Colors;
                return "<span class=\"label bg-{$colors[$method]}\">$method</span>";
            })->implode('&nbsp;');

            $grid->uri()->display(function ($uri) {
                return preg_replace('/\{.+?\}/', '<code>$0</code>', $uri);
            })->sortable();

            $grid->name();

            $grid->action()->display(function ($uri) {
                return preg_replace('/@.+/', '<code>$0</code>', $uri);
            });
            $grid->middleware()->badge(Admin::color()->warning());

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('uri');
                $filter->like('action');
            });
        });
    }

    /** route 数据仓库 */
    private function getRouteRepository(Collection $routes) {
        return new class($routes) extends Repository {
            public function __construct(
                private Collection $routes,
            ) {

            }

            public function get(Grid\Model $model) {
                if ($filters = $model->filter()->getConditions()) {
                    foreach($filters as $condition) {
                        // collection 不支持like @see \Illuminate\Support\Traits\EnumeratesValues@operatorForWhere()
                        // $this->routes = call_user_func_array([$this->routes, key($condition)], current($condition));
                        [$key, , $value] = current($condition);
                        $this->routes = $this->routes->filter(fn($it) => stripos($it[$key], str_replace('%', '', $value)) !== false);
                    }
                }
                return $this->routes;
            }
        };
    }

    /** route 数据 */
    private function getRouteData() {
        $routes = app('router')->getRoutes();
        $routes = collect($routes)->map(fn(Route $route) => [
            'host'       => $route->domain(),
            'method'     => $route->methods(),
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            // Get before filters.
            'middleware' => collect($route->gatherMiddleware())->map(function ($middleware) {
                return $middleware instanceof \Closure ? 'Closure' : $middleware;
            }),
        ]);

        if ($sort = request('_sort')) {
            $routes = $routes->sortBy($sort);
        }

        return $routes;
    }

}