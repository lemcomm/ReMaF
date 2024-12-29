<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NewsArticleType extends AbstractType {


	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'		=> 'newsarticle_2461',
			'data_class'		=> 'App\Entity\NewsArticle',
			'translation_domain'	=> 'communication'
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('title', TextType::class, [
			'label'=>'news.article.title',
			'required' => true,
			'attr' => [
				'size'=>40,
				'maxlength'=>80
			]
		]);
		$builder->add('content', TextareaType::class, [
			'label'=>'news.article.content',
			'trim'=>true,
			'required'=>true
		]);

		$builder->add('submit', SubmitType::class, [
			'label'=>'news.article.create'
		]);
	}


}
