Drupal.behaviors.deims_climate_formatter = {
	attach: function (context, settings) {

		const formatter_data = drupalSettings.deims_climate_formatter;

		Object.keys(formatter_data).forEach(function (key) {
			const input_data = formatter_data[key];
			const div_name = 'climate_chart_' + input_data['deimsid'];

			var raw_precipitation_values = input_data['air_precipitation_values'];
			var raw_temperature_values = input_data['air_temperature_values'];

			// precipitation and temperature values when using the same axis
			var harmonised_precipitation_values = [];
			var harmonised_temperature_values = [];

			var scaled_precipitation_values = [];
			var scaled_temperature_values = [];

			// compress all precipitation values above 100mm
			for (var i = 0; i < raw_precipitation_values.length; i++) {

				if (raw_precipitation_values[i] > 100) {
					scaled_precipitation_values.push(((raw_precipitation_values[i] - 100) / 4) + 100);
					harmonised_precipitation_values.push(((raw_precipitation_values[i] - 100) / 4) + 100);
				} else {
					scaled_precipitation_values.push(raw_precipitation_values[i]);
					harmonised_precipitation_values.push(raw_precipitation_values[i]);
				}

			}

			// scale all temperature values below -10°C or above 50°C	
			for (var i = 0; i < raw_temperature_values.length; i++) {
				if (raw_temperature_values[i] < -10) {
					scaled_temperature_values.push(((raw_temperature_values[i] + 10) / 4) - 10);
					harmonised_temperature_values.push((((raw_temperature_values[i] + 10) / 4) - 10) * 2);
				} else if (raw_temperature_values[i] > 50) {
					scaled_temperature_values.push(((raw_temperature_values[i] - 50) / 4) + 50);
					harmonised_temperature_values.push((((raw_temperature_values[i] - 50) / 4) + 50) * 2);
				} else {
					scaled_temperature_values.push(raw_temperature_values[i]);
					harmonised_temperature_values.push(raw_temperature_values[i] * 2);
				}
			}

			// draw next polygon from intersection point

			function intersect(x1, y1, x2, y2, x3, y3, x4, y4) {

				// Check if none of the lines are of length 0
				if ((x1 === x2 && y1 === y2) || (x3 === x4 && y3 === y4)) {
					return false
				}

				let denominator = ((y4 - y3) * (x2 - x1) - (x4 - x3) * (y2 - y1))

				// Lines are parallel
				if (denominator === 0) {
					return false
				}

				let ua = ((x4 - x3) * (y1 - y3) - (y4 - y3) * (x1 - x3)) / denominator
				let ub = ((x2 - x1) * (y1 - y3) - (y2 - y1) * (x1 - x3)) / denominator

				// is the intersection along the segments
				if (ua < 0 || ua > 1 || ub < 0 || ub > 1) {
					return false
				}

				// Return a object with the x and y coordinates of the intersection
				let x = x1 + ua * (x2 - x1)
				let y = y1 + ua * (y2 - y1)

				return {
					x,
					y
				}
			}

			var polygons = [];
			var shapes_object = [];

			if (raw_precipitation_values.length == 12 && raw_temperature_values.length == 12) {

				var intersection_points = []
				for (var i = 0; i < harmonised_precipitation_values.length - 1; i++) {
					var check_intersection_point = intersect(i, harmonised_precipitation_values[i], i + 1, harmonised_precipitation_values[i + 1], i, harmonised_temperature_values[i], i + 1, harmonised_temperature_values[i + 1]);
					if (check_intersection_point) {
						intersection_points.push(check_intersection_point);
					}
				}

				if (intersection_points.length > 1) {
					// a loop to create all necessary polygons as SVGs
					var start_point_x = 0;
					var start_point_temp = harmonised_temperature_values[0];
					var start_point_prec = harmonised_precipitation_values[0];
					var end_point_x = intersection_points[0]['x'];
					var end_point_temp = intersection_points[0]['y'];
					var end_point_prec = intersection_points[0]['y'];

					for (var i = 0; i <= intersection_points.length; i++) {
						if (i == 0) {
							var x_values = [];
							var polygon_temp_values = [];
							var polygon_prec_values = [];
						} else {
							var x_values = [start_point_x];
							var polygon_temp_values = [start_point_temp];
							var polygon_prec_values = [start_point_prec];
						}

						for (var j = Math.ceil(start_point_x); j < end_point_x; j++) {
							x_values.push(j);
							polygon_temp_values.push(harmonised_temperature_values[j]);
							polygon_prec_values.push(harmonised_precipitation_values[j]);
						}
						x_values.push(end_point_x);
						polygon_temp_values.push(end_point_temp);
						polygon_prec_values.push(end_point_prec);
						start_point_x = end_point_x;
						start_point_temp = end_point_temp;
						start_point_prec = end_point_prec;

						if (i + 1 < intersection_points.length) {
							end_point_x = intersection_points[i + 1]['x'];
							end_point_temp = intersection_points[i + 1]['y'];
							end_point_prec = intersection_points[i + 1]['y'];
						} else {
							end_point_x = 11;
							end_point_temp = harmonised_temperature_values[11];
							end_point_prec = harmonised_precipitation_values[11];
						}

						const polygon = {
							xValues: x_values,
							temp: polygon_temp_values,
							prec: polygon_prec_values
						};
						polygons.push(polygon);

					}

				} else {
					var x_values = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
					var result = draw_and_colour_in_polygon(harmonised_temperature_values, harmonised_precipitation_values, x_values);
					var current_shape = {
						type: 'path',
						layer: 'below',
						path: result.path,
						fillcolor: result.polygon_colour,
						line: {
							width: 0
						}
					};
					shapes_object.push(current_shape);
				}
			}

			for (var i = 0; i < polygons.length; i++) {
				var result = draw_and_colour_in_polygon(polygons[i]['temp'], polygons[i]['prec'], polygons[i]['xValues']);
				var current_shape = {
					type: 'path',
					layer: 'below',
					path: result.path,
					fillcolor: result.polygon_colour,
					line: {
						width: 0
					}
				};
				shapes_object.push(current_shape);
			}

			function draw_and_colour_in_polygon(temperature_values, precipitation_values, x_values) {
				var path_string = '';
				for (var i = 0; i < x_values.length; i++) {
					path_string += ' L ' + x_values[i] + ', ' + temperature_values[i];
				}
				for (var i = precipitation_values.length - 1; i >= 0; i--) {
					path_string += ' L ' + x_values[i] + ', ' + precipitation_values[i];
				}

				path_string += ' Z';
				path_string = path_string.replace(/^.{2}/g, 'M ');

				if (temperature_values[0] >= precipitation_values[0] && temperature_values[1] >= precipitation_values[1]) {
					var colour = 'rgba(255, 255, 0, 0.5)';
				} else {
					var colour = 'rgba(65, 105, 225, 0.5)';
				}

				return {
					path: path_string,
					polygon_colour: colour,
				};
			};

			var temperature = {
				x: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
				y: scaled_temperature_values,
				type: 'scatter',
				line: {
					shape: 'linear'
				},
				name: 'Average Monthly Air Temperature',
				marker: {
					color: '#e61414'
				},
				yaxis: 'y2',
				hovertemplate: '%{text}' + ' °C',
				text: raw_temperature_values,
			};

			var precipitation = {
				x: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
				y: scaled_precipitation_values,
				type: 'scatter',
				line: {
					shape: 'linear'
				},
				name: 'Average Monthly Precipitation',
				marker: {
					color: '#4169e1'
				},
				yaxis: 'y1',
				hovertemplate: '%{text}' + ' mm',
				text: raw_precipitation_values,
			};

			var data = [temperature, precipitation];

			var layout = {
				margin: {
					t: 20,
				},
				height: 350,
				xaxis1: {
					tickmode: "array",
					ticktext: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					tickvals: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
					gridcolor: 'rgba(0, 0, 0, 0.05)',
					linewidth: 1,
					range: [-0, 11],
					fixedrange: true,
					zeroline: false,
					mirror: 'ticks',

				},
				yaxis1: {
					side: 'right',
					ticksuffix: ' mm',
					range: [-50, 150],
					ticktext: ["  mm", "     0", "   20", "   40", "   60", "   80", " 100", "", "", "", "", " 200", "", "", "", "", " 300"],
					tickvals: [-40, 0, 20, 40, 60, 80, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150],
					gridcolor: 'rgba(0, 0, 0, 0.05)',
					scaleratio: 0.5,
					scaleanchor: "y2",
					tickfont: {
						color: '#4169e1'
					},
					zeroline: false,
					showline: true,
					fixedrange: true,
				},
				yaxis2: {
					side: 'left',
					ticksuffix: ' °C',
					overlaying: 'y',
					range: [-25, 75],
					gridcolor: 'rgba(0, 0, 0, 0.05)',
					ticktext: ["-70  ", "", "", "-40  ", "", "", "-10  ", "0  ", "10  ", "20  ", "30  ", "40  ", "50  ", "°C   "],
					tickvals: [-25, -22.5, -20, -17.5, -15, -12.5, -10, 0, 10, 20, 30, 40, 50, 70],
					tickfont: {
						color: '#e61414'
					},
					zeroline: false,
					zerolinewidth: 0,
					fixedrange: true,
					showline: true,
				},
				showlegend: true,
				legend: {
					orientation: "h",
				},
				shapes: shapes_object
			};

			var config = {
				responsive: true,
				displaylogo: false,
				toImageButtonOptions: {
					format: 'png', // one of png, svg, jpeg, webp
					filename: input_data['site_title'] + ' - Climate Chart',
					height: 350,
					width: 700,
					scale: 1 // Multiply title/legend/axis/canvas sizes by this factor
				},
				modeBarButtonsToRemove: ['toggleHover', 'select2d', 'lasso2d']
			}

			Plotly.newPlot(div_name, data, layout, config);

		});
	}
};
