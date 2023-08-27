<?php

namespace App\Twig;

use App\Entity\DungeonEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DungeonEventFilterExtension extends AbstractExtension {

	private EntityManagerInterface $em;
	private TranslatorInterface $translator;
	private LinksExtension $links;

	// FIXME: type hinting for $translator removed because the addition of LoggingTranslator is breaking it
	public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, LinksExtension $links) {
		$this->em = $em;
		$this->translator = $translator;
		$this->links = $links;
	}

	public function getName(): string {
		return 'dungeon_event_filter';
	}

	public function getFilters(): array {
		return array(
			'dungeoneventfilter' => new TwigFilter('dungeonEventFilter', array($this, 'dungeonEventFilter'), array('is_safe' => array('html'))),
		);
	}

	public function dungeonEventFilter(DungeonEvent $event): string {
		$data = $this->parseData($event->getData());
		return $this->translator->trans($event->getContent(), $data, "dungeons");
	}

	private function parseData($input): array {
		if (!$input) return array();
		$data=array();
		foreach ($input as $key=>$value) {
			switch ($key) {
				case 'd':
				case 'target':
					$dungeoneer = $this->em->getRepository('DungeonBundle:Dungeoneer')->find($value);
					if ($dungeoneer) {
						$data['%'.$key.'%'] = $this->links->ObjectLink($dungeoneer->getCharacter());
					} else {
						$data['%'.$key.'%'] = "(#$value)"; // FIXME: catch and report error, this should never happen!
					}
					break;
				case 'monster':
					$data['%'.$key.'%'] = $this->translator->trans("$key.$value", array('count'=>1), "dungeons");
					break;
				case 'size':
					$data['%'.$key.'%'] = $this->translator->trans("$key.$value", array(), "dungeons");
					break;
				case 'card':
					$card = $this->em->getRepository('DungeonBundle:DungeonCardType')->find($value);
					if ($card) {
						$data['%'.$key.'%'] = '<em>'.$this->translator->trans('card.'.$card->getName().'.title', array(), "dungeons").'</em>';
					} else {
						$data['%'.$key.'%'] = "[#$value]"; // FIXME: catch and report error, this should never happen!
					}
					break;
				default:
					$data['%'.$key.'%']=$value;
			}
		}
		return $data;
	}

}
