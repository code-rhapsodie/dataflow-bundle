<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\DataflowType\Writer;

use CodeRhapsodie\DataflowBundle\Exceptions\UnsupportedItemTypeException;

/**
 * Delegates the writing of each item in a collection to an embedded writer.
 */
class CollectionWriter implements DelegateWriterInterface
{
    /** @var WriterInterface */
    private $writer;

    /**
     * CollectionWriter constructor.
     *
     * @param WriterInterface $writer
     */
    public function __construct(WriterInterface $writer)
    {
        $this->writer = $writer;
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
            throw new UnsupportedItemTypeException(sprintf(
                'Item to write was expected to be an iterable, received %s.',
                is_object($collection) ? get_class($collection) : gettype($collection)
            ));
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
