<?php

namespace App\DataFixtures;

use App\Enum\RaceName;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\SkillType;
use App\Entity\SkillCategory;

class LoadSkillsData extends Fixture {
        private array $categories = array(
                # Tier 0
                "equipment" => array('pro' => null),
                "leadership" => array('pro' => null),
                "survival" => array('pro' => null),
                "combat" => array('pro' => null),
                "magic" => array('pro' => null),
		"races" => array('pro' => null),

                # Tier 1
                "bows" => array('pro' => "equipment"),
                "crossbows" => array('pro' => "equipment"),
                "thrown" => array('pro' => "equipment"),
                "slings" => array('pro' => "equipment"),
                "axes" => array('pro' => "equipment"),
                "swords" => array('pro' => "equipment"),
                "polearms" => array('pro' => "equipment"),
                "gloves" => array('pro' => "equipment"),
                "daggers" => array('pro' => "equipment"),
                "clubs" => array('pro' => "equipment"),
                "sickles" => array('pro' => "equipment"),
                "flails" => array('pro' => "equipment"),
                "hammers" => array('pro' => "equipment"),

                "command" => array('pro' => "leadership"),
                "governance" => array('pro' => "leadership"),

                "tracking" => array('pro' => "survival"),
                "medicine" => array('pro' => "survival"),
                "anatomy" => array('pro' => "survival"),
                "riding" => array('pro' => "survival"),

		"firstWorld" => ['pro'=>'races'],
		"secondWorld" => ['pro'=>'races'],
		"oldWorld" => ['pro'=>'races'],
		"highMonster" => ['pro'=>'races'],
		"lowMonster" => ['pro'=>'races'],
		"legend" => ['pro'=>'races'],

		RaceName::firstOne->value => ['pro'=>'firstWorld'],
		RaceName::secondOne->value => ['pro'=>'secondWorld'],
		RaceName::magitek->value => ['pro'=>'magitek'],
		RaceName::human->value => ['pro'=>'secondWorld'],
		RaceName::orc->value => ['pro'=>'secondWorld'],
		RaceName::elf->value => ['pro'=>'secondWorld'],
		RaceName::ogre->value => ['pro'=>'highMonster'],
		RaceName::wyvern->value => ['pro'=>'lowMonster'],
		RaceName::slime->value => ['pro'=>'lowMonster'],
		RaceName::dragon->value => ['pro'=>'legend'],
        );

        private array $skills = array(
                "short sword" => array('cat' => 'swords'),
                "long sword" => array('cat' => 'swords'),
		"falchion" => array('cat' => 'swords'),
                "machete" => array('cat' => 'swords'),

                "knife" => array('cat' => 'daggers'),
                "dagger" => array('cat' => 'daggers'),

                "battle axe" => array('cat' => 'axes'),
                "great axe" => array('cat' => 'axes'),

                "club" => array('cat' => 'clubs'),
                "mace" => array('cat' => 'clubs'),
                "morning star" => array('cat' => 'clubs'),

                "pike" => array('cat' => 'polearms'),
                "spear" => array('cat' => 'polearms'),
                "halberd" => array('cat' => 'polearms'),
                "glaive" => array('cat' => 'polearms'),
                "staff" => array('cat' => 'polearms'),
                "lance" => array('cat' => 'polearms'),
                "swordstaff" => array('cat' => 'polearms'),

                "flail" => array('cat' => 'flails'),
                "chain mace" => array('cat' => 'flails'),
                "nunchaku" => array('cat' => 'flails'),
                "triple staff" => array('cat' => 'flails'),

                "sickle" => array('cat' => 'sickles'),
                "kusarigama" => array('cat' => 'sickles'),
                "war scythe" => array('cat' => 'sickles'),
                "fauchard" => array('cat' => 'sickles'),

                "war hammer" => array('cat' => 'hammers'),
                "maul" => array('cat' => 'hammers'),
                "totokia" => array('cat' => 'hammers'),
                "war mallet" => array('cat' => 'hammers'),

                "sling" => array('cat' => 'slings'),
                "staff sling" => array('cat' => 'slings'),

                "shortbow" => array('cat' => 'bows'),
		"recurve" => array('cat' => 'bows'),
                "longbow" => array('cat' => 'bows'),

                "crossbow" => array('cat' => 'crossbows'),

                "throwing knife" => array('cat' => 'thrown'),
                "throwing axe" => array('cat' => 'thrown'),
                "javelin" => array('cat' => 'thrown'),

		"shield" => array('cat' => 'equipment'),

		"military" => ['cat' => [
			RaceName::firstOne->value,
			RaceName::secondOne->value,
			RaceName::magitek->value,
			RaceName::human->value,
			RaceName::orc->value,
			RaceName::elf->value,
			RaceName::ogre->value,
			RaceName::wyvern->value,
			RaceName::slime->value,
			RaceName::dragon->value,
		]],
        );

	public function load(ObjectManager $manager): void {
		echo 'Loading Skill Categories...';
		foreach ($this->categories as $name=>$data) {
			$type = $manager->getRepository(SkillCategory::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new SkillCategory();
				$manager->persist($type);
				$type->setName($name);
			}
			if ($data['pro'] != null) {
				$pro = $manager->getRepository(SkillCategory::class)->findOneBy(['name'=>$data['pro']]);
				if ($pro) {
					$type->setCategory($pro);
				} else {
					echo 'No Skill Category of name '.$data['pro'].' found for '.$name;
				}
			}
			$manager->flush();
		}
		echo 'Loading Skill Types...';
		foreach ($this->skills as $name=>$data) {
			if (is_array($data['cat'])) {
				foreach ($data['cat'] as $each) {
					$this->newSkillType($manager, $name, $each);
				}
			} else {
				$this->newSkillType($manager, $name, $data['cat']);
			}

			$manager->flush();
		}
	}

	private function newSkillType($manager, $name, $category) {
		$type = $manager->getRepository(SkillType::class)->findOneBy(['name'=>$name]);
		if (!$type) {
			$type = new SkillType();
			$manager->persist($type);
			$type->setName($name);
		}
		$cat = $manager->getRepository(SkillCategory::class)->findOneBy(['name'=>$category]);
		if ($cat) {
			$type->setCategory($cat);
		} else {
			echo 'No Skill category of name '.$category.' found for skill '.$name.'\n';
		}
	}
}
