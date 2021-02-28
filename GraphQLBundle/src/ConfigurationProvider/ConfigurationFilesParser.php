<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\ConfigurationProvider;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class ConfigurationFilesParser implements ConfigurationProviderInterface
{
    protected array $directories = [];

    public function __construct(array $directories = [])
    {
        $this->directories = $directories;
    }

    /**
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Search for files in given directories matching given extensions
     *
     * @throws DirectoryNotFoundException
     */
    protected function getFiles(): iterable
    {
        $directories = $this->getDirectories();
        $extensions = $this->getSupportedExtensions();
        $directories = array_filter($directories, fn (string $directory) => is_readable($directory));
        if (empty($directories)) {
            return [];
        }

        $finder = Finder::create();
        $finder->ignoreUnreadableDirs();
        $finder->files()->in($directories)->name(sprintf('*.{%s}', join(',', $extensions)));

        return $finder->getIterator();
    }

    /** Get the extensions of files to search for */
    abstract protected function getSupportedExtensions(): array;

    /** Parse a given file and return a configuration */
    abstract protected function parseFile(SplFileInfo $file);
}
