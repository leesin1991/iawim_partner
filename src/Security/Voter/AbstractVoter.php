<?php

namespace Bike\Partner\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractVoter extends Voter
{
    use ContainerAwareTrait;
}
