/*
 * Charts (ApexCharts, vendor/apexcharts.min.js). Every element with data-chart="<options as JSON>" becomes a chart:
 * the ones on the page when it loads and the ones htmx puts in later (template admin/partials/chart.tpl).
 * A page that shows charts loads this file after vendor/apexcharts.min.js.
 */
(function () {
    'use strict';

    function draw(scope) {
        if (!window.ApexCharts) {
            return;
        }
        var elements = (scope || document).querySelectorAll('[data-chart]');
        Array.prototype.forEach.call(elements, function (element) {
            if (element.chartDrawn) {
                return;
            }
            element.chartDrawn = true;
            try {
                new window.ApexCharts(element, JSON.parse(element.getAttribute('data-chart'))).render();
            } catch (error) {
                element.textContent = String(error);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { draw(document); });
    } else {
        draw(document);
    }
    document.addEventListener('htmx:afterSettle', function (event) {
        draw(document);
    });
})();
