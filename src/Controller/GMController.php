<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Journal;
use App\Entity\UpdateNote;
use App\Entity\User;
use App\Form\UpdateNoteType;
use App\Service\AppState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GMController extends AbstractController {

	private $app;
	private $em;

	public function __construct(AppState $app, EntityManagerInterface $em) {
		$this->app = $app;
		$this->em = $em;
	}
	#[Route ('/olympus', name:'maf_gm_pending')]
	public function pendingAction(): Response {
		# Security is handled by Syfmony Firewall.
		$query = $this->em->createQuery('SELECT r from App\Entity\UserReport r WHERE r.actioned = false');
		$reports = $query->getResult();

		return $this->render('GM/pending.html.twig',  [
			'reports'=>$reports,
		]);
	}

	#[Route ('/olympus/user/{id}', name:'maf_gm_user_reports')]
	public function userReportsAction(User $id): Response {
		# Security is handled by Syfmony Firewall.

		return $this->render('GM/userReports.html.twig',  [
			'by'=>$id->getReports(),
			'against'=>$id->getReportsAgainst()
		]);
	}

	#[Route ('/olympus/archive', name:'maf_gm_pending')]

	public function actionedAction() {
		# Security is handled by Syfmony Firewall.
		$query = $this->em->createQuery('SELECT r from App\Entity\UserReport r WHERE r.actioned = true');
		$reports = $query->getResult();

		return $this->render('GM/pending.html.twig',  [
			'reports'=>$reports,
		]);
	}

	#[Route ('/olympus/update/{id}', name:'maf_admin_update')]
	#[Route ('/olympus/update/')]
	#[Route ('/olympus/update')]

	public function updateNoteAction(Request $request, UpdateNote $id=null): RedirectResponse|Response {
		# Security is handled by Syfmony Firewall.

		$form = $this->createForm(new UpdateNoteType($id));
		$form->handleRequest($request);

		if ($form->isValid() && $form->isSubmitted()) {
			$data = $form->getData();
			if (!$id) {
				$note = new UpdateNote();
				$now = new \DateTime('now');
				$note->setTs($now);
				$version = $data['version'];
				$note->setVersion($version);
				$this->em->persist($note);
			} else {
				$note = $id;
			}
			$note->setText($data['text']);
			$note->setTitle($data['title']);
			$this->em->flush();
			if (!$id) {
				$this->app->setGlobal('game-version', $version);
				$this->app->setGlobal('game-updated', $now->format('Y-m-d'));
			}
			$this->addFlash('notice', 'Update note created.');
			return $this->redirectToRoute('maf_chars');
		}


		return $this->render('GM/update.html.twig', [
			'form'=>$form->createView()
		]);
	}
}
