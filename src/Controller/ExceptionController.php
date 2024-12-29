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
use Symfony\Component\Routing\Attribute\Route;

class ExceptionController extends AbstractController {
	public function __construct(
		private DiscordIntegrator $discord) {
	}

	/**
	 * Converts an Exception to a Response.
	 *
	 * @param FlattenException          $exception A FlattenException instance
	 * @param DebugLoggerInterface|null $logger    A DebugLoggerInterface instance
	 * @param Request                   $request
	 *
	 * @return Response
	 */
	#[Route ('/error/')]
	public function exceptionAction(FlattenException $exception, ?DebugLoggerInterface $logger, Request $request): Response {
		$code = $exception->getStatusCode();
		$error = $exception->getMessage();
		$trace = $exception->getTraceAsString();
		$data = [
			'status_code' => $code,
			'exception' => $error,
		];
		$type = $request->headers->get('accept');
		if ($code !== 404) {
			# Suppress 404 errors, so we don't get a message every time some random bot tries to hit /admin.php
			# Or some other similar non-existent URL because this isn't WordPress.
			try {
				$text = "Status Code: $code \nError: $error\nTrace:\n$trace";
				$this->discord->pushToErrors($text);
			} catch (Exception $e) {
				// Do nothing.
			}
		}

		return match ($type) {
			'application/json' => new JsonResponse($data, 500, ['content-type' => 'application/json']),
			default => $this->render('Exception/exception.html.twig', $data),
		};
	}
}
