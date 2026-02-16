<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NPCTranslateExtension extends AbstractExtension {

	/*
	 * So, this works a bit differently from the other extensions, and is intended to be ran first.
	 * The intent is that this will turn translation strings and translation data into actual messages that MessageTranslateExtension can then work on.
	 * So it'll turn a string into "[c:####] did thing at [s:####]" so those can be populated.
	 * This means that when you generate messages into these, you need to put the actual wikilink in the data you're packing to translate rather than the object IDs.
	 */

	public function __construct(private TranslatorInterface $trans) {
	}

	public function getFunctions(): array {
		return array(
			new TwigFunction('npctrans', $this->npctranslate(...), array('is_safe' => array('html'))),
		);
	}

	public function npcTranslate($content, $sysData) {
		# RegEx string to match {trans:THING}
		$pattern = '/({trans:[a-zA-Z0-9.\- ]+})+/';
		# Convert $Message->getSystemConent() to an array.
		$sysData = explode($sysData, ',');
		$matches = [];
		# Make sure we have things to replace. More an optimization than anyhting.
		$hits = preg_match($pattern, $content, $matches);
		if ($hits) {
			# Loop through each match to find/replace
			foreach ($matches as $match) {
				# Trim the sting of '{trans:' and '}' to get the translation key.
				$str = ltrim(rtrim($match, '}'), '{trans:');
				# Convert remaining intro translated string using $sysData as keys=>values
				$str = $this->trans->trans($str, $sysData, 'npctranslate');
				# Actually find and replace each hit in the message content itself.
				$content = preg_replace($pattern, $str, $content, 1);
			}
		}
		return $content;
	}

}
