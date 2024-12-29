<?php

namespace App\Controller;

use App\Entity\Artifact;
use App\Entity\MapPOI;
use App\Service\Dispatcher\Dispatcher;
use App\Service\Geography;
use App\Service\History;
use App\Form\CharacterSelectType;
use App\Form\InteractionType;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArtifactsController extends AbstractController {
	public function __construct(
		private Dispatcher $disp,
		private EntityManagerInterface $em,
		private Geography $geo,
		private History $history) {
	}

	#[Route ('/artifact/owned', name:'maf_artifact_owned')]
	public function ownedAction(): Response {
		$user = $this->getUser();

		return $this->render('Artifacts/owned.html.twig', [
			'artifacts'=>$user->getArtifacts(),
			'slots'=>$user->getLimits()->getArtifacts()
		]);
	}

	#[Route ('/artifact/create', name:'maf_artifact_create')]
	public function createAction(Request $request): RedirectResponse|Response {
		$user = $this->getUser();

		if ($user->getArtifacts()->count() < $user->getFreeArtifacts()) {
			$form = $this->createFormBuilder()
				->add('name', TextType::class, array(
					'required'=>true,
					'label'=>'artifact.create.name'
					))
				->add('description', TextareaType::class, array(
					'required'=>true,
					'label'=>'artifact.create.description'
					))
				->add('submit', SubmitType::class, array('label'=>'artifact.create.submit'))
				->getForm();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();
				$name = trim($data['name']);
				$desc = trim($data['description']);
				$valid = true;

				if (strlen($name) < 6) {
					$form->addError(new FormError("Your name should be at least 6 characters long."));
					$valid = false;
				}

				// TODO: this might become expensive when we have a lot, as similar_text has a complexity of O(N^3)
				if ($valid) {
					foreach ($this->em->getRepository(Artifact::class)->findAll() as $check) {
						similar_text(strtolower($name), strtolower($check->getName()), $percent);
						if ($percent > 90.0) {
							$form->addError(new FormError("Your name is too similar to an existing name (".$check->getName()."). Please choose a more unique name."));
							$valid = false;
							break;
						}
					}
				}

				if ($valid) {
					$artifact = new Artifact;
					$artifact->setName($name);
					$artifact->setOldDescription($desc);
					$artifact->setCreator($user);
					$this->em->persist($artifact);

					$this->history->logEvent(
						$artifact,
						'event.artifact.created',
						array(),
						History::MEDIUM, true
					);

					$this->em->flush();
					return $this->redirectToRoute('maf_artifact_details', array('id'=>$artifact->getId()));
				}
			}
			return $this->render('Artifacts/create.html.twig', [
				'form'=>$form->createView(),
			]);
		} else {
			return $this->render('Artifacts/create.html.twig', [
				'limit_reached' => false,
			]);
		}
	}

	#[Route ('/artifact/details/{id}', name:'maf_artifact_details', requirements:['id'=>'\d+'])]
	public function detailsAction(Artifact $id): Response {
		return $this->render('Artifacts/details.html.twig', [
			'artifact'=>$id,
		]);
	}

	#[Route ('/artifact/assign/{id}', name:'maf_artifact_assign', requirements:['id'=>'\d+'])]
	public function assignAction(Artifact $id, Request $request): Response|RedirectResponse {
		$user = $this->getUser();
		$artifact = $id;

		#TODO: Convert these to translation strings. Maybe route this through dispatcher?
		if ($artifact->getCreator() !== $user) {
			$this->addFlash('warning', 'This artifact does not belong to you.');
			return $this->redirectToRoute('maf_artifact_details', array('id'=>$artifact));
		}
		if ($artifact->getOwner()) {
			$this->addFlash('warning', 'This artifact is already held by a character.');
			return $this->redirectToRoute('maf_artifact_details', array('id'=>$artifact));
		}

		$characters = array();
		foreach ($user->getCharacters() as $char) {
			if ($char->isAlive()) {
				$characters[] = $char->getId();
			}
		}
		$form = $this->createForm(CharacterSelectType::class, null, ['characters'=>$characters, 'empty'=>'form.choose', 'label'=>'choose target', 'submit'=>'assign artifact', 'domain'=>'messages']);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();

			$artifact->setOwner($data['target']);
			$this->history->logEvent(
				$artifact,
				'event.artifact.assigned',
				['%link-character%'=>$data['target']->getId()],
				History::MEDIUM, true
			);

			$this->em->flush();
			return $this->render('Artifacts/assign.html.twig', [
				'artifact'=>$artifact, 'givento'=>$data['target']
			]);
		}
		return $this->render('Artifacts/assign.html.twig', [
			'artifact'=>$artifact, 'form'=>$form->createView()
		]);
	}

	#[Route ('/artifact/spawn/{id}', name:'maf_artifact_spawn', requirements:['id'=>'\d+'])]
	public function spawnAction(Artifact $id, Request $request): RedirectResponse|Response {
		$user = $this->getUser();
		$artifact = $id;

		$this->addFlash('warning', 'We appreciate your enthusiam but area spawning is not yet supported.');
		return $this->redirectToRoute('maf_chars');

		if ($artifact->getCreator() != $user) {
			throw new Exception("Not your artifact.");
		}
		if ($artifact->getOwner()) {
			throw new Exception("Artifact already has an owner.");
		}

		$form = $this->createFormBuilder()
			->add('poi', EntityType::class, array(
				'label'=>'choose area to drop artifact in',
				'placeholder'=>'form.choose',
				'multiple'=>false,
				'expanded'=>false,
				'class'=>MapPOI::class,
				'choice_label'=>'name'
				))
			->add('submit', SubmitType::class, array('label'=>'create'))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			[$x, $y, $geodata] = $this->geo->findRandomPointInsidePOI($data['poi']);
			if ($geodata) {
				echo "found spot near ".$geodata->getSettlement()->getName();
			} else {
				echo "nothing found";
			}

			$this->history->logEvent(
				$artifact,
				'event.artifact.spawned',
				array('%area%'=>$data['poi']->getName()),
				History::MEDIUM, true
			);
			$this->em->flush();

		}
		return $this->render('Artifacts/spawn.html.twig', [
			'artifact'=>$artifact, 'form'=>$form->createView()
		]);
	}

	#[Route ('/artifact/give', name:'maf_artifact_give')]
	public function giveAction(Request $request): Response {
		$character = $this->disp->gateway('locationGiveArtifactTest');

		$form = $this->createForm(InteractionType::class, null, ['action'=>'giveartifact', 'maxdistance'=>$this->geo->calculateInteractionDistance($character), 'me'=>$character]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$artifact = $form->get('artifact')->getData();
			$target = $form->get('target')->getData();

			$artifact->setOwner($target);

			$this->history->logEvent(
				$artifact,
				'event.artifact.given',
				array('%link-character-1%'=>$character->getId(), '%link-character-2%'=>$target->getId()),
				History::MEDIUM, true
			);

			$this->history->logEvent(
				$target,
				'event.character.gotartifact',
				array('%link-character%'=>$character->getId(), '%link-artifact%'=>$artifact->getId()),
				History::MEDIUM, true, 20
			);
			$this->em->flush();
			return $this->render('Artifacts/give.html.twig', [
				'success'=>true, 'artifact'=>$artifact, 'target'=>$target
			]);
		}
		return $this->render('Artifacts/give.html.twig', [
			'form'=>$form->createView()
		]);
	}

}
