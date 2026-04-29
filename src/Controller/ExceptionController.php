<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DiscordIntegrator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionController extends AbstractController {
	public function __construct(
		private DiscordIntegrator $discord,
		private TranslatorInterface $trans
	) {
	}

	/**
	 * Converts an Exception to a Response.
	 *
	 * @param FlattenException|null $exception A FlattenException instance
	 * @param Request               $request
	 *
	 * @return Response
	 */
	#[Route ('/error/')]
	public function exceptionAction(Request $request, FlattenException|null $exception = null): Response {
		if (!$exception) {
			$this->addFlash('notice', $this->trans->trans('error.noerror'));
			return $this->redirectToRoute('maf_index');
		}
		$code = $exception->getStatusCode();
		$error = $exception->getMessage();
		$trace = $exception->getTraceAsString();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$data = [
			'status_code' => $code,
			'exception' => $error,
		];
		/** @var User $user */
		$user = $this->getUser();
		$uri = $request->getRequestUri();
		$type = $request->headers->get('accept');
		$ref = $request->server->get('HTTP_REFERER');
		$userId = $user?->getId()?:'(none)';
		$char = $user?->getCurrentCharacter()?:'(unknown)';
		$agent = $request->headers->get('User-Agent');
		$bits = explode("::", $error);
		if ($code !== 404) {
			$forward = true;
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
					$text = "Status Code: $code \nError: `$error`\nIn file: `$file($line)`\nRequestUri:$uri\nReferer:$ref\nUser $userId playing $char\nAgent: $agent\nTrace:\n```$trace```";
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
