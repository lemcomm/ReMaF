<?php

namespace App\Controller;

use App\Service\AppState;
use App\Service\Dispatcher;
use App\Entity\Character;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class BuildingController extends AbstractController {

	private AppState $app;
	private Dispatcher $dispatcher;
	private EntityManagerInterface $em;
	
	public function __construct(AppState $app, Dispatcher $dispatcher, EntityManagerInterface $em) {
		$this->app = $app;
		$this->dispatcher = $dispatcher;
		$this->em = $em;
	}
	
	#[Route ('/building/tavern', name:'maf_building_tavern')]
	public function tavernAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationTavernTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT s FROM App:Settlement s JOIN s.geo_data g, App:GeoData me WHERE ST_Distance(g.center, me.center) < :maxdistance AND me.id = :me AND s != me');
		$query->setParameters(array('me'=>$settlement->getGeoData()->getId(), 'maxdistance'=>20000));
		$nearby_settlements = $query->getResult();

		$query = $em->createQuery('SELECT DISTINCT c FROM App:Character c JOIN c.inside_settlement s JOIN s.geo_data g JOIN c.positions p, App:GeoData me WHERE ST_Distance(g.center, me.center) < :maxdistance AND me.id = :me AND s != me AND c.slumbering = false');
		$query->setParameters(array('me'=>$settlement->getGeoData()->getId(), 'maxdistance'=>20000));
		$nearby_people = $query->getResult();

		return $this->render('Building/tavern.html.twig', [
			'settlement'=>$settlement,
			'nearby_settlements'=>$nearby_settlements,
			'nearby_people'=>$nearby_people
		]);
	}

	#[Route ('/building/library', name:'maf_building_library')]
	public function libraryAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationLibraryTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT s FROM App:Settlement s ORDER BY s.population+s.thralls DESC');
		$query->setMaxResults(5);
		$top_settlements = $query->getResult();

		$cycle = $this->app->getCycle();

		$query = $em->createQuery('SELECT s as stat, r, (s.population + s.area * 10) as size FROM App:StatisticRealm s JOIN s.realm r WHERE s.cycle = :cycle ORDER BY size DESC');
		$query->setParameter('cycle', $cycle);
		$query->setMaxResults(5);
		$top_realms = $query->getResult();


		return $this->render('Building/library.html.twig', [
			'settlement'=>$settlement,
			'top_settlements'=>$top_settlements,
			'top_realms'=>$top_realms
		]);
	}

	#[Route ('/building/map/{map}', name:'maf_building_map')]
	public function mapAction($map) {
		$character = $this->dispatcher->gateway('locationLibraryTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		// TODO: there are several better ways to do it, e.g.:
		// http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php
		// also headers and caching: http://stackoverflow.com/questions/1353850/serve-image-with-php-script-vs-direct-loading-image

		$allowed = array('allrealms.png', 'majorrealms.png', '2ndrealms.png', 'allrealms-thumb.png', 'majorrealms-thumb.png', '2ndrealms-thumb.png');
		if (in_array($map, $allowed)) {
			header('Content-type: image/png');
			if (gethostname() == 'Ghost.local') {
				readfile("/Users/Tom/Documents/BM2/$map");
			} else {
				readfile("/var/www/qgis/maps/$map");
			}
		} else {
			echo "invalid map";
		}

		exit;
	}

	#[Route ('/building/temple', name:'maf_building_temple')]
	public function templeAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationTempleTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$data = array(
			"population"	=> array("label" => "population", "data" => array()),
			"thralls"		=> array("label" => "thralls", "data" => array()),
		);
		$em = $this->em;
		$query = $em->createQuery('SELECT s FROM App:StatisticSettlement s WHERE s.settlement = :me ORDER BY s.cycle DESC');
		$query->setMaxResults(600); // TODO: max two in-game years - for now. No idea how much flot.js can handle.
		$query->setParameter('me', $settlement);
		$current_cycle = intval($this->app->getGlobal('cycle'));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle() - $current_cycle;

			$data["population"]["data"][] = array($cycle, $row->getPopulation());
			$data["thralls"]["data"][] = array($cycle, $row->getThralls());
		}
		return $this->render('Building/temple.html.twig', [
			'settlement'=>$settlement, 'data'=>$data
		]);
	}

	#[Route ('/building/barracks', name:'maf_building_barracks')]
	public function barracksAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationBarracksTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$data = array(
			"militia"		=> array("label" => "militia", "data" => array()),
		);
		$em = $this->em;
		$query = $em->createQuery('SELECT s FROM App:StatisticSettlement s WHERE s.settlement = :me ORDER BY s.cycle DESC');
		$query->setMaxResults(600); // TODO: max two in-game years - for now. No idea how much flot.js can handle.
		$query->setParameter('me', $settlement);
		$current_cycle = intval($this->app->getGlobal('cycle'));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle() - $current_cycle;

			$data["militia"]["data"][] 	= array($cycle, $row->getMilitia());
		}
		return $this->render('Building/barracks.html.twig', [
			'settlement'=>$settlement, 'data'=>$data
		]);
	}

	#[Route ('/building/archeryrange', name:'maf_building_archeryrange')]
	public function archeryRangeAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationArcheryRangeTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Building/archeryrange.html.twig', [
			'settlement'=>$settlement,
		]);
	}
	
	#[Route ('/building/garrison', name:'maf_building_garrison')]
	public function garrisonAction(): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('locationGarrisonTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Building/garrison.html.twig', [
			'settlement'=>$settlement,
		]);
	}

}
