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
	 * 	regions (array) =>
	 * 		unkeyed (array) =>
	 * 			systemName - string - Internal system name
	 * 			world - integer/string - ID or system name of world to add it to.
	 * 			biome - string - name of biome type for region
	 * 			modifiers - array - array of modifiers (TODO)
	 *
	 * 	transit links =>
	 * 		unkeyed (array) =>
	 * 			fromRegion - string - ID'd by system name
	 * 			fromWorld - integer/string - ID or system name of world the region is in.
	 * 			toRegion - string - see two lines above this.
	 * 			toWorld - see two lines above this.
	 * 			bidirectional - true/false - Makes link in both directions rather than just from-to.
	 * 			linkType - string - Should match a link type found in src/DataFixtures/LoadTransitTypes.php
	 * 			timeCost - integer - How many hours needed to traverse (base cost)
	 * 		unkeyed (array) =>
	 * 			... ---- Repeat above array for each link.
	 */
	private $input = [
		'regions' => [
			[
				'systemName' => 'regionName',
				'world' => 'worldNameOrId',
				'biome' => 'grasslands'
			],
			[
				'systemName' => 'regionName2',
				'world' => 'worldNameOrId',
				'biome' => 'forest'
			],
		],
		'links' => [
			[
				'fromRegion' => 'regionName',
				'fromWorld' => 'worldNameOrId',
				'toRegion' => 'regionName2',
				'toWorld' => 'worldNameOrId',
				'bidirectional' => true,
				'linkType' => 'land',
				'timeCost' => 6,
				'distance' => 25,
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
			->setHidden(true)
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
