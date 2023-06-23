<?php

namespace App\Form;

use App\Entity\Realm;
use App\Entity\War;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for starting a siege.
 *
 * Accepts the following options (in their legacy order):
 * * 'realms' - array (null) - List of realms the siege can be started on behalf of
 * * 'wars' - array (null) - List of wars the siege can be started on behalf of
 */
class SiegeStartType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'siegestart_9753',
			'translation_domain' => 'actions',
			'realms'	=> null,
			'wars'		=> null,
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$wars = $options['wars'];
		$realms = $options['realms'];
		$builder->add('confirm', CheckboxType::class, array(
			'required'=>true,
			'label'=> 'military.siege.menu.confirm'
		));
		$builder->add('war', EntityType::class, [
			'required'=>false,
			'choices'=> $wars,
			'class'=>War::class,
			'choice_label' => 'summary',
			'placeholder'=>'military.siege.menu.none',
			'label'=>'military.siege.menu.wars'
		]);
		$builder->add('realm', EntityType::class, [
			'required'=>false,
			'choices'=> $realms,
			'class'=>Realm::class,
			'choice_label' => 'name',
			'placeholder'=>'military.siege.menu.none',
			'label'=>'military.siege.menu.realms'
		]);
		$builder->add('submit', SubmitType::class, array('label'=>'military.siege.submit'));
	}
}
