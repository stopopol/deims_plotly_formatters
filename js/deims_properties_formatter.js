Drupal.behaviors.deims_properties_formatter = {
  attach: function (context, settings) {

	var input_data = drupalSettings.deims_properties_formatter.data_object;	
	
	var data = [{
		type: "treemap",
		ids: input_data['ids'],
		labels: input_data['labels'],
		parents: input_data['parents'],
		marker: {colors: input_data['colors']}
	}];
	
	var layout = {
		autosize: true,
		margin: {l: 0, r: 0, b: 0, t:0},
	};
	
	var config = {
		responsive: true,
		displaylogo: false,
	}; 
	  
	Plotly.newPlot('properties_div', data, layout, config);
	
  }
  
};
