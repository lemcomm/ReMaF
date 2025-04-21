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
		$uri = $request->getRequestUri();
		$type = $request->headers->get('accept');
		$ref = $request->server->get('HTTP_REFERER');
		$user = $this->getUser()?->getId()?:'(none)';
		$agent = $request->headers->get('User-Agent');
		$bits = explode("::", $error);
		if ($code !== 404) {
			$forward = true;
			if (str_contains($error, 'RFC 2822')) {
				# Filter out junk bot email addresses.
				$forward = false;
			} elseif (str_starts_with($error, 'unavailable.intro')) {
					$forward = false;
			} elseif (str_starts_with($error, 'error.noaccess')) {
				$forward = false;
			}
			if ($forward) {
				try {
					$text = "Status Code: $code \nError: $error\nRequestUri:$uri\nReferer:$ref\nUser: $user\nAgent: $agent\nTrace:\n$trace";
					$this->discord->pushToErrors($text);
				} catch (Exception $e) {
					// Do nothing.
				}
			}
		}

		if ($type==='application/json') {
			new JsonResponse($data, 500, ['content-type' => 'application/json']);
		}

		if ($bits[0] === 'messages') {
			unset($bits[0]);
		}
		$data['bits'] = $bits;

		return $this->render('Exception/exception.html.twig', $data);
	}
}
