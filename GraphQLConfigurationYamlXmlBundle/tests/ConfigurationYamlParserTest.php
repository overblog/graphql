<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ConfigurationYamlXmlBundle\Tests;

use Overblog\GraphQL\Bundle\ConfigurationYamlXmlBundle\ConfigurationYamlParser;

class ConfigurationYamlParserTest extends ConfigurationParserTest
{
    const PARSER_CLASS = ConfigurationYamlParser::class;
    protected array $excludeDirectories = ['broken', 'constant'];

    public function testBrokenYaml(): void
    {
        $dirname = __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'broken'.DIRECTORY_SEPARATOR.'yaml';
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('#The file "(.*)'.preg_quote(DIRECTORY_SEPARATOR).'broken.types.yml" does not contain valid YAML\.#');
        $parser = new ConfigurationYamlParser([$dirname]);
        $parser->getConfiguration();
    }
}
