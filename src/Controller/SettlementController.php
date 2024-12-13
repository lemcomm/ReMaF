<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Permission;
use App\Entity\ResourceType;
use App\Entity\Settlement;
use App\Entity\Unit;
use App\Form\AssocSelectType;
use App\Form\SettlementAbandonType;
use App\Form\SettlementPermissionsSetType;
use App\Form\DescriptionNewType;
use App\Service\AppState;
use App\Service\DescriptionManager;
use App\Service\Dispatcher\Dispatcher;
use App\Service\Economy;
use App\Service\Geography;
use App\Service\History;

use App\Service\Interactions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettlementController extends AbstractController {
	public function __construct(
		private Dispatcher $disp,
		private Economy $econ,
		private EntityManagerInterface $em,
		private Interactions $interactions,
		private TranslatorInterface $trans) {
	}
	
	#[Route('/settlement/{id}', name:'maf_settlement', requirements:['id'=>'\d+'])]
	public function indexAction(AppState $app, Geography $geo, Settlement $id): Response {
		$em = $this->em;
		$settlement = $id; // we use $id because it's hardcoded in the linkhelper

		// check if we should be able to see details
		$character = $app->getCharacter(false);
		if ($character instanceof Character) {
			$heralds = $character->getAvailableEntourageOfType('Herald')->count();
		} else {
			$heralds = 0;
			$character = NULL; //Override Appstate's return so we don't need to tinker with the rest of this function.
		}
		$details = $this->interactions->characterViewDetails($character, $settlement);
		if (isset($details['startme'])) {
			// still in start mode
			$form_map = $this->createFormBuilder()->add('settlement_id', HiddenType::class, array(
				'constraints' => array() // TODO: constrained to available settlements
			))->getForm();
			$details['startme'] = $form_map->createView();
		}

		// FIXME: shouldn't this use geodata?
		$query = $em->createQuery('SELECT s.id, s.name, ST_Distance(y.center, x.center) AS distance, ST_Azimuth(y.center, x.center) AS direction
			FROM App:Settlement s JOIN s.geo_data x, App:GeoData y WHERE y=:here AND ST_Touches(x.poly, y.poly)=true');
		$query->setParameter('here', $settlement);
		$neighbours = $query->getArrayResult();

		$militia = [];
		$recruits = 0;
		if ($details['spy'] || ($settlement->getOwner() == $character || $settlement->getSteward() == $character)) {
			foreach ($settlement->getUnits() as $unit) {
				if ($unit->isLocal()) {
					foreach ($unit->getActiveSoldiersByType() as $key=>$type) {
						if (array_key_exists($key, $militia)) {
							$militia[$key] += $type;
						} else {
							$militia[$key] = $type;
						}
					}
				}
				$recruits += $unit->getRecruits()->count();
				#$militia = $settlement->getActiveMilitiaByType();
			}
		} else {
			$militia = null;
		}

		// FIXME: better: some trend analysis
		$query = $em->createQuery('SELECT s.population as pop FROM App:StatisticSettlement s WHERE s.settlement = :here ORDER BY s.cycle DESC');
		$query->setParameter('here', $settlement);
		$query->setMaxResults(3);
		$data = $query->getArrayResult();
		if (isset($data[2])) {
			$popchange = $data[0]['pop'] - $data[2]['pop'];
		} else {
			$popchange = 0;
		}

		$corruption = $this->econ->calculateCorruption($settlement);
		if ($character != $settlement->getOwner()) {
			// rounding this to full percents to fuzz it a bit, to prevent people from understanding which characters belong to the same player by corruption values
			$corruption = round($corruption*100)/100;
		}

		$economy = array();
		$all = $em->getRepository(ResourceType::class)->findAll();
		foreach ($all as $resource) {
			$local = $settlement->findResource($resource);
			if ($local) {
				$base = $local->getAmount();
				$storage = $local->getStorage();
			} else {
				$base = 0;
				$storage = 0;
			}

			if ($details['spot'] && ($details['prospector'] || $settlement->getOwner() == $character || $settlement->getSteward() == $character)) {
				// TODO: we should fuzz corruption a bit to prevent people spotting same users by comparing corruption
				$full_demand = $this->econ->ResourceDemand($settlement, $resource, true);
				$demand = 0;
				foreach ($full_demand as $value) {
					$demand += $value;
				}
				$production = $this->econ->ResourceProduction($settlement, $resource);
				$base_production = $this->econ->ResourceProduction($settlement, $resource, true);
			} else {
				$full_demand = array('base'=>0, 'corruption'=>0, 'operation'=>0, 'construction'=>0);
				$demand = $production = $base_production = 0;
			}

			$economy[] = array(
				'name' => $resource->getName(),
				'base' => $base,
				'storage' => $storage,
				'base_production' => $base_production,
				'total_production' => $production,
				'tradebalance' => $this->econ->TradeBalance($settlement, $resource),
				'base_demand' => $full_demand['base'],
				'corruption' => $full_demand['corruption'],
				'total_demand' => $demand,
				'building_prod' => $production - $base_production,
				'building_demand' => $full_demand['operation'],
				'building_construction' => $full_demand['construction']
			);
			if ($resource->getName()=="food") {
				if ($demand>0) {
					$FoodSupply = $this->econ->ResourceAvailable($settlement, $resource) / $demand;
				} else {
					$FoodSupply = 1.0;
				}
			}
		}

		return $this->render('Settlement/settlement.html.twig', [
			'settlement' => $settlement,
			'familiarity' => $character?$geo->findRegionFamiliarityLevel($character, $settlement->getGeoData()):false,
			'details' => $details,
			'popchange' => $popchange,
			'foodsupply' => $FoodSupply,
			'economy' => $economy,
			'corruption' => $corruption,
			'area' => $geo->calculateArea($settlement->getGeoData()),
			'density' => $geo->calculatePopulationDensity($settlement),
			'regionpoly'=> $geo->findRegionPolygon($settlement),
			'neighbours' => $neighbours,
			'militia' => $militia,
			'recruits' => $recruits,
			'security' => round(($this->econ->EconomicSecurity($settlement)-1.0)*16),
			'heralds' => $heralds
		]);
	}

	#[Route('/settlement/{id}/permissions', name:'maf_settlement_permissions', requirements:['id'=>'\d+'])]
	public function permissionsAction(Settlement $id, Request $request): RedirectResponse|Response {
		$character = $this->disp->gateway('controlPermissionsTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		$settlement = $id;
		if ($settlement->getOwner() === $character || $settlement->getSteward() === $character) {
			$lord = true;
			$original_permissions = clone $settlement->getPermissions();
			$page = 'Settlement/permissions.html.twig';
		} else {
			$lord = false;
			$original_permissions = clone $settlement->getOccupationPermissions();
			$page = 'Settlement/occupationPermissions.html.twig';
		}

		$form = $this->createForm(SettlementPermissionsSetType::class, $settlement, ['me' => $character, 'lord'=>$lord, 's'=>$settlement]);
		// FIXME: right now, nothing happens if we disallow thralls while having some
		//			 something should happen - set them free? most should vanish, but some stay as peasants?
		//			 but do we want large numbers of people to simply disappear? where will they go?
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			# TODO: This can be combined with the code in PlaceController as part of a service function.
			if ($lord) {
				foreach ($settlement->getPermissions() as $permission) {
					$permission->setValueRemaining($permission->getValue());
					if (!$permission->getId()) {
						$em->persist($permission);
					}
				}
				foreach ($original_permissions as $orig) {
					if (!$settlement->getPermissions()->contains($orig)) {
						$em->remove($orig);
					} else {
						$em->persist($orig);
					}
				}
			} else {
				foreach ($settlement->getOccupationPermissions() as $permission) {
					$permission->setValueRemaining($permission->getValue());
					if (!$permission->getId()) {
						$em->persist($permission);
					}
				}
				foreach ($original_permissions as $orig) {
					if (!$settlement->getOccupationPermissions()->contains($orig)) {
						$em->remove($orig);
					}
				}
			}
			$em->flush();
			$this->addFlash('notice', $this->trans->trans('control.permissions.success', array(), 'actions'));
			return $this->redirect($request->getUri());
		}

		return $this->render($page, [
			'settlement' => $settlement,
			'permissions' => $em->getRepository(Permission::class)->findBy(['class'=>'settlement']),
			'form' => $form->createView(),
			'lord' => $lord
		]);
	}

	#[Route('/settlement/{id}/quests', name:'maf_settlement_quests', requirements:['id'=>'\d+'])]
	public function questsAction(Settlement $id): RedirectResponse|Response {
		$character = $this->disp->gateway('controlQuestsTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Settlement/quests.html.twig', [
			'quests'=>$id->getQuests(),
			'settlement' => $id
		]);
	}

	#[Route('/settlement/{id}/description', name:'maf_settlement_description', requirements:['id'=>'\d+'])]
	public function descriptionAction(DescriptionManager $dm, Settlement $id, Request $request): RedirectResponse|Response {
		$settlement = $id;
		$character = $this->disp->gateway('controlSettlementDescriptionTest', false, true, false, $settlement);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$desc = $settlement->getDescription();
		$text = $desc?->getText();
		$form = $this->createForm(DescriptionNewType::class, null, ['text'=>$text]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($text != $data['text']) {
				$dm->newDescription($settlement, $data['text'], $character);
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('control.description.success', array(), 'actions'));
			return $this->redirectToRoute('maf_settlement', ['id'=>$settlement->getId()]);
		}
		return $this->render('Settlement/description.html.twig', [
                        'form' => $form->createView(),
			'settlement' => $settlement
                ]);
	}

	#[Route('/settlement/{id}/abandon', name:'maf_settlement_abandon', requirements:['id'=>'\d+'])]
	public function abandonAction(Settlement $id, Request $request): RedirectResponse|Response {
		$character = $this->disp->gateway('controlAbandonTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(SettlementAbandonType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$result = $this->interactions->abandonSettlement($character, $id, $data['keep']);
			if ($result) {
				$this->addFlash('notice', $this->trans->trans('control.abandon.success', [], 'actions'));
				return $this->redirectToRoute('maf_settlement', ['id'=>$id->getId()]);
			}
			# If the form doesn't validate, they don't get here. Thus, no else case.
		}
		return $this->render('Settlement/abandon.html.twig', [
                        'form' => $form->createView(),
			'settlement' => $id
                ]);
	}

	#[Route('/settlement/{id}/supplied', name:'maf_settlement_supplied', requirements:['id'=>'\d+'])]
	public function suppliedAction(Settlement $id): RedirectResponse|Response {
		$character = $this->disp->gateway('controlSuppliedTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Settlement/supplied.html.twig', [
			'units' => $this->econ->FindFeedableUnits($id),
			'settlement' => $id
		]);
	}

	#[Route('/settlement/{id}/supplyCancel/{unit}', name:'maf_settlement_supply_cancel', requirements:['id'=>'\d+', 'unit'=>'\d+'])]
	public function supplyCancelAction(History $history, Settlement $id, Unit $unit): RedirectResponse {
		$character = $this->disp->gateway('controlSuppliedTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$units = $this->econ->FindFeedableUnits($id);
		if ($units->contains($unit) && (!$unit->isLocal() || ($unit->isLocal() && $unit->getSettlement() !== $id))) {
			$unit->setSupplier();
			$this->em->flush();
			$history->logEvent(
				$unit,
				'event.unit.supplies.food.cancel',
				array('%link-settlement%'=>$id->getId()),
				History::MEDIUM, false, 30
			);
			$this->addFlash('notice', $this->trans->trans('control.supply.success', ['%name%'=>$unit->getName()], 'actions'));
		} elseif ($unit->isLocal()) {
			$this->addFlash('notice', $this->trans->trans('control.supply.failure.local', ['%name%'=>$unit->getName()], 'actions'));
		} else {
			$this->addFlash('notice', $this->trans->trans('control.supply.failure.notyours', [], 'actions'));
		}
		return $this->redirectToRoute('maf_settlement_supplied', ['id'=>$id->getId()]);
	}

	#[Route ('/settlement/{id}/faith', name:'maf_settlement_faith', requirements:['id'=>'\d+'])]
	public function faithAction(Request $request, Settlement $id) : RedirectResponse|Response {
		$character = $this->disp->gateway('controlFaithTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$opts = new ArrayCollection();
		foreach($character->findAssociations() as $assoc) {
			if ($assoc->getFaithname() && $assoc->getFollowerName()) {
				$opts->add($assoc);
			}
		}

		$form = $this->createForm(AssocSelectType::class, null, ['assocs' => $opts, 'type' => 'faith', 'me' =>$character]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();
			$character->setFaith($data['target']);
			$this->em->flush();
			if ($data['target']) {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.settlement.success', array("%faith%"=>$data['target']->getFaithName()), 'orgs'));
			} else {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.settlement.success2', array(), 'orgs'));
			}

			return $this->redirectToRoute('maf_actions');
		}

		return $this->render('Character/faith.html.twig', [
			'form'=>$form->createView(),
		]);
	}

}
