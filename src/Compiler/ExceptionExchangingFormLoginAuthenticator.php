<?php

namespace App\Compiler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/** @noinspection PhpDocFinalChecksInspection
 * Deliberately overridden at compile time to fix error reporting.
 */
class ExceptionExchangingFormLoginAuthenticator extends FormLoginAuthenticator {

	public function authenticate(Request $request): Passport
	{
		try {
			return parent::authenticate($request);
		} catch (BadRequestHttpException $badRequestHttpException) {
			throw new BadCredentialsException('Bad credentials.', 0, $badRequestHttpException);
		}
	}

}