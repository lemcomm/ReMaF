<?php

namespace App\Command;

use App\Entity\Biome;
use App\Entity\GeoData;
use App\Entity\MapRegion;
use App\Entity\MapTransit;
use App\Entity\TransitType;
use App\Entity\World;
use Doctrine\ORM\Cache\Region;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportWorldJSONCommand extends Command {

	/*
	 * Format is:
	 *      worldId (integer/string) => ID or name of the world to import to or "new" to create a new world.
	 * 	subterranean (boolean) => true|false -- Only required for new worlds.
	 * 	travelType (string) => 'hourly' or 'turn' -- Only required for new worlds.
	 * 	regions (array) =>
	 * 		unkeyed (array) =>
	 * 			systemName - string - Internal system name
	 *      		regionType string => 'mapRegion' or 'geoData'
	 * 			world - integer/string - ID or system name of world to add it to.
	 * 			biome - string - name of biome type for region
	 * 			modifiers - array - array of modifiers (TODO)
	 * 			coastal - boolean - Is the region considered coastal?
	 * 			river - boolean - Does the region have a river in it?
	 * 			lake - boolean - Does the region border a lake?
	 * 			passable - boolean - Can you actually enter the region? Used for "distant location" views, like seeing a mountain range to the SW. To do that the linkType between them should be 'distant'.
	 *
	 * 	links =>
	 * 		unkeyed (array) =>
	 * 			fromRegion - string/array - Either a single region ID'd by system name or multiple regions in an array ID'd by system name
	 * 			fromWorld - integer/string - ID or system name of world the region is in.
	 * 			toRegion - string - see two lines above this.
	 * 			toWorld - see two lines above this.
	 * 			bidirectional - true/false - Makes link in both directions rather than just from-to.
	 * 			linkType - string - Should match a link type found in src/DataFixtures/LoadTransitTypes.php
	 * 			timeCost - integer - How many hours needed to traverse (base cost)
	 * 			distance - integer - How many miles it is away
	 * 			fromDir - string - Shortened compass directions: N/NE/E/SE/S/SW/W/NW; also accepts teriaries like NNW and ESE. Also accepts U and D for up and down, respectively.
	 * 			toDir - string - Same as above, can be left off and will default to opposite of the above.
	 * 		unkeyed (array) =>
	 * 			... ---- Repeat above array for each link.
	 */

	private array $biomes = [];

	public function __construct(private EntityManagerInterface $em) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:import:world:json')
			->setDescription('Command to import a world JSON file.')
			->addArgument('input', InputArgument::REQUIRED, 'Filename to imput from.')
			->setHidden(false)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$filename = $input->getArgument('input');
		try {
			$fs = new Filesystem();
			if (!$fs->exists($filename)) {
				$output->writeln('ERROR: "'.$filename.'" does not exist.');
				return Command::FAILURE;
			}
			$mime = mime_content_type($filename);
			if ($mime !== 'application/json') {
				$output->writeln('ERROR: "'.$filename.'" does not appear to be a valid JSON file. MIME type mismatch.');
				return Command::FAILURE;
			}
			$data = json_decode(file_get_contents($filename), true);
			if ($data === null) {
				$output->writeln('ERROR: "'.$filename.'" could not be parsed as a valid JSON file. JSON Decode failed.');
				return Command::FAILURE;
			}
			if(!array_key_exists('worldId', $data)) {
				$output->writeln("ERROR: Missing required 'worldId' key.");
				return Command::FAILURE;
			}
			$noRegions = false;
			if(!array_key_exists('regions', $data)) {
				$output->writeln("WARNING: Missing 'regions' key. No regions will be imported.");
				$noRegions = true;
			}
			if (!$noRegions) {
				if(!array_key_exists('regionType', $data)) {
					$output->writeln("ERROR: Missing required 'regionType' key.");
					return Command::FAILURE;
				}
			}
			if(!array_key_exists('links', $data)) {
				$output->writeln("WARNING: Missing 'links' key.");
				if (array_key_exists('link', $data)) {
					$output->writeln("Found 'link' key instead. Please correct this to 'links' in the future.");
					$data['links'] = $data['link'];
				} else {
					$output->writeln("WARNING: No array of link data found. Links will not be created.");
				}
			}
			$new = false;
			$em = $this->em;
			if (is_string($data['worldId'])) {
				if (strtolower($data['worldId']) === 'new') {
					$new = true;
				} else {
					$worldName = $data['worldId'];
					$world = $em->getRepository(World::class)->findOneBy(['name'=>$worldName]);
					if (!$world) {
						$new = true;
						$output->writeln("World '$worldName' not found.");
					}
				}
			} elseif (is_numeric($data['worldId'])) {
				$worldId = (int) $data['worldId'];
				$world = $em->getRepository(World::class)->find($worldId);
				if (!$world) {
					$output->writeln("World '$worldId' not found.");
				}
			}
			if ($new) {
				if (!array_key_exists('subterranean', $data)) {
					$output->writeln("ERROR: Missing required 'subterranean' key.");
					return Command::FAILURE;
				} else {
					$subterranean = (bool) $data['subterranean'];
				}
				if (!array_key_exists('travelType', $data)) {
					$output->writeln("ERROR: Missing required 'travelType' key.");
					return Command::FAILURE;
				} else {
					$travelType = $data['travelType'];
				}
				$world = new World();
				$world->setName($data['worldId']);
				$world->setSubterranean($subterranean);
				$world->setTravelType($travelType);
				$em->persist($world);
				$em->flush();
			}
			foreach ($data['regions'] as $each) {
				if (!array_key_exists('systemName', $each)) {
					$output->writeln("ERROR: Missing required 'systemName' key for region.");
					return Command::FAILURE;
				}
				if (!array_key_exists('world', $each)) {
					$output->writeln("ERROR: Missing required 'world' key for region.");
					return Command::FAILURE;
				}
				if (!array_key_exists('regionType', $each)) {
					$output->writeln("ERROR: Missing required 'regionType' key for region.");
					return Command::FAILURE;
				} else {
					if ($each['regionType'] === 'mapRegion') {
						$mapRegion = true;
						$geoData = false;
					} elseif ($each['regionType'] === 'geoData') {
						$mapRegion = false;
						$geoData = true;
					} else {
						$output->writeln("ERROR: Invalid region type.");
						return Command::FAILURE;
					}
				}
				if (!array_key_exists('biome', $each)) {
					$output->writeln("WARNING: Missing 'biome' key for region. Will default to 'grassland'.");
					$each['biome'] = 'grass';
				}
				if (array_key_exists($each['biome'], $this->biomes)) {
					$biome = $this->biomes[$each['biome']];
				} else {
					$biome = $em->getRepository(Biome::class)->findOneBy(['name'=>$each['biome']]);
					if (!$biome) {
						$output->writeln("Biome ".$each['biome']." not found.");
						return Command::FAILURE;
					} else {
						$this->biomes[$each['biome']] = $biome;
					}
				}

				if (!array_key_exists('coastal', $each)) {
					$output->writeln("WARNING: Missing 'coastal' key for region. Will default to 'FALSE'.");
					$each['coastal'] = false;
				}
				if (!array_key_exists('river', $each)) {
					$output->writeln("WARNING: Missing 'river' key for region. Will default to 'FALSE'.");
					$each['river'] = false;
				}
				if (!array_key_exists('lake', $each)) {
					$output->writeln("WARNING: Missing 'lake' key for region. Will default to 'FALSE'.");
					$each['lake'] = false;
				}
				if (!array_key_exists('passable', $each)) {
					$output->writeln("WARNING: Missing 'passable' key for region. Will default to 'TRUE'.");
					$each['passable'] = true;
				}
				if ($geoData) {
					if(!array_key_exists('altitude', $each)) {
						$output->writeln("ERROR: Missing 'altitude' key for region.");
						return Command::FAILURE;
					}
					if (!array_key_exists('poly', $each)) {
						$output->writeln("ERROR: Missing 'poly' key for region.");
						return Command::FAILURE;
					}
				}
				if (is_numeric($each['world'])) {
					$world = $em->getRepository(World::class)->find($each['world']);
				} else {
					$world = $em->getRepository(World::class)->findOneBy(['name'=>$each['world']]);
				}
				if (!$world) {
					$output->writeln('World '.$each['world'].' not found.');
					return Command::FAILURE;
				}
				if ($mapRegion) {
					$new = new MapRegion();
				} elseif ($geoData) {
					$new = new GeoData();
				}
				$world = null;
				/** @var GeoData|MapRegion $new */
				$new->setWorld($world);
				$new->setName($each['name']);
				$new->setBiome($biome);
				$new->setCoast($each['coastal']);
				$new->setRiver($each['river']);
				$new->setLake($each['lake']);
				$new->setPassable($each['passable']);
				$new->setHills($each['hills']);
				if ($geoData) {
					$new->setAltitude($each['altitude']);
					$new->setPoly($each['poly']);
				}
				$em->persist($new);
			}
			$em->flush();
			foreach ($data['links'] as $each) {
				if(!array_key_exists('fromWorld', $each)) {
					$output->writeln("ERROR: Missing 'fromWorld' key for link.");
					return Command::FAILURE;
				} else {
					if (is_numeric($each['world'])) {
						$fromWorld = $em->getRepository(World::class)->find($each['world']);
					} else {
						$fromWorld = $em->getRepository(World::class)->findOneBy(['name'=>$each['world']]);
					}
					if (!$fromWorld) {
						$output->writeln('Unable to locate fromWorld of '.$each['fromWorld'].'.');
						return Command::FAILURE;
					}
				}
				if(!array_key_exists('fromRegion', $each)) {
					$output->writeln("ERROR: Missing 'fromRegion' key for link.");
					return Command::FAILURE;
				} else {
					if (is_numeric($each['fromRegion'])) {
						$fromRegion = $em->getRepository(World::class)->find($each['fromRegion']);
					} else {
						$fromRegion = $em->getRepository(World::class)->findOneBy(['name'=>$each['fromRegion'], 'fromWorld'=>$fromWorld]);
					}
					if (!$fromRegion) {
						$output->writeln('Unable to locate fromRegion of '.$each['fromRegion'].'.');
						return Command::FAILURE;
					}
				}
				if(!array_key_exists('toWorld', $each)) {
					$output->writeln("ERROR: Missing 'toWorld' key for link.");
					return Command::FAILURE;
				} else {
					if (is_numeric($each['toWorld'])) {
						$toWorld = $em->getRepository(World::class)->find($each['toWorld']);
					} else {
						$toWorld = $em->getRepository(World::class)->findOneBy(['name'=>$each['toWorld']]);
					}
					if (!$toWorld) {
						$output->writeln('Unable to locate toWorld of '.$each['toWorld'].'.');
						return Command::FAILURE;
					}
				}
				if(!array_key_exists('toRegion', $each)) {
					$output->writeln("ERROR: Missing 'toRegion' key for link.");
					return Command::FAILURE;
				} else {
					if (is_numeric($each['toRegion'])) {
						$toRegion = $em->getRepository(World::class)->find($each['toRegion']);
					} else {
						$toRegion = $em->getRepository(World::class)->findOneBy(['name'=>$each['toRegion'], 'toWorld'=>$toWorld]);
					}
					if (!$toRegion) {
						$output->writeln('Unable to locate toRegion of '.$each['toRegion'].'.');
						return Command::FAILURE;
					}
				}
				if(!array_key_exists('bidirectional', $each)) {
					$output->writeln("WARNING: Missing 'bidirectional' key for link. Assuming FALSE.");
					$each['bidirectional'] = false;
				}
				if(!array_key_exists('linkType', $each)) {
					$output->writeln("ERROR: Missing 'linkType' key for link.");
					return Command::FAILURE;
				} else {
					$linkType = $em->getRepository(TransitType::class)->findOneBy(['name'=>$each['linkType']]);
					if (!$linkType) {
						$output->writeln('Unable to locate linkType of '.$each['linkType'].'.');
						return Command::FAILURE;
					}
				}
				if(!array_key_exists('timeCost', $each)) {
					$output->writeln("ERROR: Missing 'timeCost' key for link.");
					return Command::FAILURE;
				}
				if(!array_key_exists('distance', $each)) {
					$output->writeln("ERROR: Missing 'distance' key for link.");
					return Command::FAILURE;
				}
				if(!array_key_exists('fromDir', $each)) {
					$output->writeln("ERROR: Missing 'fromDir' key for link.");
					return Command::FAILURE;
				} else {
					$dirSet = MapTransit::horizontalDirections;
					if (!in_array($each['fromDir'], MapTransit::horizontalDirections)) {
						$dirSet = MapTransit::verticalDirections;
						if (!in_array($each['fromDir'], MapTransit::verticalDirections)) {
							$output->writeln("ERROR: Invalid direction for 'fromDir'.");
							return Command::FAILURE;
						}
					}
				}
				if(!array_key_exists('toDir', $each)) {
					$output->writeln("NOTICE: Missing 'toDir' key for link. If set as bidirectional, it will use the inverse of fromDir.");
					$dirs = count($dirSet);
					$key = key($dirSet[$each['fromDir']]);
					$each['toDir'] = $dirSet[$key+$dirs];
				} else {
					if (!in_array($each['toDir'], $dirSet)) {
						$output->writeln("ERROR: Invalid direction for 'toDir'. Should be in same set as 'fromDir'.");
					}
				}
				$linkA = $this->findLink($fromRegion, $fromWorld, $toRegion, $toWorld, $linkType);
				$newA = false;
				if (!$linkA) {
					$linkA = new MapTransit();
					$newA = true;
				}
				$this->updateLink($linkA, $fromWorld, $toWorld, $fromRegion, $toRegion, $linkType, $each['timeCost'], $each['distance'], $each['fromDir'], $newA);
				if ($each['bidirectional']) {
					$newB = false;
					$linkB = $this->findLink($toRegion, $toWorld, $fromRegion, $fromWorld, $linkType);
					if (!$linkB) {
						$linkB = new MapTransit();
						$newB = true;
					}
					$this->updateLink($linkB, $toWorld, $fromWorld, $toRegion, $fromRegion, $linkType, $each['timeCost'], $each['distance'], $each['toDir'], $newB);
				}

			}
			$em->flush();
			return Command::SUCCESS;
		} catch (Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return Command::FAILURE;
		}
	}

	private function findLink (MapRegion $fromRegion, World $fromWorld, MapRegion $toRegion, World $toWorld, TransitType $type): ?MapTransit {
		return $this->em->getRepository(MapTransit::class)->findOneBy(['fromRegion'=>$fromRegion, 'fromWorld'=>$fromWorld, 'toRegion'=>$toRegion, 'toWorld'=>$toWorld, 'linkType'=>$type]);
	}

	private function updateLink(MapTransit $link, World $fW, World $tW, MapRegion $fR, MapRegion $tR, TransitType $type, $cost, $dist, $dir, $new = true): MapTransit {
		if ($new) {
			$this->em->persist($link);
			$link->setFromRegion($fR);
			$link->setToRegion($tR);
			$link->setFromWorld($fW);
			$link->setToWorld($tW);
			$link->setType($type);
		}
		$link->setTravelTime($cost);
		$link->setDistance($dist);
		$link->setDirection($dir);
		return $link;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('input')) {
			$need = new Question('Which file should be imported? Please supply the filepath as relative to where you called this command.');
			$need->setValidator(function ($file) {
				if (empty($file)) {
					throw new Exception('Username cannot be empty!');
				}
				return $file;
			});
			$input->setArgument('input', $helper->ask($input, $output, $need));
		}
	}
}
