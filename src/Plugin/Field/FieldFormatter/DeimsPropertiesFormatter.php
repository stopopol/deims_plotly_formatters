<?php

namespace Drupal\deims_plotly_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'DeimsPropertiesFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "deims_properties_formatter",
 *   label = @Translation("DEIMS Properties Formatter"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
 
class DeimsPropertiesFormatter extends FormatterBase {

  /**
   * https://www.drupal.org/docs/creating-custom-modules/adding-stylesheets-css-and-javascript-js-to-a-drupal-module
   * https://plotly.com/javascript/sunburst-charts/
   * 	always show all categories
   *		and only print covered compartments and variables
   * {@inheritdoc}
   */
 
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Formats the observed properties');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
		
	$agricultural_colour 	= '#d18942'; // brown
	$atmospheric_colour 	= '#89e1ff'; // light blue
	$biological_colour 		= '#e8ccd7'; // queen pink
	$chemical_colour 		= '#DC143C'; // red
	$ecosystem_colour 		= '#228B22'; // forest green
	$environmental_colour 	= '#7CFC00'; // grass green
	$genetic_colour 		= '#f533ff'; // pink
	$landscape_colour 		= '#AFE1AF'; // light green
	$physical_colour 		= '#ff8800 '; // orange
	$remotesensing_colour 	= '#808080'; // grey
	$soil_colour 			= '#6F4E37'; // dark brown
	$water_colour 			= '#191970'; // dark blue
	
	$labels = array();
	$ids = array();
	$parents = array();
	$colors = array();


	// looping through all ticked terms
    foreach ($items as $delta => $item) {
		$item_value = $item->getValue();
		$term_id = $item_value['target_id'];
		$term_label = \Drupal\taxonomy\Entity\Term::load($term_id)->get('name')->value;
	    
	    	// replace all whitespaces in label with linebreaks
	    	$term_label = preg_replace('/\s+/', '<br>', $term_label);
		
		// get parent
		$parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term_id);
		$parent_item = reset($parent);
		
		if (!$parent_item) {
			continue;
		}
		
		$parent_term_id = $parent_item->id();
		$parent_term_label = \Drupal\taxonomy\Entity\Term::load($parent_term_id)->get('name')->value;
		
		if (!in_array($parent_term_id, $ids)) {
			array_push($labels,$parent_term_label);
			array_push($ids,$parent_term_id);
			array_push($parents,'');
			
			// add colors depending on covered category
			switch ($parent_term_id) {
				case 21012:
					array_push($colors,$agricultural_colour);
					break;
				case 21073:
					array_push($colors,$atmospheric_colour);
					break;
				case 21135:
					array_push($colors,$biological_colour);
					break;
				case 53422:
					array_push($colors,$chemical_colour);
					break;
				case 21157:
					array_push($colors,$ecosystem_colour);
					break;
				case 53703:
					array_push($colors,$environmental_colour);
					break;
				case 21298:
					array_push($colors,$genetic_colour);
					break;
				case 21300:
					array_push($colors,$landscape_colour);
					break;
				case 53702:
					array_push($colors,$physical_colour);
					break;
				case 53699:
					array_push($colors,$remotesensing_colour);
					break;
				case 21315:
					array_push($colors,$soil_colour);
					break;
				case 21391:
					array_push($colors,$water_colour);
					break;
			}
			
		}
		
		array_push($labels,$term_label);
		array_push($ids,$term_id);
		array_push($parents,$parent_term_id);
		
		
		// add colors depending on covered category
		switch ($parent_term_id) {
			case 21012:
				array_push($colors,$agricultural_colour);
				break;
			case 21073:
				array_push($colors,$atmospheric_colour);
				break;
			case 21135:
				array_push($colors,$biological_colour);
				break;
			case 53422:
				array_push($colors,$chemical_colour);
				break;
			case 21157:
				array_push($colors,$ecosystem_colour);
				break;
			case 53703:
				array_push($colors,$environmental_colour);
				break;
			case 21298:
				array_push($colors,$genetic_colour);
				break;
			case 21300:
				array_push($colors,$landscape_colour);
				break;
			case 53702:
				array_push($colors,$physical_colour);
				break;
			case 53699:
				array_push($colors,$remotesensing_colour);
				break;
			case 21315:
				array_push($colors,$soil_colour);
				break;
			case 21391:
				array_push($colors,$water_colour);
				break;
		}
			
	}
	  
	if(empty($ids)) {
		return $elements;
	}
	    
	$elements[$delta] = [
		'#markup' => '<div id="properties_div"></div>',
		'#attached' => array(
			'library'=> array('deims_plotly_formatter/deims-properties-formatter'),
				'drupalSettings' => array(
					'deims_properties_formatter' => array(
						'data_object' => array(
							'parents' => $parents,
							'ids' => $ids,
							'labels' => $labels,
							'colors' => $colors,
						),
					)
				),
		),
	];	  

    return $elements;
  }
}
