<?php

namespace Bike\Partner\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractExtension extends \Twig_Extension
{
    use ContainerAwareTrait;
}
