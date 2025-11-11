<?php

namespace App\Enum;

enum CharacterStatus: int {

	case currently = 13; # Pointer value for the below.
	case normal = 0;
	case battling = 1;
	case sieging = 2;
	case annexing = 3;
	case supporting = 4;
	case opposing = 5;
	case looting = 6;
	case blocking = 7;
	case granting = 8;
	case renaming = 9;
	case reclaiming = 10;
	case following = 11;
	case followed = 12;
	case newOccupant = 14;
	case training = 15;
	case researching = 16;
	case escaping = 17;
	case assigning = 18;
	case damaging = 19;
	case prebattle = 20;
	case prisoner = 21;
	case siegeLead = 22;
	case travelling = 23;

	case location = 50; # Pointer value for the below.
	case inWorld = 51;
	case inSettlement = 54;
	case inPlace = 55;
	case nearSettlement = 56;
	case atSettlement = 57;
	case atSea = 58;

	# Unread stuff
	case messages = 101;
	case requests = 102;
	case events = 103;

	#Military stuffs
	case soldiers = 200;
	case entourage = 201;

}
