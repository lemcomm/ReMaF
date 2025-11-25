<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\SkillType;
use App\Enum\RaceName;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class SkillManager {

	public function __construct(
		private EntityManagerInterface $em,
	) {}

	public static array $noRaceGroupSkills = [
		'highMonster',
		'lowMonster',
	];

	public function trainSkill(Character $char, ?SkillType $type=null, $pract = 0, $theory = 0): bool {
		if (!$type) {
			# Not all weapons have skills, this just catches those.
			return false;
		}
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Skill s WHERE s.character = :me AND s.type = :type ORDER BY s.id ASC')->setParameters(['me'=>$char, 'type'=>$type])->setMaxResults(1);
		$training = $query->getResult();
		if ($pract && $pract < 1) {
			$pract = 1;
		} elseif ($pract) {
			$pract = round($pract);
		}
		if ($theory && $theory < 1) {
			$theory = 1;
		} elseif ($theory) {
			$theory = round($theory);
		}
		if (!$training) {
			$training = new Skill();
			$this->em->persist($training);
			$training->setCharacter($char);
			$training->setType($type);
			$training->setCategory($type->getCategory());
			$training->setPractice($pract);
			$training->setTheory($theory);
			$training->setPracticeHigh($pract);
			$training->setTheoryHigh($theory);
		} else {
			$training = $training[0];
			if ($pract) {
				$newPract = $training->getPractice() + $pract;
				$training->setPractice($newPract);
				if ($newPract > $training->getPracticeHigh()) {
					$training->setPracticeHigh($newPract);
				}
			}
			if ($theory) {
				$newTheory = $training->getTheory() + $theory;
				$training->getTheory($newTheory);
				if ($newTheory > $training->getTheoryHigh()) {
					$training->setTheoryHigh($newTheory);
				}
			}
		}
		$training->setUpdated(new \DateTime('now'));
		$this->em->flush();
		return true;
	}

	public function setupSkill(Character $char, string $which, ?string $category = null) {
		if ($which === 'military') {
			/** @var Skill $skill */
			foreach ($char->getSkills() as $skill) {
				if (str_ends_with($skill->getType()->getName(), $which)) {
					return false;
				}
			}
			if ($char->getRace()->getName() === RaceName::firstOne->value) {
				$this->setupFirstOneMilitary($char);
			}
			return true;
		}
		return false;
	}

	private function setupFirstOneMilitary(Character $char) {
		$raceName = RaceName::firstOne->value;
		$category = $this->em->getRepository(SkillCategory::class)->findOneBy(['name'=>$raceName]);
		if (!$category) {
			throw new Exception("Missing skill category for $raceName");
		}
		$type = $this->em->getRepository(SkillType::class)->findOneBy(['name'=>$raceName.'-military', 'category'=>$category]);
		if (!$type) {
			throw new Exception("Missing skill type for $raceName-military");
		}
		$skill = new Skill();
		$this->em->persist($skill);
		$skill->setCharacter($char);
		$skill->setType($type);
		$skill->setCategory($category);
		$skill->setPractice(1000);
		$skill->setTheory(1000);
		$skill->setPracticeHigh(1000);
		$skill->setTheoryHigh(1000);
		$skill->setUpdated(new \DateTime('now'));
		$raceName = RaceName::secondOne->value;
		$category = $this->em->getRepository(SkillCategory::class)->findOneBy(['name'=>$raceName]);
		$type = $this->em->getRepository(SkillType::class)->findOneBy(['name'=>$raceName.'-military', 'category'=>$category]);
		$skill = new Skill();
		$this->em->persist($skill);
		$skill->setCharacter($char);
		$skill->setType($type);
		$skill->setCategory($category);
		$skill->setPractice(200);
		$skill->setTheory(200);
		$skill->setPracticeHigh(200);
		$skill->setTheoryHigh(200);
		$skill->setUpdated(new \DateTime('now'));
	}

}