<?php

namespace Drupal\deims_plotly_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'DeimsParamsFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "deims_params_formatter",
 *   label = @Translation("DEIMS Params Formatter"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
 
class DeimsParamsFormatter extends FormatterBase {

  /**
   * https://www.drupal.org/docs/creating-custom-modules/adding-stylesheets-css-and-javascript-js-to-a-drupal-module
   * https://plotly.com/javascript/sunburst-charts/
   * 	always show all categories
   *		and only print covered compartments and variables
   * {@inheritdoc}
   */
 
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Formats the observations of high relevance field of Drupal.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
	
	$abiotic_color = '#ffff33'; //yellow
	$biotic_heterogeneity_color = '#4daf4a'; //green
	$energy_budget_color = '#ef553b'; //red
	$matter_budget_color = '#ff7f00'; //orange
	$socio_ecology_color = '#f533ff'; //pink
	$water_balance_color = '#377eb8'; //blue
	
	$labels = array();
	$ids = array();
	$parents = array();
	$colors = array();

	// looping through all ticked terms
    foreach ($items as $delta => $item) {
		$item_value = $item->getValue();
		$term_id = $item_value['target_id'];
		$term_label = \Drupal\taxonomy\Entity\Term::load($term_id)->get('name')->value;
	  	
		// get compartment
		$compartment = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term_id);
		$compartment_item = reset($compartment);
		$compartment_term_id = $compartment_item->id();
		$compartment_term_label = \Drupal\taxonomy\Entity\Term::load($compartment_term_id)->get('name')->value;
		
		// if the compartment is not in the list, add it
		if (!in_array($compartment_term_id, $ids)) {
			$category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($compartment_term_id);
			$category_item = reset($category);
			$category_term_id = $category_item->id();
			
			// add linebreaks to selected compartments
			switch ($compartment_term_label) {
				case 'Radiation Budget':
					$compartment_term_label = 'Radiation<br>Budget';
					break;
				case 'Governance and stakeholders':
					$compartment_term_label = 'Governance and<br>stakeholders';
					break;
				case 'Land use and land cover change':
					$compartment_term_label = 'Land use and<br>land cover change';
					break;
			}
			
			array_push($parents,$category_term_id);
			array_push($ids,$compartment_term_id);
			array_push($labels,$compartment_term_label);
			
			// if the category is not in the list, add it			
			if (!in_array($category_term_id, $ids)) {
				$category_term_label = \Drupal\taxonomy\Entity\Term::load($category_term_id)->get('name')->value;
				
				switch ($category_term_label) {
					case 'Biotic heterogeneity':
						$category_term_label = 'Biotic<br>heterogeneity';
						break;
					case 'Energy budget':
						$category_term_label = 'Energy<br>budget';
						break;
					case 'Matter budget':
						$category_term_label = 'Matter<br>budget';
						break;
					case 'Socio-Ecology':
						$category_term_label = 'Socio<br>Ecology';
						break;
					case 'Water Balance':
						$category_term_label = 'Water<br>Balance';
						break;
				}
				
				array_push($parents,'');
				array_push($ids,$category_term_id);
				array_push($labels,$category_term_label);
				
				// add colors depending on covered category
				switch ($category_term_id) {
					case 54356:
						array_push($colors,$abiotic_color);
						break;
					case 54380:
						array_push($colors,$biotic_heterogeneity_color);
						break;
					case 54410:
						array_push($colors,$energy_budget_color);
						break;
					case 54431:
						array_push($colors,$matter_budget_color);
						break;
					case 54481:
						array_push($colors,$socio_ecology_color);
						break;
					case 54533:
						array_push($colors,$water_balance_color);
						break;
				}
				
			}	
			
		}
	  
		array_push($parents,$compartment_term_id);
		array_push($ids,$term_id);
		array_push($labels,$term_label);
	  
	  
		$elements[$delta] = [
			'#markup' => '<div id="params_div"></div>',
			'#attached' => array(
				'library'=> array('deims_plotly_formatter/deims-params-formatter'),
				'drupalSettings' => array(
					'deims_params_formatter' => array(
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
	  
    }

    return $elements;
  }
}
