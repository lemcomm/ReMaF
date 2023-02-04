<?php

namespace App\Controller;

use App\Entity\Patron;
use App\Form\CultureType;
use App\Form\GiftType;
use App\Form\SubscriptionType;
use App\Service\AppState;
use App\Service\MailManager;
use App\Service\PaymentManager;
use Doctrine\ORM\EntityManagerInterface;
use Patreon\OAuth as POA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentController extends AbstractController {

	private array $giftchoices = array(100=>100, 200=>200, 300=>300, 400=>400, 500=>500, 600=>600, 800=>800, 1000=>1000, 1200=>1200, 1500=>1500, 2000=>2000, 2500=>2500);
	private EntityManagerInterface $em;
	private Security $sec;
	private MailManager $mail;
	private PaymentManager $pay;
	private TranslatorInterface $trans;
	private LoggerInterface $logger;

	public function __construct(AppState $app, EntityManagerInterface $em, LoggerInterface $logger, MailManager $mail, PaymentManager $pay, Security $sec, TranslatorInterface $trans) {
		$this->app = $app;
		$this->em = $em;
		$this->logger = $logger;
		$this->mail = $mail;
		$this->pay = $pay;
		$this->sec = $sec;
		$this->trans = $trans;
	}

	private function fetchPatreon($creator = null) {
		if (!$creator) {
			$query = $this->em->createQuery("SELECT p FROM App:Patreon p WHERE p.id > 0");
			$result = $query->getResult();
		} else {
			$query = $this->em->createQuery("SELECT p FROM App:Patreon p WHERE p.creator = :name");
			$query->setParameters(["name"=>$creator]);
			$result = $query->getSingleResult();
		}
		return $result;
	}

	#[Route ('/payment', name:'maf_payment')]
	public function paymentAction(Request $request): Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();

		$form = $this->createFormBuilder()
			->add('hash', TextType::class, [
				'required' => true,
				'label' => 'account.code.label',
			])
			->add('submit', SubmitType::class, array('label'=>'account.code.submit'))
			->getForm();

		$redeemed = false;

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			list($code, $result) = $this->pay->redeemHash($user, $data['hash']);

			if ($result === true) {
				$redeemed = $code;
			} else {
				$form->addError(new FormError($this->trans->trans($result)));
			}
		}

		return $this->render('Payment/payment.html.twig', [
			'form' => $form->createView(),
			'redeemed' => $redeemed
		]);
	}

	#[Route ('/payment/stripe/{currency}/{amount}', name:'maf_stripe', requirements:['currency'=>'[A-Z]+', 'amount'=>'\d+'])]
	public function stripeAction($currency, $amount, Request $request): RedirectResponse {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();

		$success = $this->generateUrl('maf_stripe_success', [], true);
		$cancel = $this->generateUrl('maf_payment', [], true);
		$checkout = $this->pay->buildStripeIntent($currency, $amount, $user, $success, $cancel);
		if ($checkout === 'notfound') {
			$this->addFlash('error', "Unable to locate the requested product.");
			return $this->redirectToRoute('maf_payment');
		} else {
			return $this->redirect($checkout->url);
		}
	}

	#[Route ('/payment/stripe_success}', name:'maf_stripe_success')]
	public function stripeSuccessAction(Request $request): RedirectResponse {
		$user = $this->getUser();
		$user_id = $user->getId();
		try {
			list($result, $items) = $this->pay->retrieveStripe($request->query->get('session_id'));
		} catch (Exception $e) {
			$this->addFlash('error', "Stripe Payment Failed. If you've received this it's because we weren't able to complete the transaction for some reason. Please let an administrator know about this in a direct message either on Discord or via email to andrew@iungard.com.");
			return $this->redirectToRoute('maf_payment');
		}

		$total = $result->amount_subtotal/100;
		$currency = strtoupper($result->currency);
		$txid = $result->payment_intent;
		$status = strtolower($result->payment_status);
		$pid = $items->data[0]->price->id;

		if ($status === 'paid' || $status == 'no_payment_required') {
			$txt = "Stripe Payment calback: $total $currency // for user ID $user_id // tx id $txid";
			$this->pay->log_info($txt);
			$this->pay->account($user, 'Stripe Payment', $pid, $txid);
			$this->get('notification_manager')->spoolPayment('M&F '.$txt);
			$this->addFlash('notice', 'Payment Successful! Thank you!');
			return $this->redirectToRoute('maf_payment');
		} else {
			$this->addFlash('error', "Stripe Payment Incomplete. If you believe you reached this incorrectly, please contact an Adminsitrator. Having your M&F username and transaction time handy will make this easier to look into. Transactions that aren't immediate will require manual processing at this time.");
			return $this->redirectToRoute('maf_payment');
		}
	}

	#[Route ('/payment/stripe_cancel', name:'maf_stripe_cancel')]
	public function stripeCancelAction(Request $request): RedirectResponse {
		$this->addFlash('warning', "You appear to have cancelled your payment. Transaction has ended.");
		return $this->redirectToRoute('maf_payment');
	}

	#[Route ('/payment/credits', name:'maf_payment_credits')]
	public function creditsAction(): Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();

		return $this->render('Payment/credits.html.twig', [
			'myfee' => $this->pay->calculateUserFee($user),
			'concepturl' => $this->generateUrl('maf_about_payment')
		]);
	}

	#[Route ('/payment/subscription', name:'maf_payment_subscription')]
	public function subscriptionAction(Request $request): RedirectResponse|Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();
		$levels = $this->pay->getPaymentLevels();

		$sublevel = [];
		foreach ($user->getPatronizing() as $patron) {
			if ($patron->getCreator()->getCreator() == 'andrew' && $patron->getStatus() == 'active_patron') {
				$sublevel['andrew'] = $patron->getCurrentAmount();
			}
		}

		$form = $this->createForm(SubscriptionType::class, null, ['all_levels'=>$levels, 'old_level'=>$user->getAccountLevel()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			// TODO: this should require an e-mail confirmation
			// TODO: should not allow lowering while above (new) character limit

			$check = $this->pay->changeSubscription($user, $data['level']);
			if ($check) {
				$this->addFlash('notice', 'account.sub.changed');
				return $this->redirectToRoute('maf_payment_credits');
			}
		}

		return $this->render('Payment/subscription.html.twig', [
			'myfee' => $this->pay->calculateUserFee($user),
			'refund' => $this->pay->calculateRefund($user),
			'levels' => $levels,
			'sublevel' => $sublevel,
			'concepturl' => $this->generateUrl('maf_about_payment'),
			'creators' => $this->fetchPatreon(),
			'form'=> $form->createView()
		]);
	}

	#[Route ('/payment/patreon/update', name:'maf_patreon_update')]
	public function patreonUpdateAction(Request $request): RedirectResponse {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();
		$patreons = $user->getPatronizing();
		$pm = $this->pay;

		$now = new \DateTime('now');
		$amount = 0;
		$wait = false;
		if ($patreons->count() > 1) {
			$wait = true;
		}

		foreach ($patreons as $patron) {
			if ($patron->getExpires() < $now) {
				$pm->refreshPatreonTokens($patron);
				usleep(100000); #Wait a tenth a second, then continue, to avoid overloading the API.
			}
			list($status, $entitlement) = $pm->refreshPatreonPledge($patron);
			# NOTE: Expand this later for other creators if we have any.
			if ($patron->getCreator()->getCreator()=='andrew') {
				$amount += $entitlement;
			}
		}
		if ($amount > 0) {
			$amount = $amount/100;
		}
		$this->em->flush();
		$this->addFlash('notice', $this->trans->trans('account.patronizing', ['%entitlement%'=>$amount]));
		return $this->redirectToRoute('maf_account');
	}

	#[Route ('/payment/patreon/{creator}', name:'maf_patreon', requirements:['creator'=>'[A-Za-z]+'])]
	public function patreonAction(Request $request, $creator): RedirectResponse {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();

		$code = $request->query->get('code');
		$creator = $this->fetchPatreon($creator);
		if (isset($code) && !empty($code)) {
			$pm = $this->pay;
			$auth = new POA($creator->getClientId(), $creator->getClientSecret());
			$tokens = $auth->get_tokens($code, $creator->getReturnUri());
			$patron = $this->em->getRepository('App:Patron')->findOneBy(["user"=>$user, "creator"=>$creator]);
			if (!$patron) {
				$patron = new Patron();
				$em->persist($patron);
				$patron->setUser($user);
				$patron->setCreator($creator);
			}
			$patron->setAccessToken($tokens['access_token']);
			$patron->setRefreshToken($tokens['refresh_token']);
			$patron->setExpires(new \DateTime('+'.$tokens['expires_in'].' seconds'));
			list($status, $entitlement) = $pm->refreshPatreonPledge($patron);
			if ($status === false) {
				#This only returns false if the API spits garbage at us.
				$this->addFlash('error', $this->trans->trans('account.patreonapifailure'));
				return $this->redirectToRoute('maf_account');
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('account.patronizing', ['%entitlement%'=>$entitlement/100]));
			return $this->redirectToRoute('maf_account');
		} else {
			$this->addFlash('notice', $this->trans->trans('account.patronfailure'));
			return $this->redirectToRoute('maf_payment_subscription');
		}
	}

	#[Route ('/payment/culture', name:'maf_payment_culture')]
	public function cultureAction(Request $request): Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$allcultures = $this->em->createQuery('SELECT c FROM App:Culture c INDEX BY c.id')->getResult();
		$nc = $this->em->createQuery('SELECT c.id as id, count(n.id) as amount FROM App:NameList n JOIN n.culture c GROUP BY c.id')->getResult();
		$namescount = array();
		foreach ($nc as $ncx) {
			$namescount[$ncx['id']] = $ncx['amount'];
		}

		$form = $this->createForm(CultureType::class, null, ['user'=>$this->getUser(), 'available'=>false]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$buying = $data['culture'];
			$total = 0;
			foreach ($buying as $buy) {
				$total += $buy->getCost();
			}
			if ($total > $this->getUser()->getCredits()) {
				$form->addError(new FormError($this->trans->trans("account.culture.notenoughcredits")));
			} else {
				foreach ($buying as $buy) {
					// TODO: error handling here?
					$this->pay->spend($this->getUser(), "culture pack", $buy->getCost());
					$this->getUser()->getCultures()->add($buy);
				}
				$em->flush();

				return $this->render('Payment/culture.html.twig', [
					'bought'=>$buying,
					'namescount'=>$namescount
				]);
			}
		}

		return $this->render('Payment/culture.html.twig', [
			'cultures'=>$allcultures,
			'namescount'=>$namescount,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/payment/gift', name:'maf_payment_gift')]
	public function giftAction(Request $request): array|Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();
		$form = $this->createForm(GiftType::class, null, [
			'credits' => $this->giftchoices,
			'invite' => false
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$value = $this->giftchoices[$data['credits']];

			$target = $this->em->getRepository('App:User')->findOneByEmail($data['email']);
			if (!$target) {
				sleep(1); // to make brute-forcing e-mail addresses a bit slower
				return array('error'=>'notarget');
			}
			if ($target == $user) {
				return array('error'=>'self');
			}

			$code = $this->pay->createCode($value, 0, $data['email'], $user);
			$this->pay->spend($user, "gift", $value);

			$em->flush();

			$text = $this->trans->trans('account.gift.mail.body', array("%credits%"=>$value, "%code%"=>$code->getCode(), "%message%"=>strip_tags($data['message'])));
			$this->mail->sendEmail(
				$data['email'],
				$this->trans->trans('account.gift.mail.subject', array()),
				$text,
				$user->getEmail()
			);
			$this->logger->info("sent gift from ".$user->getId()." to ".$data['email']." for $value credits");

			return $this->render('Payment/gift.html.twig', [
				'success'=>true, 'credits'=>$value
			]);

		}

		return $this->render('Payment/gift.html.twig', [
			'form'=>$form->createView()
		]);
	}

	#[Route ('/payment/invite', name:'maf_payment_invite')]
	public function inviteAction(Request $request): Response {
		$banned = $this->app->checkbans();
		if ($banned instanceof AccessDeniedException) {
			throw $banned;
		}
		$user = $this->getUser();

		$form = $this->createForm(GiftType::class, null, [
			'credits' => $this->giftchoices,
			'invite' => true
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$value = $this->giftchoices[$data['credits']];

			$code = $this->pay->createCode($value, 0, $data['email'], $user);
			$this->pay->spend($user, "gift", $value);

			$this->em->flush();

			$text = $this->trans->trans('account.invite.mail.body', array("%credits%"=>$value, "%code%"=>$code->getCode(), "%message%"=>strip_tags($data['message'])));
			$this->mail->sendEmail(
				$data['email'],
				$this->trans->trans('account.invite.mail.subject', array()),
				$text,
				$user->getEmail()
			);
			$this->logger->info("sent friend invite from ".$user->getId()." to ".$data['email']." for $value credits");

			return $this->render('Payment/invite.html.twig', [
				'success'=>true, 'credits'=>$value
			]);
		}

		return $this->render('Payment/invite.html.twig', [
			'form'=>$form->createView()
		]);
	}

}