<?php

namespace App\Controller;

use App\Entity\AspectType;
use App\Entity\Association;
use App\Entity\AssociationDeity;
use App\Entity\AssociationMember;
use App\Entity\AssociationRank;
use App\Entity\AssociationType;
use App\Entity\Character;
use App\Entity\Deity;

use App\Form\AreYouSureType;
use App\Form\AssocCreationType;
use App\Form\AssocDeityType;
use App\Form\AssocDeityUpdateType;
use App\Form\AssocDeityWordsType;
use App\Form\AssocUpdateType;
use App\Form\AssocManageMemberType;
use App\Form\AssocCreateRankType;
use App\Form\AssocJoinType;

use App\Service\AppState;
use App\Service\AssociationManager;
use App\Service\DescriptionManager;
use App\Service\Dispatcher\AssociationDispatcher;
use App\Service\GameRequestManager;
use App\Service\History;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssociationController extends AbstractController {
	public function __construct(
		private AssociationManager $am,
		private AssociationDispatcher $disp,
		private EntityManagerInterface $em,
		private TranslatorInterface $trans) {
	}
	
	private function gateway($test, $secondary = null): string|Character {
		return $this->disp->gateway($test, false, true, false, $secondary);
	}

	#[Route('/assoc/{id}', name:'maf_assoc', requirements: ['id'=>'\d+'])]
	public function viewAction(AppState $app, Association $id): Response {
		$assoc = $id;
		$details = false;
		$owner = false;
		$public = false;
		$char = $app->getCharacter(false, true, true);
		if ($char instanceof Character) {
			if ($member = $this->am->findMember($id, $char)) {
				$details = true;
				$public = true;
				$rank = $member->getRank();
				if ($rank && $rank->getOwner()) {
					$owner = true;
				}
			}
		}
		if (!$public && $assoc->isPublic()) {
			$public = true;
		}

		return $this->render('Assoc/view.html.twig', [
			'assoc' => $assoc,
			'public' => $public,
			'details' => $details,
			'owner' => $owner
		]);
	}

	#[Route('/assoc/create', name:'maf_assoc_create')]
	public function createAction(Request $request): RedirectResponse|Response {
		$char = $this->gateway('assocCreateTest');
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$form = $this->createForm(AssocCreationType::class, null, ['types'=>$this->em->getRepository(AssociationType::class)->findAll(), 'assocs'=>$char->findSubcreateableAssociations()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['type'] = $form->get('type')->getdata();
			$data['superior'] = $form->get('superior')->getdata();
			$form->getExtraData();

			$place = $char->getInsidePlace();
			$this->am->create($data, $place, $char);
			# No flush needed, this->am flushes.
			$this->addFlash('notice', $this->trans->trans('assoc.route.new.created', [], 'orgs'));
			return $this->redirectToRoute('maf_politics_assocs');
		}
		return $this->render('Assoc/create.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/{id}/update', name:'maf_assoc_update', requirements: ['id'=>'\d+'])]
	public function updateAction(Association $id, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocUpdateTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$form = $this->createForm(AssocUpdateType::class, null, ['types'=>$this->em->getRepository(AssociationType::class)->findAll(), 'assocs'=>$char->findSubcreateableAssociations($assoc), 'me'=>$assoc]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['type'] = $form->get('type')->getData();
			$data['superior'] = $form->get('superior')->getData();
			$this->am->update($assoc, $form->getData(), $char);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('assoc.route.updated.success', [], 'orgs'));
			return $this->redirectToRoute('maf_politics_assocs');
		}
		return $this->render('Assoc/update.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/{id}/deities', name:'maf_assoc_deities', requirements: ['id'=>'\d+'])]
	public function assocDeitiesAction(Association $id): Response {
		$assoc = $id;
		$char = $this->gateway('assocDeitiesMineTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		
		$owner = false;
		if ($member = $this->am->findMember($assoc, $char)) {
			if ($rank = $member->getRank()) {
				$owner = $rank->getOwner();
			}
		}

		return $this->render('Assoc/viewDeities.html.twig', [
			'deities' => $assoc->getDeities(),
			'owner' => $owner,
			'assoc' => $assoc
		]);
	}

	#[Route('/assoc/{id}/allDeities', name:'maf_assoc_all_deities', requirements: ['id'=>'\d+'])]
	public function allDeitiesAction(Association $id): Response {
		
		$char = $this->gateway('assocDeitiesAllTest', $id);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		return $this->render('Assoc/viewAllDeities.html.twig', [
			'deities' => $this->em->getRepository(Deity::class)->findAll(),
			'assoc' => $id
		]);
	}

	#[Route('/assoc/deity/{id}', name:'maf_deity', requirements: ['id'=>'\d+'])]
	public function deityAction(Deity $id): Response {
		return $this->render('Assoc/deity.html.twig', [
			'deity' => $id
		]);
	}

	#[Route('/assoc/{id}/newDeity', name:'maf_assoc_deity_new', requirements: ['id'=>'\d+'])]
	public function newDeityAction(Association $id, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocNewDeityTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		
		$form = $this->createForm(AssocDeityType::class, null, ['aspects'=>$this->em->getRepository(AspectType::class)->findAll()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['aspects'] = $form->get('aspects')->getData();

			$this->am->newDeity($assoc, $char, $data);
			# No flush needed, this->am flushes.
			$this->addFlash('notice', $this->trans->trans('assoc.route.deity.created', [], 'orgs'));
			return $this->redirectToRoute('maf_assoc_deities', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/newDeity.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/assoc/{id}/updateDeity/{deity}', name:'maf_assoc_deity_update', requirements: ['id'=>'\d+', 'deity'=>'\d+'])]
	public function updateDeityAction(History $hist, Association $id, Deity $deity, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocUpdateDeityTest', [$assoc, $deity]);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		
		$form = $this->createForm(AssocDeityUpdateType::class, null, ['deity'=>$deity, 'aspects'=>$this->em->getRepository(AspectType::class)->findAll()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['aspects'] = $form->get('aspects')->getData();

			$this->am->updateDeity($deity, $char, $data);
			foreach ($deity->getAssociations() as $bassoc) {
				if ($bassoc !== $assoc) {
					$hist->logEvent(
						$bassoc->getAssociation(),
						'event.assoc.deity.changeother',
						array('%link-deity%'=>$deity->getId(), '%link-assoc%'=>$assoc->getId())
					);
				} else {
					$hist->logEvent(
						$bassoc->getAssociation(),
						'event.assoc.deity.changeself',
						array('%link-deity%'=>$deity->getId())
					);
				}
			}
			# No flush needed, this->am flushes.
			$this->addFlash('notice', $this->trans->trans('assoc.route.deity.updated', [], 'orgs'));
			return $this->redirectToRoute('maf_assoc_deities', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/updateDeity.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/assoc/{id}/wordsDeity/{deity}', name:'maf_assoc_deity_words', requirements: ['id'=>'\d+', 'deity'=>'\d+'])]
	public function wordsDeityAction(History $hist, Association $id, AssociationDeity $deity, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocWordsDeityTest', [$assoc, $deity]);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		
		$form = $this->createForm(AssocDeityWordsType::class, null, ['deity'=>$deity]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($deity->getWords() !== $data['words']) {
				$deity->setWords($data['words']);
			}
			$deity->setWordsTimestamp(new DateTime("now"));
			$deity->setWordsFrom($char);
			$hist->logEvent(
				$assoc,
				'event.assoc.deity.newwords',
				array('%link-deity%'=>$deity->getDeity()->getId()),
				History::LOW
			);
			$this->em->flush();

			$this->addFlash('notice', $this->trans->trans('assoc.route.deity.updated', [], 'orgs'));
			return $this->redirectToRoute('maf_assoc_deities', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/wordsDeity.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/assoc/{id}/addDeity/{deity}', name:'maf_assoc_deities_add', requirements: ['id'=>'\d+', 'deity'=>'\d+'])]
	public function addDeityAction(Association $id, Deity $deity): RedirectResponse {
		$assoc = $id;
		$char = $this->gateway('assocAddDeityTest', [$assoc, $deity]);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$this->am->addDeity($assoc, $deity, $char);

		$this->addFlash('notice', $this->trans->trans('assoc.route.deity.added', ['%link-deity%'=>$deity->getId()], 'orgs'));
		return $this->redirectToRoute('maf_assoc_deities', ['id'=>$assoc->getId()]);
	}

	#[Route('/assoc/{id}/removeDeity/{deity}', name:'maf_assoc_deities_remove', requirements: ['id'=>'\d+', 'deity'=>'\d+'])]
	public function removeDeityAction(Association $id, Deity $deity): RedirectResponse {
		$assoc = $id;
		$char = $this->gateway('assocRemoveDeityTest', [$assoc, $deity]);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$this->am->removeDeity($assoc, $deity, $char);

		$this->addFlash('notice', $this->trans->trans('assoc.route.deity.removed', ['%link-deity%'=>$deity->getId()], 'orgs'));
		return $this->redirectToRoute('maf_assoc_deities', ['id'=>$assoc->getId()]);
	}

	#[Route('/assoc/{id}/adoptDeity/{deity}', name:'maf_assoc_deities_adopt', requirements: ['id'=>'\d+', 'deity'=>'\d+'])]
	public function adoptDeityAction(Association $id, Deity $deity): RedirectResponse {
		$assoc = $id;
		$char = $this->gateway('assocAdoptDeityTest', [$assoc, $deity]);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$this->am->adoptDeity($assoc, $deity, $char);

		$this->addFlash('notice', $this->trans->trans('assoc.route.deity.adopted', ['%link-deity%'=>$deity->getId()], 'orgs'));
		return $this->redirectToRoute('maf_assoc_deities', ['id'=>$assoc->getId()]);
	}

	#[Route('/assoc/{id}/viewRanks', name:'maf_assoc_viewranks', requirements: ['id'=>'\d+'])]
	public function viewRanksAction(Association $id): Response {
		$assoc = $id;
		$char = $this->gateway('assocViewRanksTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$member = $this->am->findMember($assoc, $char);
		$rank = false;
		$canManage = false;
		$allRanks = false;
		$mngRanks = false;
		if ($member) {
			$rank = $member->getRank();
			if ($rank) {
				$allRanks = $member->getRank()->findAllKnownRanks();
				$mngRanks = $member->getRank()->findManageableSubordinates();
				$canManage = $rank->canManage();
				$rank = true; # Flip this back to boolean so we can resuse the below bit for those that don't hold ranks as well, without doing costly object comparisons.
			} else {
				$rank = false;
			}
		}
		if (!$member || !$rank) {
			$allRanks = $assoc->findPubliclyVisibleRanks();
			$mngRanks = new ArrayCollection; # No rank, can't manage any. Return empty collection.
		}

		return $this->render('Assoc/viewRanks.html.twig', [
			'assoc' => $assoc,
			'member' => $member,
			'ranks' => $allRanks,
			'manageable' => $mngRanks,
			'canManage' => $canManage
		]);
	}

	#[Route('/assoc/{id}/viewMembers', name:'maf_assoc_viewmembers', requirements: ['id'=>'\d+'])]
	public function viewMembersAction(Association $id): Response {
		$assoc = $id;
		$char = $this->gateway('assocViewMembersTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$member = $this->am->findMember($assoc, $char);
		$rank = false;
		$canManage = false;
		$mngRanks = false;
		if ($member) {
			$rank = $member->getRank();
			if ($rank) {
				$mngRanks = $member->getRank()->findManageableSubordinates();
				$canManage = $rank->canManage();
				$rank = true; # Flip this back to boolean so we can resuse the below bit for those that don't hold ranks as well, without doing costly object comparisons.
			} else {
				$rank = false;
			}
		}
		if (!$member || !$rank) {
			$mngRanks = new ArrayCollection; # No rank, can't manage any. Return empty collection.
		}

		return $this->render('Assoc/viewMembers.html.twig', [
			'assoc' => $assoc,
			'myMbr' => $member,
			'allMbrs' => $assoc->getMembers(),
			'manageable' => $mngRanks,
			'canManage' => $canManage
		]);
	}

	#[Route('/assoc/{id}/graphRanks', name:'maf_assoc_graphranks', requirements: ['id'=>'\d+'])]
	public function graphRanksAction(Association $id): Response {
		$assoc = $id;
		$char = $this->gateway('assocGraphRanksTest', $assoc); #Same test is deliberate.
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		
		$member = $this->am->findMember($assoc, $char);
		$rank = false;
		$me = null;
		if ($member) {
			$rank = $member->getRank();
			if ($rank) {
				$allRanks = $member->getRank()->findAllKnownRanks();
				$me = $rank;
				$rank = true; # Flip this back to boolean so we can resuse the below bit for those that don't hold ranks as well, without doing costly object comparisons.
			} else {
				$rank = false;
			}
		}
		if (!$member || !$rank) {
			$allRanks = $assoc->findPubliclyVisibleRanks();
		}

	   	$descriptorspec = array(
			   0 => array("pipe", "r"),  // stdin
			   1 => array("pipe", "w"),  // stdout
			   2 => array("pipe", "w") // stderr
			);

   		$process = proc_open('dot -Tsvg', $descriptorspec, $pipes, '/tmp', array());

	   	if (is_resource($process)) {
	   		$dot = $this->renderView('Assoc/graphRanks.dot.twig', array('hierarchy'=>$allRanks, 'me'=>$me));

	   		fwrite($pipes[0], $dot);
	   		fclose($pipes[0]);

	   		$svg = stream_get_contents($pipes[1]);
	   		fclose($pipes[1]);

	   		proc_close($process);
	   	}

		return $this->render('Assoc/graphRanks.html.twig', [
			'svg'=>$svg
		]);
	}
	
	#[Route('/assoc/{id}/createRank', name:'maf_assoc_createrank', requirements: ['id'=>'\d+'])]
	public function createRankAction(DescriptionManager $dm, Association $id, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocCreateRankTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		$member = $this->am->findMember($assoc, $char);
		$myRank = $member->getRank();
		if ($myRank->isOwner()) {
			$ranks = $assoc->getRanks();
		} else {
			$ranks = $myRank->findAllKnownSubordinates();
			$ranks->add($myRank);
		}

		$form = $this->createForm(AssocCreateRankType::class, null, ['ranks'=>$ranks, 'me'=>false]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$superior = $form->get('superior')->getData();

			$rank = $this->am->newRank($assoc, $myRank, $data['name'], $data['viewAll'], $data['viewUp'], $data['viewDown'], $data['viewSelf'], $superior, $data['build'], $data['createSubs'], $data['manager'], $data['createAssocs']);
			if (!$rank->getDescription() || $rank->getDescription()->getText() !== $data['description']) {
				$dm->newDescription($rank, $data['description'], $char);
			}
			# No flush needed, this->am and DescMan flushes.
			$this->addFlash('notice', $this->trans->trans('assoc.route.rank.created', array(), 'orgs'));
			return $this->redirectToRoute('maf_assoc_viewranks', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/createRank.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/manageRank/{rank}', name:'maf_assoc_managerank', requirements: ['rank'=>'\d+'])]
	public function manageRankAction(DescriptionManager $dm, AssociationRank $rank, Request $request): RedirectResponse|Response {
		$char = $this->gateway('assocManageRankTest', $rank);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		
		$assoc = $rank->getAssociation();
		$member = $this->am->findMember($assoc, $char);
		$myRank = $member->getRank();
		if ($myRank->isOwner()) {
			$ranks = $assoc->getRanks();
		} else {
			$ranks = $myRank->findAllKnownSubordinates();
			$ranks->add($myRank);
		}

		$form = $this->createForm(AssocCreateRankType::class, null, ['ranks'=>$ranks, 'me'=>$rank]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($rank === $myRank && $myRank->getOwner()) {
				$owner = true;
			} else {
				$owner = false;
			}
			$superior = $form->get('superior')->getData();

			$this->am->updateRank($myRank, $rank, $data['name'], $data['viewAll'], $data['viewUp'], $data['viewDown'], $data['viewSelf'], $superior, $data['build'], $data['createSubs'], $data['manager'], $data['createAssocs'], $owner);
			if (!$rank->getDescription() || $rank->getDescription()->getText() !== $data['description']) {
				$dm->newDescription($rank, $data['description'], $char);
			}
			# No flush needed, this->am and DescMan flushes.
			$this->addFlash('notice', $this->trans->trans('assoc.route.rank.updated', array(), 'orgs'));
			return $this->redirectToRoute('maf_assoc_viewranks', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/manageRank.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/manageMember/{mbr}', name:'maf_assoc_managemember', requirements: ['mbr'=>'\d+'])]
	public function manageMemberAction(AssociationMember $mbr, Request $request): RedirectResponse|Response {
		$char = $this->gateway('assocManageMemberTest', $mbr);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		
		$assoc = $mbr->getAssociation();
		$member = $this->am->findMember($assoc, $char);
		$myRank = $member->getRank();
		$subordinates = $myRank->findManageableSubordinates();

		$form = $this->createForm(AssocManageMemberType::class, null, ['ranks'=>$subordinates, 'me'=>$mbr]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$newRank = $form->get('rank')->getData();
			if ($newRank !== $mbr->getRank() && $subordinates->contains($newRank)) {
				$this->am->updateMember($assoc, $newRank, $mbr->getCharacter());
			}
			$this->addFlash('notice', $this->trans->trans('assoc.route.manageMember.updated', array(), 'orgs'));
			return $this->redirectToRoute('maf_assoc_viewmembers', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/manageMember.html.twig', [
			'form' => $form->createView()
		]);
	}
	
	#[Route('/assoc/evictMember/{mbr}', name:'maf_assoc_evictmember', requirements: ['mbr'=>'\d+'])]
	public function evictMemberAction(AssociationMember $mbr, Request $request): RedirectResponse|Response {
		$char = $this->gateway('assocEvictMemberTest', $mbr);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		
		$assoc = $mbr->getAssociation();

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['sure']) {
				$this->am->removeMember($assoc, $mbr->getCharacter());
			}
			$this->addFlash('notice', $this->trans->trans('assoc.route.evictMember.success', array(), 'orgs'));
			return $this->redirectToRoute('maf_assoc_viewmembers', array('id'=>$assoc->getId()));
		}
		return $this->render('Assoc/evictMember.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/{id}/join', name:'maf_assoc_join', requirements: ['id'=>'\d+'])]
	public function joinAction(GameRequestManager $grm, Association $id, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocJoinTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$form = $this->createForm(AssocJoinType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['sure']) {
				$grm->newRequestFromCharacterToAssociation('assoc.join', null, null, null, $data['subject'], $data['text'], $char, $assoc);
				$this->addFlash('notice', $this->trans->trans('assoc.route.join.success', ['%name%'=>$assoc->getName()], 'orgs'));
				return $this->redirectToRoute('maf_assoc', array('id'=>$assoc->getId()));
			} else {
				$this->addFlash('notice', $this->trans->trans('assoc.route.member.joinfail', array(), 'orgs'));
				return $this->redirectToRoute('maf_assoc_join', ['id'=>$assoc->getId()]);
			}
		}
		return $this->render('Assoc/join.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route('/assoc/{id}/leave', name:'maf_assoc_leave', requirements: ['id'=>'\d+'])]
	public function leaveAction(Association $id, Request $request): RedirectResponse|Response {
		$assoc = $id;
		$char = $this->gateway('assocLeaveTest', $assoc);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['sure']) {
				$this->am->removeMember($assoc, $char);
				$this->addFlash('notice', $this->trans->trans('assoc.route.leave.success', ['%name%'=>$assoc->getName()], 'orgs'));
				return $this->redirectToRoute('maf_place_actionable');
			}
		}
		return $this->render('Assoc/leave.html.twig', [
			'form' => $form->createView(),
			'assoc' => $assoc,
		]);
	}

}
