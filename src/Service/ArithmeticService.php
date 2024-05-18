<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\ActivityReportObserver;
use App\Entity\BattleReportObserver;
use App\Entity\Character;
use App\Entity\Setting;
use App\Entity\Skill;
use App\Entity\SkillType;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Here, we do math. Or maths.
 * Ideally, this service will exist on it's own, without calling other App services, only vendor code as needed.
 */
class ArithmeticService {

	/**
	 * Returns the Circumcircle Radius measurement for an equal sided polygon of given sides with given side lengths.
	 * @param int $sides
	 * @param int $sideLength
	 *
	 * @return float|int
	 */
	public function polygonCircumcircleRadius(int $sides, int $sideLength): float|int {
		return $sideLength/(2*sin(pi()/$sides));
	}

}
