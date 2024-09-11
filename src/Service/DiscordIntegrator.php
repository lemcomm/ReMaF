<?php

namespace App\Service;

class DiscordIntegrator {

	protected mixed $generalHook;
	protected mixed $olympusHook;
	protected mixed $paymentsHook;
	protected mixed $errorsHook;

	public function __construct() {
		$this->generalHook = $_ENV['DISCORD_WEBHOOK_GENERAL'];
		if (!str_starts_with(strtolower($this->generalHook), "http")) {
			$this->generalHook = false;
		}
		$this->olympusHook = $_ENV['DISCORD_WEBHOOK_OLYMPUS'];
		if (!str_starts_with(strtolower($this->olympusHook), "http")) {
			$this->olympusHook = false;
		}
		$this->paymentsHook = $_ENV['DISCORD_WEBHOOK_PAYMENT'];
		if (!str_starts_with(strtolower($this->paymentsHook), "http")) {
			$this->paymentsHook = false;
		}
		$this->errorsHook = $_ENV['DISCORD_WEBHOOK_ERRORS'];
		if (!str_starts_with(strtolower($this->errorsHook), "http")) {
			$this->errorsHook = false;
		}
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
