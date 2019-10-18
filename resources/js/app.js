/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('chart.js');

$(function(){
	$('[data-plot]').each(function(){
		var data = $(this).data('plot');

		var radarChart = new Chart(this, {
			type: 'radar',
			data: data,
			options: {
				fill: true,
				scale: {
					ticks: {
						beginAtZero: true,
						min: 0,
						max: 10,
						stepSize: 1,
						fontSize: 20
					},
					pointLabels: {
				    	fontSize: 20,
				    	fontColor: '#111'
				    }
				},
				tooltips: {
		            callbacks: {
		                label: function(tooltipItems, data) {
		                    return data.datasets[tooltipItems.datasetIndex].label +': ' + tooltipItems.yLabel;
		                }
		            }

		        },
		        "legend": {
		            "display": true,
		            "labels": {
		                "fontSize": 18
		            }
		        }
			}
		});
	});

	var outerContent = $('.plot');
    var innerContent = $('.plot-wrapper');

    outerContent.scrollLeft((innerContent.width() - outerContent.width()) / 2);

    $('select').on('change', function() {
    	var value = $(this).val();
    	document.location.href = document.location.origin + '?date=' + value;
    })
})