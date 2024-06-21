<?php

namespace App\Form;

use App\Entity\Listing;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class ListingType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'listing_12354',
			'data_class'		=> 'App\Entity\Listing',
		));
		$resolver->setRequired(['em', 'available']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('name', TextType::class, [
			'required' => true,
		]);
		$builder->add('public', CheckboxType::class, [
			'required' => false,
		]);

		$available = $options['available'];
		if (!empty($available)) {
			$builder->add('inheritFrom', EntityType::class, [
				'required' => false,
				'placeholder'=>'form.none',
				'class'=>Listing::class,
				'choice_label'=>'name',
				'query_builder'=>function(EntityRepository $er) use ($available) {
					return $er->createQueryBuilder('l')->where('l IN (:avail)')->setParameter('avail', $available);
				}
			]);
		}

		$builder->add('members', CollectionType::class, [
			'entry_type'		=> ListMemberType::class,
			'entry_options' => ['em' => $options['em'], 'listing'=>$builder->getData()],
			'allow_add'	=> true,
			'allow_delete' => true,
		]);
	}
}
