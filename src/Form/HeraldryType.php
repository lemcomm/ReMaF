<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class HeraldryType extends AbstractType {

	private array $colours = array(
		'metals' => array(
			"argent"=>"rgb(240,240,240)",
			"or"=>"rgb(255,220,10)",
			"copper"=>"rgb(184,115,51)",
			"iron"=>"rgb(161,157,148)",
			"lead"=>"rgb(68,79,83)",
			"buff"=>"rgb(230,178,115)",
		),
		'colours' => array(
			"vert" => "rgb(0,150,0)",
			"aquamarine" => "rgb(127,255,212)",
			"azure" => "rgb(0,0,255)",
			"blue celeste" => "rgb(150,200,250)",
			"eisen-farbe" => "rgb(176,196,222)",
			"cendree" => "rgb(128,128,128)",
			"white" => "rgb(255,255,255)",
			"ochre" => "rgb(203,157,6)",
			"red ochre" => "rgb(145,56,50)",
			"carnation" => "rgb(237,205,194)",
			"amaranth" => "rgb(241,156,187)",
			"rosr" => "rgb(255,0,127)",
			"gules" => "rgb(255,0,0)",
			"purpure" => "rgb(170,0,170)",
			"sable" => "rgb(0,0,0)",
		),
		'stains' => array(
			"murrey" => "rgb(140,0,75)",
			"sanguine" => "rgb(190,0,0)",
			"tenne" => "rgb(250,150,50)",
			"brunatre" => "rgb(101,67,33)",
		)
	);


	private array $shields = array(
		'badge', 'french', 'german', 'italian', 'polish', 'spanish', 'swiss', 'draconian'
	);

	private array $patterns = array(
		"base", "bend", "bend_sinister", "chevron", "chief", "cross", "fess", "flaunches", "gryon",  "pale",
		"per_bend", "per_bend_sinister", "per_chevron", "per_fess", "per_pale", "per_saltire",
		"pile", "quarterly", "saltire", "shakefork",
	);

	private array $charges = array(
		'beasts' => array(
			"bear_head_couped", "bear_head_erased", "bear_head_muzzled", "bear_passant", "bear_rampant", "bear_sejant_erect", "bear_statant",
			"boar_head_couped", "boar_head_erased", "boar_passant", "boar_rampant", "boar_statant",
			"buck_head_couped",
			"catamount_passant_guardant", "catamount_sejant_guardant", "catamount_sejant_guardant_erect",
			"cock", "coney",
			"dragon_passant", "dragon_rampant", "dragon_statant",
			"eagle_displayed",
			"falcon",
			"fox_mask", "fox_passant", "fox_sejant",
			"hind",
			"horse_courant", "horse_passant", "horse_rampant",
			"lion_rampant",
			"lynx_coward",
			"martlet_volant",
			"pegasus_passant",
			"reindeer",
			"serpent_nowed",
			"squirrel_sejant_erect",
			"stag-atgaze", "stag-lodged", "stag-springing", "stag-statant", "stag-trippant",
			"stagshead-caboshed", "stagshead-erased",
			"unicorn_rampant",
			"winged_stag_rampant",
			"wolf_courant", "wolf_passant", "wolf_rampant", "wolf_salient", "wolf_statant",
		),
		'objects' => array(
			"arm_cubit_habited", "arm_cubit_in_armor", "arm_embowed_in_armor",
			"battle_axe",
			"broad_arrow",
			"caltrap",
			"chess_rook",
			"chevalier_on_horseback",
			"church_bell",
			"crescent", "decrescent",
			"fluer_de_lis",
			"javelin",
			"scymitar",
			"sun_in_splendor",
			"sword",
		)
	);

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'heraldry_561561',
			'data_class'		=> 'App\Entity\Heraldry',
			'translation_domain' => 'heraldry'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', TextType::class, array(
			'label' => "label.name"
		));
		$builder->add('shield', ChoiceType::class, array(
			'label' => "label.shield",
			'required' => true,
			'placeholder' => 'form.choose',
			'choices' => array_combine($this->shields, $this->shields)
		));
		$builder->add('shield_colour', ChoiceType::class, array(
			'label' => "label.shieldc",
			'required' => true,
			'placeholder' => 'form.choose',
			'choices' => $this->colours
		));

		$builder->add('pattern', ChoiceType::class, array(
			'label' => "label.pattern",
			'required' => false,
			'placeholder' => 'form.choose',
			'choices' => array_combine($this->patterns, $this->patterns)
		));
		$builder->add('pattern_colour', ChoiceType::class, array(
			'label' => "label.patternc",
			'required' => false,
			'placeholder' => 'form.choose',
			'choices' => $this->colours
		));
		$charges = array();
		foreach ($this->charges as $key=>$data) {
			$charges[$key] = array_combine($data, $data);
		}
		$builder->add('charge', ChoiceType::class, array(
			'label' => "label.charge",
			'required' => false,
			'placeholder' => 'form.choose',
			'choices' => $charges
		));
		$builder->add('charge_colour', ChoiceType::class, array(
			'label' => "label.chargec",
			'required' => false,
			'placeholder' => 'form.choose',
			'choices' => $this->colours
		));

		$builder->add('shading', CheckboxType::class, array(
			'label' => "label.shading",
			'required' => false,
		));
	}
}
