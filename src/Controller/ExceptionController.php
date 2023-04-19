<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionController extends AbstractController {
	/**
	 * Converts an Exception to a Response.
	 *
	 * @param FlattenException $exception A FlattenException instance
	 * @param DebugLoggerInterface|null $logger A DebugLoggerInterface instance
	 * @param string $format The format to use for rendering (html, xml, ...)
	 * @param Boolean $embedded Whether the rendered Response will be embedded or not
	 *
	 * @return Response
	 */
	#[Route ('/error/')]
	public function exceptionAction(FlattenException $exception, DebugLoggerInterface $logger = null, string $format = 'html', bool $embedded = false): Response {
		return $this->render('Exception/exception.html.twig', [
			'status_code' => $exception->getStatusCode(), 
			'status_text' => $exception->getMessage()
		]);
	}
}
