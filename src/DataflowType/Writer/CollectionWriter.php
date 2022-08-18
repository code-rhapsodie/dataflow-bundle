<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\Exceptions\UnsupportedItemTypeException;

/**
 * Delegates the writing of each item in a collection to an embedded writer.
 */
class CollectionWriter implements DelegateWriterInterface
{
    /**
     * CollectionWriter constructor.
     */
    public function __construct(private WriterInterface $writer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->writer->prepare();
    }

    /**
     * {@inheritdoc}
     */
    public function write($collection)
    {
        if (!is_iterable($collection)) {
            throw new UnsupportedItemTypeException(sprintf('Item to write was expected to be an iterable, received %s.', get_debug_type($collection)));
        }

        foreach ($collection as $item) {
            $this->writer->write($item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        $this->writer->finish();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($item): bool
    {
        return is_iterable($item);
    }
}
