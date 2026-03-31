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
			$texts = str_split($text, 1000);
			if (strlen($text) < 1000) {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($text)]), $this->generalHook);
			} else {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($texts[0])]), $this->generalHook);
				unset($texts[0]);
				foreach ($texts as $each) {
					sleep(0.1);
					$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($each)]), $this->generalHook);
				}
			}
		}
	}

	public function pushToOlympus($text): void {
		if ($this->olympusHook) {
			$texts = str_split($text, 1000);
			if (strlen($text) < 1000) {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($text)]), $this->olympusHook);
			} else {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($texts[0])]), $this->olympusHook);
				unset($texts[0]);
				foreach ($texts as $each) {
					sleep(0.1);
					$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($each)]), $this->olympusHook);
				}
			}
		}
	}

	public function pushToPayments($text): void {
		if ($this->paymentsHook) {
			$texts = str_split($text, 1000);
			if (strlen($text) < 1000) {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($text)]), $this->paymentsHook);
			} else {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($texts[0])]), $this->paymentsHook);
				unset($texts[0]);
				foreach ($texts as $each) {
					sleep(0.1);
					$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($each)]), $this->paymentsHook);
				}
			}
		}
	}

	public function pushToErrors($text): void {
		if ($this->errorsHook) {
			$texts = str_split($text, 1000);
			if (strlen($text) < 1000) {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($text)]), $this->errorsHook);
			} else {
				$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($texts[0])]), $this->errorsHook);
				unset($texts[0]);
				foreach ($texts as $each) {
					sleep(0.1);
					$this->curlToDiscord(json_encode(['content' => $this->convertToMarkdown($each)]), $this->errorsHook);
				}
			}
		}
	}

	private function convertToMarkdown($text): string {
		$text = str_replace(["<i>", "</i>"], "*", $text); # Replace <i> and </i>
		$text = str_replace(["<b>", "</b>"], "**", $text); # Replace <b> and </b>
		$text = str_replace(["<u>", "</u>"], "", $text); # Remove <u> and </u>, not that I think we use it anywhere.
		return $text;
	}

}
