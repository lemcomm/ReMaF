<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\SecurityLog;
use App\Entity\Setting;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class AppState {

	private CommonService $common;
	private EntityManagerInterface $em;
	private TokenStorageInterface $tokenStorage;
	private RequestStack $requestStack;

	private array $languages = array(
		'en' => 'english',
		'de' => 'deutsch',
		'es' => 'español',
		'fr' => 'français',
		'it' => 'italiano'
		);

	public function __construct(CommonService $common, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, RequestStack $requestStack) {
		$this->common = $common;
		$this->em = $em;
		$this->tokenStorage = $tokenStorage;
		$this->requestStack = $requestStack;
	}

	public function availableTranslations(): array {
		return $this->languages;
	}

	public function getCharacter($required=true, $ok_if_dead=false, $ok_if_notstarted=false) {
		/* This used to throw exceptions rather than adding flashes and returning strings.
		The change was done in order to ensure that when you're somewhere you shouldn't be,
		that the game is smart enough to redirect you to the right spot.

		Technically speaking, the first two returns don't actually do anything, because they're
		intercepted by the Symfony Firewall and sent to the secuirty/detect route which does
		something similar. */
		# Check if we have a user first
		$token = $this->tokenStorage->getToken();
		if (!$token) {
			if (!$required) {
				return null;
			} else {
				return 'maf_login';
			}
		}
		$user = $token->getUser();
		if (! $user instanceof UserInterface) {
			if (!$required) {
				return null;
			} else {
				return 'maf_login';
			}
		}

		# Let the ban checks begin...
		if ($user->isBanned()) {
			if (!$required) { return null; } else { throw new AccessDeniedException($user->isBanned()); }
		}

		# Check if we have a character, if not redirect to character list.
		$character = $user->getCurrentCharacter();
		$session = $this->requestStack->getSession();
		if (!$character) {
			if (!$required) {
				return null;
			} else {
				$session->getFlashBag()->add('error', 'error.missing.character');
				return 'maf_chars';
			}
		}
		# Check if it's okay that the character is dead. If not, then character list they go.
		if (!$ok_if_dead && !$character->isAlive()) {
			if (!$required) {
				return null;
			} else {
				$session->getFlashBag()->add('error', 'error.missing.soul');
				return 'maf_chars';
			}
		}
		# Check if it's okay that the character is not started. If not, then character list they go.
		if (!$ok_if_notstarted && !$character->getLocation()) {
			if (!$required) {
				return null;
			} else {
				$session->getFlashBag()->add('error', 'error.missing.location');
				return 'maf_chars';
			}
		}

		if ($character->isAlive()) {
			$character->setLastAccess(new DateTime('now')); // no flush here, most actions will issue one anyways and we don't need 100% reliability
		}
		return $character;
	}

	public function getDate($cycle=null): array {
		// our in-game date - 6 days a week, 60 weeks a year = 1 year about 2 months
		if (null===$cycle) {
			$cycle = $this->getCycle();
		}

		$year = floor($cycle/360)+1;
		$week = floor($cycle%360/6)+1;
		$day = ($cycle%6)+1;
		return array('year'=>$year, 'week'=>$week, 'day'=>$day);
	}

	public function getCycle(): int {
		return (int)($this->getGlobal('cycle', 0));
	}

	public function getGlobal($name, $default=false) {
		$setting = $this->em->getRepository(Setting::class)->findOneBy(['name'=>$name]);
		if (!$setting) return $default;
		return $setting->getValue();
	}
	public function setGlobal($name, $value): void {
		$setting = $this->em->getRepository(Setting::class)->findOneBy(['name'=>$name]);
		if (!$setting) {
			$setting = new Setting();
			$setting->setName($name);
			$this->em->persist($setting);
		}
		$setting->setValue($value);
		$this->em->flush($setting);
	}


	public function setSessionData(Character $character): void {
		$session = $this->requestStack->getSession();
		$session->clear();
		if ($character->isAlive()) {
			if ($character->getInsideSettlement()) {
				$session->set('nearest_settlement', $character->getInsideSettlement());
			} elseif ($character->getLocation()) {
				$near = $this->common->findNearestSettlement($character);
				$session->set('nearest_settlement', $near[0]);
			}
			#$this->session->set('soldiers', $character->getLivingSoldiers()->count());
			#$this->session->set('entourage', $character->getLivingEntourage()->count());
			$query = $this->em->createQuery('SELECT s.id, s.name FROM App:Settlement s WHERE s.owner = :me');
			$query->setParameter('me', $character);
			$settlements = array();
			foreach ($query->getResult() as $row) {
				$settlements[$row['id']] = $row['name'];
			}
			$session->set('settlements', $settlements);
			$realms = array();
			foreach ($character->findRulerships() as $realm) {
				$realms[$realm->getId()] = $realm->getName();
			}
			$session->set('realms', $realms);
		}
	}

	public function findEmailOptOutToken(User $user): string {
		return $user->getEmailOptOutToken()?:$this->generateEmailOptOutToken($user);
	}

	/**
	 * @throws \Exception
	 */
	public function generateEmailOptOutToken(User $user): string {
		$token = $this->generateToken();
		$user->setEmailOptOutToken($token);
		$this->em->flush();
		return $token;
	}

	/**
	 * @throws \Exception
	 */
	public function generateToken($length = 128, $method = 'trimbase64'): string {
		if ($method = 'trimbase64') {
			$token = rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
		}
		return $token;
	}

        public function generateAndCheckToken($length, $check = 'User', $against = 'reset_token'): bool|string {
                $valid = false;
                $token = false;
                $em = $this->em;
                if ($check == 'User') {
                        while (!$valid) {
                                $token = $this->generateToken($length, 'bin2hex');
                                $result = $em->getRepository(User::class)->findOneBy([$against => $token]);
                                if (!$result) {
                                        $valid = true;
                                }
                        }
                }
                return $token;
        }

	public function logSecurityViolation($ip, $route, $user, $type): void {
		$em = $this->em;
		$datetime = new DateTime();
		$log = new SecurityLog;
		$em->persist($log);
		$log->setUser($user);
		$log->setType($type);
		$log->setTimestamp($datetime);
		$log->setRoute($route);
		$log->setIp($ip);
		$em->flush();
	}


}
