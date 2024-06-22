<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserSettingsType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'	=> 'settings_41234',
			'attr'		=> array('class'=>'wide'),
			'languages'	=> []
		));
		$resolver->setRequired(['user']);
	}

	// FIXME: change this to use the user object (it's very old code, I didn't know about it)
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('newsletter', CheckboxType::class, array(
			'label' => 'account.settings.newsletter',
			'required' => false,
			'data'=>$options['user']->getNewsletter()
		));
		$builder->add('notifications', CheckboxType::class, array(
			'label' => 'account.settings.notifications',
			'required' => false,
			'data'=>$options['user']->getNotifications()
		));
		$builder->add('emailDelay', ChoiceType::class, array(
			'label' => 'account.settings.delay.name',
			'required' => false,
			'placeholder' => 'account.settings.delay.choose',
			'choices'=>[
				'account.settings.delay.now' => 'now',
				'account.settings.delay.hourly' => 'hourly',
				'account.settings.delay.6h' => '6h',
				'account.settings.delay.12h' => '12h',
				'account.settings.delay.daily' => 'daily',
				'account.settings.delay.sundays' => 'sundays',
				'account.settings.delay.mondays' => 'mondays',
				'account.settings.delay.tuesdays' => 'tuesdays',
				'account.settings.delay.wednesdays' => 'wednesdays',
				'account.settings.delay.thursdays' => 'thursdays',
				'account.settings.delay.fridays' => 'fridays',
				'account.settings.delay.saturdays' => 'saturdays',
			],
			'data'=>$options['user']->getEmailDelay()
		));
		$builder->add('language', ChoiceType::class, array(
			'label' => 'account.settings.language',
			'placeholder' => 'form.browser',
			'required' => false,
			'choices' => $options['languages'],
			'data'=>$options['user']->getLanguage()
		));

		$builder->add('submit', SubmitType::class, array('label'=>'account.settings.submit'));
	}
}
