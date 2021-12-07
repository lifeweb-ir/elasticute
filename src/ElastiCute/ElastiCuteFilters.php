<?php

namespace ElastiCute\ElastiCute;

/**
 * Trait ElastiCuteFilters
 *
 * @package ElastiCute\ElastiCute
 */
trait ElastiCuteFilters
{
    /**
     * @param callable $filters
     *
     * @return $this
     */
    public function groupShould(callable $filters): self
    {
        return $this->booleanGroup($filters, 'should');
    }

    /**
     * @param callable $filters
     *
     * @return $this
     */
    public function groupMust(callable $filters): self
    {
        return $this->booleanGroup($filters, 'must');
    }

    /**
     * @param callable $filters
     *
     * @return $this
     */
    public function groupMustNot(callable $filters): self
    {
        return $this->booleanGroup($filters, 'must_not');
    }

    /**
     * @param callable $filters
     *
     * @return $this
     */
    public function groupFilter(callable $filters): self
    {
        return $this->booleanGroup($filters, 'filter');
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $match_phrase
     *
     * @return $this
     */
    public function whereTextContains(string $name, string $value = '', bool $match_phrase = false): self
    {
        return $this->doWhere($name, $value, $match_phrase ? 'match_phrase' : 'match');
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $match_phrase
     *
     * @return $this
     */
    public function whereTextNotContains(string $name, string $value = '', bool $match_phrase = false): self
    {
        return $this->groupMustNot(function (QueryBuilder $builder) use ($name, $value, $match_phrase) {
            $builder->doWhere($name, $value, $match_phrase ? 'match_phrase' : 'match');
        });
    }

    /**
     * @param string $name
     * @param        $value
     * @param string $operator
     * @param array $extra
     * @return $this
     */
    public function where(string $name, $value, string $operator = 'match', array $extra = []): self
    {
        return $this->doWhere($name, $value, $operator, $extra);
    }

    /**
     * @param string $name
     * @param        $value
     * @param string $operator
     * @param array $extra
     * @return $this
     */
    public function whereNot(string $name, $value, string $operator = 'match', array $extra = []): self
    {
        return $this->groupMustNot(function (QueryBuilder $builder) use ($name, $value, $operator, $extra) {
            $builder->doWhere($name, $value, $operator, $extra);
        });
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return $this
     */
    public function whereEqual(string $name, $value): self
    {
        return $this->doWhere($name, $value, 'term');
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return $this
     */
    public function whereNotEqual(string $name, $value): self
    {
        return $this->groupMustNot(function (QueryBuilder $builder) use ($name, $value) {
            $builder->doWhere($name, $value, 'term');
        });
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function whereExists(string $name): self
    {
        return $this->doWhere('field', $name, 'exists');
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function whereNotExists(string $name): self
    {
        return $this->groupMustNot(function (QueryBuilder $builder) use ($name) {
            $builder->doWhere('field', $name, 'exists');
        });
    }

    /**
     * @param string $name
     * @param mixed $from
     * @param mixed $to
     * @param mixed $format
     * @param mixed $timezone
     * @return $this
     */
    public function whereRange(string $name, $from = null, $to = null, $format = null, $timezone = null): self
    {
        $query = [];

        if($from !== null){
            $query['gte'] = $from;
        }

        if($to !== null){
            $query['lte'] = $to;
        }

        if($format !== null){
            $query['format'] = $format;
        }

        if($timezone !== null){
            $query['time_zone'] = $timezone;
        }

        return $this->doWhere($name, $query, 'range');
    }

    /**
     * @param string $name
     * @param        $gte
     * @param null $format
     * @param null $timezone
     * @return $this
     */
    public function whereGreaterThanOrEqual(string $name, $gte, $format = null, $timezone = null): self
    {
        return $this->whereRange($name, $gte, null, $format, $timezone);
    }

    /**
     * @param string $name
     * @param        $lte
     * @param null $format
     * @param null $timezone
     * @return $this
     */
    public function whereLessThanOrEqual(string $name, $lte, $format = null, $timezone = null): self
    {
        return $this->whereRange($name, null, $lte, $format, $timezone);
    }

    /**
     * @param array $raw
     * @return $this
     */
    public function whereRaw(array $raw): self
    {
        return $this->addWhereRawCondition($raw);
    }
}
