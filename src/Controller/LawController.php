<?php

namespace App\Controller;

use App\Entity\Association;
use App\Entity\Character;
use App\Entity\Law;
use App\Entity\LawType;
use App\Entity\Realm;

use App\Form\AreYouSureType;
use App\Form\LawTypeSelectType;
use App\Form\LawEditType;

use App\Service\Dispatcher\AssociationDispatcher;
use App\Service\Dispatcher\Dispatcher;
use App\Service\LawManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LawController extends AbstractController {
	public function __construct(
		private Dispatcher $disp,
		private AssociationDispatcher $assocDisp,
		private EntityManagerInterface $em,
		private LawManager $lawMan,
		private TranslatorInterface $trans) {
	}

	private function gateway($test, $secondary = null) {
		return $this->disp->gateway($test, false, true, false, $secondary);
	}

	private function assocGateway($test, $secondary = null) {
		return $this->assocDisp->gateway($test, false, true, false, $secondary);
	}

	#[Route ('/laws/r{realm}', name:'maf_realm_laws', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/r{realm}/', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/a{assoc}', name:'maf_assoc_laws', requirements:['assoc'=>'\d+'])]
	#[Route ('/laws/a{assoc}/', requirements:['assoc'=>'\d+'])]
	public function lawsAction(Request $request, ?Realm $realm=null, ?Association $assoc=null): RedirectResponse|Response {
		if (!$realm && !$assoc) {
			$this->addFlash('error', $this->trans->trans('law.route.list.noorg', [], 'orgs'));
			return $this->redirectToRoute('maf_actions');
		}
		if ($request->get('_route') === 'maf_realm_laws') {
			$char = $this->gateway('hierarchyRealmLawsTest', $realm);
		} else {
			$char = $this->assocGateway('assocLawsTest', $assoc);
		}
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		$change = false;
		if ($realm) {
			$org = $realm;
			$update = 'maf_realm_laws_update';
			$new = 'maf_realm_laws_new';
			$type = 'realm';
			foreach ($realm->getPositions() as $pos) {
				if ($pos->getRuler() && $pos->getHolders()->contains($char)) {
					$change = true;
					break;
				} elseif ($pos->getLegislative() && $pos->getHolders()->contains($char)) {
					$change = true;
					break;
				}
			}
		} else {
			$org = $assoc;
			$mbr = $assoc->findMember($char);
			if ($rank = $mbr->getRank()) {
				if ($rank->isOwner()) {
					$change = true;
				}
			}
			$update = 'maf_assoc_laws_update';
			$new = 'maf_assoc_laws_new';
			$type = 'assoc';
		}

		#TODO: Add inactive laws display.
		return $this->render('Law/lawsList.html.twig', [
			'org' => $org,
			'active' => $org->findActiveLaws(),
			'update' => $update,
			'orgType' => $type,
			'change' => $change,
			'new' => $new
		]);
	}

	#[Route ('/laws/r{realm}/new', name:'maf_realm_laws_new', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/a{assoc}/new', name:'maf_assoc_laws_new', requirements:['assoc'=>'\d+'])]
	public function newLawAction(Request $request, ?Realm $realm=null, ?Association $assoc=null): RedirectResponse|Response {
		if ($request->get('_route') === 'maf_realm_laws_new') {
			$char = $this->gateway('hierarchyRealmLawNewTest', $realm);
			$rCheck = true;
			$aCheck = false;
		} else {
			$char = $this->assocGateway('assocLawNewTest', $assoc);
			$rCheck = false;
			$aCheck = true;
		}
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}
		if ($rCheck) {
			$org = $realm;
			$type = 'realm';
		} elseif ($aCheck) {
			$org = $assoc;
			$type = 'assoc';
		}

		$form = $this->createForm(LawTypeSelectType::class, null, ['types' => $this->em->getRepository(LawType::class)->findBy(['category'=>$type])]);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$target = $form->get('target')->getData();
			if ($realm) {
				return $this->redirectToRoute('maf_realm_laws_finalize', ['realm'=>$realm->getId(), 'type'=>$target->getId()]);
			} else {
				return $this->redirectToRoute('maf_assoc_laws_finalize', ['assoc'=>$assoc->getId(), 'type'=>$target->getId()]);
			}
		}

		return $this->render('Law/new.html.twig', [
			'org' => $org,
			'form' => $form->createView(),
		]);
	}

	#[Route ('/laws/repeal/{law}', name:'maf_law_repeal', requirements:['law'=>'\d+'])]
	public function repealAction(Request $request, Law $law): RedirectResponse|Response {
		$char = $this->gateway('lawRepealTest', $law);
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['sure']) {
				$this->lawMan->repealLaw($law, $char);
				$this->addFlash('notice', $this->trans->trans('law.route.repeal.success', [], 'orgs'));
				if ($law->getRealm()) {
					return $this->redirectToRoute('maf_realm_laws', ['realm'=>$law->getRealm()->getId()]);
				}
				return $this->redirectToRoute('maf_assoc_laws', ['assoc'=>$law->getAssociation()->getId()]);
			}
		}

		return $this->render('Law/repeal.html.twig', [
			'law' => $law,
			'form' => $form->createView(),
			'org' => $law->getOrg()
		]);
	}

	#[Route ('/laws/r{realm}/{type}', name:'maf_realm_laws_finalize', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/r{realm}/{type}/', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/r{realm}/{type}/{law}', name:'maf_realm_laws_update', requirements:['realm'=>'\d+'])]
	#[Route ('/laws/a{assoc}/{type}', name:'maf_assoc_laws_finalize', requirements:['assoc'=>'\d+'])]
	#[Route ('/laws/a{assoc}/{type}/', requirements:['assoc'=>'\d+'])]
	#[Route ('/laws/a{assoc}/{type}/{law}', name:'maf_assoc_laws_update', requirements:['assoc'=>'\d+'])]
	public function finalizeLawAction(Request $request, ?Realm $realm=null, ?Association $assoc=null, ?Law $law=null, ?LawType $type=null): RedirectResponse|Response {
		if (in_array($request->get('_route'), ['maf_realm_laws_finalize', 'maf_realm_laws_update'])) {
			$char = $this->gateway('hierarchyRealmLawNewTest', $realm);
			$rCheck = true;
			$aCheck = false;
		} else {
			$char = $this->assocGateway('assocLawNewTest', $assoc);
			$rCheck = false;
			$aCheck = true;
		}
		if (!($char instanceof Character)) {
			return $this->redirectToRoute($char);
		}

		if ($law && $type !== $law->getType()) {
			$this->addFlash('error', $this->trans->trans('unavailable.badlawtype'));
			if ($rCheck) {
				return $this->redirectToRoute('maf_realm_laws', ['realm'=>$realm->getId()]);
			} else {
				return $this->redirectToRoute('maf_assoc_laws', ['assoc'=>$assoc->getId()]);
			}
		}
		$settlements = false;
		if ($rCheck) {
			$org = $realm;
			$settlements = $realm->findTerritory();
		} elseif ($aCheck) {
			$org = $assoc;
		}
		$lawMan = $this->lawMan;
		if ($type->getName() == 'realmFaith') {
			$faiths = new ArrayCollection();
			$query = $this->em->createQuery('SELECT a FROM App\Entity\Association a WHERE a.faith_name is not null and a.follower_name is not null');
			$all = $query->getResult();
			foreach ($all as $each) {
				if ($each->isPublic()) {
					$faiths->add($each);
				}
			}
			if ($char->getFaith() && !$faiths->contains($char->getFaith())) {
				$faiths->add($char->getFaith());
			}
		} else {
			$faiths = null;
		}

		$form = $this->createForm(LawEditType::class, null, ['type' => $type, 'law' => $law, 'choices' => $lawMan->choices, 'settlements' => $settlements, 'faiths'=>$faiths]);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['faith'] = $form->get('faith')->getData();
			$data['settlement'] = $form->get('settlement')->getData();
			#updateLaw($org, $type, $setting, $title, $description = null, Character $character, $allowed, $mandatory, $cascades, $sol, $flush=true)
			$result = $this->lawMan->updateLaw($org, $type, $data['value'], $data['title'], $data['description'], $char, $data['mandatory'], $data['cascades'], $data['sol'], $data['settlement'], $law, true, $data['faith']);
			if ($result instanceof Law) {
				$this->addFlash('error', $this->trans->trans('law.form.edit.success', [], 'orgs'));
				# These return a different redirect due to how the route is built. if you use the other ones ($this->redirectToRoute) Symfony complains that the controller isn't returning a response.
				if ($rCheck) {
					return new RedirectResponse($this->generateUrl('maf_realm_laws', ['realm'=>$realm->getId()]).'#'.$result->getId());
				} else {
					return new RedirectResponse($this->generateUrl('maf_assoc_laws', ['assoc'=>$assoc->getId()]).'#'.$result->getId());
				}
			} else {
				$this->addFlash('error', $this->trans->trans('law.form.edit.fail'.$result[1], [], 'orgs'));
			}
		}

		return $this->render('Law/edit.html.twig', [
			'org' => $org,
			'type' => $type,
			'form' => $form->createView(),
			'law' => $law,
		]);
	}

}
