<?php
namespace Icicle\Tests\Concurrent\Threading;

use Icicle\Concurrent\Sync\Internal\ExitSuccess;
use Icicle\Concurrent\Threading\Thread;
use Icicle\Coroutine;
use Icicle\Loop;
use Icicle\Tests\Concurrent\AbstractContextTest;

/**
 * @group threading
 * @requires extension pthreads
 */
class ThreadTest extends AbstractContextTest
{
    public function createContext(callable $function)
    {
        return new Thread($function);
    }

    public function testSpawnStartsThread()
    {
        Coroutine\create(function () {
            $thread = Thread::spawn(function () {
                usleep(100);
            });

            yield $thread->join();
        })->done();

        Loop\run();
    }
}
