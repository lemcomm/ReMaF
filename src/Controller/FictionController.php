<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FictionController extends AbstractController {

	private $pages = array(
		'creation',
		'firstones',
		'fall',
		'lendan',
		'geas',
	);

	/**
	  * @Route("/{page}", name="bm2_fiction", defaults={"page"="index"})
	  */
  	#[Route ('/fiction/{page}', name:'maf_fiction')]
	public function indexAction($page='index') {

		return $this->render('Fiction/index.html.twig', [
			"simple"=>true, "page"=>$page, "allpages"=>$this->pages
		]);
	}

	/**
	  * @Route("/fiction/content/{page}", name="maf_fiction_content")
	  */
	public function contentAction($page) {
		$pr = $this->get('pagereader');
		$locale = $this->getRequest()->getLocale();

		$content = $pr->getPage('fiction', $page, $locale);

		return $this->render('Fiction/content.html.twig', [
			'title' => $page,
			'content'=>$content
		]);
	}

}
