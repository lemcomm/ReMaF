<?php

namespace App\Controller;

use App\Entity\Character;
use App\Service\AppState;
use App\Service\DiscordIntegrator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ExceptionController extends AbstractController {
	public function __construct(
		private DiscordIntegrator $discord,
		private AppState $app,
		private EntityManagerInterface $em) {
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
		$userId = $this->getUser()?->getId()?:'(none)';
		$user = $this->getUser()?:'(none)';
		$agent = $request->headers->get('User-Agent');
		$bits = explode("::", $error);
		if ($code !== 404) {
			$forward = true;
			$forward2 = false;
			$errBits = explode("::", $error);
			if (str_contains($error, 'RFC 2822')) {
				# Filter out junk bot email addresses.
				$forward = false;
			} elseif (count($errBits) > 0) {
				if ($errBits[0] === 'messages') {
					# These are errors the players get by accessing pages they shouldn't be able to, and are thus, intentional game design, not errors in the sense of this file.
					$forward = false;
				}
			}
			if ($forward) {
				try {
					$text = "Status Code: $code \nError: $error\nRequestUri:$uri\nReferer:$ref\nUser: ".$userId."\nAgent: $agent\nTrace:\n$trace";
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
