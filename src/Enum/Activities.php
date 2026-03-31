<?php

namespace App\Enum;

enum Activities: string {
	case fightsSolo = 'solo fights';
	case fightsDuo = 'duo fights';
	case fightsTeam = 'team fights';
	case fightsFFA = 'free for all';
	case fightsAll = 'all fights';
}
