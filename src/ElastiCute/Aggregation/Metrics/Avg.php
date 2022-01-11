<?php

namespace ElastiCute\ElastiCute\Aggregation\Metrics;

use ElastiCute\ElastiCute\Aggregation\Aggregate;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Avg extends Aggregate
{
    /**
     * @var string
     */
    protected string $aggregationName = 'avg';

    /**
     * @var string
     */
    protected string $scriptName;

    /**
     * @var string
     */
    protected string $scriptParams;

    /**
     * @var string
     */
    protected string $missing;

    /**
     * @param string $scriptName
     * @return self
     */
    public function scriptName(string $scriptName): self
    {
        $this->scriptName = $scriptName;
        return $this;
    }

    /**
     * @param string $scriptParams
     * @return self
     */
    public function scriptParams(string $scriptParams): self
    {
        $this->scriptParams = $scriptParams;
        return $this;
    }

    /**
     * @param string $missing
     * @return self
     */
    public function missing(string $missing): self
    {
        $this->missing = $missing;
        return $this;
    }

    /**
     * @return array
     */
	protected function generateQuery(): array
	{
		$aggregationBody = [];

		if ( isset( $this->field ) )
            $aggregationBody['avg']['field'] = $this->field;

		if ( isset( $this->scriptName ) && isset( $this->scriptParams ) )
            $aggregationBody['avg']['script'] = [
				'id' => $this->scriptName,
				'params' => $this->scriptParams,
			];

		if ( isset( $this->scriptName ) && ! isset( $this->scriptParams ) )
            $aggregationBody['avg']['script'] = [
				'source' => $this->scriptName,
			];

		if ( isset( $this->missing ) )
            $aggregationBody['avg']['missing'] = $this->missing;

		return $aggregationBody;
	}
}
