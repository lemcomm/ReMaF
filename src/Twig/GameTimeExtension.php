<?php

namespace App\Twig;

use App\Service\AppState;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GameTimeExtension extends AbstractExtension {

	protected $appstate;
	protected $trans;

	public function __construct(AppState $appstate, TranslatorInterface $trans) {
		$this->appstate = $appstate;
		$this->trans = $trans;
	}

	public function getFilters() {
		return array(
			new TwigFilter('gametime', array($this, 'gametimeFilter'), array('is_safe' => array('html'))),
			new TwigFilter('realtime', array($this, 'realtimeFilter'), array('is_safe' => array('html'))),
		);
	}

	public function getFunctions() {
		return array(
			'gametime' => new TwigFunction('gametime', array($this, 'gametimeFilter'), array('is_safe' => array('html'))),
			'untilturn' => new TwigFunction('untilturn', array($this, 'untilTurnFunction'), array('is_safe' => array('html'))),
		);
	}

	public function realtimeFilter($seconds) {
		$days = round($seconds/86400);
		$hours = round($seconds/3600);
		$min = round($seconds/60);

		if ($days > 30) {
			return $this->trans->trans("realtime.forever");
		} elseif ($days > 1) {
			return $this->trans->trans("realtime.day", array('%d%'=>$days, '%count%'=>$days));
		} elseif ($hours > 1) {
			return $this->trans->trans("realtime.hour", array('%h%'=>$hours));
		} else {
			return $this->trans->trans("realtime.minute", array('%m%'=>$min));
		}
	}


	public function gametimeFilter($cycle=false, $format='normal') {
		if ($cycle===false) {
			$cycle = $this->appstate->getCycle();
		}

		// our in-game date - 6 days a week, 60 weeks a year = 1 year about 2 months
		// FIXME: lots of hardcoded values, including the hour counter that is regulated in crontab
		$year = floor($cycle/360)+1;
		$week = floor($cycle%360/6)+1;
		$day = ($cycle%6)+1;

		return $this->trans->trans("gametime.".$format, array('%year%'=>$year, '%week%'=>$week, '%day%'=>$day));
	}

	public function untilTurnFunction() {
		$next = ceil((date("G")+1)/6)*6; // remember to change this when the cronjob is changed

		$hours = $next - date("G") - 1;
		$minutes = 60 - date("i");

		$m_text = $this->trans->trans("minute", ['%count%'=>$minutes]);
		if ($hours>0) {
			$h_text = $this->trans->trans("hour", ['%count%'=>$hours]);
			return $this->trans->trans("untilturn", array('%h%'=>$hours, '%hours%'=>$h_text, '%m%'=>$minutes, '%minutes%'=>$m_text));
		} else {
			return $this->trans->trans("untilturn2", array('%m%'=>$minutes, '%minutes%'=>$m_text));
		}
	}


	public function getName() {
		return 'gametime_extension';
	}
}
