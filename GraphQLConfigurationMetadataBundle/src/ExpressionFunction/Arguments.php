<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ConfigurationMetadataBundle\ExpressionFunction;

use Overblog\GraphQLBundle\ExpressionLanguage\ExpressionFunction;
use Overblog\GraphQLBundle\Generator\TypeGenerator;

final class Arguments extends ExpressionFunction
{
    public function __construct()
    {
        parent::__construct(
            'arguments',
            fn ($mapping, $data) => "$this->gqlServices->get('container')->get('overblog_graphql.arguments_transformer')->getArguments($mapping, $data, \$info)",
            static fn (array $arguments, $mapping, $data) => $arguments[TypeGenerator::GRAPHQL_SERVICES]->get('container')->get('overblog_graphql.arguments_transformer')->getArguments($mapping, $data, $arguments['info'])
        );
    }
}
