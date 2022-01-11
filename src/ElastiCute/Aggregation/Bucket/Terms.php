<?php

namespace ElastiCute\ElastiCute\Aggregation\Bucket;

use ElastiCute\ElastiCute\Aggregation\Aggregate;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Terms extends Aggregate
{
    /**
     * @var string
     */
    protected string $aggregationName = 'terms';

    /**
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * @param int|null $size
     * @return Terms
     */
    public function size(?int $size): Terms
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return array
     */
    protected function generateQuery(): array
    {
        $aggregationBody = [];

        if (isset($this->field))
            $aggregationBody['terms']['field'] = $this->field;

        if (isset($this->size))
            $aggregationBody['terms']['size'] = $this->size;

        return $aggregationBody;
    }
}
