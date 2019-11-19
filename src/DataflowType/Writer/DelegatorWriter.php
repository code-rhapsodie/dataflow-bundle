<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\Exceptions\UnsupportedItemTypeException;

/**
 * Writer that delegated the actual writing to other writers.
 */
class DelegatorWriter implements DelegateWriterInterface
{
    /** @var DelegateWriterInterface[] */
    private $delegates;

    /**
     * DelegatorWriter constructor.
     */
    public function __construct()
    {
        $this->delegates = [];
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        foreach ($this->delegates as $delegate) {
            $delegate->prepare();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($item)
    {
        foreach ($this->delegates as $delegate) {
            if (!$delegate->supports($item)) {
                continue;
            }

            $delegate->write($item);

            return;
        }

        throw new UnsupportedItemTypeException(sprintf(
            'None of the registered delegate writers support the received item of type %s',
            is_object($item) ? get_class($item) : gettype($item)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        foreach ($this->delegates as $delegate) {
            $delegate->finish();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($item): bool
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->supports($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registers a collection of delegates.
     *
     * @param iterable|DelegateWriterInterface[] $delegates
     */
    public function addDelegates(iterable $delegates): void
    {
        foreach ($delegates as $delegate) {
            $this->addDelegate($delegate);
        }
    }

    /**
     * Registers one delegate.
     *
     * @param DelegateWriterInterface $delegate
     */
    public function addDelegate(DelegateWriterInterface $delegate): void
    {
        $this->delegates[] = $delegate;
    }
}
