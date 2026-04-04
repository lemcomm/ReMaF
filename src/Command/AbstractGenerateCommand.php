<?php

namespace App\Command;

use App\Entity\Battle;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\GeoData;
use App\Entity\MapRegion;
use App\Entity\Place;
use App\Entity\Settlement;
use App\Entity\World;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractGenerateCommand extends Command {

	protected EntityManagerInterface $em;
	protected OutputInterface $output;
	protected $whereString = '';

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:test:abstract')
			->setDescription('abstract testing command - do not call directly')
			->setHidden()
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->output->writeln("do not call this command directly");
		return Command::INVALID;
	}

	protected function findWeapon($string): EquipmentType|null {
		return $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>$string]);
	}

	protected function findCharacter($string): Character|false {
		$char = $this->em->getRepository(Character::class)->findOneBy(['id' => $string]);
		if ($char) {
			return $char;
		}
		return false;
	}

	protected function findCharacters($string): ArrayCollection {
		$all = new ArrayCollection();
		$string = explode(',', $string);
		foreach ($string as $char) {
			$char = $this->em->getRepository(Character::class)->findOneBy(['id' => $char]);
			if ($char) {
				$all->add($char);
			}
		}
		return $all;
	}

	protected function findWhere(string $where, Battle $battle): Battle {
		$set = explode(':', $where);
		if (!array_key_exists(1, $set)) {
			$battle->setLocation(null);
			$world = $this->em->getRepository(World::class)->findOneBy(['name'=>'old world']);
			$battle->setWorld($world);
			$this->whereString = 'Unknown Location???';
		} else {
			switch ($set[0]) {
				case 'G':
				case 'GeoData':
					$here = $this->em->getRepository(GeoData::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var GeoData $here */
						$battle->setLocation($here->getCenter());
						$battle->setWorld($here->getWorld());
					}
					$this->whereString = 'GeoData: '. $here->getId();
					break;
				case 'M':
				case 'MapRegion':
					$here = $this->em->getRepository(MapRegion::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var MapRegion $here */
						$battle->setMapRegion($here);
						$battle->setWorld($here->getWorld());
					}
					$this->whereString = 'MapRegion: '. $here->getId();
					break;
				case 'P':
				case 'Place':
					$here = $this->em->getRepository(Place::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var Place $here */
						$battle->setPlace($here);
						$battle->setWorld($here->getWorld());
						if ($here->getGeoData()) {
							$battle->setLocation($here->getGeoData()->getCenter());
						} else {
							$battle->setMapRegion($here->getMapRegion());
						}
					}
					$this->whereString = 'Place: '. $here->getId();
					break;
				case 'S':
				case 'Settlement':
					$here = $this->em->getRepository(Settlement::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var Settlement $here */
						$battle->setSettlement($here);
						$battle->setWorld($here->getWorld());
						if ($here->getGeoData()) {
							$battle->setLocation($here->getGeoData()->getCenter());
						} else {
							$battle->setMapRegion($here->getMapRegion());
						}
					}
					$this->whereString = 'Settlement: '. $here->getId();
					break;
			}
		}
		return $battle;
	}

}
