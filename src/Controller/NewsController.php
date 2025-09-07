<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\NewsArticle;
use App\Entity\NewsEdition;
use App\Entity\NewsPaper;
use App\Entity\NewsReader;
use App\Form\InteractionType;
use App\Form\NewsArticleType;
use App\Form\NewsEditorType;
use App\Service\AppState;
use App\Service\Geography;
use App\Service\NewsManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsController extends AbstractController {
	public function __construct(
		private AppState $app,
		private NewsManager $news,
		private TranslatorInterface $trans) {
	}

	#[Route ('/news/', name:'maf_news')]
	public function indexAction(): Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('News/current.html.twig', [
			"editor_list"=>$character->getNewspapersEditor(),
			"reader_list"=>$character->getNewspapersReader(),
			"local_list"=>$this->news->getLocalList($character),
			"can_create"=>$this->news->canCreatePaper($character)
		]);
	}

	#[Route ('/news/read/{edition}', name:'maf_news_read', requirements:['edition'=>'\d+'])]
	public function readAction(EntityManagerInterface $em, NewsEdition $edition): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$reader = $this->news->readEdition($edition, $character);
		if (!$reader) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.edition'));
		}

		$can_subscribe = false;
		if (true === $reader) {
			// temporary access of a local publication
			// FIXME: check for the $paper->getSubscription() boolean we've already defined - but right now the paper owner can't change it anywhere
			$can_subscribe = true;
		} elseif ($reader->getRead()===false || $reader->getUpdated()===true) {
			$reader->setRead(true);
			$reader->setUpdated(false);
			$em->flush();
		}

		return $this->render('News/read.html.twig', [
			'paper'	=>	$edition->getPaper(),
			'edition' => $edition,
			'can_subscribe' => $can_subscribe
		]);
	}

	#[Route ('/news/subscribe/{edition}', name:'maf_news_subscribe', requirements:['edition'=>'\d+'])]
	 public function subscribeAction(EntityManagerInterface $em, NewsEdition $edition): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		// FIXME: catch exception if $paper can not be found and throw this:
		//			throw $this->createNotFoundException('error.notfound.paper');

		$reader = $this->news->readEdition($edition, $character);
		if (!$reader) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.edition'));
		}
		if (true === $reader) {
			$reader = new NewsReader;
			$reader->setCharacter($character);
			$reader->setEdition($edition);
			$reader->setRead(true)->setUpdated(false);
			$em->persist($reader);
			$em->flush();
			$this->addFlash('notice', $this->trans->trans('news.subscribe.done', array('%paper%'=>$edition->getPaper()->getName()), 'communication'));

		}
		return new RedirectResponse($this->generateUrl('maf_news'));
	}

	#[Route ('/news/create', name:'maf_news_create', requirements:['id'=>'\d+'])]
	public function createAction(EntityManagerInterface $em, Request $request): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if (!$this->news->canCreatePaper($character)) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.library'));
		}

		$form = $this->createFormBuilder()
			->add('name', TextType::class, array(
				'required'=>true,
				'label'=>'news.create.newname',
				'translation_domain' => 'communication'
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$newspaper = $this->news->newPaper($data['name'], $character);
			$em->flush();
			
			return new RedirectResponse($this->generateUrl('maf_news_editor', ['paper'=>$newspaper->getId()]));
		}

		return $this->render('News/create.html.twig', [
			'form'=>$form->createView()
		]);
	}

	#[Route ('/news/editor/{paper}', name:'maf_news_editor', requirements:['paper'=>'\d+'])]
	 public function editorAction(NewsPaper $paper): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$editor = $this->news->accessPaper($paper, $character);
		if (!$editor) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paper'));
		}

		$form = $this->createForm(NewsEditorType::class, null, ['paper'=>$paper]);

		return $this->render('News/editor.html.twig', [
			'paper'	=>	$paper,
			'editor'	=> $editor,
			'form'	=> $form->createView()
		]);
	}

	#[Route ('/news/editor/change}', name:'maf_news_editor_change', defaults:['_format'=>'json'])]
	 public function editorchangeAction(EntityManagerInterface $em, Request $request): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($request->isMethod('POST')) {
			$paperId = $request->request->get('paper');
			$targetId = $request->request->get('character');
			$paper = $em->getRepository(NewsPaper::class)->find($paperId);
			if (!$paper) {
				throw $this->createNotFoundException('error.notfound.paper');
			}

			$editor = $this->news->accessPaper($paper, $character);
			if (!$editor || $editor->getOwner()===false) {
				throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paperowner'));
			}

			$form = $this->createForm(NewsEditorType::class, null, ['paper'=>$paper]);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();

				$target = $em->getRepository(Character::class)->find($targetId);
				if (!$target) {
					throw $this->createNotFoundException($this->trans->trans('error.notfound.character'));
				}
				$target_editor = $this->news->accessPaper($paper, $target);
				if (!$target_editor) {
					throw $this->createNotFoundException($this->trans->trans('error.notfound.character'));
				}
				// TODO: make sure the paper always has at least one owner!
				// TODO: probably move this into the news manager
				if (!$data['owner'] && !$data['editor'] && !$data['author'] && !$data['publisher']) {
					$em->remove($target_editor);
					$paper->removeEditor($target_editor);
					$target->removeNewspapersEditor($target_editor);
				} else {
					$target_editor->setOwner($data['owner']);
					$target_editor->setEditor($data['editor']);
					$target_editor->setAuthor($data['author']);
					$target_editor->setPublisher($data['publisher']);
				}

				// TODO: notify target

				$em->flush();
				return new Response(json_encode(true));
			}
		}

		return new Response(json_encode(false));
	}

	#[Route ('/news/editor/addform/{paperId}', name:'maf_news_editor_addform', requirements:['paperId'=>'\d+'])]
	 public function editoraddformAction(Geography $geo, $paperId): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$distance = $geo->calculateInteractionDistance($character);
		$form = $this->createForm(InteractionType::class, null, [
			'subaction'=>'publication',
			'maxdistance'=>$distance,
			'me'=>$character,
			'multiple'=>true
		]);

		return $this->render('News/editoraddform.html.twig', [
			'paperid'=>$paperId,
			'form'=>$form->createView()
		]);
	 }

	#[Route ('/news/editor/add', name:'maf_news_editor_add')]
	 public function editoraddAction(EntityManagerInterface $em, Geography $geo, Request $request): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($request->isMethod('POST')) {
			$paperId = $request->request->get('paper');
			$paper = $em->getRepository(NewsPaper::class)->find($paperId);
			if (!$paper) {
				throw $this->createNotFoundException($this->trans->trans('error.notfound.paper'));
			}

			$editor = $this->news->accessPaper($paper, $character);
			if (!$editor || $editor->getOwner()===false) {
				throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paperowner'));
			}

			$distance = $geo->calculateInteractionDistance($character);
			$form = $this->createForm(InteractionType::class, null, [
				'subaction'=>'publication',
				'maxdistance'=>$distance,
				'me'=>$character,
				'multiple'=>true
			]);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();
				$data['target'] = $form->get('target')->getData();

				foreach ($data['target'] as $target) {
					$target_editor = $this->news->accessPaper($paper, $target);
					// FIXME: json response doesn't work here, use something else!
					if ($target_editor) {
						$this->addFlash('notice', $this->trans->trans('error.iseditor', array('%character%'=> $target->getName())));

					} else {
						$this->news->addEditor($paper, $target);
					}
				}

				$em->flush();
				return new RedirectResponse($this->generateUrl('maf_news_editor', ['paper'=>$paperId]));
			}
		}

		return new Response(json_encode(false));
	}

	#[Route ('/news/edition/create/{paper}', name:'maf_news_edition_create', requirements:['paper'=>'\d+'])]
	 public function createeditionAction(EntityManagerInterface $em, NewsPaper $paper): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$editor = $this->news->accessPaper($paper, $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paper'));
		}

		$edition = $this->news->newEdition($paper);
		if ($paper->getEditions()->count()<=1) {
			// first edition - add example articles
			for ($i=0;$i<4;$i++) {
				$article = new NewsArticle;
				$article->setAuthor($character);
				$article->setWritten(new DateTime("now"));
				$article->setEdition($edition);
				$article->setTitle($this->trans->trans('news.examples.title'.$i, array(), "communication"));
				$article->setContent($this->trans->trans('news.examples.content'.$i, array(), "communication"));
				$this->news->addArticle($article);
				switch ($i) {
					case 0:	$article->setRow(1)->setCol(3)->setSizeX(2)->setSizeY(2); break;
					case 1:	$article->setRow(1)->setCol(1)->setSizeX(2)->setSizeY(1); break;
					case 2:	$article->setRow(2)->setCol(1)->setSizeX(1)->setSizeY(1); break;
					case 3:	$article->setRow(2)->setCol(2)->setSizeX(1)->setSizeY(1); break;
				}
			}
		}
		$em->flush();

		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$edition->getId()]));
	 }

	#[Route ('/news/edition/{edition}', name:'maf_news_edition', requirements:['edition'=>'\d+'])]
	 public function editionAction(NewsEdition $edition): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$editor = $this->news->accessPaper($edition->getPaper(), $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.edition'));
		}

		$article = new NewsArticle;
		$article->setEdition($edition);
		$form = $this->createForm(NewsArticleType::class, $article);

		return $this->render('News/edition.html.twig', [
			'paper'	=>	$edition->getPaper(),
			'editor'	=> $editor,
			'edition' => $edition,
			'form' => $form->createView()
		]);
	}

	#[Route ('/news/edition/{edition}/publish', name:'maf_news_edition_publish', requirements:['edition'=>'\d+'], defaults:['_format'=>'json'], methods:['POST'])]
	public function publishAction(EntityManagerInterface $em, NewsEdition $edition): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$editor = $this->news->accessPaper($edition->getPaper(), $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.edition'));
		}

		$this->news->publishEdition($edition);
		$em->flush();

		return new Response(json_encode(array('success'=>true)));
	}

	#[Route ('/news/layout', name:'maf_news_layout', requirements:['_format'=>'json'], methods:['POST'])]
	 public function layoutAction(EntityManagerInterface $em, Request $request): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$editionId = $request->request->get('edition');
		$layout_data = json_decode($request->request->get('layout'));
		$layout = array();
		foreach ($layout_data as $data) {
			$layout[$data->article] = $data;
		}

		$edition = $em->getRepository(NewsEdition::class)->find($editionId);
		if (!$edition) {
			return new Response(json_encode(array(
				'success'=>false,
				'message'=>$this->trans->trans('error.notfound.edition'),
			)));
		}

		$editor = $this->news->accessPaper($edition->getPaper(), $character);
		if (!$editor || $editor->getEditor()===false) {
			return new Response(json_encode(array(
				'success'=>false,
				'message'=>$this->trans->trans('error.noaccess.edition'),
			)));
		}

		foreach ($edition->getArticles() as $article) {
			if (isset($layout[$article->getId()])) {
				$box = $layout[$article->getId()];
				$article->setRow($box->row);
				$article->setCol($box->col);
				$article->setSizeX($box->x);
				$article->setSizeY($box->y);
			}
		}
		$em->flush();

		return new Response(json_encode(array('success'=>true)));
	}

	#[Route ('/news/article/new', name:'maf_news_article_new')]
	public function newarticleAction(EntityManagerInterface $em, Request $request): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$article = new NewsArticle;
		$form = $this->createForm(NewsArticleType::class, $article);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$article = $form->getData();
			$paper = $article->getEdition()->getPaper();
			if (!$paper) {
				throw $this->createNotFoundException($this->trans->trans('error.notfound.paper'));
			}

			$editor = $this->news->accessPaper($paper, $character);
			if (!$editor || $editor->getEditor()===false) {
				throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paper'));
			}

			$article->setAuthor($character);
			$article->setWritten(new DateTime("now"));
			$this->news->addArticle($article);
			$em->flush();
		}

		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$article->getEdition()->getId()]));
	 }

	#[Route ('/news/article/edit/{article}', name:'maf_news_article_edit', requirements:['article'=>'\d+'])]
	public function editarticleAction(EntityManagerInterface $em, NewsArticle $article, Request $request): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(NewsArticleType::class, $article);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$article = $form->getData();
			$paper = $article->getEdition()->getPaper();
			if (!$paper) {
				throw $this->createNotFoundException($this->trans->trans('error.notfound.paper'));
			}

			$editor = $this->news->accessPaper($paper, $character);
			if (!$editor || $editor->getEditor()===false) {
				throw new AccessDeniedHttpException($this->trans->trans('error.noaccess.paper'));
			}

			$article->setAuthor($character);
			$article->setWritten(new DateTime("now"));
			$em->flush();
		}

		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$article->getEdition()->getId()]));
	 }

	#[Route ('/news/article/store/{article}', name:'maf_news_article_store', requirements:['article'=>'\d+'], methods:['POST'])]
	public function storearticleAction(EntityManagerInterface $em, NewsArticle $article): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$paper = $article->getEdition()->getPaper();
		if (!$paper) {
			throw $this->createNotFoundException('error.notfound.paper');
		}
		$editor = $this->news->accessPaper($paper, $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException('error.noaccess.paper');
		}
		
		$collection = $em->getRepository(NewsEdition::class)->findOneBy(array('paper'=>$paper, 'collection'=>true));
		if (!$collection) {
			// FIXME: should never happen
			throw $this->createNotFoundException("Article collection not found for paper {$paper->getId()} - this should never happen. Please report as a bug.");
		}

		$returnto = $article->getEdition()->getId();
		$article->setEdition($collection);
		$em->flush();

		$this->addFlash('notice', $this->trans->trans('news.moved', array(), 'communication'));

		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$returnto]));
	}

	#[Route ('/news/article/restore/{article}', name:'maf_news_article_restore', requirements:['article'=>'\d+'], methods:['POST'])]
	public function restorearticleAction(EntityManagerInterface $em, NewsArticle $article, Request $request): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$paper = $article->getEdition()->getPaper();
		if (!$paper) {
			throw $this->createNotFoundException('error.notfound.paper');
		}
		$editor = $this->news->accessPaper($paper, $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException('error.noaccess.paper');
		}
		
		$newedition = $em->getRepository(NewsEdition::class)->find($request->request->get("edition"));
		if (!$newedition) {
			throw $this->createNotFoundException('form error: invalid edition');
		}
		if ($newedition->getPaper() !== $paper) {
			throw new AccessDeniedHttpException('form error: wrong paper');
		}
		if ($newedition->isPublished()) {
			throw new AccessDeniedHttpException('form error: edition already published');
		}

		$article->setEdition($newedition);
		$em->flush();

		$this->addFlash('notice', $this->trans->trans('news.restored', array(), 'communication'));

		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$article->getEdition()->getId()]));
	}

	#[Route ('/news/article/delete/{article}', name:'maf_news_article_delete', requirements:['article'=>'\d+'], methods:['POST'])]
	public function delarticleAction(EntityManagerInterface $em, NewsArticle $article): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$paper = $article->getEdition()->getPaper();
		if (!$paper) {
			throw $this->createNotFoundException('error.notfound.paper');
		}
		$editor = $this->news->accessPaper($paper, $character);
		if (!$editor || $editor->getEditor()===false) {
			throw new AccessDeniedHttpException('error.noaccess.paper');
		}
		
		$collection = $em->getRepository(NewsEdition::class)->findOneBy(array('paper'=>$paper, 'collection'=>true));
		if (!$collection) {
			// FIXME: should never happen
			throw $this->createNotFoundException("Article collection not found for paper {$paper->getId()} - this should never happen. Please report as a bug.");
		}

		$returnto = $article->getEdition()->getId();
		$article->getEdition()->removeArticle($article);
		$em->remove($article);
		$em->flush();

		$this->addFlash('notice', $this->trans->trans('news.del', array(), 'communication'));
		
		return new RedirectResponse($this->generateUrl('maf_news_edition', ['edition'=>$returnto]));
	}
}
