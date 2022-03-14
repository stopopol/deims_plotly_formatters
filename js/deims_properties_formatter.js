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

	Plotly.newPlot('properties_div', data)
	
  }
};
