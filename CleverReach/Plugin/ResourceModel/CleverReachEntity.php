<?php

namespace CleverReach\Plugin\ResourceModel;

use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryCondition;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CleverReachEntity extends AbstractDb
{
    /**
     * CleverReachEntity constructor.
     */
    protected function _construct()
    {
    }

    /**
     * Set resource model table name.
     *
     * @param string $tableName Name of the database table.
     */
    public function setTableName(string $tableName): void
    {
        $this->_init($tableName, 'id');
    }

    /**
     * Returns all rows from entity table in database.
     *
     * @return array
     *
     * @throws LocalizedException
     */
    public function selectAll(): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable());
        $result = $connection->fetchAll($select);

        return !empty($result) ? $result : [];
    }

    /**
     * Performs a select query over a specific type of entity with given CleverReach query filter.
     *
     * @param QueryFilter $filter
     * @param Entity $entity
     *
     * @return array Array of selected records.
     *
     * @throws LocalizedException|QueryFilterInvalidParamException
     */
    public function selectEntities(QueryFilter $filter, Entity $entity): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('type = ?', $entity->getConfig()->getType());

        if ($filter !== null) {
            $fieldIndexMap = IndexHelper::mapFieldsToIndexes($entity);

            if (!empty($filter->getConditions())) {
                $select->where($this->buildWhereCondition($filter, $fieldIndexMap));
            }

            if ($filter->getLimit()) {
                $select->limit($filter->getLimit(), $filter->getOffset());
            }

            $select = $this->buildOrderBy($select, $filter, $fieldIndexMap);
        }

        $result = $connection->fetchAll($select);

        return !empty($result) ? $result : [];
    }

    /**
     * Inserts a new record in CleverReach entity table.
     *
     * @param Entity $entity CleverReach entity.
     *
     * @return int ID of the inserted record.
     *
     * @throws LocalizedException
     */
    public function saveEntity(Entity $entity): int
    {
        $connection = $this->getConnection();

        $indexes = IndexHelper::transformFieldsToIndexes($entity);
        $data = $this->prepareDataForInsertOrUpdate($entity, $indexes);
        $data['type'] = $entity->getConfig()->getType();

        $connection->insert($this->getMainTable(), $data);

        return (int)$connection->fetchOne('SELECT last_insert_id()');
    }

    /**
     * Updates an existing record in Packlink entity table identified by ID.
     *
     * @param Entity $entity Packlink entity.
     *
     * @return bool Returns TRUE if updateEntity has been successful, otherwise returns FALSE.
     *
     * @throws LocalizedException
     */
    public function updateEntity(Entity $entity): bool
    {
        $connection = $this->getConnection();

        $indexes = IndexHelper::transformFieldsToIndexes($entity);
        $data = $this->prepareDataForInsertOrUpdate($entity, $indexes);
        $whereCondition = [$this->getIdFieldName() . '=?' => (int)$entity->getId()];

        $rows = $connection->update($this->getMainTable(), $data, $whereCondition);

        return $rows === 1;
    }

    /**
     * Deletes a record from CleverReach entity table.
     *
     * @param int $id ID of the record.
     *
     * @return bool Returns TRUE if updateEntity has been successful, otherwise returns FALSE.
     *
     * @throws LocalizedException
     */
    public function deleteEntity(int $id): bool
    {
        $connection = $this->getConnection();

        $rows = $connection->delete(
            $this->getMainTable(),
            [
                $connection->quoteInto('id = ?', $id),
            ]
        );

        return $rows === 1;
    }

    /**
     * Prepares data for inserting a new record or updating an existing one.
     *
     * @param Entity $entity CleverReach entity object.
     * @param array $indexes Array of index values.
     *
     * @return array Prepared record for inserting or updating.
     */
    protected function prepareDataForInsertOrUpdate(Entity $entity, array $indexes): array
    {
        $record = ['data' => $this->serializeEntity($entity)];

        foreach ($indexes as $index => $value) {
            $record['index_' . $index] = $value;
        }

        return $record;
    }

    /**
     * Returns index mapped to given property.
     *
     * @param string $property Property name.
     * @param string $entityType Entity type.
     *
     * @return string Index column in CleverReach entity table.
     */
    protected function getIndexMapping(string $property, string $entityType): ?string
    {
        $indexMapping = IndexHelper::mapFieldsToIndexes(new $entityType);

        if (array_key_exists($property, $indexMapping)) {
            return 'index_' . $indexMapping[$property];
        }

        return null;
    }

    /**
     * Builds WHERE condition part of SELECT query.
     *
     * @param QueryFilter $filter
     * @param array $fieldIndexMap
     *
     * @return string WHERE part of SELECT query.
     *
     * @throws QueryFilterInvalidParamException
     */
    private function buildWhereCondition(QueryFilter $filter, array $fieldIndexMap): string
    {
        $whereCondition = '';
        if ($filter->getConditions()) {
            foreach ($filter->getConditions() as $index => $condition) {
                if ($index !== 0) {
                    $whereCondition .= ' ' . $condition->getChainOperator() . ' ';
                }

                if ($condition->getColumn() === 'id') {
                    $whereCondition .= 'id = ' . $this->getConnection()->quote($condition->getValue());
                    continue;
                }

                if (!array_key_exists($condition->getColumn(), $fieldIndexMap)) {
                    throw new QueryFilterInvalidParamException(
                        sprintf('Field %s is not indexed!', $condition->getColumn())
                    );
                }

                $whereCondition .= $this->addCondition($condition, $fieldIndexMap);
            }
        }

        return $whereCondition;
    }

    /**
     * Filters records by given condition.
     *
     * @param QueryCondition $condition Query condition object.
     * @param array $indexMap Map of property indexes.
     *
     * @return string A single WHERE condition.
     */
    private function addCondition(QueryCondition $condition, array $indexMap): string
    {
        $column = $condition->getColumn();
        $columnName = $column === 'id' ? 'id' : 'index_' . $indexMap[$column];
        if ($column === 'id') {
            $conditionValue = (int)$condition->getValue();
        } else {
            $conditionValue = IndexHelper::castFieldValue($condition->getValue(), $condition->getValueType());
        }

        if (in_array($condition->getOperator(), [Operators::NOT_IN, Operators::IN], true)) {
            $values = array_map(function ($item) {
                if (is_string($item)) {
                    return "'$item'";
                }

                if (is_int($item)) {
                    $val = IndexHelper::castFieldValue($item, 'integer');
                    return "'{$val}'";
                }

                $val = IndexHelper::castFieldValue($item, 'double');

                return "'{$val}'";
            }, $condition->getValue());
            $conditionValue = '(' . implode(',', $values) . ')';
        } else {
            $conditionValue = "'$conditionValue'";
        }

        return $columnName . ' ' . $condition->getOperator()
            . (!in_array($condition->getOperator(), [Operators::NULL, Operators::NOT_NULL], true)
                ? $conditionValue : ''
            );
    }

    /**
     * Builds ORDER BY part of SELECT query.
     *
     * @param Select $select
     * @param QueryFilter $filter
     * @param array $fieldIndexMap
     *
     * @return Select Updated Magento SELECT query object.
     *
     * @throws QueryFilterInvalidParamException
     */
    private function buildOrderBy(Select $select, QueryFilter $filter, array $fieldIndexMap): Select
    {
        $orderByColumn = $filter->getOrderByColumn();
        if ($orderByColumn) {
            $indexedColumn = null;
            if ($orderByColumn === 'id') {
                $indexedColumn = 'id';
            } elseif (array_key_exists($orderByColumn, $fieldIndexMap)) {
                $indexedColumn = 'index_' . $fieldIndexMap[$orderByColumn];
            }

            if ($indexedColumn === null) {
                throw new QueryFilterInvalidParamException(
                    sprintf('Unknown or not indexed OrderBy column %s', $orderByColumn)
                );
            }

            $select->order($indexedColumn . ' ' . $filter->getOrderDirection());
        }

        return $select;
    }

    /**
     * Serializes CleverReachEntity to string.
     *
     * @param Entity $entity CleverReachEntity object to be serialized
     *
     * @return string Serialized entity
     */
    private function serializeEntity(Entity $entity)
    {
        return json_encode($entity->toArray());
    }
}
