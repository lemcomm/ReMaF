<?php

namespace App\Form;

use App\Entity\Biome;
use App\Entity\GeoData;
use App\Entity\Settlement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class EditGeoDataType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'data_class' => GeoData::class,
			'food'=>null,
			'wood'=>null,
			'metal'=>null,
		));
	}

	# TODO: Incorporate the GeoResources into this.
        public function buildForm(FormBuilderInterface $builder, array $options): void {
		$food = $options['food'];
		$wood = $options['wood'];
		$metal = $options['metal'];
                $builder
                        ->add('altitude')
                        ->add('hills')
                        ->add('coast')
                        ->add('lake')
                        ->add('river')
                        ->add('humidity')
                        ->add('passable')
                        ->add('settlement', EntityType::class, [
                        'class'=>Settlement::class,
                        'label'=>'Settlement',
                        'choice_label'=>'name'
                        ])
                        ->add('biome', EntityType::class, [
                        'class'=>Biome::class,
                        'label'=>'Biome',
                        'choice_label'=>'name'
                        ])
                        ->add('food', NumberType::class, [
        			'label'=>'Base Food',
        			'data'=>$food?:0,
        			'required'=>false,
                                'mapped'=>false,
                        ])
                        ->add('wood', NumberType::class, [
        			'label'=>'Base Wood',
        			'data'=>$wood?:0,
        			'required'=>false,
                                'mapped'=>false,
                        ])
                        ->add('metal', NumberType::class, [
        			'label'=>'Base Metal',
        			'data'=>$metal?:0,
        			'required'=>false,
                                'mapped'=>false,
                        ]);

                $builder->add('submit', SubmitType::class)
                ;
        }
}
