<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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

	public function npcTranslate($content): string {
		return $this->trans->trans($content['key'], $content['data'], 'npctranslate');
	}
}
