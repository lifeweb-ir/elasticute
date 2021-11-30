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
}
