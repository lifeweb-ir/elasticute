<?php

namespace ElastiCute\ElastiCute;

class ElasticuteQuerySingular
{
    /**
     * @var array
     */
    protected array $children = [];

    /**
     * @var array
     */
    protected array $query = [];

    /**
     * @var bool
     */
    public bool $isGroup = false;

    /**
     * @var string
     */
    protected string $groupType = 'must';

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param ElasticuteQuerySingular $child
     */
    public function addChild(ElasticuteQuerySingular $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getGroupType(): string
    {
        return $this->groupType;
    }

    /**
     * @param string $groupType
     */
    public function setGroupType(string $groupType): void
    {
        $this->groupType = $groupType;
    }
}
