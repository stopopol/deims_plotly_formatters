Drupal.behaviors.deims_params_formatter = {
  attach: function (context, settings) {

	var input_data = drupalSettings.deims_params_formatter.data_object;
	  
	var data = [{
		type: 'sunburst',
		ids: input_data['ids'],
		labels: input_data['labels'],
		parents: input_data['parents'],
		outsidetextfont: {size: 20, color: "#377eb8"},
		leaf: {opacity: 1.0},
		marker: {line: {width: 2}},
		insidetextorientation: 'auto',
		sort: false // need to turn off automatic sorting in order for the colours to be correctly assigned
	}]; 
	
	var config = {
		responsive: true,
		displaylogo: false,
		toImageButtonOptions: {
			format: 'png', // one of png, svg, jpeg, webp
			filename: 'elter_standard_observations',
			height: 1200,
			width: 1200,
			scale: 1 // Multiply title/legend/axis/canvas sizes by this factor
		},
		modeBarButtonsToRemove: ['toggleHover','toImage']

	}

	var layout = {
		autosize: true,
		margin: {l: 0, r: 0, b: 0, t:0},
		sunburstcolorway: input_data['colors'],
	};
	
	Plotly.newPlot('params_div', data, layout, config);
	
  }
};
