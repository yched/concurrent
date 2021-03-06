<?php
namespace Icicle\Concurrent\Worker;

use Icicle\Concurrent\ContextInterface;
use Icicle\Concurrent\Exception\StatusError;
use Icicle\Concurrent\Worker\Internal\TaskFailure;

abstract class Worker implements WorkerInterface
{
    /**
     * @var \Icicle\Concurrent\ContextInterface
     */
    private $context;

    /**
     * @var bool
     */
    private $idle = true;

    /**
     * @var bool
     */
    private $shutdown = false;

    /**
     * @param \Icicle\Concurrent\ContextInterface $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return $this->context->isRunning();
    }

    /**
     * {@inheritdoc}
     */
    public function isIdle()
    {
        return $this->idle;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $this->context->start();
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(TaskInterface $task)
    {
        if (!$this->context->isRunning()) {
            throw new StatusError('The worker has not been started.');
        }

        if ($this->shutdown) {
            throw new StatusError('The worker has been shutdown.');
        }

        $this->idle = false;

        yield $this->context->send($task);

        $result = (yield $this->context->receive());

        $this->idle = true;

        if ($result instanceof TaskFailure) {
            throw $result->getException();
        }

        yield $result;
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        if (!$this->context->isRunning() || $this->shutdown) {
            throw new StatusError('The worker is not running.');
        }

        $this->shutdown = true;

        yield $this->context->send(0);

        yield $this->context->join();
    }

    /**
     * {@inheritdoc}
     */
    public function kill()
    {
        $this->context->kill();
    }
}
