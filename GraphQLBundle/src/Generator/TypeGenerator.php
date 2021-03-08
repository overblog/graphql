<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Generator;

use Composer\Autoload\ClassLoader;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\RootTypeConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\ConfigurationProvider\Processor;
use Overblog\GraphQLBundle\Event\SchemaCompiledEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function file_exists;
use function file_put_contents;
use function str_replace;
use function var_export;

/**
 * @final
 */
class TypeGenerator
{
    public const MODE_DRY_RUN = 1;
    public const MODE_MAPPING_ONLY = 2;
    public const MODE_WRITE = 4;
    public const MODE_OVERRIDE = 8;

    public const GRAPHQL_SERVICES = 'services';

    private static bool $classMapLoaded = false;
    private ?string $cacheDir;
    protected int $cacheDirMask;
    private Configuration $configuration;
    private bool $useClassMap;
    private ?string $baseCacheDir;
    private string $classNamespace;
    private TypeBuilder $typeBuilder;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        string $classNamespace,
        ?string $cacheDir,
        Configuration $configuration,
        TypeBuilder $typeBuilder,
        EventDispatcherInterface $eventDispatcher,
        bool $useClassMap = true,
        ?string $baseCacheDir = null,
        ?int $cacheDirMask = null
    ) {
        $this->cacheDir = $cacheDir;
        $this->configuration = $configuration;
        $this->useClassMap = $useClassMap;
        $this->baseCacheDir = $baseCacheDir;
        $this->typeBuilder = $typeBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->classNamespace = $classNamespace;

        if (null === $cacheDirMask) {
            // Apply permission 0777 for default cache dir otherwise apply 0775.
            $cacheDirMask = null === $cacheDir ? 0777 : 0775;
        }

        $this->cacheDirMask = $cacheDirMask;
    }

    public function getBaseCacheDir(): ?string
    {
        return $this->baseCacheDir;
    }

    public function setBaseCacheDir(string $baseCacheDir): void
    {
        $this->baseCacheDir = $baseCacheDir;
    }

    public function getCacheDir(bool $useDefault = true): ?string
    {
        if ($useDefault) {
            return $this->cacheDir ?: $this->baseCacheDir.'/overblog/graphql-bundle/__definitions__';
        } else {
            return $this->cacheDir;
        }
    }

    public function setCacheDir(?string $cacheDir): self
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }

    public function compile(int $mode): array
    {
        $cacheDir = $this->getCacheDir();
        $writeMode = $mode & self::MODE_WRITE;

        // Configure write mode
        if ($writeMode && file_exists($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        // Process configs
        // $configs = Processor::process($this->configs);

        // Generate classes
        $classes = [];
        foreach ($this->configuration->getTypes() as $typeConfiguration) {
            /*
            $config['config']['name'] ??= $name;
            $config['config']['class_name'] = $config['class_name'];
            */
            /*
            $config = [
                'name' => $type->getName(),
                'type' => $type->getGraphQLType(),
                'config' => $type->toArray() + ['class_name' => $type->getName().'Type.php'],
            ];
            */
            $classMap = $this->generateClass($typeConfiguration, $cacheDir, $mode);
            $classes = array_merge($classes, $classMap);
        }

        /*
        foreach ($configs as $name => $config) {
            $config['config']['name'] ??= $name;
            $config['config']['class_name'] = $config['class_name'];
            $classMap = $this->generateClass($config, $cacheDir, $mode);
            $classes = array_merge($classes, $classMap);
        }
        */

        // Create class map file
        if ($writeMode && $this->useClassMap && count($classes) > 0) {
            $content = "<?php\nreturn ".var_export($classes, true).';';

            // replaced hard-coded absolute paths by __DIR__
            // (see https://github.com/overblog/GraphQLBundle/issues/167)
            $content = str_replace(" => '$cacheDir", " => __DIR__ . '", $content);

            file_put_contents($this->getClassesMap(), $content);

            $this->loadClasses(true);
        }

        $this->eventDispatcher->dispatch(new SchemaCompiledEvent());

        return $classes;
    }

    public function generateClass(RootTypeConfiguration $typeConfiguration, ?string $outputDirectory, int $mode = self::MODE_WRITE): array
    {
        $className = $typeConfiguration->getClassName();
        $path = "$outputDirectory/$className.php";
        
        if (!($mode & self::MODE_MAPPING_ONLY)) {
            $phpFile = $this->typeBuilder->build($typeConfiguration);

            if ($mode & self::MODE_WRITE) {
                if (($mode & self::MODE_OVERRIDE) || !file_exists($path)) {
                    $phpFile->save($path);
                }
            }
        }

        return ["$this->classNamespace\\$className" => $path];
    }

    public function loadClasses(bool $forceReload = false): void
    {
        if ($this->useClassMap && (!self::$classMapLoaded || $forceReload)) {
            $classMapFile = $this->getClassesMap();
            $classes = file_exists($classMapFile) ? require $classMapFile : [];

            /** @var ClassLoader $mapClassLoader */
            static $mapClassLoader = null;

            if (null === $mapClassLoader) {
                $mapClassLoader = new ClassLoader();
                $mapClassLoader->setClassMapAuthoritative(true);
            } else {
                $mapClassLoader->unregister();
            }

            $mapClassLoader->addClassMap($classes);
            $mapClassLoader->register();

            self::$classMapLoaded = true;
        }
    }

    private function getClassesMap(): string
    {
        return $this->getCacheDir().'/__classes.map';
    }
}
