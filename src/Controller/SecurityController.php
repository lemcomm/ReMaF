<?php

namespace App\Controller;

use App\Entity\AppKey;
use App\Entity\User;
use App\Form\ForgotUsernameType;
use App\Form\RegistrationFormType;
use App\Form\RequestResetFormType;
use App\Form\ResetPasswordFormType;
use App\Form\NewTokenFormType;
use App\Form\UserDataType;
use App\Form\UserDeleteType;
use App\Service\AppState;
use App\Service\DescriptionManager;
use App\Service\MailManager;

use App\Service\NotificationManager;
use App\Service\UserManager;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController {

	#[Route ('/login', name:'maf_login')]
	public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $em): Response {
		# Fetch the previous error, if there is one.
		$error = $authenticationUtils->getLastAuthenticationError();

		#Fetch last username entered by the user.
		$last = $authenticationUtils->getLastUsername();
		$query = $em->createQuery('SELECT u from App\Entity\User u where LOWER(u.username) like :name and u.watched = true and u.enabled = false');
		$query->setParameters(['name'=>$last]);
		$query->setMaxResults(1);
		$check = $query->getOneOrNullResult();
		if ($check) {
			$this->addFlash('notice', 'This account was disabled for security reasons. To re-enable it, please reset your password using the link below.');
		}

		return $this->render('Account/login.html.twig', [
			'last' => $last,
			'error' => $error
		]);
	}

	#[Route ('/logout', name:'maf_logout')]
	public function logout() {
		# This page works like magic and requires NOTHING!
		# It only exists here so we can remember it exists and give it proper naming.
	}


	#[Route ('/security/register', name:'maf_register')]
	public function register(AppState $app, EntityManagerInterface $em, MailManager $mail, TranslatorInterface $trans, Request $request, UserPasswordHasherInterface $passwordHasher, UserManager $userManager): Response {
		$user = new User();
		$form = $this->createForm(RegistrationFormType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
			$resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
			if (!$resp->isSuccess()) {
				$form->addError(new FormError("reCAPTCHA failed to validate. Are you a bot?"));
			} elseif ($form->isValid()) {
				$check = $em->getRepository(User::class)->findOneBy(['username' => $form->get('_username')->getData()]);
				if ($check) {
					$this->addFlash('error', $trans->trans('form.register.duplicate', [], 'core'));
					return new RedirectResponse($this->generateUrl('maf_register'));
				}
				$check = $em->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);
				if ($check) {
					$this->addFlash('error', $trans->trans('form.register.emailinuse', [], 'core'));
					return new RedirectResponse($this->generateUrl('maf_register'));
				}
				$data = $form->getData();
				$newsletter = $data['newsletter'];
				if ($newsletter === null) {
					$newsletter = false;
				}
				$user = $userManager->createUser($user, $newsletter);
				$user = $userManager->addUserDetails($user, $data['_username'], $data['plainPassword'], $data['email'], $data['display_name']);

				#Generate activation token

				#Log user creation time and set user to inactive.
				$until = new DateTime('now');
				$until->add(new DateInterval('P30D'));
				$method = $_ENV['ACTIVATION'];
				if ($method == 'automatic') {
					$user->setEnabled(true);
				} else {
					if ($method == 'email') {
						$user->setToken($app->generateAndCheckToken(16, 'User', 'token'));
					}
					$user->setEnabled(false);
				}
				$em->persist($user);
				$em->flush();

				if ($method == 'email') {
					# Generate Activation Email
					$link = $this->generateUrl('maf_account_activate', [
						'id' => $user->getId(),
						'token' => $user->getToken()
					], UrlGeneratorInterface::ABSOLUTE_URL);
					$text = $trans->trans('security.register.email.text1', [
						'%username%' => $user->getUsername(),
						'%sitename%' => $_ENV['SITE_NAME'],
						'%link%' => $link,
						'%adminemail%' => $_ENV['ADMIN_EMAIL']
					], 'core');
					$subject = $trans->trans('security.register.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

					$mail->sendEmail($user->getEmail(), $subject, $text);
				}

				$this->addFlash('notice', $trans->trans('security.register.flash', [], 'core'));
				return new RedirectResponse($this->generateUrl('maf_login'));
			}
		}

		return $this->render('Account/register.html.twig', [
			'form' => $form->createView(),
			'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
		]);
	}

	#[Route ('/security/activate/{id}/{token}', name:'maf_account_activate')]
	public function activate(EntityManagerInterface $em, TranslatorInterface $trans, string $id, string $email, string $token): RedirectResponse {
		# Handles user activation after a user registers.
		$user = $em->getRepository(User::class)->findOneBy(['id' => $id, 'email' => $email]);
		if ($user && $user->getActive() === false && $token == $user->getToken()) {
			$user->unsetToken();
			$user->setConfirmed(true);
			$em->flush();
			$this->addFlash('notice', $trans->trans('security.activate.flash.success', [], 'core'));
			return new RedirectResponse($this->generateUrl('maf_login'));
		} elseif ($user && $user->getActive() === true) {
			$this->addFlash('notice', $trans->trans('security.activate.flash.already', [], 'core'));
			return new RedirectResponse($this->generateUrl('maf_index'));
		} else {
			$link = $this->generateUrl('maf_token_new');
			$this->addFlash('error', $trans->trans('security.activate.flash.failed', [], 'core'));
			return new RedirectResponse($this->generateUrl('maf_token_new'));
		}
	}

	#[Route ('/security/reset', name:'maf_account_reset')]
        public function reset(AppState $app, EntityManagerInterface $em, MailManager $mail, TranslatorInterface $trans, Request $request, UserPasswordHasherInterface $passwordHasher, string $token = '0', string $email = '0'): RedirectResponse|Response {
                if ($token == '0') {
                        $form = $this->createForm(RequestResetFormType::class);
			echo "what?";
                        $form->handleRequest($request);
                        if ($form->isSubmitted() && $form->isValid()) {
				$recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
				$resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
				if (!$resp->isSuccess()) {
					$form->addError(new FormError("reCAPTCHA failed to validate. Are you a bot?"));
				} elseif ($form->isValid()) {
					$data = $form->getData();
					$user = $em->getRepository(User::class)->findOneBy(['email'=>$data['text']]);
					if (!$user) {
						$user = $em->getRepository(User::class)->findOneBy(['username'=>$data['text']]);
					}
					if ($user) {
						$user->setResetToken($app->generateAndCheckToken(64, 'User', 'reset_token'));
						$em->flush();
						$link = $this->generateUrl('maf_account_reset', [
							'token' => $user->getResetToken(),
							'email' => $user->getEmail()
						], UrlGeneratorInterface::ABSOLUTE_URL);
						$text = $trans->trans('security.reset.email.text', [
							'%sitename%' => $_ENV['SITE_NAME'],
							'%link%' => $link,
							'%adminemail%' => $_ENV['ADMIN_EMAIL']
						], 'core');
						$subject = $trans->trans('security.reset.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

						$mail->sendEmail($user->getEmail(), $subject, $text);
					}
					$this->addFlash('notice', $trans->trans('security.reset.flash.requested', [], 'core'));
					return new RedirectResponse($this->generateUrl('maf_index'));
				}
                        }
                        return $this->render('Account/reset.html.twig', [
                                'request_form' => $form->createView(),
				'reset_form' => false,
				'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
                        ]);
                } else {
                        $user = $em->getRepository(User::class)->findOneBy(['reset_token' => $token, 'email' => $email]);
                        if ($user) {
                                $form = $this->createForm(ResetPasswordFormType::class);
                                $form->handleRequest($request);
                                if ($form->isSubmitted() && $form->isValid()) {
					$user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
					$user->setLastPassword(new DateTime('now'));
                                        $user->unsetResetToken();
                                        $user->unsetResetTime();
                                        $em->flush();

                                        $this->addFlash('notice', $trans->trans('security.reset.flash.completed', [], 'core'));
                                        return new RedirectResponse($this->generateUrl('maf_index'));
                                }
                                return $this->render('Account/reset.html.twig', [
                                        'reset_form' => $form->createView(),
					'request_form' => false,
                                ]);
                        } else {
                                $app->logSecurityViolation($request->getClientIP(), 'core_reset', $this->getUser(), 'bad reset');
                                return new RedirectResponse($this->generateUrl('maf_index'));
                        }
                }
        }

	#[Route ('/security/remind', name:'maf_remind')]
        public function remind(AppState $app, EntityManagerInterface $em, MailManager $mail, TranslatorInterface $trans, Request $request): RedirectResponse|Response {
                $form = $this->createForm(ForgotUsernameType::class);
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
			$recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
			$resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
			if (!$resp->isSuccess()) {
				$form->addError(new FormError("reCAPTCHA failed to validate. Are you a bot?"));
			} elseif ($form->isValid()) {
				$data = $form->getData();
				$user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

				if ($user) {
					$user->setResetToken($app->generateAndCheckToken(64, 'User', 'reset_token'));
					$em->flush();
					$resetLink = $this->generateUrl('maf_account_reset', [], UrlGeneratorInterface::ABSOLUTE_URL);
					$loginLink = $this->generateUrl('maf_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
					$text = $trans->trans('security.remind.email.text', [
						'%sitename%' => $_ENV['SITE_NAME'],
						'%username%' => $user->getUsername(),
						'%login%' => $loginLink,
						'%reset%' => $resetLink,
						'%adminemail%' => $_ENV['ADMIN_EMAIL']
					], 'core');
					$subject = $trans->trans('security.remind.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

					$mail->sendEmail($user->getEmail(), $subject, $text);
				}
				$this->addFlash('notice', $trans->trans('security.remind.flash', [], 'core'));
				return new RedirectResponse($this->generateUrl('maf_index'));
			}
                }
                return $this->render('Account/remind.html.twig', [
                        'form' => $form->createView(),
			'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
                ]);
        }


	#[Route ('/security/newToken', name:'maf_token_new')]
        public function newToken(AppState $app, EntityManagerInterface $em, MailManager $mail, TranslatorInterface $trans, Request $request): RedirectResponse|Response {
                $form = $this->createForm(NewTokenFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
			$recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
			$resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
			if (!$resp->isSuccess()) {
				$form->addError(new FormError("reCAPTCHA failed to validate. Are you a bot?"));
			} elseif ($form->isValid()) {
				$data = $form->getData();
				$user = $em->getRepository(User::class)->findOneBy([
					'username' => $data['username'],
					'email' => $data['email']
				]);
				if ($user) {
					$user->setToken($app->generateAndCheckToken(16, 'User', 'token'));
					$em->flush();

					$link = $this->generateUrl('maf_account_activate', [
						'username' => $user->getUsername(),
						'email' => $user->getEmail(),
						'token' => $user->getToken()
					], UrlGeneratorInterface::ABSOLUTE_URL);
					$text = $trans->trans('security.register.email.text2', [
						'%username%' => $user->getUsername(),
						'%sitename%' => $_ENV['SITE_NAME'],
						'%link%' => $link,
						'%adminemail%' => $_ENV['ADMIN_EMAIL']
					], 'core');
					$subject = $trans->trans('security.register.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

					$mail->sendEmail($user->getEmail(), $subject, $text);
				}
				$this->addFlash('notice', $trans->trans('security.newtoken.flash', [], 'core'));
				return new RedirectResponse($this->generateUrl('maf_index'));
			}
                }

                return $this->render('Account/newtoken.html.twig', [
                        'form' => $form->createView(),
			'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
                ]);
        }

	#[Route ('/security/confirm/{token}/{email}', name:'maf_account_confirm')]
        public function confirm(EntityManagerInterface $em, TranslatorInterface $trans, string $token, string $email): RedirectResponse {
                $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user && $user->getConfirmed() === false && $token == $user->getToken()) {
                        $user->unsetEmailToken();
                        $user->setConfirmed(true);
                        if (!$user->getActive()) {
                                # Weird, but this accomplishes the same thing.
                                $user->setActive(true);
                        }
                        $em->flush();
                        $this->addFlash('notice', $trans->trans('security.confirm.flash.success', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                } elseif ($user && $user->getConfirmed() === true) {
                        $this->addFlash('notice', $trans->trans('security.confirm.flash.already', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                } else {
                        $link = $this->generateUrl('maf_token_new');
                        $this->addFlash('error', $trans->trans('security.confirm.flash.failed', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_token_new'));
                }
        }

	#[Route ('/security/data', name:'maf_account_data')]
	public function account(Request $request, AppState $app, DescriptionManager $descman, EntityManagerInterface $em, MailManager $mail, Security $sec, TranslatorInterface $trans, UserPasswordHasherInterface $passwordHasher): RedirectResponse|Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$admin = false;
		$gm = false;
		$gm_name = null;
		$public_admin = null;
		if ($sec->isGranted('ROLE_OLYMPUS')) {
			$gm = true;
			$gm_name = $user->getGmName();
			if ($sec->isGranted('ROLE_ADMIN')) {
				$admin = true;
				$public_admin = $user->getPublicAdmin();
			}
		}
		$text = $user->getDescription()?->getText();
		$opts = [
			'username' => $user->getUsername(),
			'email' => $user->getEmail(),
			'display' => $user->getDisplayname(),
			'public' => $user->getPublic(),
			'show_patronage'=> $user->getShowPatronage(),
			'gm' => $gm,
			'gm_name' => $gm_name,
			'admin' => $admin,
			'public_admin' => $public_admin,
			'text' => $text,
		];

		$form = $this->createForm(UserDataType::class, null, $opts);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$username = $form->get('username')->getData();
			if ($username !== NULL && $username !== $user->getUsername()) {
				$query = $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.username LIKE :new')->setParameters(['new'=>$username]);
				if ($query->getSingleScalarResult() > 0) {
					$this->addFlash('error', $trans->trans('security.account.change.username.notunique', [], 'core'));
					return new RedirectResponse($this->generateUrl('maf_account_data'));
				}
				$user->setUsername($username);
				$this->addFlash('notice', $trans->trans('security.account.change.username.success', [], 'core'));
			}
			$password = $form->get('plainPassword')->getData();
			if ($password !== NULL) {
				$hash = $passwordHasher->hashPassword($user, $password);
				$user->setPassword($hash);
				if (str_starts_with($hash, '$argon2')) {
					$user->setSalt(null);
				}
				$this->addFlash('notice', $trans->trans('security.account.change.password', [], 'core'));
			}
			$email = $form->get('email')->getData();
			if ($email !== NULL && $email !== $user->getEmail()) {
				$query = $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.email LIKE :new')->setParameters(['new'=>$email]);
				if ($query->getSingleScalarResult() > 0) {
					$this->addFlash('error', $trans->trans('security.account.change.email.notunique', [], 'core'));
					return new RedirectResponse($this->generateUrl('maf_account_data'));
				}
				$user->setEmailToken($app->generateAndCheckToken(16, 'User', 'email_token'));
				$user->setEmail($email);
				$user->setEnabled(false);

				$link = $this->generateUrl('maf_account_confirm', ['token' => $user->getEmailToken(), 'email' => $form->get('email')->getData()], UrlGeneratorInterface::ABSOLUTE_URL);
				$text = $trans->trans(
					'security.account.email.text', [
					'%sitename%' => $_ENV['SITE_NAME'],
					'%link%' => $link,
					'%adminemail%' => $_ENV['ADMIN_EMAIL']
				], 'core');
				$subject = $trans->trans('security.account.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');
				$mail->sendEmail($user->getEmail(), $subject, $text);
				$this->addFlash('notice', $trans->trans('security.account.change.email.success', [], 'core'));
			}
			$display = $form->get('display_name')->getData();
			if ($display !== NULL && $display != $user->getDisplayName()) {
				$user->setDisplayName($display);
				$this->addFlash('notice', $trans->trans('security.account.change.display', [], 'core'));
			}
			$desc = $form->get('text')->getData();
			if ($desc !== null && $desc !== $text) {
				$descman->newDescription($user, $desc);
				$this->addFlash('notice', $trans->trans('security.account.change.text', [], 'core'));
			}
			$em->flush();
			return new RedirectResponse($this->generateUrl('maf_account'));
		}
		return $this->render('Account/data.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route ('/security/delete', name:'maf_account_delete')]
	public function delete(EntityManagerInterface $em, TranslatorInterface $trans, NotificationManager $note, Request $request): RedirectResponse|Response {
		/** @var User $user */
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}

		$form = $this->createForm(UserDeleteType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if (strtolower($data['email']) === strtolower($user->getEmail())) {
				$id = $user->getId();
				$note->spoolError('Initiating deletion of account #'.$id.' at user request.');
				foreach ($user->getLogs() as $each) {
					# Keep old logs, as if they're being logged, they're probably a multi (or otherwise cheating).
					$each->setOldUserId($id);
					$each->setUser(null);
				}
				foreach ($user->getDescriptions() as $each) {
					$each->setNext(null);
				}
				$em->flush();
				foreach ($user->getDescriptions() as $each) {
					$em->remove($each);
				}
				foreach ($user->getPayments() as $each) {
					$each->setUser(null); #These serve as evidence that a transaction completed successfully on M&F's side.
				}
				foreach ($user->getCreditHistory() as $each) {
					$em->remove($each);
				}
				foreach ($user->getCharacters() as $each) {
					$each->setAbandonedBy($id);
					$each->setUser(null);
				}
				foreach ($user->getRatingsGiven() as $each) {
					$each->setGivenByUser(null);
				}
				foreach ($user->getRatingVotes() as $each) {
					$em->remove($each);
				}
				foreach ($user->getArtifacts() as $each) {
					$each->setUser(null);
				}
				foreach ($user->getListings() as $each) {
					$each->setOwner(null);
					#TODO: Cleanup function for removing orphaned lists that aren't used.
				}
				foreach ($user->getCrests() as $each) {
					$each->setUser(null);
				}
				foreach ($user->getCultures() as $each) {
					$em->remove($each);
				}
				foreach ($user->getPatronizing() as $each) {
					$em->remove($each);
				}
				foreach ($user->getReports() as $each) {
					$each->setOldUserId($id);
					$each->setUser(null);
				}
				foreach ($user->getReportsAgainst() as $each) {
					$each->setOldUserId($id);
					$each->setUser(null);
				}
				foreach ($user->getAddedReportNotes() as $each) {
					$each->setUser(null);
				}
				foreach ($user->getMailEntries() as $each) {
					$em->remove($each);
				}
				$em->remove($user->getLimits());
				foreach ($user->getKeys() as $each) {
					$em->remove($each);
				}
				$em->flush();
				$em->remove($user);
				$note->spoolError('Account deleted successfully.');
				$this->addFlash('notice', $trans->trans('user.delete.success', [], 'core'));
				return new RedirectResponse($this->generateUrl('maf_account'));
			} else {
				$form->addError(new FormError($trans->trans('user.delete.emailmistmatch', [], 'core')));
			}
		}
		return $this->render('Account/delete.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route ('/security/keys', name:'maf_account_keys')]
	public function keysAction(EntityManagerInterface $em): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		if ($user->getKeys()->count() === 0) {
			$valid = false;
			$i = 0;
			while (!$valid && $i < 10) {
				$token = bin2hex(random_bytes(32));
				$result = $em->getRepository(User::class)->findOneBy(['user'=>$user->getId(), 'token' => $token]);
				if (!$result) {
					$valid = true;
				} else {
					$i++;
				}
			}
			$key = new AppKey;
			$em->persist($key);
			$key->setUser($user);
			$key->setToken($token);
			$em->flush();
		}

		return $this->render('Account/keys.html.twig', [
			'keys' => $user->getKeys(),
		]);
	}

	#[Route ('/security/keys/reset/{key}', name:'maf_account_keys_reset', requirements:['key'=>'\d+'])]
	public function keyResetAction(EntityManagerInterface $em, TranslatorInterface $trans, AppKey $key): RedirectResponse {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		if ($user->getKeys()->containes($key)) {
			$valid = false;
			while (!$valid) {
				$token = bin2hex(random_bytes(32));
				$result = $em->getRepository(User::class)->findOneBy(['user'=>$user->getId(), 'token' => $token]);
				if (!$result) {
					$valid = true;
				}
			}
			$key->setToken($token);
			$em->flush();
			$this->addFlash('notice', $trans->trans('account.key.reset.success', [], "messages"));
		} else {
			$this->addFlash('notice', $trans->trans('account.key.unauthorized', [], "messages"));
		}
		return $this->redirectToRoute('maf_account_keys');
	}

	#[Route ('/security/keys/{key}/delete', name:'maf_key_delete', requirements:['key'=>'\d+'])]
	public function keyDeleteAction(EntityManagerInterface $em, TranslatorInterface $trans, AppKey $key): RedirectResponse {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$user = $this->getUser();
		if ($user->getKeys()->containes($key)) {
			$em->remove($key);
			$em->flush();
			$this->addFlash('notice', $trans->trans('account.key.delete.success', [], "messages"));
		} else {
			$this->addFlash('notice', $trans->trans('account.key.unauthorized', [], "messages"));
		}
		return $this->redirectToRoute('maf_account_keys');
	}
	#[Route ('/account/keys/new', name:'maf_account_keys_new')]
	public function keyNewAction(EntityManagerInterface $em, TranslatorInterface $trans): RedirectResponse {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$user = $this->getUser();
		$valid = false;
		$i = 0;
		if ($user->getKeys()->contains() > 10) {
			$this->addFlash('notice', $trans->trans('account.key.toomany', [], "messages"));
		} else {
			while (!$valid && $i < 10) {
				$token = bin2hex(random_bytes(32));
				$result = $em->getRepository(User::class)->findOneBy(['user'=>$user->getId(), 'token' => $token]);
				if (!$result) {
					$valid = true;
				} else {
					$i++;
				}
			}
			if ($valid) {
				$key = new AppKey;
				$em->persist($key);
				$key->setUser($user);
				$key->setToken($token);
				$em->flush();
				$this->addFlash('notice', $trans->trans('account.key.new.success', [], "messages"));
			} else {
				$this->addFlash('notice', $trans->trans('account.key.fail', [], "messages"));
			}
		}
		return $this->redirectToRoute('maf_account_keys');
	}
}
