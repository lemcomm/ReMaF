<?php

namespace App\Twig;

use App\Entity\Character;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Psr\Log\LoggerInterface;


class LinksExtension extends AbstractExtension {

	private EntityManagerInterface $em;
	private UrlGeneratorInterface $generator;
	private TranslatorInterface $translator;
	private LoggerInterface $logger;
	private RequestStack $request_stack; // for debugging until I've fixed the bug below where it is used

	// FIXME: type hinting for $translator removed because the addition of LoggingTranslator is breaking it
	public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $generator, TranslatorInterface $translator, LoggerInterface $logger, RequestStack $rs) {
		$this->em = $em;
		$this->generator = $generator;
		$this->translator = $translator;
		$this->logger = $logger;
		$this->request_stack = $rs;
	}

	public function getFunctions(): array {
		return array(
			'objectlink' => new TwigFunction('link', array($this, 'ObjectLink'), array('is_safe' => array('html'))),
			'idnamelink' => new TwigFunction('*_link', array($this, 'IdNameLink'), array('is_safe' => array('html'))),
		);
	}

	public function getFilters(): array {
		return array(
			new TwigFilter('wikilinks', array($this, 'wikilinksFilter'), array('is_safe' => array('html'))),
			new TwigFilter('manuallinks', array($this, 'manuallinksFilter'), array('is_safe' => array('html'))),
			);
	}

	public function wikilinksFilter($input): array|string|null {
		$pattern = '/\[[a-zA-Z]+:[0-9]+]/';
		return preg_replace_callback($pattern, array(get_class($this), "wikilinksReplacer"), $input);
	}

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function wikilinksReplacer($matches): string {
		$link = '';
		foreach ($matches as $match) {
			$data = explode(':', trim($match, "[]"));
			$id = $data[1];
			switch (strtolower($data[0])) {
				case 'r':
				case 'realm':
					$type = 'Realm';
					break;
				case 's':
				case 'settlement':
				case 'e':
				case 'estate':
					$type = 'Settlement';
					break;
				case 'c':
				case 'character':
				case 'n':
				case 'noble':
					$type = 'Character';
					break;
				case 'assoc':
				case 'association':
				case 'guild':
				case 'religion':
				case 'corps':
				case 'company':
				case 'temple':
				case 'order':
				case 'faith':
				case 'g':
					$type = 'Association';
					break;
				case 'act':
				case 'activity':
					$type = 'Activity';
					break;
				case 'p':
				case 'poi':
				case 'place':
				case 'placeofinterest':
					$type = 'Place';
					break;
				case 'vote':
					$type = 'Election';
					break;
#These presently do not actually reference anything and using them will error out an entire conversation for all users.
#				case 'i':
#				case 'item':
#					$type = 'Item';
#					break;
				case 'a':
				case 'artifact':
					$type = 'Artifact';
					break;
				case 'w':
				case 'war':
					$type = 'War';
					break;
				case 'news':
				case 'newspaper':
				case 'newsedition':
				case 'edition':
				case 'pub':
				case 'publication':
					$type = 'NewsEdition';
					break;
				case 'pos':
				case 'position':
				case 'realmpos':
				case 'realmposition':
					$type = 'RealmPosition';
					break;
				case 'h':
				case 'f':
				case 'house':
				case 'dynasty':
				case 'clan':
				case 'family':
					$type = 'House';
					break;
				case 'u':
				case 'unit':
					$type = 'Unit';
					break;
				case 'conv':
				case 'topic':
				case 'conversation':
					$type = 'Conversation';
					break;
				case 'l':
				case 'law':
					$type = 'Law';
					break;
				case 'd':
				case 'deity':
				case 'god':
					$type = 'Deity';
					break;
				case 'j':
				case 'journal':
					$type = 'Journal';
					break;
				default:
					return "[<em>invalid reference</em>]";
			}
			$entity = $this->em->getRepository('App\Entity\\'.$type)->find($id);
			if ($entity) {
				if ($type == 'Unit') {
					$url = $this->generator->generate($this->getLink($type), array('unit' => $id));
				} elseif ($type == 'NewsEdition') {
					$url = $this->generator->generate($this->getLink($type), array('edition' => $id));
				} elseif ($type == 'Conversation') {
					if($entity->getLocalFor()) {
						$url = $this->generator->generate('maf_conv_local');
					} else {
						$url = $this->generator->generate($this->getLink($type), array('conv' => $id));
					}
				} else {
					$url = $this->generator->generate($this->getLink($type), array('id' => $id));
				}
				if (!in_array($type, ['NewsEdition', 'Unit', 'Conversation', 'Journal'])) {
					$name = $entity->getName();
				} elseif ($type == 'Unit') {
					$name = $entity->getSettings()->getName();
				} elseif ($type == 'Conversation') {
					if ($entity->getLocalFor()) {
						$name = 'Local Conversation';
					} else {
						$name = $entity->getTopic();
					}
				} elseif ($type == 'Journal') {
					$name = $entity->getTopic();
				} else {
					$name = $entity->getPaper()->getName();
				}
				$link .= '<a href="'.$url.'">'.$name.'</a>';
			} else {
				$link = "[<em>invalid reference</em>]";
			}
		}
		return $link;
	}

	public function manuallinksFilter($input): array|string|null {
		$pattern = '/\[[a-zA-Z_]+]/';
		return preg_replace_callback($pattern, array(get_class($this), "manuallinksReplacer"), $input);
	}

	private function manuallinksReplacer($matches): string {
		$link = '';
		foreach ($matches as $match) {
			// FIXME: this makes sure the translation string fits, but it should restore at least first-letter case
			$page = strtolower(trim($match, "[]"));
			$url = $this->generator->generate('maf_manual', array('page' => $page));
			$name = $this->translator->trans("manual.".$page);
			$link .= '<a href="'.$url.'">'.$name.'</a>';
		}
		return $link;
	}

	private function getLink($name): string {
		return match (strtolower($name)) {
			'activity' => 'maf_activity',
			'activityreport' => 'maf_activity_report',
			'character' => 'maf_char_view',
			'settlement' => 'maf_settlement',
			'battle' => 'maf_queue_battle',
			'battlereport' => 'maf_battlereport',
			'realm' => 'maf_realm',
			'realmposition' => 'maf_position',
			'eventlog' => 'maf_events_log',
			'feature', 'featuretype' => 'maf_info_featuretype',
			'building', 'buildingtype' => 'maf_info_buildingtype',
			'entourage', 'entouragetype' => 'maf_info_entouragetype',
			'equipmenttype' => 'maf_info_equipmenttype',
			'action' => 'maf_queue_details',
			'election' => 'maf_realm_vote',
			'mercenaries' => 'maf_mercenaries',
			'quest' => 'maf_quests_details',
			'artifact' => 'maf_artifact_details',
			'war' => 'maf_war_view',
			'newsedition' => 'maf_news_read',
			'house' => 'maf_house',
			'place' => 'maf_place',
			'unit' => 'maf_unit_info',
			'conversation' => 'maf_conv_read',
			'assoc', 'association' => 'maf_assoc',
			'law' => 'maf_law',
			'deity' => 'maf_deity',
			'journal' => 'maf_journal',
			default => 'invalid link entity "' . $name . '", this should never happen!',
		};
	}

	// TODO: pluralization!
	public function ObjectLink($entity, $raw=false, $absolute=false, $number=1): string {
		if (!is_object($entity)) {
			$this->logger->error("link() called without object - $entity"); // fuck, it's impossible to get a backtrace! - out of memory
			$this->logger->error("dump: ".\Doctrine\Common\Util\Debug::dump($entity, 1, true, false));
			if ($this->request_stack->getCurrentRequest()) {
				$this->logger->error("request: ".$this->request_stack->getCurrentRequest()->getRequestUri());
			}
			return "[invalid object]";
		}
		$classname = implode('', array_slice(explode('\\', get_class($entity)), -1));
		$linktype = null;
		switch ($classname) {
			case 'GeoFeature':
				$entity = $entity->getType();
                // break missing, intentional
			case 'FeatureType':
				$id = $entity->getId();
				$name = $this->featurename($entity->getName());
				$linktype = 'feature';
				break;
			case 'BattleReport':
				$id = $entity->getId();
				$loc = $entity->getLocationName();
				$name = $this->translator->trans($loc['key'], array('%location%'=>$loc['name']));
				break;
			case 'ActivityReport':
				$id = $entity->getId();
				$name = $this->translator->trans('activity.'.$entity->getType()->getName().'.'.$entity->getSubType()->getName());
				$linktype = 'report';
				break;
			case 'Building':
				$entity = $entity->getType();
                // break missing, intentional
			case 'BuildingType':
				$id = $entity->getId();
				$name = $this->buildingname($entity->getName());
				$linktype = 'building';
				break;
			case 'Entourage':
				$id = $entity->getId();
				$name = $this->npcname($entity->getType()->getName(), $number);
				$linktype = 'npc';
				break;
			case 'EntourageType':
				$id = $entity->getId();
				$name = $this->npcname($entity->getName(), $number);
				$linktype = 'npc';
				break;
			case 'Action':
				$id = $entity->getId();
				$name = $this->actionname($entity->getType());
				$linktype = 'action';
				break;
			case 'Equipment':
				$entity = $entity->getType();
                // break missing, intentional
			case 'EquipmentType':
				$id = $entity->getId();
				$name = $this->equipmentname($entity->getName());
				$linktype = 'equipment';
				break;
			case 'Quest':
			case 'War':
				$id = $entity->getId();
				$name = $entity->getSummary();
				break;
			case 'NewsEdition':
				$id = $entity->getId();
				$name = $entity->getPaper->getName();
				break;
			case 'Law':
				$id = $entity->getId();
				$name = $entity->getTitle();
				break;
			case 'Journal':
				$id = $entity->getId();
				$name = $entity->getTopic();
				break;
			case 'Unit':
				$id = $entity->getId();
				$name = $entity->getSettings()->getName();
				$linktype = 'unit';
				break;
			default:
				$id = $entity->getId();
				$name = $entity->getName();
		}
		// setting link-types only for some entities:
		switch ($classname) {
			case 'Character':		$linktype = 'character'; break;
			case 'EquipmentType':		$linktype = 'equipment'; break;
			case 'RealmPosition':		$linktype = 'position'; break;
		}

		return $this->linkhelper($this->getLink($classname), $id, $name, $linktype, $raw, $absolute);
	}

	public function IdNameLink($type, $id, $name = null, $raw=false, $absolute=false): string {
		$linktype = null;
		switch (strtolower($type)) {
			case 'character':
				$linktype = 'character';
				if (!$name) {
					$name = $this->em->getRepository(Character::class)->find($id)->getName();
					# Yes, this exists solely for battle reports. *sigh*
					# For the record, if you fail to declare $name, linkhelper, below, will fail out. :)
				}
				break;
			case 'geofeature':
			case 'feature':         $linktype = 'feature'; $name = $this->featurename($name); break;
			case 'building':
			case 'buildingtype':    $linktype = 'building'; $name = $this->buildingname($name); break;
			case 'entourage':
			case 'entouragetype':   $linktype = 'npc'; $name = $this->npcname($name); break;
			case 'weapon':
			case 'armour':
			case 'equipment':
			case 'equipmenttype':   $linktype = 'equipment'; $name = $this->equipmentname($name); break;
		}
		return $this->linkhelper($this->getLink($type), $id, $name, $linktype, $raw, $absolute);
	}



	private function featurename($name): string {
		return $this->translator->trans("feature.".$name, array(), "economy");
	}

	private function buildingname($name): string {
		return $this->translator->trans("building.".$name, array(), "economy");
	}

	private function npcname($name, $number=1): string {
		return $this->translator->trans("npc.".$name, ['count'=>$number]);
	}

	private function actionname($type): string {
		return $this->translator->trans("queue.".$type, array(), "actions");
	}

	private function equipmentname($name): string {
		return $this->translator->trans("item.".$name);
	}



	private function linkhelper($path, $id, $name, $class=null, $raw=false, $absolute=false): string {
		if ($absolute) {
			$type = UrlGeneratorInterface::ABSOLUTE_URL;
		} else {
			$type = UrlGeneratorInterface::ABSOLUTE_PATH;
		}

		// FIXME: above still not working, so trying with all absolute paths now
		$type = UrlGeneratorInterface::ABSOLUTE_URL;

		if ($class === 'unit') {
			$url = $this->generator->generate($path, array('unit' => $id), $type);
		} elseif ($class === 'report') {
			$url = $this->generator->generate($path, array($class => $id), $type);
		} else {
			$url = $this->generator->generate($path, array('id' => $id), $type);
		}
		if ($raw) return $url;
		$link = '<a ';
		if ($class !== 'report') { $link .= 'class="link_'.$class.'" '; }
		$link .= 'href="'.$url.'">'.$name.'</a>';
		return $link;
	}


	public function getName(): string {
		return 'links_extension';
	}
}
