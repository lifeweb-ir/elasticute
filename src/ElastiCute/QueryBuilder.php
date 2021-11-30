<?php

namespace ElastiCute\ElastiCute;

use Elasticsearch\Client;
use ElastiCute\ElastiCute\Aggregation\AggregationQuery;
use ElastiCute\ElastiCute\Response\ElastiCuteResponse;
use ElastiCute\ElastiCute\Response\MappableResponse;

/**
 * Class QueryBuilder
 *
 * @package ElastiCute\ElastiCute
 * @author  Payam Jafari/payamjafari.ir
 * @see     http://payamweber.github.io/elasticute
 */
class QueryBuilder
{
    use ElastiCuteFilters;

    /**
     * Database credentials
     */
    protected string $indexName;

    /**
     * @var bool $connected is database connected successfully
     */
    protected bool $connected = false;

    /**
     * @var array $query
     */
    protected array $query = [];

    /**
     * @var array $sort
     */
    protected array $sort = [];

    /**
     * @var array $aggregations
     */
    protected array $aggregations = [];

    /**
     * @var array $select
     */
    protected array $select = [];

    /**
     * @var bool $isGroupWhere
     */
    protected bool $isGroupWhere = false;

    /**
     * @var int
     */
    protected int $size = 10;

    /**
     * @var array
     */
    protected array $body = [];

    /**
     * @var int
     */
    protected int $paginationNumber = 1;

    /**
     * @var array $currentDepthInfo
     */
    protected static array $currentDepthInfo = [
        0 => [
            'type' => 'must',
            'conditions' => [],
        ],
    ];

    /**
     * @var Client $elastic
     */
    protected Client $elastic;

    /**
     * this is allowed operators for queries
     */
    protected const ALLOWED_OPERATORS = ['term', 'match', 'match_phrase', 'match_all'];

    /**
     * Model constructor.
     */
    public function __construct()
    {
        try {
            $this->connected = true;
        } catch (\Exception $e) {
            $this->connected = false;
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed|static
     */
    public static function __callStatic($name, $arguments)
    {
        $self = new static();

        if (!$name) {
            return $self;
        }

        return call_user_func_array([$self, $name], $arguments);
    }

    /**
     * Start the query builder
     *
     * @return static
     */
    public static function query(): self
    {
        return self::__callStatic('', []);
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function size(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @return int
     */
    public function getPaginationNumber(): int
    {
        return $this->paginationNumber;
    }

    /**
     * @param int $paginationNumber
     */
    public function paginationNumber(int $paginationNumber): void
    {
        $this->paginationNumber = $paginationNumber;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @param array $sort
     */
    public function setSort(array $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @param array $aggregations
     */
    public function setAggregations(array $aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @param int $paginationNumber
     */
    public function setPaginationNumber(int $paginationNumber): void
    {
        $this->paginationNumber = $paginationNumber;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param        $key
     * @param string $value
     * @param string $operator
     *
     * @return $this
     */
    protected function doWhere($key, string $value = '', string $operator = 'match'): self
    {
        $operator = in_array($operator, self::ALLOWED_OPERATORS) ? $operator : 'match';

        if ($key) {
            $this->addWhereCondition($key, $value, $operator);
        }

        return $this;
    }

    /**
     * @param        $key
     * @param        $value
     * @param string $operator
     * @param array $extra
     */
    protected function addWhereCondition($key, $value, string $operator = 'match', array $extra = [])
    {
        $currentInfoCount = count($this::$currentDepthInfo);

        // set group where conditions
        $conditionQuery = [
            $operator => [
                $key => $value,
            ],
        ];

        if ($this->isGroupWhere) {
            $this::$currentDepthInfo[$currentInfoCount - 1]['conditions'][] = $conditionQuery;
        } else {
            $this->query[] = $conditionQuery;
        }
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function sort(array $fields): self
    {
        $this->sort = $fields;

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function select(array $fields): self
    {
        $this->select = $fields;

        return $this;
    }

    /**
     * @param callable $filters
     * @param string $operator
     *
     * @return $this
     */
    protected function booleanGroup(callable $filters, string $operator = 'must'): self
    {
        $currentInfoCount = count($this::$currentDepthInfo);
        $isAlreadyInGroup = $this->isGroupWhere;

        $this->isGroupWhere = true;
        $this::$currentDepthInfo[$currentInfoCount] = [
            'type' => $operator,
            'conditions' => [],
        ];
        $filters($this);
        if ($isAlreadyInGroup) {
            $this::$currentDepthInfo[$currentInfoCount - 1]['conditions'][] = [
                'bool' => [
                    $this::$currentDepthInfo[$currentInfoCount]['type'] =>
                        $this::$currentDepthInfo[$currentInfoCount]['conditions'],
                ],
            ];
        } else {
            $this->isGroupWhere = false;

            $this->query[]['bool'] = [
                $this::$currentDepthInfo[$currentInfoCount]['type'] =>
                    $this::$currentDepthInfo[$currentInfoCount]['conditions'],
            ];
        }

        return $this;
    }

    /**
     * @param      $id
     * @param bool $sourceOnly
     *
     * @return ElastiCuteResponse
     * @throws ElastiCuteException
     */
    public function find($id, bool $sourceOnly = true): ElastiCuteResponse
    {
        $this->initializeDatabaseAndCollection();

        $runner = new ElastiCuteRunner();

        return $runner->find([
            'index' => $this->indexName,
            'id' => $id,
            '_source' => $this->select,
        ], $sourceOnly);
    }

    /**
     * @param int $count
     *
     * @return MappableResponse
     * @throws ElastiCuteException
     */
    public function get(int $count = 10): MappableResponse
    {
        return $this->doGet($count);
    }

    /**
     * @param int $documentPerPage
     * @param int $pageNumber
     *
     * @return MappableResponse
     * @throws ElastiCuteException
     */
    public function paginate(int $documentPerPage = 10, int $pageNumber = 1): MappableResponse
    {
        $this->setPaginationNumber($pageNumber);
        $this->setSize($documentPerPage);

        return $this->doGet();
    }

    /**
     * @return array
     */
    public function getAndCreateBody(): array
    {
        $this->body = [];

        if ($this->getQuery()) {
            $this->body['query'] = ['bool' => ['must' => $this->getQuery()]];
        } else {
            $this->body['query'] = [
                'match_all' => (object)[],
            ];
        }

        if ($this->getSort()) {
            $this->body['sort'] = $this->getSort();
        }

        if ($this->getSize()) {
            $this->body['size'] = $this->getSize();
        }

        if ($this->getSelect()) {
            $this->body['_source'] = $this->getSelect();
        }

        if ($this->getPaginationNumber()) {
            $this->body['from'] = $this->getPaginationNumber() * $this->getSize();
        }

        if ($this->getAggregations()) {
            $this->body['aggs'] = $this->getAggregations();
        }

        return $this->body;
    }

    /**
     * @param int $count
     * @return MappableResponse
     * @throws ElastiCuteException
     */
    protected function doGet(int $count = 10): MappableResponse
    {
        $this->initializeDatabaseAndCollection();
        $this->setSize($count);

        $runner = new ElastiCuteRunner();

        return $runner->search([
            'index' => $this->indexName,
            'body' => $this->getAndCreateBody()
        ]);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function index(string $name): self
    {
        $self = $this ?? new static();

        $self->indexName = $name;
        return $self;
    }

    /**
     * @throws ElastiCuteException
     */
    protected function initializeDatabaseAndCollection()
    {
        if (!$this->connected) {
            throw new ElastiCuteException('Could not connect to database');
        }

        if (!$this->indexName) {
            throw new ElastiCuteException('Index name has not been set');
        }
    }

    /**
     * @return ElastiCuteResponse
     * @throws ElastiCuteException
     */
    public function mapping(): ElastiCuteResponse
    {
        $this->initializeDatabaseAndCollection();

        $runner = new ElastiCuteRunner();

        return $runner->mapping([
            'index' => $this->indexName,
        ]);
    }

    /**
     * @param callable $aggregations
     *
     * @return $this
     */
    public function aggregate(callable $aggregations): self
    {
        $aggregationQuery = new AggregationQuery($this);
        $aggregations($aggregationQuery);
        $this->aggregations = $aggregationQuery->getAggregationList();
        return $this;
    }

    /**
     * Die and dump
     * Developer Only
     *
     * @param $var
     */
    public static function dieAndDump($var)
    {
        if (is_array($var)) {
            header('Content-Type: application/json');
            echo json_encode($var);
        } else {
            echo "<pre>";
            var_dump($var);
        }

        die();
    }
}
