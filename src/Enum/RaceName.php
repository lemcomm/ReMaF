<?php

namespace App\Enum;

enum RaceName: string {
	# Under no circumstance should races in this list have hyphens. It breaks familiarity.
	case firstOne = 'first one';
	case secondOne = 'second one';
	case magitek = 'magitek';
	case human = 'human';
	case orc = 'orc';
	case ogre = 'ogre';
	case dragon = 'dragon';
	case wyvern = 'wyvern';
	case slime = 'slime';
	case elf = 'elf';
	case daimon = 'daimon';
}
