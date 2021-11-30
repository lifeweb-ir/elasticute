<?php

namespace ElastiCute\ElastiCute;

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use ElastiCute\ElastiCute\Response\ElastiCuteResponse;
use ElastiCute\ElastiCute\Response\MappableResponse;

/**
 * Class ElastiCuteRunner
 *
 * @package ElastiCute\ElastiCute
 */
class ElastiCuteRunner
{
    protected Client $official_builder;

    /**
     * ElastiCuteRunner constructor.
     */
    public function __construct()
    {
        $ref = new \ReflectionClass(ClassLoader::class);
        $envReader = Dotenv::createImmutable(dirname($ref->getFileName()) . '/../../');
        $envReader->safeLoad();

        $this->official_builder = ClientBuilder::create()
            ->setHosts(
                [
                    [
                        'host' => self::getEnv('ELASTICUTE_ADDRESS', '127.0.0.1'),
                        'port' => self::getEnv('ELASTICUTE_PORT', '9200'),
                        'user' => self::getEnv('ELASTICUTE_USERNAME', ''),
                        'pass' => self::getEnv('ELASTICUTE_PASSWORD', ''),
                    ]
                ]
            )
            ->build();
    }

    /**
     * @param array $params
     *
     * @return MappableResponse
     * @throws ElastiCuteException
     */
    public function search(array $params): MappableResponse
    {
        try {
            $search = $this->official_builder->search($params);

            return new MappableResponse($search, $search['hits']['hits'] ?? []);
        } catch (BadRequest400Exception $exception) {
            $this->manageException($exception);
        }
    }

    /**
     * @param array $params
     * @param bool $source_only
     *
     * @return ElastiCuteResponse
     * @throws ElastiCuteException
     */
    public function find(array $params, bool $source_only): ElastiCuteResponse
    {
        $method = $source_only ? 'getSource' : 'get';

        try {
            $search = $this->official_builder->$method($params);

            return new ElastiCuteResponse($search);
        } catch (BadRequest400Exception $exception) {
            $this->manageException($exception);
        }
    }

    /**
     * @param array $params
     *
     * @return ElastiCuteResponse
     * @throws ElastiCuteException
     */
    public function mapping(array $params): ElastiCuteResponse
    {
        try {
            $search = $this->official_builder->indices()->getMapping($params);

            return new ElastiCuteResponse($search);
        } catch (BadRequest400Exception $exception) {
            $this->manageException($exception);
        }
    }

    /**
     * @param \Exception $exception
     *
     * @throws ElastiCuteException
     */
    protected function manageException(\Exception $exception)
    {
        $error_message = json_decode($exception->getMessage(), true);

        throw new ElastiCuteException($error_message['error']['root_cause'][0]['reason'] ?? $exception->getMessage(), 400);
    }

    /**
     * @param       $name
     * @param mixed $default
     *
     * @return array|false|string|null
     */
    protected static function getEnv($name, $default = null)
    {
        return $_ENV[$name] ?? $default;
    }
}
