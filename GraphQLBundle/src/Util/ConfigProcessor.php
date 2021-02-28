<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Util;

use Symfony\Component\Config\Definition\NodeInterface;

final class ConfigProcessor
{
    /**
     * Processes and validate a mixed configuration
     *
     * @param mixed $configuration
     *
     * @return mixed The processed configuration
     */
    public function process(NodeInterface $configTree, $configuration)
    {
        return $configTree->finalize($configTree->normalize($configuration));
    }
}
