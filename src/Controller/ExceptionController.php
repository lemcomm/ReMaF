<?php

namespace App\Controller;

use App\Service\DiscordIntegrator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionController extends AbstractController {

	private DiscordIntegrator $discord;

	public function __construct(DiscordIntegrator $discord) {
		$this->discord = $discord;
	}
	/**
	 * Converts an Exception to a Response.
	 *
	 * @param FlattenException $exception A FlattenException instance
	 * @param DebugLoggerInterface $logger A DebugLoggerInterface instance
	 *
	 * @return Response
	 */
	#[Route ('/error/')]
	public function exceptionAction(FlattenException $exception, DebugLoggerInterface $logger, Request $request): Response {
		$code = $exception->getStatusCode();
		$error = $exception->getMessage();
		$trace = $exception->getTraceAsString();
		$data = [
			'status_code' => $code,
			'exception' => $error,
		];
		$type = $request->headers->get('accept');
		try {
			$text = "Status Code: $code \nError: $error\nTrace:\n$trace";
			$this->discord->pushToErrors($text);
		} catch (Exception $e) {
			// Do nothing.
		}
		switch ($type) {
			case 'application/json':
				return new JsonResponse($data, 500, ['content-type'=>'application/json']);
			default:
				return $this->render('Exception/exception.html.twig', $data);
		}
	}
}
