<?php

namespace TSSGGSModule\App\Kernel;

/**
 * Options of a chart for ApexCharts (resources/assets/js/vendor/apexcharts.min.js, drawn by resources/assets/js/
 * charts.js into the element that carries them: template admin/partials/chart.tpl).
 *
 *   Chart::series(Chart::AREA, ['June', 'July'], ['Income' => [10, 12.5]])     lines / areas / bars over categories
 *   Chart::parts(['Active' => 19, 'Pending' => 9])                              donut: the share of each part
 */
class Chart
{
    const AREA = 'area';
    const BAR  = 'bar';
    const LINE = 'line';

    /** series colours, in this order */
    const COLORS = ['#008FFB', '#00E396', '#FEB019', '#9B78DD', '#00A9DC', '#36E388', '#FBA81C', '#7362CB', '#00C3BD', '#A2E16C', '#F29023', '#8775D4'];

    /**
     * @param string[] $categories labels of the x axis
     * @param array    $series     name => values, one value per category
     */
    public static function series(string $type, array $categories, array $series, string $height = '380px'): array
    {
        $list = [];
        foreach($series as $name => $values)
        {
            $list[] = ['name' => (string)$name, 'data' => array_values($values)];
        }

        return self::base($type, $height) + [
            'series' => $list,
            'xaxis'  => ['categories' => array_values($categories)],
            'yaxis'  => ['show' => true, 'title' => ['text' => '']],
        ];
    }

    /** @param array $parts label => value */
    public static function parts(array $parts, string $height = '380px'): array
    {
        return self::base('donut', $height) + [
            'series' => array_values(array_map('floatval', $parts)),
            'labels' => array_map('strval', array_keys($parts)),
        ];
    }

    protected static function base(string $type, string $height): array
    {
        return [
            'chart'      => ['type' => $type, 'height' => $height, 'toolbar' => ['show' => false], 'zoom' => ['enabled' => false]],
            'colors'     => self::COLORS,
            'dataLabels' => ['enabled' => false],
            'legend'     => ['show' => true, 'position' => 'top', 'horizontalAlign' => 'center', 'markers' => ['shape' => 'circle']],
            'tooltip'    => ['enabled' => true],
        ];
    }
}
