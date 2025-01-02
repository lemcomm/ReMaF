<?php /** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Form;

use App\Entity\Artifact;
use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


/**
 * Form for handling an interaction between characters.
 *
 * Accepts the following options (in their legacy order):
 * * 'action' - string - type of action
 * * 'maxdistance' - integer - interaction range to search for characters in. Use Geography->calculateInteractionDistance($char)
 * * 'me' - Character Entity - the character to search from, generally who is doing the action
 * * 'multiple' - boolean (false) - Allow multiple targets
 * * 'settlementcheck' - boolean (false) - Respect inside/outside settlements
 * * 'required' - boolean (true) - Set target field as required/don't accept null.
 */
class InteractionType extends AbstractType {
	public function getName(): string {
		return 'interaction';
	}

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(array(
			'intention'       => 'interaction_12331',
			'multiple'	=> false,
			'settlementcheck' => false,
			'required'	=> true,
		));
		$resolver->setRequired(['subaction', 'maxdistance', 'me']);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$me = $options['me'];
		$maxdistance = $options['maxdistance'];
		$settlementcheck = $options['settlementcheck'];
		$builder->add('target', EntityType::class, array(
			'label'=>'interaction.'.$options['subaction'].'.name',
			'placeholder'=>$options['multiple']?'character.none':null,
			'multiple'=>$options['multiple'],
			'expanded'=>true,
			'required'=>$options['multiple'],
			'class'=>Character::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me, $maxdistance, $settlementcheck) {
				$qb = $er->createQueryBuilder('c');
				$qb->from('App:Character', 'me');
				$qb->where('c.alive = true');
				$qb->andWhere('c.prisoner_of IS NULL');
				$qb->andWhere('c.system NOT LIKE :gm OR c.system IS NULL')->setParameter('gm', 'GM');
				$qb->andWhere('me = :me')->andWhere('c != me')->setParameter('me', $me);
				if ($maxdistance) {
					$qb->andWhere('ST_Distance(me.location, c.location) < :maxdistance')->setParameter('maxdistance', $maxdistance);
				}
				if ($settlementcheck) {
					if (!$me->getInsideSettlement()) {
						// if I am not inside a settlement, I can only attack others who are outside as well
						$qb->andWhere('c.inside_settlement IS NULL');
					}
				}
				$qb->orderBy('c.name', 'ASC');
				return $qb;
		}));

		$method = $options['subaction']."Fields";
		if (method_exists(__CLASS__, $method)) {
			$this->$method($builder, $options);
		}

		$builder->add('submit', SubmitType::class, array('label'=>'interaction.'.$options['subaction'].'.submit'));
	}

	private function messageFields(FormBuilderInterface $builder): void {
		$builder->add('subject', TextType::class, array(
			'label' => 'interaction.message.subject',
			'required' => true
		));
		$builder->add('body', TextareaType::class, array(
			'label' => 'interaction.message.body',
			'required' => true,
			'empty_data' => '(no message)'
		));
	}

	private function grantFields(FormBuilderInterface $builder): void {
		$builder->add('withrealm', CheckboxType::class, array(
			'required' => false,
			'label' => 'control.grant.withrealm',
			'attr' => array('title'=>'control.grant.withrealm2'),
			'translation_domain' => 'actions'
		));
		$builder->add('keepclaim', CheckboxType::class, array(
			'required' => false,
			'label' => 'control.grant.keepclaim',
			'attr' => array('title'=>'control.grant.keeprealm2'),
			'translation_domain' => 'actions'
		));
	}

	private function givegoldFields(FormBuilderInterface $builder): void {
		$builder->add('amount', IntegerType::class, array(
			'required' => true,
			'label' => 'interaction.givegold.amount',
		));
	}

	private function giveartifactFields(FormBuilderInterface $builder, array $options): void {
		$me = $options['me'];
		$builder->add('artifact', EntityType::class, array(
			'required' => true,
			'label' => 'interaction.giveartifact.which',
			'class'=>Artifact::class,
			'choice_label'=>'name',
			'query_builder'=>function(EntityRepository $er) use ($me) {
				return $er->createQueryBuilder('a')->where('a.owner = :me')->setParameter('me', $me);
			}
		));
	}

}
