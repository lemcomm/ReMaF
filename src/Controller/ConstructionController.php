<?php

namespace App\Controller;

use App\Entity\Character;
use App\Service\Dispatcher\Dispatcher;
use App\Service\Economy;
use App\Service\Geography;
use App\Entity\Building;
use App\Entity\GeoFeature;
use App\Entity\Road;
use App\Form\BuildingconstructionType;
use App\Form\FeatureconstructionType;
use App\Form\RoadconstructionType;
use CrEOF\Geo\WKB\Parser as BinaryParser;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LongitudeOne\Spatial\PHP\Types\Geometry\{Point, LineString};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Router;

class ConstructionController extends AbstractController {

	// FIXME: dispatcher uses permission system, but we need to check again to get the reserve values

	private Dispatcher $dispatcher;
	private Economy $econ;
	private EntityManagerInterface $em;
	private Geography $geo;
	
	public function __construct(Dispatcher $dispatcher, Economy $econ, EntityManagerInterface $em, Geography $geo) {
		$this->dispatcher = $dispatcher;
		$this->econ = $econ;
		$this->em = $em;
		$this->geo = $geo;
	}
	
	#[Route ('/build/roads', name:'maf_construction_roads')]
	public function roadsAction(Request $request): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('economyRoadsTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		// FIXME: This was a hack for alpha, where we didn't pre-populate the geofeatures - probably I can remove it?
/*
		if (!$settlement->getGeoMarker()) {
			$marker = new GeoFeature;
			$hidden = $em->getRepository('App:FeatureType')->findOneByName('settlement');
			if (!$hidden) {
				throw new \Exception('required hidden feature type not found');
			}
			$marker->setName($settlement->getName());
			$marker->setLocation($settlement->getGeoData()->getCenter());
			$marker->setWorkers(0)->setCondition(0)->setActive(true);
			$marker->setType($hidden);
			$marker->setGeoData($settlement->getGeoData());
			$em->persist($marker);
			$settlement->setGeoMarker($marker);
			$em->flush();
		}
*/

		$roadsdata = $this->geo->findSettlementRoads($settlement);
		foreach ($roadsdata as $key=>$data) {
			$mod = $settlement->getGeoData()->getBiome()->getRoadConstruction();
			$roadsdata[$key]['required'] = $this->econ->RoadHoursRequired($data['road'], $data['length'], $mod);
		}
		$form = $this->createForm(RoadconstructionType::class, null, ['settlement' => $settlement, 'roads' => $roadsdata]);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$existing = $data['existing'];
			$new = $data['new'];
			$totalworkers=0;

			if ($existing) {
				foreach ($existing as $id=>$amount) {
					if ($amount>0) { $totalworkers+=$amount; }
				}
				if ($settlement->getAvailableWorkforcePercent() + $settlement->getRoadWorkersPercent() - $totalworkers < 0.0) {
					// bail out - can't assign more than 100%
					$form->addError(new FormError("economy.toomany"));
				} else {
					foreach ($existing as $id=>$amount) {
						// we are also setting 0 values here because they might currently be > 0
						$road = $em->getRepository('App:Road')->find($id);
						if ($road->getQuality()>=5) {
							// max road level: 5
							$amount = 0.0;
						}
						$road->setWorkers(max(0,floatval($amount)));
					}
					$em->flush();
				}
			}

			if ($new && floatval($new['workers'])>0 && $new['from'] && $new['to']) {
				if ($new['from']==$new['to']) {
					$form->addError(new FormError("road.same"));
				} else {
					// verify that we don't already have this identical road :-)
					$exists = false;
					foreach ($roadsdata as $data) {
						$pts = array($data['road']->getWaypoints()->first(), $data['road']->getWaypoints()->last());
						if (in_array($new['from'], $pts) && in_array($new['to'], $pts)) {
							$exists = true;
						}
					}
					if ($exists) {
						$form->addError(new FormError("road.exists"));
					} else {
						$from = $new['from']->getLocation();
						$to = $new['to']->getLocation();
						if (!$from || !$to) {
							$form->addError(new FormError("road.invalid"));
							return $this->redirect($request->getUri());
						}

						$a = abs($from->getX()-$to->getX());
						$b = abs($from->getY()-$to->getY());
						$length = sqrt($a*$a + $b*$b);
						$jitter = max(2,round(sqrt($length/100))); // ensure at least 2 points or it won't be a linestring
						$points = array();
						$points[] = $from;
						$geom = ''; // FIXME: there must be a better way to do this!
						$xdiff = (($to->getX() - $from->getX()) / ($jitter+1));
						$ydiff = (($to->getY() - $from->getY()) / ($jitter+1));
						for ($i=1;$i<=$jitter;$i++) {
							$x = $from->getX() + $i * $xdiff;
							$y = $from->getY() + $i * $ydiff;
							// jitter - max 25% deviation - TODO: this should depend on biome type...
							// TODO: maybe use Perlin noise here so the same road will always jitter in the same way?
							$x += $ydiff * rand(-25, 25)/100;
							$y += $xdiff * rand(-25, 25)/100;
							$points[] = new Point($x, $y);
							if ($geom!='') {
								$geom.=', ';
							}
							$geom.=$x." ".$y;
						}
						$points[] = $to;
						$path = new LineString($points);

						// test if we cross any impassable terrain, or a cliff
						// FIXME: this sometimes results in an error, with $gemo being only 1 point - why?
						$query = $em->createQuery('SELECT ST_Length(ST_Intersection(g.poly, ST_GeomFromText(:path))) as blocked FROM App:GeoData g WHERE g.passable = false AND ST_Intersects(g.poly, ST_GeomFromText(:path))=true');
						$query->setParameter('path', 'LINESTRING('.$geom.')');
						$invalid = $query->getOneOrNullResult();
						if ($invalid && $invalid['blocked']> 5.0) { // small tolerance because otherwise it would sometimes trigger when connecting to docks
							$form->addError(new FormError("road.invalid"));
						} else {
							$road = new Road;
							$road->setQuality(0)->setCondition(0);
							$road->setWorkers(max(0,floatval($new['workers'])));
							$road->setGeoData($settlement->getGeoData());
							$road->addWaypoint($new['from']);
							$road->addWaypoint($new['to']);
							$road->setPath($path);
							// TODO: check for rivers, only go there if we go to a bridge (and never through, even if to a bridge!)
							$em->persist($road);

							$em->flush();
						}
					}
					return $this->redirect($request->getUri());
				}
			}

		}
		return $this->render('Construction/roads.html.twig', [
			'settlement'=>$settlement,
			'roadsdata'=>$roadsdata,
			'regionpoly'=>$this->geo->findRegionPolygon($settlement),
			'buildingworkers'=>$settlement->getBuildingWorkersPercent(),
			'featureworkers'=>$settlement->getFeatureWorkersPercent(),
			'otherworkers'=>1.0-$settlement->getAvailableWorkforcePercent()+$settlement->getRoadWorkersPercent(),
			'form'=>$form->createView()
		]);
	}

	#[Route ('/build/features', name:'maf_construction_features')]
	public function featuresAction(Request $request): RedirectResponse|Response {
		// TODO: add a way to remove / demolish features
		list($character, $settlement) = $this->dispatcher->gateway('economyFeaturesTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		list($features, $active, $building, $workhours) = $this->featureData($settlement);
		$form = $this->createForm(FeatureconstructionType::class, null, ['features' => $features, 'river' => $settlement->getGeoData()->getRiver(), 'coast' => $settlement->getGeoData()->getCoast()]);

		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$existing = $data['existing'];
			$new = $data['new'];
			$totalworkers = 0;

			$em = $this->em;

			if ($existing) {
				foreach ($existing as $id=>$amount) {
					if ($amount>0) { $totalworkers+=$amount; }
				}
				if ($settlement->getAvailableWorkforcePercent() + $settlement->getFeatureWorkersPercent() - $totalworkers < 0.0) {
					$form->addError(new FormError("economy.toomany"));
				} else {
					foreach ($existing as $id=>$value) {
						$feature = $em->getRepository('App:GeoFeature')->find($id);
						if ($feature->getActive()) {
							$feature->setName($value);
						} else {
							// we are also setting 0 values here because they might currently be > 0
							$feature->setWorkers(max(0,floatval($value)));
						}
					}
				}
			} // end existing features

			if ($new['type']) {
				$valid = false;
				if ($new['workers']<=0) {
					$form->addError(new FormError("feature.needworkers"));
				} else {
					if ($settlement->getAvailableWorkforcePercent() + $settlement->getFeatureWorkersPercent(true) < 0.0) {
						$form->addError(new FormError("feature.toomany"));
					} else {
						if (!$new['location_x'] || !$new['location_y']) {
							$form->addError(new FormError("feature.location"));
						} else {
							switch ($new['type']->getName()) {
								case 'docks':
									if (!$settlement->getGeoData()->getCoast()) {
										$form->addError(new FormError("features.nocoast"));
									} else {
										$location = $this->buildDocks($new);
										$valid=true;
									}
									break;
								case 'bridge':
									if (!$settlement->getGeoData()->getRiver()) {
										$form->addError(new FormError("features.noriver"));
									} else {
										$location = $this->buildBridge($new);
										$valid=true;
									}
									break;
								case 'borderpost':
									// TODO: don't allow at rivers - players should use bridges there
									$location = $this->buildBorder($new, $settlement->getGeoData());
									$valid=true;
									break;
								default:
									// check if location is within our settlement polygon
									$location = new Point($new['location_x'], $new['location_y']);

									$within = $this->geo->checkContains($settlement->getGeoData(), $location);
									if ($within) {
										$valid = true;
									} else {
										// TODO: maybe snap it, like we do with rivers above?
										$form->addError(new FormError("feature.outside"));
									}
							}
						}
					}
				}
				if ($valid) {
					$feature = new GeoFeature;
					$feature->setType($new['type']);
					$feature->setLocation($location);
					$feature->setGeoData($settlement->getGeoData());
					$feature->setWorkers($new['workers']);
					$feature->setName($new['name']);
					$feature->setActive(false)->setCondition(-$new['type']->getBuildHours());
					$em->persist($feature);
					$settlement->getGeoData()->addFeature($feature);
				}
			} // end new feature

			$em->flush();
			list($features, $active, $building, $workhours) = $this->featureData($settlement);
			$form = $this->createForm(FeatureconstructionType::class, null, ['features' => $features, 'river' => $settlement->getGeoData()->getRiver(), 'coast' => $settlement->getGeoData()->getCoast()]);
		}
		return $this->render('Construction/features.html.twig', [
			'settlement'=>$settlement,
			'regionpoly'=>$this->geo->findRegionPolygon($settlement),
			'features'=>$features,
			'workhours'=>$workhours,
			'active'=>$active,
			'building'=>$building,
			'roadworkers'=>$settlement->getRoadWorkersPercent(),
			'buildingworkers'=>$settlement->getBuildingWorkersPercent(),
			'otherworkers'=>1.0-$settlement->getAvailableWorkforcePercent()+$settlement->getFeatureWorkersPercent(),
			'form'=>$form->createView()
		]);
	}


	private function featureData($settlement): array {
		$features = $settlement->getGeoData()->getFeatures();
		$active=0; $building=0; $workhours=array();
		foreach ($features as $feature) {
			if (!$feature->getType()->getHidden()) {
				if ($feature->getActive()) { $active++; } else { $building++; }
				$workhours[$feature->getId()] = $this->econ->calculateWorkHours($feature, $settlement);
			}
		}
		return array($features, $active, $building, $workhours);
	}

	private function buildDocks($new): Point {
		// find point to build the docks
		$em = $this->em;
		$query = $em->createQuery('SELECT ST_ClosestPoint(o.poly, ST_POINT(:x,:y)), ST_Distance(o.poly, ST_POINT(:x,:y)) AS distance FROM App:GeoData o JOIN o.biome b WHERE b.name = :ocean ORDER BY distance ASC');
		$query->setParameters(array('ocean'=>'ocean', 'x'=>$new['location_x'], 'y'=>$new['location_y']));
		$query->setMaxResults(1);
		$result = $query->getSingleResult();
		$parser = new BinaryParser(array_shift($result));
		$p = $parser->parse();
		return new Point($p['value'][0], $p['value'][1]);
	}

	private function buildBridge($new): Point {
		// find point to build the bridge
		$em = $this->em;
		$query = $em->createQuery('SELECT ST_ClosestPoint(r.course, ST_POINT(:x,:y)), ST_Distance(r.course, ST_POINT(:x,:y)) AS distance FROM App:River r ORDER BY distance ASC');
		$query->setParameters(array('x'=>$new['location_x'], 'y'=>$new['location_y']));
		$query->setMaxResults(1);
		$result = $query->getSingleResult();
		$parser = new BinaryParser(array_shift($result));
		$p = $parser->parse();
		return new Point($p['value'][0], $p['value'][1]);
	}

	private function buildBorder($new, $geo): Point {
		// snap to nearest border
		return $this->nearestBorderPoint($new, $geo);
	}

	private function nearestBorderPoint($new, $geo): Point {
		$em = $this->em;
		$query = $em->createQuery('SELECT ST_ClosestPoint(ST_Boundary(g.poly), ST_POINT(:x,:y)) FROM App:GeoData g WHERE g = :geo');
		$query->setParameters(array('geo'=>$geo, 'x'=>$new['location_x'], 'y'=>$new['location_y']));
		$result = $query->getSingleResult();
		$parser = new BinaryParser(array_shift($result));
		$p = $parser->parse();
		return new Point($p['value'][0], $p['value'][1]);
	}

	#[Route ('/build/buildings', name:'maf_construction_buildings')]
	public function buildingsAction(Request $request): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('economyBuildingsTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$available=array();
		$unavailable=array();
		$all = $em->getRepository('App:BuildingType')->findAll();
		foreach ($all as $type) {
			if ($settlement->hasBuilding($type, true) OR !in_array('city',$type->getBuiltIn())) continue; # Already have it? Not buildable here? Move along.
			$data = $this->checkBuildability($settlement, $type);

			if ($data['buildable']) {
				$available[]=$data;
			} else {
				$unavailable[]=$data;
			}
		}

		// TODO: also check prerequisites so you cannot abandon buildings that are required for other buildings you have or are constructing (no abandoning palisades once you've built wood walls, etc.)

		$form = $this->createForm(BuildingconstructionType::class, null, ['existing'=>$settlement->getBuildings(), 'available'=>$available]);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$totalworkers=0;

			foreach ($data['existing'] as $id=>$amount) {
				if ($amount>0) { $totalworkers+=$amount; }
			}
			foreach ($data['available'] as $id=>$amount) {
				if ($amount>0) { $totalworkers+=$amount; }
			}
			if ($settlement->getAvailableWorkforcePercent() + $settlement->getBuildingWorkersPercent() - $totalworkers < 0.0) {
				// bail out - not enough people left to work
				$form->addError(new FormError("economy.toomany"));
			} else {
				foreach ($data['existing'] as $id=>$amount) {
					// we are also setting 0 values here because they might currently be > 0
					$building = $em->getRepository('App:Building')->find($id);
					if ($building->getType()->getMinPopulation() * 0.5 > $settlement->getFullPopulation()) {
						// unsustainable
						$amount = 0;
					}
					$building->setWorkers(max(0,floatval($amount)));
				}

				foreach ($data['available'] as $id=>$amount) {
					if ($amount>0) {
						$buildingtype = $em->getRepository('App:BuildingType')->find($id);
						$building = new Building;
						$building->setType($buildingtype);
						$building->setSettlement($settlement);
						$building->startConstruction(max(0,floatval($amount)));
						$building->setResupply(0)->setCurrentSpeed(1.0)->setFocus(0);
						$em->persist($building);
					}
				}

				$em->flush();
				return $this->redirect($request->getUri());
			}
		}
		return $this->render('Construction/buildings.html.twig', [
			'settlement'=>$settlement,
			'buildings'=>$settlement->getBuildings(),
			'available'=>$available,
			'unavailable'=>$unavailable,
			'roadworkers'=>$settlement->getRoadWorkersPercent(),
			'featureworkers'=>$settlement->getFeatureWorkersPercent(),
			'otherworkers'=>1.0-$settlement->getAvailableWorkforcePercent()+$settlement->getBuildingWorkersPercent(),
			'form'=>$form->createView()
		]);
	}

	private function checkBuildability($settlement, $type): array {
		// TODO: filter out already existing ones
		$data = array('id'=>$type->getId(), 'name'=>$type->getName(), 'buildhours'=>$type->getBuildHours());

		if ($type->getMinPopulation() > $settlement->getFullPopulation()) {
			return array_merge($data,array(
				'buildable' => false,
				'reason' => 'population',
				'value' => $type->getMinPopulation()
			));
		}

		foreach ($settlement->getBuildings() as $old) {
			if ($old->getType()==$type) {
				return array_merge($data,array(
					'buildable' => false,
					'reason' => 'already',
				));
			}
		}

		// special conditions - these are hardcoded because they can be complex
		if (!$this->econ->checkSpecialConditions($settlement, $type->getName())) {
			return array_merge($data,array('buildable' => false, 'reason' => 'conditions'));
		}

		$need=array();
		foreach ($type->getRequires() as $required) {
			$have=false;
			foreach ($settlement->getBuildings() as $old) {
				if ($old->getType()==$required && $old->getActive()) {
					$have=true;
					continue;
				}
			}
			if (!$have) {
				$need[]=$required->getName();
			}
		}
		if (!empty($need)) {
			return array_merge($data,array(
				'buildable' => false,
				'reason' => 'prerequisite',
				'value' => implode(', ', $need)
			));
		}

		return array_merge($data,array(
			'buildable' => true
		));
	}

	#[Route ('/build/abandon/{building}', name:'maf_construction_abandon', methods:['post'])]
	public function abandonbuildingAction(Router $router, Building $building): RedirectResponse {
		$character = $this->dispatcher->gateway('economyBuildingsTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$building->abandon();
		$this->em->flush();

		return new RedirectResponse($router->generate('maf_build_buildings'));
	}

	#[Route ('/build/focus', name:'maf_construction_focus', methods:['post'])]
	public function focusAction(Request $request): RedirectResponse|Response {
		list($character, $settlement) = $this->dispatcher->gateway('economyBuildingsTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if (!$request->request->has("building") || !$request->request->has("focus")) {
			throw new Exception("invalid request");
		}
		$id = $request->request->get("building");
		$focus = intval($request->request->get("focus"));

		$focus = max(0, min(3,$focus));

		$em = $this->em;
		$building = $em->getRepository('App:Building')->find($id);
		if (!$building) {
			throw $this->createNotFoundException("building $id not found");
		}
		if ($building->getSettlement() != $settlement) {
			throw new Exception("invalid building");
		}

		$building->setFocus($focus);

		/*$response = array(
			"focus" => $focus,
			"base" => round($building->getCurrentSpeed()*100),
			"final" => round($building->getCurrentSpeed()*100*pow(1.5, $focus)),
			"workers" => $building->getEmployees()
		);*/

		$em->flush();

		return $this->render('element/buildingrow.html.twig', [
			'build'=>$building
		]);
	}

}
