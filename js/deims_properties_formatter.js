Drupal.behaviors.deims_properties_formatter = {
  attach: function (context, settings) {

	var input_data = drupalSettings.deims_properties_formatter.data_object;	
	
	data = [{
		type: "treemap",
		ids: input_data['ids'],
		labels: input_data['labels'],
		parents: input_data['parents'],
		marker: {colors: input_data['colors']}
	}]
	
	var layout = {
		margin: {
			l: 10,
			r: 10,
			b: 10,
			t: 10,
			pad: 2
		},
	};
	
	Plotly.newPlot('properties_div', data, layout)
	
  }
  
};
