<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Resolver;

use Exception;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use GraphQL\Executor\Promise\Adapter\ReactPromiseAdapter;
use Overblog\GraphQLBundle\Resolver\MiddlewaresResolver;
use PHPUnit\Framework\TestCase;
use React\Promise\FulfilledPromise;
use stdClass;
use Symfony\Component\Process\PhpProcess;
use function sprintf;

class MiddlewaresResolverTest extends TestCase
{
    private ReactPromiseAdapter $adapter;

    public function setUp(): void
    {
        $this->adapter = new ReactPromiseAdapter();
    }

    protected function getResolver($resolver, array $middlewares = [])
    {
        return new MiddlewaresResolver($this->adapter, $resolver, $middlewares);
    }
  

    protected function deferedResult($output, $seconds = 1)
    {
        $process = new PhpProcess(
            <<<EOF
<?php
sleep($seconds);
echo '$output';
EOF
        );

        return $this->adapter->create(function (callable $resolve) use (&$process) {
            $process->start();
            $process->wait(function () use ($resolve, $process) {
                $resolve($process->getOutput());
            });
        });
    }

    public function testMiddlewaresResolver(): void
    {
        $resolver = function ($a, $b, $c) {
            dump("start resolver with ", $b);
            return $this->deferedResult("return_from_resolver");
        };
        $middlewares = [
            function ($a, $b, $c, $next) {
                dump("middleware 1 ".$b);
                return $this->adapter->create(function (callable $resolve) use ($next) {
                    return $resolve($next(function ($res) {
                        dump("middleware 1 - ret with ".$res);
                        return $res."_addonfrom1";
                    }));
                });
            },
            function ($a, $b, $c, $next) {
                dump("middleware 2");
                return $next(function ($res) {
                    dump("middleware 2 - ret with ".$res);
                    
                    return $this->deferedResult("aaa", 1)->then(function ($r) use ($res) {
                        return $res.$r;
                    });
                });
            }
        ];
        $a = 1;
        $b = "coucou";
        $c = true;
        $middlewaresResolver = $this->getResolver($resolver, $middlewares);

        // Pourquoi attend-il la résolution ?
        $res = $middlewaresResolver->execute($a, $b, $c);

        if ($this->adapter->isThenable($res)) {
            $wait = true;
            $res->adoptedPromise->then(function ($result) use (&$wait, &$res) {
                $wait = false;
                $res = $result;
            });
            while ($wait) {
                usleep(5);
            }
        } else {
            dump("non thenable");
        }

        $this->assertSame("toto", $res);
    }
}
