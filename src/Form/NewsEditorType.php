<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsEditorType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'newseditor_93245',
			'translation_domain' => 'communication',
		));
		$resolver->setRequired(['paper']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$paper = $options['paper'];

		$builder->add('owner', CheckboxType::class, array(
			'required'=>false,
			'label'=>'news.owner',
			'attr' => array('title'=>'news.help.owner')
		));
		$builder->add('editor', CheckboxType::class, array(
			'required'=>false,
			'label'=>'news.editor',
			'attr' => array('title'=>'news.help.editor')
		));
		$builder->add('author', CheckboxType::class, array(
			'required'=>false,
			'label'=>'news.author',
			'attr' => array('title'=>'news.help.author')
		));
		$builder->add('publisher', CheckboxType::class, array(
			'required'=>false,
			'label'=>'news.publisher',
			'attr' => array('title'=>'news.help.publisher')
		));
	}


}
