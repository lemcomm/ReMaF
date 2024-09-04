<?php

namespace App\Controller;

use App\Entity\UpdateNote;
use App\Entity\User;
use App\Form\UpdateNoteType;
use App\Service\CommonService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorldForgeController extends AbstractController {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}

	#[Route ('/wf', name:'maf_wf')]
	public function pendingAction(): Response {
		return $this->render('WF/index.html.twig');
	}
}
