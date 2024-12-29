<?php

namespace App\Service;

use App\Entity\Code;
use App\Entity\CreditHistory;
use App\Entity\User;
use App\Entity\UserLimits;
use App\Entity\UserPayment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Patreon\API as PAPI;
use Patreon\OAuth as POA;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Contracts\Translation\TranslatorInterface;


class PaymentManager {
	private mixed $ruleset;
	private string $stripeSecret;
	private string $stripeVersion;
	private array $stripePrices;
	private array $patreonAlikes = ['patreon'];

	public function __construct(
		private EntityManagerInterface $em,
		private TranslatorInterface $translator,
		private LoggerInterface $logger,
		private MailManager $mailer,
		private UserManager $usermanager,
		private AppState $app
	) {
		$this->ruleset = $_ENV['RULESET'];
		$this->stripeSecret = $_ENV['STRIPE_SECRET'];
		$this->stripeVersion = $_ENV['STRIPE_VERSION'];
		$this->stripePrices = [
			'USD' => [
				2 => $_ENV['STRIPEUSD2'],
				5 => $_ENV['STRIPEUSD5'],
				10 => $_ENV['STRIPEUSD10'],
				20 => $_ENV['STRIPEUSD20'],
				50 => $_ENV['STRIPEUSD50'],
				100 => $_ENV['STRIPEUSD100'],
			],
			'EUR' => [
				2 => $_ENV['STRIPEEUR2'],
				5 => $_ENV['STRIPEEUR5'],
				10 => $_ENV['STRIPEEUR10'],
				20 => $_ENV['STRIPEEUR20'],
				50 => $_ENV['STRIPEEUR50'],
				100 => $_ENV['STRIPEEUR100'],
			],
			'BRL' => [
				2 => $_ENV['STRIPEBRL2'],
				5 => $_ENV['STRIPEBRL5'],
				10 => $_ENV['STRIPEBRL10'],
				20 => $_ENV['STRIPEBRL20'],
				50 => $_ENV['STRIPEBRL50'],
				100 => $_ENV['STRIPEBRL100'],
			]
		];
	}

	public function getPaymentLevels(?User $user = null, $system = false): array {
		if ($this->ruleset === 'maf') {
			return [
				 0 =>	array('name' => 'storage',	'characters' =>    0, 'fee' =>   0, 'selectable' => false, 'patreon'=>false, 'creator'=>false),
				10 =>	array('name' => 'trial',	'characters' =>   15, 'fee' =>   0, 'selectable' => true,  'patreon'=>false, 'creator'=>false),
				23 =>	array('name' => 'supporter',	'characters' =>	  15, 'fee' => 250, 'selectable' => true,  'patreon'=>false,	'creator'=>false),
				20 =>	array('name' => 'basic',	'characters' =>   10, 'fee' => 200, 'selectable' => false,  'patreon'=>false, 'creator'=>false),
				21 =>	array('name' => 'volunteer',	'characters' =>   10, 'fee' =>   0, 'selectable' => false, 'patreon'=>false, 'creator'=>false),
				22 =>   array('name' => 'traveler',	'characters' =>   10, 'fee' =>   0, 'selectable' => false,  'patreon'=>200,   'creator'=>'andrew'),
				40 =>	array('name' => 'intense',	'characters' =>   25, 'fee' => 300, 'selectable' => false,  'patreon'=>false, 'creator'=>false),
				41 =>	array('name' => 'developer',	'characters' =>   25, 'fee' =>   0, 'selectable' => false, 'patreon'=>false, 'creator'=>false),
				42 =>   array('name' => 'explorer',	'characters' =>   25, 'fee' =>   0, 'selectable' => false,  'patreon'=>300,   'creator'=>'andrew'),
				50 =>	array('name' => 'ultimate',	'characters' =>   50, 'fee' => 400, 'selectable' => false,  'patreon'=>false, 'creator'=>false),
				51 =>   array('name' => 'explorer+',	'characters' =>   50, 'fee' =>   0, 'selectable' => false,  'patreon'=>400,   'creator'=>'andrew'),
			];
		}
		return [0 => ['name' => 'user', 'characters' => 5, 'fee' => 0, 'selectable' => false, 'patreon' => false, 'creator' => false]];
	}

	public function calculateUserFee(User $user) {
		$days = 0;
		$now = new \DateTime("now");
		if ($user->getLastLogin()) {
			$diff = $user->getLastLogin()->diff($now);
			$days = $diff->d;
		}

		$fees = $this->getPaymentLevels();
		if ($days>60) {
			$fee = $fees[0]['fee'];
		} else {
			$fee = $fees[$user->getAccountLevel()]['fee'];
			if ($user->getVipStatus() >= 20) {
				// legend or immortal = free basic account
				$fee = max(0, $fee - $fees[20]['fee']);
			}
		}
		return $fee;
	}

	public function getCostOfHeraldry(): int {
		return 250;
	}


	public function calculateRefund(User $user): float {
		$today = new \DateTime("now");
		$days = $today->diff($user->getPaidUntil())->format("%a");
		$today->modify('+1 month');
		$month = $today->diff(new \DateTime("now"))->format("%a");

		$refund = ceil( ($days/$month) * $this->calculateUserFee($user) );
		return $refund;
	}

	public function calculateUserMaxCharacters(User $user) {
		$fees = $this->getPaymentLevels();
		return $fees[$user->getAccountLevel()]['characters'];
	}

	public function paymentCycle($patronsOnly = false): array {
		$this->logger->info("Payment Cycle...");
		$free = 0;
		$patronCount = 0;
		$active = 0;
		$expired = 0;
		$storage = 0;
		$credits = 0;
		$bannedcount = 0;
		$bannedquery = $this->em->createQuery("SELECT u FROM App\Entity\User u WHERE u.account_level > 0 AND u.roles LIKE '%ROLE_BANNED%'");
		foreach ($bannedquery->getResult() as $banned) {
			$bannedcount++;
			$this->changeSubscription($banned, 0);
			$banned->setNotifications(FALSE);
			$banned->setNewsletter(FALSE);
			$bannedusername = $banned->getUsername();
			$this->logger->info("$bannedusername has been banned, and email notifications have been disabled.");
			$this->em->flush();
		}
		$this->logger->info("Refreshing patreon pledges for users with connected accounts.");
		$uCount = 0;
		$pledges = 0;
		$skip = false;
		$patronquery = $this->em->createQuery('SELECT u, p FROM App\Entity\User u JOIN u.patronizing p');
		$users = new ArrayCollection();
		$now = new \DateTime("now");
		$old = new \DateTime("-12 hours");
		foreach ($patronquery->getResult() as $user) {
			if (!$users->contains($user)) {
				$userpatron = new ArrayCollection();
				foreach ($user->getPatronizing() as $patron) {
					if ($patron->getUpdateNeeded()) {
						$skip = true;
					}
					if (!$userpatron->contains($patron)) {
						$status = null;
						$entitlement = null;
						$updated = false;
						$expires = $patron->getExpires();
						if ($expires > $old) {
							if ($expires < $now) {
								$this->logger->info($patron->getId());
								$updated = $this->refreshPatreonTokens($patron);
							}
							if ($updated) {
								[$status, $entitlement] = $this->refreshPatreonPledge($patron);
								if ($status === false) {
									# API failure. Only wayt staus would be false.
									$this->logger->info("Patreon API failed to return expected format. Expected JSON, got: ".$entitlement);
									if (is_array($entitlement)) {
										$this->logger->info("Array detected: ".implode(" || ", $entitlement));
									}
									$this->logger->info("Errored on User ".$user->getUsername()." (".$user->getId().")");
									$patron->setUpdateNeeded(true);

									$skip = true;
									continue;
								} else {
									$pledges++;
								}
								$userpatron->add($patron);
							}
						} else {
							$patron->setUpdateNeeded(true);
						}
					}
				}
				if ($skip) {
					continue;
				}
				$users->add($user);
				$uCount++;
			}
		}
		$this->em->flush();
		$this->logger->info("Refreshed ".$pledges." pledges for ".$uCount." users.");

		$now = new \DateTime("now");
		$query = $this->em->createQuery('SELECT u FROM App\Entity\User u WHERE u.account_level > 0 AND u.paid_until < :now');
		$query->setParameters(array('now'=>$now));

		$this->logger->info("  User Subscription Processing...");
		foreach ($query->getResult() as $user) {
			#$this->logger->info("  --Calculating ".$user->getUsername()." (".$user->getId().")...");
			$myfee = $this->calculateUserFee($user);
			$levels = $this->getPaymentLevels();
			if ($myfee > 0) {
				$this->logger->info("  --Calculating fee for ".$user->getUsername()." (".$user->getId().")...");
				if ($this->spend($user, 'subscription', $myfee, true)) {
					$this->logger->info("    --Credit spend successful...");
					$active++;
					$credits += $myfee;
				} else {
					$this->logger->info("    --Credit spend failed. Reducing account...");
					// not enough credits left! - change to trial
					$user->setAccountLevel(10);
					$this->ChangeNotification($user, 'expired', 'expired2');
					// TODO: check that this recalculates correctly if someone is far beyond the due date and then renews subscription
					$expired++;
				}
			} elseif ($levels[$user->getAccountLevel()]['patreon'] != false) {
				$this->logger->info("  --Patron ".$user->getUsername()." (".$user->getId().")...");
				$patreonLevel = $levels[$user->getAccountLevel()]['patreon'];
				$sufficient = false;
				#TODO: We'll need to expand this to support other creators, if we add any.
				foreach ($user->getPatronizing() as $patron) {
					$this->logger->info("    --Supporter of creator ".$patron->getCreator()->getCreator()."...");
					$status = $patron->getStatus();
					$entitlement = $patron->getCurrentAmount();

					$this->logger->info("    --Status of '".$status."'; entitlement of ".$entitlement."; versus need of ".$patreonLevel." for sub level ...");

					if ($patreonLevel <= $entitlement) {
						#$this->logger->info("    --Pledge is sufficient...");
						$sufficient = true;
					}
				}
				if (!$sufficient) {
					$this->logger->info("    --Pledge insufficient, reducing subscription...");
					$this->logger->info($patron->getId());
					# insufficient pledge level
					if (in_array($user->getAccountLevel(), ['22', '42', '51'])) {
						$user->setOldAccountLevel($user->getAccountLevel());
					}
					$user->setAccountLevel(10);
					$this->ChangeNotification($user, 'insufficient', 'insufficient2');
					$expired++;
				} else {
					$this->logger->info("    --Pledge sufficent, running spend routine...");
					$this->spend($user, 'subscription', $myfee, true);
					$active++;
					$patronCount++;
					# TODO: Give overpledge back as credits?
				}
			} else {
				#$this->logger->info("    --Non-payer detected, either trial or dev account...");
				if ($user->getLastLogin()) {
					$inactive_days = $user->getLastLogin()->diff(new \DateTime("now"), true)->days;
				} else {
					$inactive_days = $user->getCreated()->diff(new \DateTime("now"), true)->days;
				}
				if ($inactive_days > 60) {
					#$this->logger->info("    --Account inactive, storing account...");
					// after 2 months, we put you into storage
					$user->setAccountLevel(0);
					$storage++;
				} else {
					#$this->logger->info("    --Accont active, logging as free...");
					$free++;
				}
			}
		}
		$this->logger->info("  Updating Limits...");
		$query = $this->em->createQuery('SELECT u FROM App\Entity\User u');
		$now = new \DateTime("now");
		foreach ($query->getResult() as $user) {
			/** @var UserLimits $limits */
			$limits = $user->getLimits();
			if (!$limits) {
				$this->usermanager->createLimits($user);
			} else {
				if ($limits->getPlacesDate() <= $now) {
					if (!$user->isNewPlayer()) {
						$limits->setPlaces(4);
					} else {
						$limits->setPlaces(0);
					}
					$limits->setPlacesDate(new \DateTime("+1 day"));
				}
			}
		}
		$this->logger->info("  Cycle ended. Flushing...");
		$this->em->flush();
		return array($free, $patronCount, $active, $credits, $expired, $storage, $bannedcount);
	}

	public function refreshPatreonTokens($patron): bool {
		$now = new \DateTime("now");
		if ($patron->getExpires() < $now) {
			$creator = $patron->getCreator();
			$poa = new POA($creator->getClientId(), $creator->getClientSecret());
			$tokens = $poa->refresh_token($patron->getRefreshToken());
			if (array_key_exists('access_token', $tokens)) {
				$patron->setAccessToken($tokens['access_token']);
				$patron->setRefreshToken($tokens['refresh_token']);
				$patron->setExpires(new \DateTime('+'.$tokens['expires_in'].' seconds'));
				return true;
			} else {
				$patron->setUpdateNeeded(true);
				return false;
			}
		}
		return true; # No update needed. Why did you call this?
	}

	public function refreshPatreonPledge($patron, $args = ['skip_read_from_cache'=>true, 'skip_add_to_cache'=>true]): array {
		$papi = new PAPI($patron->getAccessToken());
		$check = false;
		$counter = 0;
		while (!$check) {
			$member = $papi->fetch_user($args);
			$check = $this->checkPatreonFetch($member);
			usleep(100000);
			/*
			Wait a tenth a second, then continue, to avoid overloading the API.
			I realize that unless we're doing more than one there's no point, but the user isn't going to notice the difference here.
			*/
			if ($counter == 1 && !$check) {
				#API might be down. Abort.
				#echo "<pre>".print_r($check,true)."</pre>";
				return [false, $member];
			}
			$counter++;
		}
		if (!$patron->getPatreonId()) {
			$patron->setPatreonId($member['data']['id']);
		}
		#echo "<pre>".print_r($member,true)."</pre>";
		$status = $member['included'][0]['attributes']['patron_status'];
		$patron->setStatus($status);
		$entitlement = $member['included'][0]['attributes']['currently_entitled_amount_cents'];
		$lifetime = $member['included'][0]['attributes']['lifetime_support_cents'];
		$patron->setCurrentAmount($entitlement);
		if ($patron->getCredited() != $lifetime) {
			if ($patron->getCredited() === null) {
				$dif = $lifetime;
			} else {
				$dif = $lifetime - $patron->getCredited();
			}
			$dif = $dif / 100; #Patreon provides in cents. We want full dollars!
			$this->account($patron->getUser(), 'Patron Credit', $dif, null, 'patreon');
			$patron->setCredited($lifetime); #We do track it in cents though.
		}
		if ($patron->getUpdateNeeded()) {
			$levels = $this->getPaymentLevels();
			$patron->setUpdateNeeded(false);
			$user = $patron->getUser();
			if (in_array($user->getOldAccountLevel(), ['22','42','51'])) {
				#check for legacy patreon levels and restore them if they used to have one.
				# Maybe some day we'll be able to remove this. That'd be nice.
				if ($levels[$user->getAccountLevel()]['patreon'] <= $entitlement) {
					$user->setAccountLevel($user->getOldAccountLevel());
					$user->setOldAccountLevel(null);
				}
			}
		}
		return [$status, $entitlement];
	}

	public function checkPatreonFetch($member): bool {
		# Validate that results are waht we expect them to be.
		if (!is_numeric($member['data']['id'])) {
			$this->logger->info($member);
			return false; #This shoudl ALWAYS return an integer.
		}
		if (!in_array($member['included'][0]['attributes']['patron_status'], ['active_patron', 'declined_patron', 'former_patron'])) {
			return false; #This should always be active_patron, declined_patron, or former_patron.
		}
		if (!is_numeric($member['included'][0]['attributes']['currently_entitled_amount_cents'])) {
			return false; #This should always be a cents count expressed as a full number, an int.
		}
		if (!is_numeric($member['included'][0]['attributes']['lifetime_support_cents'])) {
			return false;
		}
		return true;
	}

	public function buildStripeIntent($currency, $amt, $user, $success, $cancel): StripeSession|string {
		$prices = $this->stripePrices;
		if (array_key_exists($currency, $prices)) {
			$iCurrency = $prices[$currency];
			if (array_key_exists($amt, $iCurrency)) {
				$pID = $iCurrency[$amt];
			} else {
				return 'notfound';
			}
		} else {
			return 'notfound';
		}

		Stripe::setApiKey($this->stripeSecret);
		$checkout = StripeSession::create([
			'line_items' => [[
				'price' => $pID,
				'quantity' => 1,
				'adjustable_quantity' => [
					'enabled'=>false
				],
				]],
			'mode' => 'payment',
			'success_url' => $success.'?session_id={CHECKOUT_SESSION_ID}',
			'cancel_url' => $cancel,
			'client_reference_id' => $user->getId(),
			'customer_email' => $user->getEmail(),
			'allow_promotion_codes' => true,
			'automatic_tax' => [
				'enabled' => true,
			],
		]);
		return $checkout;
	}

	private function stripeAmtFromPid($pid): array {
		foreach ($this->stripePrices as $aKey=>$aData) {
			foreach ($aData as $bKey=>$each) {
				if ($each === $pid) {
					return [$aKey, $bKey];
				}
			}
		}
		return [0,0];
	}

	public function retrieveStripe($session): array {
		$stripe = new StripeClient([
			'api_key'=>$this->stripeSecret,
			'stripe_version'=>'2023-10-16'
		]);
		return [$stripe->checkout->sessions->retrieve($session), $stripe->checkout->sessions->allLineItems($session)];
	}

	private function ChangeNotification(User $user, $subject, $text): void {
		$subject = $this->translator->trans("account.payment.mail.".$subject, array());
		$content = $this->translator->trans("account.payment.mail.".$text, array());

		$this->mailer->sendEmail($user->getEmail(), $subject, $content);
	}

	public function changeSubscription(User $user, $newlevel): bool {
		if (!array_key_exists($newlevel, $this->getPaymentLevels())) {
			return false;
		}
		$oldlevel = $user->getAccountLevel();
		$oldpaid = $user->getPaidUntil();

		$levels = $this->getPaymentLevels();
		if ($levels[$newlevel]['patreon'] != false) {
			$valid = false;
			foreach ($user->getPatronizing() as $patron) {
				if ($levels[$newlevel]['creator'] == $patron->getCreator()->getCreator()) {
					if ($patron->getStatus() == 'active_patron' && $patron->getCurrentAmount() >= $levels[$newlevel]['patreon']) {
						$valid = true;
						if ($valid) {
							break;
						}
					}
				}
			}
			if ($valid) {
				if ($user->getRestricted()) {
					$user->setRestricted(false);
				}
				$refund = $this->calculateRefund($user);
				$user->setAccountLevel($newlevel);
				$user->setPaidUntil(new \DateTime("now"));
				$this->em->flush();
				return true;
			}
			# Either they are a valid patron, and the above returns true. Or they aren't, and this call fails. The rest doesn't matter.
			return false;
		}

		$fee = $this->calculateUserFee($user);
		$refund = $this->calculateRefund($user);

		if ($fee > $user->getCredits()+$refund) {
			return false;
		} else {
			if ($refund>0) {
				$this->spend($user, 'refund', -$refund, false);
			}
			$user->setAccountLevel($newlevel);
			$user->setPaidUntil(new \DateTime("now"));
			$check = $this->spend($user, 'subscription', $fee, true);
			if ($check) {
				// reset account restriction, so it is recalculated
				if ($user->getRestricted()) {
					$user->setRestricted(false);
					$this->em->flush();
				}
				return true;
			} else {
				// this should never happen - alert me
				$this->logger->alert('error in change subscription for user '.$user->getId().", change from $oldlevel to $newlevel");
				return false;
			}
		}
	}


	public function account(User $user, $type, $amount, $transaction=null, $src='paypal'): true {
		if ($type === 'Stripe Payment') {
			[$currency, $amount] = $this->stripeAmtFromPid($amount);
		} else {
			$currency = 'USD';
		}
		$credits = ceil($amount*100);
		$original = $credits;
		if ($type !== 'Patron Credit') {
			if ($amount >= 100) {
				$bonus = $credits * 0.5;
			} elseif ($amount >= 50) {
				$bonus = $credits * 0.4;
			} elseif ($amount >= 20) {
				$bonus = $credits * 0.3;
			} elseif ($amount >= 10) {
				$bonus = $credits * 0.2;
			} elseif ($amount >= 5) {
				$bonus = $credits * 0.1;
			} else {
				$bonus = 0;
			}
		} else {
			$bonus = $credits * 0.5;
		}

		$credits = ceil($credits); # Not that this should ever be a decimal but...
		$bonus = ceil($bonus);
		$credits = $credits + $bonus;

		if ($user->getPayments()->isEmpty()) {
			$first = true;
		} else {
			$first = false;
		}

		$payment = new UserPayment;
		$payment->setTs(new \DateTime("now"));
		$payment->setCurrency(strtoupper($currency));
		$payment->setAmount($amount);
		$payment->setType($type);
		$payment->setUser($user);
		$payment->setCredits($original);
		$payment->setBonus(ceil($bonus));
		$payment->setTransactionCode($transaction);
		$this->em->persist($payment);
		$user->addPayment($payment);

		$history = new CreditHistory;
		$history->setTs(new \DateTime("now"));
		$history->setCredits($original);
		$history->setBonus(ceil($bonus));
		$history->setUser($user);
		$history->setType($type);
		$history->setPayment($payment);
		$this->em->persist($history);
		$user->addCreditHistory($history);

		$user->setCredits($user->getCredits()+$credits);

		if ($first) {
			// give us our free vanity item
			$limits = $user->getLimits();
			if (!$limits) {
				$limits = $this->usermanager->createLimits($user);
			}
			if (!$limits->getArtifactSubBonus()) {
				$limits->setArtifacts($limits->getArtifacts()+1);
				$limits->setArtifactSubBonus(true);
			}

			if (!in_array($src, $this->patreonAlikes)) {
				// check if we had a friend code
				$query = $this->em->createQuery('SELECT c FROM App\Entity\Code c WHERE c.used_by = :me AND c.sender IS NOT NULL AND c.sender != :me ORDER BY c.used_on ASC');
				$query->setParameter('me', $user);
				$query->setMaxResults(1);
				$code = $query->getOneOrNullResult();
				if ($code) {
					$sender = $code->getSender();
					$value = round(min($credits, $code->getCredits()) / 2);

					$h = new CreditHistory;
					$h->setTs(new \DateTime("now"));
					$h->setCredits($value);
					$h->setUser($sender);
					$h->setType('friendinvite');
					$this->em->persist($h);
					$user->addCreditHistory($h);

					$sender->setCredits($sender->getCredits()+$value);
					$this->em->flush();

					$text = $this->translator->trans('account.invite.mail2.body', array("%mail%"=>$user->getEmail(), "%credits%"=>$value));
					$this->mailer->sendEmail($sender->getEmail(), $this->translator->trans('account.invite.mail2.subject'), $text);
					$this->logger->info('sent friend subscriber email: '.$text);
				}
			}
		}

		// TODO: this is not quite complete, what about people going into negative credits?
		$this->em->flush();
		return true;
	}

	public function redeemHash(User $user, $hash): array {
		$code = $this->em->getRepository(Code::class)->findOneBy(['code'=>$hash]);
		if ($code) {
			return array($code, $this->redeemCode($user, $code));
		} else {
			return array(null, "error.payment.nosuchcode");
		}
	}

	public function redeemCode(User $user, Code $code): true|string {
		if ($code->getUsed()) {
			$this->logger->alert("user #{$user->getId()} tried to redeem already-used code {$code->getId()}");
			return "error.payment.already";
		}

		if ($code->getSentToEmail() && $code->getLimitToEmail() && $code->getSentToEmail() != $user->getEmail()) {
			$this->logger->alert("user #{$user->getId()} tried to redeem code not for him - code #{$code->getId()}");
			return "error.payment.notforyou";
		}

		if ($code->getCredits() > 0) {
			$history = new CreditHistory;
			$history->setTs(new \DateTime("now"));
			$history->setCredits($code->getCredits());
			$history->setUser($user);
			$history->setType("code");
			$this->em->persist($history);
			$user->addCreditHistory($history);

			$user->setCredits($user->getCredits()+$code->getCredits());
		}

		if ($code->getVipStatus() && $code->getVipStatus() > $user->getVipStatus()) {
			// TODO: report back if this doesn't change our status
			$user->setVipStatus($code->getVipStatus());
		}
		// TODO: unlock characters, also check if we were due a payment - how ?

		$code->setUsed(true);
		$code->setUsedOn(new \DateTime("now"));
		$code->setUsedBy($user);

		$this->em->flush();

		return true;
	}

	public function createCode($credits, $vip_status=0, $sent_to=null, ?User $sent_from=null, $limit=false): Code {
		$code = new Code;
		$code->setCode($this->app->generateAndCheckToken(40, 'Code', 'code'));
		#$code->setCode(sha1(time()."mafcode".mt_rand(0,1000000)));
		$code->setCredits($credits);
		$code->setVipStatus($vip_status);
		$code->setUsed(false);
		$code->setSentOn(new \DateTime("now"));
		if ($sent_from) {
			$code->setSender($sent_from);
		}
		if ($sent_to) {
			$code->setSentToEmail($sent_to);
		} else {
			$code->setSentToEmail("");
		}
		$code->setLimitToEmail($limit);
		$this->em->persist($code);
		$this->em->flush();
		return $code;
	}

	/**
	 * @param User   $user               User spending credits.
	 * @param string $type               Spend type, like culture or realm pack
	 * @param int    $credits            Positive integer for number of credits to deduct from user as spent
	 * @param bool   $renew_subscription Flag to indicate a subscription renewal.
	 *
	 * @return bool
	 * @throws \DateMalformedStringException
	 * @throws \DateMalformedStringException
	 */
	public function spend(User $user, string $type, int $credits, bool $renew_subscription=false): bool {
		if ($credits>0 && $user->getCredits()<$credits) {
			return false;
		}
		$history = new CreditHistory;
		$history->setTs(new \DateTime("now"));
		$history->setCredits(-$credits);
		$history->setUser($user);
		$history->setType($type);
		$this->em->persist($history);
		$user->addCreditHistory($history);

		$user->setCredits($user->getCredits()-$credits);
		if ($renew_subscription) {
			// NOTICE: This will add +1 to the month value and can skip into the following month
			// example: January 31st + 1 month == March 3rd (because there is no February 31st)
			$paid = clone $user->getPaidUntil();
			$paid->modify('+1 month');
			$user->setPaidUntil($paid);
		}
		$this->em->flush();
		$this->logger->info("Payment: User ".$user->getId().", $type, $credits credits");
		return true;
	}


	public function log_info($text): void {
		$this->logger->info($text);
	}

	public function log_error($text): void {
		$this->logger->error($text);
	}

}
