<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AppState;
use App\Service\PageReader;
use App\Service\PaymentManager;
use App\Service\UserManager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController {

	#[Route ('/', name:'maf_index')]
	#[Route ('/', name:'maf_homepage')]
	public function indexAction(EntityManagerInterface $em) {
		$query = $em->createQuery('SELECT j, c from App:Journal j JOIN j.character c WHERE j.public = true AND j.graphic = false AND j.pending_review = false AND j.GM_private = false AND j.GM_graphic = false ORDER BY j.date DESC')->setMaxResults(3);
		$journals = $query->getResult();
		return $this->render('Default/index.html.twig', [
			"simple"=>true,
			"journals"=>$journals
		]);
	}

	#[Route ('/about', name:'maf_about')]
	public function aboutAction(Request $req, PageReader $pr, PaymentManager $pay) {
		$locale = $req->getLocale();

		$intro = $pr->getPage('about', 'introduction', $locale);
		$concept = $pr->getPage('about', 'concept', $locale);
		$gameplay = $pr->getPage('about', 'gameplay', $locale);
		$tech = $pr->getPage('about', 'technology', $locale);

		return $this->render('Default/about.html.twig', [
			"simple"=>true,
			'intro' => $intro,
			'concept' => $concept,
			'gameplay' => $gameplay,
			'tech' => $tech,
			'levels' => $pay->getPaymentLevels(),
			'concepturl' => $this->generateUrl('maf_about_payment'),
		]);
	}
	#[Route ('/manual/{page}', name:'maf_manual')]
	public function manualAction(Request $req, PageReader $pr, $page='intro') {
		return $this->render('Default/manual.html.twig', [
			"page" => $page,
			"toc" => $pr->getPage('manual', 'toc', $req->getLocale()),
			"content" => $pr->getPage('manual', $page, $req->getLocale())
		]);
	}

	#[Route ('/vips', name:'maf_vips')]
	public function vipsAction(EntityManagerInterface $em) {
		$query = $em->createQuery('SELECT u.display_name, u.vip_status FROM App:User u WHERE u.vip_status > 0 ORDER BY u.vip_status DESC, u.display_name');
		$vips = $query->getResult();

		return $this->render('Default/vips.html.twig', [
			"simple"=>true, "vips"=>$vips
		]);
	}


  	#[Route ('/contact', name:'maf_contact')]
	public function contactAction() {

		return $this->render('Default/contact.html.twig', [
			"simple"=>true
		]);
	}

  	#[Route ('/credits', name:'maf_credits')]
	public function creditsAction(EntityManagerInterface $em) {
		$query = $em->createQuery('SELECT u FROM App:User u JOIN u.patronizing p WHERE u.show_patronage = :true ORDER BY u.display_name ASC');
		$query->setParameters(['true'=>true]);

		return $this->render('Default/credits.html.twig', [
			"simple"=>true,
			"patrons"=>$query->getResult()
		]);
	}

  	#[Route ('/terms', name:'maf_terms')]
	public function termsAction() {

		return $this->render('Default/terms.html.twig');
	}

  	#[Route ('/privacy', name:'maf_privacy')]
	public function privacyAction() {

		return $this->render('Default/privacy.html.twig');
	}

  	#[Route ('/cookies', name:'maf_cookies')]
	public function cookiesAction() {

		return $this->render('Default/cookies.html.twig');
	}

    	#[Route ('/user/{user}', name:'maf_user')]
	public function userAction($user) {
		# This allows us to not have a user returned and sanitize the output. No user? Pretend they just private :)
		$user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['id'=>$user]);
		$gm = $this->get('security.authorization_checker')->isGranted('ROLE_OLYMPUS');

		return $this->render('Default/user.html.twig', [
			"user"=>$user,
			"gm"=>$gm,
		]);
	}

    	#[Route ('/paymentconcept', name:'maf_about_payment')]
	public function paymentConceptAction(Request $request, PageReader $pr, PaymentManager $pay) {
		return $this->render('Default/paymentConcept.html.twig', [
			"simple"=>true,
			"content"=>$pr->getPage('about', 'payment', $request->getLocale()),
			"paylevels"=>$pay->getPaymentLevels()
		]);
	}


	public function localeRedirectAction(AppState $appstate, $url) {
		if ($url=="-") $url="";
		if (preg_match('/^[a-z]{2}\//', $url)===1) {
			if (substr($url, 0, 2)=='en') {
        		throw $this->createNotFoundException('error.notfound.page');
        	}
			// unsupported locale - default to english - en
			$locale = 'en';
			$url = substr($url,3);
		} else {
			// no locale parameter - use the user's setting, defaulting to browser settings
			if ($user = $this->getUser()) {
				$locale = $user->getLanguage();
			}
			if (!isset($locale) || !$locale) {
				$locale = substr($this->getRequest()->getPreferredLanguage(),0,2);
			}
			if ($locale) {
				$languages = $appstate->availableTranslations();
				if (!isset($languages[$locale])) {
					$locale='en';
				}
			} else {
				$locale='en';
			}
		}
		return $this->redirect('/'.$locale.'/'.$url);
	}

}
