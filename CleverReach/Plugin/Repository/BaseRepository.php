<?php
/**
 * @package    Packlink_PacklinkPro
 * @author     Packlink Shipping S.L.
 * @copyright  2019 Packlink
 */

namespace CleverReach\Plugin\Repository;

use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\Plugin\ResourceModel\CleverReachEntity;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class BaseRepository
 *
 * @package Packlink\PacklinkPro\Repository
 */
class BaseRepository implements RepositoryInterface
{
    /**
     * Fully qualified name of this class.
     */
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * Number of indexes in CleverReach entity table.
     */
    const NUMBER_OF_INDEXES = 7;
    /**
     * @var string
     */
    protected $entityClass;
    /**
     * @var CleverReachEntity
     */
    protected $resourceEntity;
    /**
     * Name of the base entity table in database.
     */
    const TABLE_NAME = 'cleverreach_entity';

    /**
     * Returns full class name.
     *
     * @return string Full class name.
     */
    public static function getClassName(): string
    {
        return static::THIS_CLASS_NAME;
    }

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->resourceEntity = ObjectManager::getInstance()->create($this->getResourceEntity());
        $this->resourceEntity->setTableName(static::TABLE_NAME);
    }

    /**
     * Sets repository entity.
     *
     * @param string $entityClass Repository entity class.
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Selects all CleverReach entities in the system.
     *
     * @return array All entities as arrays.
     *
     * @throws LocalizedException
     */
    public function selectAll(): array
    {
        return $this->resourceEntity->selectAll();
    }

    /**
     * Executes select query.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return Entity[] A list of found entities ot empty array.
     *
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    public function select(QueryFilter $filter = null): array
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass;

        $records = $this->resourceEntity->selectEntities($filter, $entity);

        return $this->deserializeEntities($records);
    }

    /**
     * Executes select query and returns first result.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return Entity|null First found entity or NULL.
     *
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    public function selectOne(QueryFilter $filter = null): ?Entity
    {
        if ($filter === null) {
            $filter = new QueryFilter();
        }

        $filter->setLimit(1);
        $results = $this->select($filter);

        return empty($results) ? null : $results[0];
    }

    /**
     * Executes saveEntity query and returns ID of created entity. CleverReachEntity will be updated with new ID.
     *
     * @param Entity $entity CleverReachEntity to be saved.
     *
     * @return int Identifier of saved entity.
     *
     * @throws \Exception
     */
    public function save(Entity $entity): int
    {
        $id = $this->resourceEntity->saveEntity($entity);
        $entity->setId($id);
        $this->resourceEntity->updateEntity($entity);

        return $id;
    }

    /**
     * Executes updateEntity query and returns success flag.
     *
     * @param Entity $entity CleverReachEntity to be updated.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     *
     * @throws LocalizedException
     */
    public function update(Entity $entity): bool
    {
        return $this->resourceEntity->updateEntity($entity);
    }

    /**
     * Executes delete query and returns success flag.
     *
     * @param Entity $entity CleverReachEntity to be deleted.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     *
     * @throws LocalizedException
     */
    public function delete(Entity $entity): bool
    {
        return $this->resourceEntity->deleteEntity((int)$entity->getId());
    }

    /**
     * Counts records that match filter criteria.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return int Number of records that match filter criteria.
     *
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    public function count(QueryFilter $filter = null): int
    {
        return count($this->select($filter));
    }

    /**
     * Returns resource entity.
     *
     * @return string Resource entity class name.
     */
    protected function getResourceEntity(): string
    {
        return CleverReachEntity::class;
    }

    /**
     * Translates database records to CleverReach entities.
     *
     * @param array $records Array of database records.
     *
     * @return Entity[]
     */
    protected function deserializeEntities(array $records): array
    {
        $entities = [];
        foreach ($records as $record) {
            /** @var Entity $entity */
            $entity = $this->deserializeEntity($record['data']);
            if ($entity !== null) {
                $entity->setId((int)$record['id']);
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Deserialize entity from given string.
     *
     * @param string $data Serialized entity as string.
     *
     * @return Entity Created entity object.
     */
    private function deserializeEntity(string $data): ?Entity
    {
        $jsonEntity = json_decode($data, true);

        if (empty($jsonEntity)) {
            return null;
        }

        if (array_key_exists('class_name', $jsonEntity)) {
            $entity = new $jsonEntity['class_name'];
        } else {
            $entity = new $this->entityClass;
        }

        /** @var Entity $entity */
        $entity->inflate($jsonEntity);

        return $entity;
    }
}
