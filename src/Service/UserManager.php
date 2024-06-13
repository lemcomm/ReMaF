<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLimits;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserManager {
	private string $genome_all = 'abcdefghijklmnopqrstuvwxyz';
	private int $genome_setsize = 15;
	private EntityManagerInterface $em;
	private UserPasswordHasherInterface $passwordHasher;
	private AppState $app;

	public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, AppState $app) {
		$this->em = $em;
		$this->passwordHasher = $passwordHasher;
		$this->app = $app;
	}

	public function refreshUser( UserInterface $user ) {
		return $this->em->getRepository(User::class)->findOneBy( array( 'id' => $user->getId() ) );
	}

	public function supportsClass( $class ) {
		return $class instanceof User;
	}

	public function createUser(?User $user = null, $notifications = false): User{
		if (!$user) {
			$user = new User;
		}
		$user->setCreated(new \DateTime("now"));
		$user->setNewCharsLimit(3);
		$user->setArtifactsLimit(0);
		$user->setNotifications($notifications);
		$user->setNewsletter($notifications);
		$user->setCredits(0);
		$user->setVipStatus(0);
		$user->setRestricted(false);
		// new users subscription is 30-days, as in the old trial, but mostly because our payment interval is monthly for them
		$until = new \DateTime("now");
		$until->add(new \DateInterval('P30D'));
		$user->setAccountLevel(10)->setPaidUntil($until);
		$user->setAppKey(sha1(time()."-maf-".mt_rand(0,1000000)));
		$user->setGenomeSet($this->createGenomeSet());
		$user->addRole('ROLE_USER');

		return $user;
	}

	public function addUserDetails(User $user, string $username, string $plainPassword, string $email, string $displayName = '(anonymous)'): User {
		$user->setUsername($username);
		$user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
		$user->setLastPassword(new \DateTime('now'));
		$user->setEmail($email);
		$user->setDisplayName($displayName);
		return $user;
	}

	public function createGenomeSet() {
		$genome = str_split($this->genome_all);

		while (count($genome) > $this->genome_setsize) {
		    $pick = array_rand($genome);
		    unset($genome[$pick]);
		}

		return implode('', $genome);
	}

	public function calculateCharacterSpawnLimit(User $user, $refresh = false) {
		$newest = null;
		$count = 0;
		foreach ($user->getActiveCharacters() as $char) {
			if ($char->getLocation() && $char->getCreated() > $newest) {
				$newest = $char->getCreated();
			}
			$count++;
		}
		if ($count < 5) {
			$change = 0;
		} elseif (11 > $count && $count > 3) {
			$change = 3;
		} elseif (26 > $count && $count > 10) {
			$change = 7;
		} else {
			$change = 15;
		}
		if ($newest) {
			$newest->modify('+'.$change.' days');
			if ($newest !== $user->getNextSpawnTime()) {
				$user->setNextSpawnTime($newest);
			}
		}
		# If there are no characters, this can legitimately return null.
		return $newest;
	}

	public function checkIfUserCanSpawnCharacters(User $user, $refresh = false) {
		$now = new \DateTime('now');
		if ($user->getNextSpawnTime() === null || $refresh) {
			$next = $this->calculateCharacterSpawnLimit($user, $refresh);
		} else {
			$next = $user->getNextSpawnTime();
		}
		if ($next) {
			if ($user->getActiveCharacters()->count() > 3 && $next > $now) {
				return false;
			} else {
				return true;
			}
		} else {
			# Next can only be null if there are no characters to check against.
			return true;
		}

	}

	public function createLimits(User $user) {
		$limits = new UserLimits();
		$limits->setUser($user);
		$limits->setPlaces(4);
		$limits->setArtifacts(max(0, $user->getArtifactsLimit()));
		$limits->setArtifactSubBonus(false);
		$limits->setPlacesDate(new DateTime('now')); #TODO: Remove this property from usage.
		$this->em->persist($limits);
		return $limits;
	}

	public function findEmailOptOutToken(User $user): string {
		return $user->getEmailOptOutToken()?:$this->generateEmailOptOutToken($user);
	}

	/**
	 * @throws Exception
	 */
	public function generateEmailOptOutToken(User $user): string {
		$token = $this->app->generateToken();
		$user->setEmailOptOutToken($token);
		$this->em->flush();
		return $token;
	}

	public function legacyPasswordCheck(User $user): bool {
		if (str_starts_with($user->getPassword(), '$argon2')) {
			return false;
		}
		return true;
	}

}
