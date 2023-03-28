<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscordIntegrator {

	protected EntityManagerInterface $em;
	protected AppState $appstate;
	protected TranslatorInterface $trans;
	protected mixed $generalHook;
	protected mixed $olympusHook;
	protected mixed $paymentsHook;
	protected mixed $errorsHook;

	public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, AppState $appstate) {
		$this->em = $em;
		$this->appstate = $appstate;
		$this->trans = $translator;
		$this->generalHook = $_ENV['DISCORD_WEBHOOK_GENERAL'];
		$this->olympusHook = $_ENV['DISCORD_WEBHOOK_OLYMPUS'];
		$this->paymentsHook = $_ENV['DISCORD_WEBHOOK_PAYMENT'];
		$this->errorsHook = $_ENV['DISCORD_WEBHOOK_ERRORS'];
	}

	private function curlToDiscord($json, $webhook): void {
		$curl = curl_init($webhook);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_exec($curl);
	}

	public function pushToGeneral($text): void {
		if ($this->generalHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->generalHook);
		}
	}

	public function pushToOlympus($text): void {
		if ($this->olympusHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->olympusHook);
		}
	}

	public function pushToPayments($text): void {
		if ($this->paymentsHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->paymentsHook);
		}
	}

	public function pushToErrors($text): void {
		if ($this->errorsHook) {
			$this->curlToDiscord(json_encode(['content' => $text]), $this->errorsHook);
		}
	}

}
