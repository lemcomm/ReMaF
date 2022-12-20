<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotUsernameFormType;
use App\Form\RegistrationFormType;
use App\Form\RequestResetFormType;
use App\Form\ResetPasswordFormType;
use App\Form\NewTokenFormType;
use App\Form\UserDetailsFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\AppState;
use App\Service\MailManager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController {

        private $core;
        private $trans;
        private $em;
        private $mail;

        public function __construct(AppState $appstate, TranslatorInterface $trans, EntityManagerInterface $em, MailManager $mail) {
                $this->appstate = $appstate;
                $this->trans = $trans;
                $this->em = $em;
                $this->mail = $mail;
        }

	#[Route ('/login', name:'maf_login')]
        public function login(AuthenticationUtils $authenticationUtils): Response {
                # Fetch the previous error, if there is one.
                $error = $authenticationUtils->getLastAuthenticationError();

                #Fetch last username entered by the user.
                $last = $authenticationUtils->getLastUsername();

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

	#[Route ('/account', name:'maf_account')]
        public function account(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
                $user = $this->getUser();
                $em = $this->em;
                $trans = $this->trans;
                $form = $this->createForm(UserDetailsFormType::class, null, [
                        'username' => $user->getUsername(),
                        'email' => $user->getEmail(),
                        'display' => $user->getDisplayname(),
                ]);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                        $data = $form->getData();
                        if ($form->get('username')->getData() != NULL && $form->get('username')->getData() != $user->getUsername()) {
                                $user->setUsername($form->get('username')->getData());
                                $this->addFlash('notice', $trans->trans('security.account.change.username', [], 'core'));
                        }
                        if ($form->get('plainPassword')->getData() != NULL) {
                                $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
                                $this->addFlash('notice', $trans->trans('security.account.change.password', [], 'core'));
                        }
                        if ($form->get('email')->getData() != NULL && $form->get('email')->getData() != $user->getEmail()) {
                                $user->setEmailToken($this->appstate->generateAndCheckToken(16, 'User', 'email_token'));
                                $user->setEmail($form->get('email')->getData());
                                $user->setConfirmed(false);

                                $link = $this->generateUrl('maf_account_confirm', ['token' => $user->getEmailToken(), 'email' => $form->get('email')->getData()], UrlGeneratorInterface::ABSOLUTE_URL);
                                $text = $trans->trans(
                                        'security.account.email.text', [
                                                '%sitename%' => $_ENV['SITE_NAME'],
                                                '%link%' => $link,
                                                '%adminemail%' => $_ENV['ADMIN_EMAIL']
                                        ], 'core');
                                $subject = $trans->trans('security.account.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');
                                $this->mail->createMail(null, $user->getEmail(), null, null, $subject, $text, null);
                                $this->addFlash('notice', $trans->trans('security.account.change.email', [], 'core'));
                        }
                        if ($form->get('display_name')->getData() != NULL && $form->get('display_name')->getData() != $user->getDisplayName()) {
                                $user->setDisplayName($form->get('display_name')->getData());
                                $this->addFlash('notice', $trans->trans('security.account.change.username', [], 'core'));
                        }
                        $em->flush();
                        return new RedirectResponse($this->generateUrl('maf_index'));
                }
                return $this->render('Account/account.html.twig', [
                        'form' => $form->createView()
                ]);
        }

	#[Route ('/reset', name:'maf_account_reset')]
        public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = '0', string $email = '0') {
                $trans = $this->trans;
                $em = $this->em;
                if ($token == '0') {
                        $form = $this->createForm(RequestResetFormType::class);
                        $form->handleRequest($request);
                        if ($form->isSubmitted() && $form->isValid()) {
                                $data = $form->getData();
                                $user = $em->getRepository(User::class)->findOneByEmail($data['text']);
                                if (!$user) {
                                        $user = $em->getRepository(User::class)->findOneByUsername($data['text']);
                                }
                                if ($user) {
                                        $user->setResetToken($this->appstate->generateAndCheckToken(64, 'User', 'reset_token'));
                                        $em->flush();
                                        $link = $this->generateUrl('maf_account_reset', ['token' => $user->getResetToken(), 'email'=>$user->getEmail()], UrlGeneratorInterface::ABSOLUTE_URL);
                                        $text = $trans->trans(
                                                'security.reset.email.text', [
                                                        '%sitename%' => $_ENV['SITE_NAME'],
                                                        '%link%' => $link,
                                                        '%adminemail%' => $_ENV['ADMIN_EMAIL']
                                                ], 'core');
                                        $subject = $trans->trans('security.reset.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

                                        $this->mail->createMail(null, $user->getEmail(), null, null, $subject, $text, null);
                                } else {
                                        # Do nothing, actually. Failed attempts just fail.
                                }
                                $this->addFlash('notice', $trans->trans('security.reset.flash.requested', [], 'core'));
                                return new RedirectResponse($this->generateUrl('maf_index'));
                        }
                        return $this->render('Account/reset.html.twig', [
                                'form' => $form->createView(),
                        ]);
                } else {
                        $user = $em->getRepository(User::class)->findOneBy(['reset_token' => $token, 'email' => $email]);
                        if ($user) {
                                $form = $this->createForm(ResetPasswordFormType::class);
                                $form->handleRequest($request);
                                if ($form->isSubmitted() && $form->isValid()) {
                                        $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
                                        $user->unsetResetToken();
                                        $user->unsetResetTime();
                                        $em->flush();

                                        $this->addFlash('notice', $trans->trans('security.reset.flash.completed', [], 'core'));
                                        return new RedirectResponse($this->generateUrl('maf_index'));
                                }
                                return $this->render('Account/reset.html.twig', [
                                        'form' => $form->createView(),
                                ]);
                        } else {
                                $this->appstate->logSecurityViolation($request->getClientIP(), 'core_reset', $this->getUser(), 'bad reset');
                                return new RedirectResponse($this->generateUrl('maf_index'));
                        }
                }
        }

	#[Route ('/remind', name:'maf_remind')]
        public function remind(Request $request) {
                $form = $this->createForm(ForgotUsernameFormType::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                        $data = $form->getData();
                        $em = $this->em;
                        $trans = $this->trans;
                        $user = $em->getRepository(User::class)->findOneByEmail($data['email']);

                        if ($user) {
                                $user->setResetToken($this->appstate->generateAndCheckToken(64, 'User', 'reset_token'));
                                $em->flush();
                                $resetLink = $this->generateUrl('core_reset', [], UrlGeneratorInterface::ABSOLUTE_URL);
                                $loginLink = $this->generateUrl('core_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                                $text = $trans->trans(
                                        'security.remind.email.text', [
                                                '%sitename%' => $_ENV['SITE_NAME'],
                                                '%username%' => $user->getUsername(),
                                                '%login%' => $loginLink,
                                                '%reset%' => $resetLink,
                                                '%adminemail%' => $_ENV['ADMIN_EMAIL']
                                        ], 'core');
                                $subject = $trans->trans('security.remind.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

                                $this->mail->createMail(null, $user->getEmail(), null, null, $subject, $text, null);
                        } else {
                                # Do nothing, actually. Failed attempts just fail.
                        }
                        $this->addFlash('notice', $trans->trans('security.remind.flash', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                }
                return $this->render('Account/remind.html.twig', [
                        'form' => $form->createView(),
                ]);
        }

	#[Route ('/register', name:'maf_account_register')]
        public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response {
                $user = new User();
                $form = $this->createForm(RegistrationFormType::class, $user);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                        $em = $this->em;
                        $trans = $this->trans;
                        $check = $em->getRepository(User::class)->findOneByUsername($form->get('username')->getData());
                        if ($check) {
                                $this->addFlash('error', $trans->trans('security.register.duplicate.username', [], 'core'));
                                return new RedirectResponse($this->generateUrl('maf_account_register'));
                        }
                        # Encode plain password in database
                        $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
                        #Generate activation token
                        $user->setToken($this->appstate->generateAndCheckToken(16, 'User', 'token'));

                        #Log user creation time and set user to inactive.
                        $user->setCreated(new \DateTime("now"));
                        $method = $_ENV['ACTIVATION'];
                        if ($method == 'email' || $method == 'manual') {
                                $user->setActive(false);
                        } else {
                                $user->setActive(true);
                        }
                        $user->setConfirmed(false);

                        $em->persist($user);
                        $em->flush();

                        if ($method == 'email') {
                                # Generate Activation Email
                                $link = $this->generateUrl('maf_account_activate', ['username' => $user->getUsername(), 'email' => $user->getEmail(), 'token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                                $text = $trans->trans(
                                        'security.register.email.text1', [
                                                '%username%' => $user->getUsername(),
                                                '%sitename%' => $_ENV['SITE_NAME'],
                                                '%link%' => $link,
                                                '%adminemail%' => $_ENV['ADMIN_EMAIL']
                                        ], 'core');
                                $subject = $trans->trans('security.register.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

                                $this->mail->createMail(null, $user->getEmail(), null, null, $subject, $text, null);
                        }

                        $this->addFlash('notice', $trans->trans('security.register.flash', [], 'core'));

                        return $guardHandler->authenticateUserAndHandleSuccess(
                                $user,
                                $request,
                                $authenticator,
                                'main' // firewall name in security.yaml
                        );
                }

                return $this->render('Account/register.html.twig', [
                        'registrationForm' => $form->createView(),
                ]);
        }

	#[Route ('/newToken', name:'maf_token_new')]
        public function newToken(Request $request) {
                $form = $this->createForm(NewTokenFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                        $trans = $this->trans;
                        $em = $this->em;
                        $data = $form->getData();
                        $user = $em->getRepository(User::class)->findOneBy(['username' => $data['username'], 'email' => $data['email']]);
                        if ($user) {
                                $user->setToken($this->appstate->generateAndCheckToken(16, 'User', 'token'));
                                $em->flush();

                                $link = $this->generateUrl('maf_account_activate', ['username' => $user->getUsername(), 'email' => $user->getEmail(), 'token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                                $text = $trans->trans(
                                        'security.register.email.text2', [
                                                '%username%' => $user->getUsername(),
                                                '%sitename%' => $_ENV['SITE_NAME'],
                                                '%link%' => $link,
                                                '%adminemail%' => $_ENV['ADMIN_EMAIL']
                                        ], 'core');
                                $subject = $trans->trans('security.register.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'core');

                                $this->mail->createMail(null, $user->getEmail(), null, null, $subject, $text, null);
                        } else {
                                # Do nothing. Failed attempts just fail.
                        }
                        $this->addFlash('notice', $trans->trans('security.newtoken.flash.sent', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                }

                return $this->render('Account/newtoken.html.twig', [
                        'form' => $form->createView(),
                ]);
        }

	#[Route ('/activate/{username}/{email}/{token}', name:'maf_account_activate')]
        public function activate(string $username, string $email, string $token) {
                $em = $this->em;
                $trans = $this->trans;
                $user = $em->getRepository(User::class)->findOneBy(['username' => $username, 'email' => $email]);
                if ($user && $user->getActive() === false && $token == $user->getToken()) {
                        $user->unsetToken();
                        $user->setActive(true);
                        $em->flush();
                        $this->addFlash('notice', $trans->trans('security.activate.flash.success', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                } elseif ($user && $user->getActive() === true) {
                        $this->addFlash('notice', $trans->trans('security.activate.flash.already', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_index'));
                } else {
                        $link = $this->generateUrl('core_newtoken');
                        $this->addFlash('error', $trans->trans('security.activate.flash.failed', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_token_new'));
                }

                return $this->render('Security/activate.html.twig', [
                        'registrationForm' => $form->createView(),
                ]);
        }

	#[Route ('/confirm/{token}/{email}', name:'maf_account_confirm')]
        public function confirm(string $token, string $email) {
                $em = $this->em;
                $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                $trans = $this->trans;
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
                        $link = $this->generateUrl('core_newtoken');
                        $this->addFlash('error', $trans->trans('security.confirm.flash.failed', [], 'core'));
                        return new RedirectResponse($this->generateUrl('maf_token_new'));
                }

                return $this->render('Account/activate.html.twig', [
                        'registrationForm' => $form->createView(),
                ]);
        }

        #[Route ('/postlogin/{key}', name:'maf_account_postlogin')]
        public function postlogin(Request $request, $key) {
                $em = $this->getDoctrine()->getManager();
                $this->getUser()->setLastLogin(new \DateTime("now"));
                $em->flush();
                if ($key == 'null') {
                        return new RedirectResponse($this->generateUrl('core_index'));
                } else {
                        return new RedirectResponse($request->getSession()->get('_security.'.$key.'.target_path'));
                }

        }
}
