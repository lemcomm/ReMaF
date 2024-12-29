<?php

namespace App\Command;

use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StatisticsNetworkCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
		->setName('maf:stats:network')
		->setDescription('statistics: character relations network')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		echo "graph characters {\n";
		$characters = $this->em->getRepository(Character::class)->findAll();
		foreach ($characters as $character) {
			echo "\"".$character->getId()."\" [label=\"".addslashes($character->getName())."\"];\n";
			foreach ($character->getParents() as $parent) {
				$this->link($character, $parent, "orange");
			}
			foreach ($character->getPartnerships() as $partnership) {
				if ($partnership->getActive() && $partnership->getPublic() && $partnership->getType()=="marriage") {
					$other = $partnership->getOtherPartner($character);
					if ($character->getId() > $other->getId()) { // so we don't get the line twice
						$this->link($character, $other, "red");
					}
				}
			}
			if ($character->getLiege()) {
				$this->link($character, $character->getLiege(), "blue");
			}
			if ($character->getSuccessor()) {
				$this->link($character, $character->getSuccessor(), "green");				
			}
		}

		echo "}\n";
	}


	private function link(Character $from, Character $to, $color): void {
		echo "\"".$from->getId()."\" -- \"".$to->getId()."\" [color=\"$color\"];\n";
	}
}


