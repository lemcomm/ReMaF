<?php

namespace App\Service;


/**
 * For doing armor calculations, primarily.
 * This file should not reference other services, except maybe an ItemCalculator later.
 */
class ArmorCalculator {

	/*
	* Form determines coverage.
	* Layer determines layer materials.
	*/

	public static array $layers = [
		'plate' => [
			'protection' => [
				'bashing' => 6,
				'cutting' => 10,
				'piercing' => 6,
			],
			'type' => 'plate',
			'weight' => 7.7
		],
		'scale' => [
			'protection' => [
				'bashing' => 5,
				'cutting' => 9,
				'piercing' => 4,
			],
			'type' => 'mail',
			'weight' => 6.1
		],
		'mail' => [
			'protection' => [
				'bashing' => 2,
				'cutting' => 8,
				'piercing' => 5,
			],
			'type' => 'mail',
			'weight' => 4.9
		],
		'ring' => [
			'protection' => [
				'bashing' => 3,
				'cutting' => 6,
				'piercing' => 4,
			],
			'type' => 'mail',
			'weight' => 3.4
		],
		'hard leather' => [
			'protection' => [
				'bashing' => 4,
				'cutting' => 5,
				'piercing' => 4,
			],
			'type' => 'plate',
			'weight' => 2.1
		],
		'leather' => [
			'protection' => [
				'bashing' => 2,
				'cutting' => 4,
				'piercing' => 3,
			],
			'type' => 'flexible',
			'weight' => 1.1
		],
		'quilt' => [
			'protection' => [
				'bashing' => 5,
				'cutting' => 3,
				'piercing' => 2,
			],
			'type' => 'flexible',
			'weight' => 0.8
		],
		'cloth' => [
			'protection' => [
				'bashing' => 1,
				'cutting' => 1,
				'piercing' => 1,
			],
			'type' => 'flexible',
			'weight' => 0.4
		],
	];

	public static array $forms = [
		'tunic' => [
			'coverage' => ['upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin'],
			'type' => 'flexible'
		],
		'surcoat' => [
			'coverage' => ['shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'flexible mail'
		],
		'gambeson' => [
			'coverage' => ['forearm', 'elbow', 'upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'flexible'
		],
		'boots' => [
			'coverage' => ['calf', 'foot'],
			'type' => 'flexible mail'
		],
		'shoes' => [
			'coverage' => ['foot'],
			'type' => 'flexible mail'
		],

		'gauntlets' => [
			'coverage' => ['hand'],
			'type' => 'flexible mail'
		],

		'cowl' => [
			'coverage' => ['skull', 'neck'],
			'type' => 'flexible mail'
		],
		'leggings' => [
			'coverage' => ['hip', 'groin', 'thigh', 'knee', 'calf'],
			'type' => 'flexible mail'
		],
		'hauberk' => [
			'coverage' => ['forearm', 'elbow', 'upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'mail'
		],
		'byrnie' => [
			'coverage' => ['upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin'],
			'type' => 'mail'
		],
		'vest' => [
			'coverage' => ['shoulder', 'torso', 'abdomen'],
			'type' => 'flexible mail'
		],
		'skirt' => [
			'coverage' => ['hip', 'groin', 'thigh'],
			'type' => 'plate mail'
		],


		'cap' => [
			'coverage' => ['skull'],
			'type' => 'flexible plate'
		],
		'helm' => [
			'coverage' => ['skull', 'face'],
			'type' => 'plate'
		],
		'breastplate' => [
			'coverage' => ['torso', 'abdomen'],
			'type' => 'plate'
		],
		'greaves' => [
			'coverage' => ['calf'],
			'type' => 'plate'
		],
		'ailettes' => [
			'coverage' => ['shoulder'],
			'type' => 'plate'
		],
		'rerebraces' => [
			'coverage' => ['upper arm'],
			'type' => 'plate'
		],
		'vambraces' => [
			'coverage' => ['forearm'],
			'type' => 'plate'
		],
	];

	private array $index = [];

	public function calculateWeight(string $armorName, array $armorData): float {
		if (array_key_exists($armorName, $this->index)) return $this->index[$armorName];
		$weight = 0;
		foreach ($armorData as $each) {
			# ArmorData is stored as follows
			# [form => formName, layer => layerName], [...],
			# This indexes into forms to get coverages, then foreach coverage calculates the weights from the layers.
			foreach (self::$forms[$each['form']][0]['coverage'] as $loc) {
				$weight += self::$layers[$each['layer']['weight']];
			}
		}
		# Add it to the quick lookup to save time in battles.
		$this->index[$armorName] = $weight;
		return $weight;
	}
}
