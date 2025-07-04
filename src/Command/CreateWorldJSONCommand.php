<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class CreateWorldJSONCommand extends Command {

	/*
	 * Format is:
	 *      worldId (integer/string) => ID or name of the world to import to or create.
	 * 	subterranean (boolean) => true|false -- Only required for new worlds.
	 * 	travelType (string) => 'hourly' or 'turn' -- Only required for new worlds.
	 * 	regions (array) =>
	 * 		unkeyed (array) =>
	 * 			systemName - string - Internal system name
	 *      		regionType string - 'mapRegion' or 'geoData'
	 * 			world - integer/string - ID or system name of world to add it to.
	 * 			biome - string - name of biome type for region, defaults to grassland
	 * 			modifiers - array - array of modifiers (TODO)
	 * 			hills - boolean - Does the region have hills? Defaults to false.
	 * 			coastal - boolean - Is the region considered coastal? Defaults to false.
	 * 			river - boolean - Does the region have a river in it? Defaults to false.
	 * 			lake - boolean - Does the region border a lake? Defaults to false.
	 * 			passable - boolean - Can you actually enter the region? Used for "distant location" views, like seeing a mountain range to the SW. To do that the linkType between them should be 'distant'. Defaults to true.
	 *
	 * 	links => Only required if regionType is NOT geoData.
	 * 		unkeyed (array) =>
	 * 			fromRegion - string/array - Either a single region ID'd by system name or multiple regions in an array ID'd by system name
	 * 			fromWorld - integer/string - ID or system name of world the region is in.
	 * 			toRegion - string - see two lines above this.
	 * 			toWorld - see two lines above this.
	 * 			bidirectional - true/false - Makes link in both directions rather than just from-to. Defaults to false.
	 * 			linkType - string - Should match a link type found in src/DataFixtures/LoadTransitTypes.php
	 * 			timeCost - integer - How many hours needed to traverse (base cost)
	 * 			distance - integer - How many miles it is away
	 * 			fromDir - string - Shortened compass directions: N/NE/E/SE/S/SW/W/NW; also accepts teriaries like NNW and ESE. Also accepts U and D for up and down, respectively.
	 * 			toDir - string - Same as above, can be left off and will default to opposite of the above.
	 * 		unkeyed (array) =>
	 * 			... ---- Repeat above array for each link.
	 */
	private $input = [
		'worldId' => 'demoland',
		'subterranean' => false,
		'travelType' => 'turn',
		'regions' => [
			[
				'systemName' => 'aurem',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'grass',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'ilandrithcypzo',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'pevenotaarren',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => true,
				'passable' => true,
			],
			[
				'systemName' => 'alaghcypno',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'grass',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'khaloss',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'grass',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'kislaadargen',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'baleagasmenora',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'firklastor',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'grass',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'smerla',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'grass',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'wastiircojhry',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'rathorvimorgo',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'forest',
				'hills' => false,
				'coastal' => true,
				'river' => true,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'uintyr',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'thin scrub',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'thaugretis',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'scrub',
				'hills' => false,
				'coastal' => false,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'fenpherarar',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'scrub',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'bagasxarvor',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'mountain',
				'hills' => false,
				'coastal' => true,
				'river' => false,
				'lake' => false,
				'passable' => true,
			],
			[
				'systemName' => 'demolandocean',
				'regionType' => 'mapRegion',
				'world' => 'demoland',
				'biome' => 'ocean',
				'hills' => false,
				'coastal' => false,
				'river' => false,
				'lake' => false,
				'passable' => false,
			],
		],
		'links' => [
			[
				'fromRegion' => 'aurem',
				'fromWorld' => 'demoland',
				'toRegion' => 'ilandrithcypzo',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 12,
				'distance' => 57,
				'fromDir' => 'NE',
			],
			[
				'fromRegion' => 'ilandrithcypzo',
				'fromWorld' => 'demoland',
				'toRegion' => 'pevenotaarren',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 4,
				'distance' => 19,
				'fromDir' => 'NE',
			],
			[
				'fromRegion' => 'pevenotaarren',
				'fromWorld' => 'demoland',
				'toRegion' => 'alaghcypno',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 9,
				'distance' => 47,
				'fromDir' => 'ESE',
			],
			[
				'fromRegion' => 'pevenotaarren',
				'fromWorld' => 'demoland',
				'toRegion' => 'fenpherarar',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 7,
				'distance' => 31,
				'fromDir' => 'NNW',
			],
			[
				'fromRegion' => 'alaghcypno',
				'fromWorld' => 'demoland',
				'toRegion' => 'khaloss',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 6,
				'distance' => 25,
				'fromDir' => 'ENE',
			],
			[
				'fromRegion' => 'khaloss',
				'fromWorld' => 'demoland',
				'toRegion' => 'kislaadargen',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 7,
				'distance' => 32,
				'fromDir' => 'ENE',
			],
			[
				'fromRegion' => 'khaloss',
				'fromWorld' => 'demoland',
				'toRegion' => 'rathorvimorgo',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 12,
				'distance' => 51,
				'fromDir' => 'N',
			],
			[
				'fromRegion' => 'kislaadargen',
				'fromWorld' => 'demoland',
				'toRegion' => 'baleagasmenora',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 7,
				'distance' => 34,
				'fromDir' => 'N',
			],
			[
				'fromRegion' => 'baleagasmenora',
				'fromWorld' => 'demoland',
				'toRegion' => 'firklastor',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 8,
				'distance' => 38,
				'fromDir' => 'NNW',
			],
			[
				'fromRegion' => 'baleagasmenora',
				'fromWorld' => 'demoland',
				'toRegion' => 'wastiircojhry',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 9,
				'distance' => 41,
				'fromDir' => 'NW',
			],
			[
				'fromRegion' => 'firklastor',
				'fromWorld' => 'demoland',
				'toRegion' => 'wastiircojhry',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 5,
				'distance' => 19,
				'fromDir' => 'WSW',
			],
			[
				'fromRegion' => 'firklastor',
				'fromWorld' => 'demoland',
				'toRegion' => 'smerla',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 3,
				'distance' => 11,
				'fromDir' => 'N',
			],
			[
				'fromRegion' => 'wastiircojhry',
				'fromWorld' => 'demoland',
				'toRegion' => 'rathorvimorgo',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 5,
				'distance' => 22,
				'fromDir' => 'WSW',
			],
			[
				'fromRegion' => 'rathorvimorgo',
				'fromWorld' => 'demoland',
				'toRegion' => 'uintyr',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 6,
				'distance' => 25,
				'fromDir' => 'WNW',
			],
			[
				'fromRegion' => 'rathorvimorgo',
				'fromWorld' => 'demoland',
				'toRegion' => 'uintyr',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 8,
				'distance' => 35,
				'fromDir' => 'WNW',
			],
			[
				'fromRegion' => 'smerla',
				'fromWorld' => 'demoland',
				'toRegion' => 'uintyr',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'ferry',
				'timeCost' => 5,
				'distance' => 63,
				'fromDir' => 'SE',
				'toDir' => 'SW'
			],
			[
				'fromRegion' => 'uintyr',
				'fromWorld' => 'demoland',
				'toRegion' => 'thaugretis',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 12,
				'distance' => 25,
				'fromDir' => 'W',
			],
			[
				'fromRegion' => 'thaugretis',
				'fromWorld' => 'demoland',
				'toRegion' => 'fenpherarar',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 12,
				'distance' => 24,
				'fromDir' => 'SW',
			],
			[
				'fromRegion' => 'thaugretis',
				'fromWorld' => 'demoland',
				'toRegion' => 'bagasxarvor',
				'toWorld' => 'demoland',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 6,
				'distance' => 25,
				'fromDir' => 'N',
			],
			[
				'fromRegion' => ['fenpherarar', 'pevenotaarren', 'ilandrithcypzo', 'aurem', 'alaghcypno'],
				'fromWorld' => 'demoland',
				'toRegion' => 'bagasxarvor',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'N',
			],
			[
				'fromRegion' => ['khaloss', 'kislaadargen', 'baleagasmenora', 'firklastor', 'smerla', 'wastiircojhry', 'rathorvimorgo'],
				'fromWorld' => 'demoland',
				'toRegion' => 'bagasxarvor',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'NW',
			],
			[
				'fromRegion' => ['aurem', 'ilandrithcypzo', 'pevenotaarren', 'fenpherarar', 'bagasxarvor'],
				'fromWorld' => 'demoland',
				'toRegion' => 'demolandocean',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'W',
			],
			[
				'fromRegion' => ['bagasxarvor', 'uintyr', 'rathorvimorgo', 'wastiircojhry', 'firklastor', 'smerla'],
				'fromWorld' => 'demoland',
				'toRegion' => 'demolandocean',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'N',
			],
			[
				'fromRegion' => ['firklastor', 'smerla', 'baleagasmenora', 'kislaadargen'],
				'fromWorld' => 'demoland',
				'toRegion' => 'demolandocean',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'E',
			],
			[
				'fromRegion' => ['kislaadargen', 'khaloss', 'alaghcypno', 'ilandrithcypno', 'aurem'],
				'fromWorld' => 'demoland',
				'toRegion' => 'demolandocean',
				'toWorld' => 'demoland',
				'bidirectional' => false,
				'linkType' => 'distant',
				'fromDir' => 'S',
			],
		],
	];

	public function __construct(private KernelInterface $kernel) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:generate:world:json')
			->setDescription('Command to create an importable world JSON file.')
			->addArgument('output', InputArgument::OPTIONAL, 'Filename to output to. Defaults to world.json.')
			->setHidden()
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$filename = $input->getArgument('output');
		if (!$filename) {
			$filename = 'world.json';
		}
		try {
			$fs = new Filesystem();
			$txt = json_encode($this->input, JSON_PRETTY_PRINT);
			$root = $this->kernel->getProjectDir().'/';
			$fs->dumpFile($root.$filename, $txt);
			$output->writeln('<info>World JSON file written to: '.$root.$filename.'</info>');
			return Command::SUCCESS;
		} catch (Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return Command::FAILURE;
		}
	}
}
