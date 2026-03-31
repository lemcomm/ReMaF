<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use App\Compiler\ExceptionExchangingFormLoginAuthenticator;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    private const string DEFINITION = 'security.authenticator.form_login.main';

    public function process(ContainerBuilder $container): void {
	    if (false === $container->hasDefinition(self::DEFINITION)) {
		    return;
	    }

	    # All of this code is here to catch _username being submitted as null on the login form.
	    # This code runs it through ExceptionEchangingFormLoginAuthenticator to swap that error from
	    # BadRequestHttpException to BadCredentialsException.
	    $formLoginFirewall = $container->getDefinition(self::DEFINITION);
	    $formLogin = $container->getDefinition($formLoginFirewall->getParent());

	    $args = array_merge(
		    [$formLogin->getArgument(0)],
		    array_values($formLoginFirewall->getArguments()),
		    [new Reference(RequestStack::class)]
	    );

	    $container->register(ExceptionExchangingFormLoginAuthenticator::class, ExceptionExchangingFormLoginAuthenticator::class)->setArguments($args);

	    $container->setAlias(self::DEFINITION, ExceptionExchangingFormLoginAuthenticator::class);
    }
}
