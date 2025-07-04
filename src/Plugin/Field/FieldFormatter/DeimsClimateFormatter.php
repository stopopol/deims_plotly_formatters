<?php

namespace Drupal\deims_plotly_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'DeimsClimateFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "deims_climate_formatter",
 *   label = @Translation("DEIMS Climate Formatter"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "string",
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
 
class DeimsClimateFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
 
	public function settingsSummary() {
		$summary = [];
		$summary[] = $this->t('Queries and visualises temperature/precipitation values');
		return $summary;
	}

	/**
	  * {@inheritdoc}
	  */
	public function viewElements(FieldItemListInterface $items, $langcode) {
		$elements = [];
		$formatter_settings = [];
		// Render each element as markup in case of multi-values.

		foreach ($items as $delta => $item) {
			  
			$record_uuid = $item->value;
			$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $record_uuid]);

			if (!empty($nodes)) {
				foreach ($nodes as $node) {

					$site_title = $node->getTitle();
					$air_temperature_values_object = $node->get('field_air_temp');
					$precipitation_values_object = $node->get('field_precipitation');
					$location_reference = $node->get('field_observation_location');
					$locations_text = '';
					
					if (!$location_reference->isEmpty()) {
						
						$counter = 1;
						$locations_references = '';
						
						foreach ($location_reference as $item) {
							$location = $item->entity;
							
							if ($location) {
								$location_id = $location->get('field_uuid')->value;								
								$locations_references .= "<a href='/$location_id'>[$counter]</a>&nbsp;";
								$counter++;								
							}
						}
						
						$locations_text = "Exact location";
						if ($counter > 1) {
							$locations_text .= "s";
						}
						$locations_text .= " of measurements: " . $locations_references;
							
					}
						
					if ($node->get('field_standard_reference_period')->entity) {
						$reference_period = 'Standard Reference Period: ' . $node->get('field_standard_reference_period')->entity->label().'<br>';
					}
					else {
						$reference_period = "No reference period has been provided<br>";
					}
					
					$air_temperature_values = array();
					$air_temperature_mean = null;

					if (!$air_temperature_values_object->isEmpty()) {
						foreach ($air_temperature_values_object as $air_temperature_value) {
							$air_temperature_values[]= floatval($air_temperature_value->value);
						}
						if (sizeof($air_temperature_values) == 12) {
							$air_temperature_mean = 'Temperature Mean: ' . round(array_sum($air_temperature_values)/12, 2) . ' °C<br>';
						}
					}
					
					$precipitation_values = array();
					$precipitation_sum = null;
					
					if (!$precipitation_values_object->isEmpty()) {
						foreach ($precipitation_values_object as $precipitation_value) {
							$precipitation_values[] = floatval($precipitation_value->value);
						}
						if (sizeof($precipitation_values) == 12) {
							$precipitation_sum = 'Precipitation Sum:&nbsp;&nbsp;&nbsp;&nbsp;' . array_sum($precipitation_values) . ' mm<br>';
						}
					}
					
					if (empty($air_temperature_values) && empty($precipitation_values)) {
						return $elements;
					}
										
					$legend_text = '';
					$legend_text .= $precipitation_sum ?? '';
					$legend_text .= $air_temperature_mean ?? '';
					$legend_text .= $reference_period ?? '';
					$legend_text .= $locations_text ?? '';
					
					$formatter_settings[$delta] = [
						'deimsid' => $record_uuid,
						'site_title' => $site_title,
						'air_temperature_values' => $air_temperature_values,
						'air_precipitation_values' => $precipitation_values,
					];
					
					$elements[$delta] = [
						'#markup' => '<div id="climate_chart_' . $record_uuid . '"></div><div class="legend-text">' . $legend_text . '</div>',
					];
				}
			}
		}
		
		$elements['#attached'] = [
			'library' => ['deims_plotly_formatter/deims-climate-formatter'],
			'drupalSettings' => [
				'deims_climate_formatter' => $formatter_settings,
			],
		];

		return $elements;

    }

}
