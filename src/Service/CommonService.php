<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\Action;
use App\Entity\ActivityReportObserver;
use App\Entity\BattleReportObserver;
use App\Entity\Character;
use App\Entity\Setting;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\SkillType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;


class CommonService {

	/*
	This service exists purely to prevent code duplication and circlic service requiremenets.
	Things that provide core game functions (besides security), that are not handled in a more concise service should be here.
	Security is handled by AppState.
	*/

	private array $languages = array(
		'en' => 'english',
		'de' => 'deutsch',
		'es' => 'español',
		'fr' => 'français',
		'it' => 'italiano'
	);

	public function __construct(
		private EntityManagerInterface $em) {
	}

	public function availableTranslations(): array {
		return $this->languages;
	}

	public function getClassName($entity): false|int|string {
		$classname = get_class($entity);
		if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
		return $pos;
	}

	public function getCycle(): int {
		return (int)($this->getGlobal('cycle', 0));
	}

	public function getDate($cycle=null, $percents=false): array {
		// our in-game date - 6 days a week, 60 weeks a year = 1 year about 2 months
		if (null===$cycle) {
			$cycle = $this->getCycle();
		}

		$year = floor($cycle/360)+1;
		$week = floor($cycle%360/6)+1;
		$day = ($cycle%6)+1;
		if (!$percents) {
			return array('year'=>$year, 'week'=>$week, 'day'=>$day);
		} else {
			return array('%year%'=>$year, '%week%'=>$week, '%day%'=>$day);
		}
	}
	public function getGlobal($name, $default=false) {
		$setting = $this->em->getRepository(Setting::class)->findOneBy(['name'=>$name]);
		if (!$setting) return $default;
		return $setting->getValue();
	}

	public function newObserver($type): true|BattleReportObserver|ActivityReportObserver {
		if ($type === 'battle') {
			return new BattleReportObserver;
		}
		if ($type === 'act') {
			return new ActivityReportObserver;
		}
		return true;
	}

	public function setGlobal($name, $value): void {
		$setting = $this->em->getRepository(Setting::class)->findOneBy(['name'=>$name]);
		if (!$setting) {
			$setting = new Setting();
			$setting->setName($name);
			$this->em->persist($setting);
		}
		$setting->setValue($value);
		$this->em->flush($setting);
	}
	public function queueAction(Action $action): array {
		$action->setStarted(new DateTime("now"));

		// store in database and queue
		$max=0;
		foreach ($action->getCharacter()->getActions() as $act) {
			if ($act->getPriority()>$max) {
				$max=$act->getPriority();
			}
		}
		$action->setPriority($max+1);
		$this->em->persist($action);

		$this->em->flush();

		return array('success'=>true);
	}

	/* achievements */
	public function getAchievement(Character $character, $key) {
		# The below bypasses the doctrine cache, meaning it will always pull the current value from the database.
		$query = $this->em->createQuery('SELECT a FROM App\Entity\Achievement a WHERE a.character = :me AND a.type = :type ORDER BY a.id ASC')->setParameters(['me'=>$character, 'type'=>$key])->setMaxResults(1);
		$result = $query->getResult();
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	public function getAchievementValue(Character $character, $key) {
		if ($a = $this->getAchievement($character, $key)) {
			return $a->getValue();
		} else {
			return null;
		}
	}

	public function setAchievement(Character $character, $key, $value): void {
		$this->setMaxAchievement($character, $key, $value, false);
	}

	public function setMaxAchievement(Character $character, $key, $value, $only_raise=true): void {
		if ($a = $this->getAchievement($character, $key)) {
			if (!$only_raise || $a->getValue() < $value) {
				$a->setValue($value);
			}
		} else {
			$a = new Achievement;
			$a->setType($key);
			$a->setValue($value);
			$a->setCharacter($character);
			$this->em->persist($a);
			$character->addAchievement($a);
		}
	}

	public function addAchievement(Character $character, $key, $value=1): void {
		if ($value==0) return; // this way we can call this method without checking and it'll not update if not necessary
		$value = round($value);
		if ($a = $this->getAchievement($character, $key)) {
			$a->setValue($a->getValue() + $value);
		} else {
			$a = new Achievement;
			$a->setType($key);
			$a->setValue($value);
			$a->setCharacter($character);
			$this->em->persist($a);
			$character->addAchievement($a);
		}
	}
}
