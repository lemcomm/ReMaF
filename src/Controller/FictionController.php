<?php

namespace App\Controller;

use App\Service\PageReader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FictionController extends AbstractController {

	private array $pages = array(
		'creation',
		'firstones',
		'fall',
		'lendan',
		'geas',
	);

  	#[Route ('/fiction/{page}', name:'maf_fiction')]
	public function indexAction($page='index') {

		return $this->render('Fiction/index.html.twig', [
			"simple"=>true, "page"=>$page, "allpages"=>$this->pages
		]);
	}

	#[Route ('/fiction/content/{page}', name:'maf_fiction_content')]
	public function contentAction(PageReader $pr, Request $request, $page) {
		$locale = $request->getLocale();

		$content = $pr->getPage('fiction', $page, $locale);

		return $this->render('Fiction/content.html.twig', [
			'title' => $page,
			'content'=>$content
		]);
	}

}
