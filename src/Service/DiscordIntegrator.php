<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\MailEntry;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscordIntegrator {

	protected $em;
	protected $appstate;
	protected $trans;
	protected $generalHook;
	protected $olympusHook;
	protected $paymentsHook;
	protected $errorsHook;

	public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, AppState $appstate) {
		$this->em = $em;
		$this->appstate = $appstate;
		$this->trans = $translator;
		$this->generalHook = $_ENV['DISCORD_WEBHOOK_GENERAL'];
		$this->olympusHook = $_ENV['DISCORD_WEBHOOK_OLYMPUS'];;
		$this->paymentsHook = $_ENV['DISCORD_WEBHOOK_PAYMENT'];;
		$this->errorsHook = $_ENV['DISCORD_WEBHOOK_ERRORS'];;
	}

	private function curlToDiscord($json, $webhook) {
		$curl = curl_init($webhook);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
	}

	public function pushToGeneral($text) {
		if ($this->generalHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->generalHook);
		}
	}

	public function pushToOlympus($text) {
		if ($this->olympusHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->olympusHook);
		}
	}

	public function pushToPayments($text) {
		if ($this->paymentsHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->paymentsHook);
		}
	}

	public function pushToErrors($text) {
		if ($this->errorsHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->errorsHook);
		}
	}

}
