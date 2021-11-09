<?php
/**
 * @package    Packlink_PacklinkPro
 * @author     Packlink Shipping S.L.
 * @copyright  2019 Packlink
 */

namespace CleverReach\Plugin\ResourceModel;

use Exception;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use Magento\Framework\Exception\LocalizedException;
use function is_array;

/**
 * Class QueueItemEntity
 *
 * @package Packlink\PacklinkPro\ResourceModel
 */
class QueueItemEntity extends CleverReachEntity
{
    /**
     * Finds list of earliest queued queue items per queue. Following list of criteria for searching must be satisfied:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be returned
     *
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned
     *
     * @param Entity $entity CleverReach entity.
     *
     * @return QueueItem[] Found queue item list
     *
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    public function findOldestQueuedItems(Entity $entity, int $limit = 10): array
    {
        $runningQueueNames = $this->getRunningQueueNames($entity);

        return $this->getQueuedItems($runningQueueNames, $limit);
    }

    /**
     * Returns names of queues containing items that are currently in progress.
     *
     * @param Entity $entity CleverReach entity.
     *
     * @return array Names of queues containing items that are currently in progress.
     *
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    private function getRunningQueueNames(Entity $entity): array
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::EQUALS, QueueItem::IN_PROGRESS);

        /** @var QueueItem[] $runningQueueItems */
        $runningQueueItems = $this->selectEntities($filter, $entity);
        $fieldIndexMap = IndexHelper::mapFieldsToIndexes($entity);
        $queueNameIndex = 'index_' . $fieldIndexMap['queueName'];

        return array_map(
            function ($runningQueueItem) use ($queueNameIndex) {
                return $runningQueueItem[$queueNameIndex];
            },
            $runningQueueItems
        );
    }

    /**
     * Returns all queued items.
     *
     * @param array $runningQueueNames Array of queues containing items that are currently in progress.
     * @param int $limit Maximum number of records that can be retrieved.
     *
     * @return array Array of queued items.
     *
     * @throws LocalizedException
     */
    private function getQueuedItems(array $runningQueueNames, int $limit): array
    {
        $queueNameIndex = $this->getIndexMapping('queueName', QueueItem::getClassName());

        $condition = $this->buildWhereString(
            [
                'type' => 'QueueItem',
                $this->getIndexMapping('status', QueueItem::getClassName()) => QueueItem::QUEUED,
            ]
        );

        if (!empty($runningQueueNames)) {
            $quotedNames = array_map(
                function ($name) {
                    return $this->getConnection()->quote($name);
                },
                $runningQueueNames
            );

            $condition .= sprintf(' AND ' . $queueNameIndex . ' NOT IN (%s)', implode(', ', $quotedNames));
        }

        $query = 'SELECT queueTable.id, queueTable.data '
            . 'FROM ( '
            . 'SELECT ' . $queueNameIndex . ', MIN(id) AS id '
            . 'FROM ' . $this->getMainTable() . ' '
            . 'WHERE ' . $condition . ' '
            . 'GROUP BY ' . $queueNameIndex . ' '
            . 'LIMIT ' . $limit
            . ' ) AS queueView '
            . 'INNER JOIN ' . $this->getMainTable() . ' AS queueTable '
            . 'ON queueView.id = queueTable.id';

        $records = $this->getConnection()->fetchAll($query);

        return is_array($records) ? $records : [];
    }

    /**
     * Builds where condition string based on given key/value parameters.
     *
     * @param array $whereFields Key value pairs of where condition
     *
     * @return string Properly sanitized where condition string
     */
    private function buildWhereString(array $whereFields = []): string
    {
        $where = [];
        foreach ($whereFields as $field => $value) {
            $where[] = $field . Operators::EQUALS . $this->getConnection()->quote($value);
        }

        return implode(' AND ', $where);
    }

    /**
     * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise
     * update will be performed.
     *
     * @param QueueItem $queueItem Item to save
     * @param array $additionalWhere List of key/value pairs that must be satisfied upon saving queue item. Key is
     *  queue item property and value is condition value for that property. Example for MySql storage:
     *  $storage->save($queueItem, array('status' => 'queued')) should produce query
     *  UPDATE queue_storage_table SET .... WHERE .... AND status => 'queued'
     *
     * @return int Id of saved queue item
     *
     * @throws QueueItemSaveException if queue item could not be saved
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int
    {
        $savedItemId = null;

        try {
            $itemId = $queueItem->getId();
            if ($itemId === null || $itemId <= 0) {
                $savedItemId = $this->saveEntity($queueItem);
            } else {
                $this->updateQueueItem($queueItem, $additionalWhere);
            }
        } catch (Exception $e) {
            throw new QueueItemSaveException('Failed to save queue item.', 0, $e);
        }

        return $savedItemId ?: $itemId;
    }

    /**
     * Updates queue item.
     *
     * @param QueueItem $queueItem Queue item entity.
     * @param array $additionalWhere Array of additional where conditions.
     *
     * @throws QueueItemSaveException
     * @throws LocalizedException
     * @throws QueryFilterInvalidParamException
     */
    private function updateQueueItem(QueueItem $queueItem, array $additionalWhere)
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());

        foreach ($additionalWhere as $name => $value) {
            if ($value === null) {
                $filter->where($name, Operators::NULL);
            } else {
                $filter->where($name, Operators::EQUALS, $value);
            }
        }

        $filter->setLimit(1);
        $results = $this->selectEntities($filter, new QueueItem());
        if (empty($results)) {
            throw new QueueItemSaveException("Can not update queue item with id {$queueItem->getId()}.");
        }

        $this->updateEntity($queueItem);
    }
}
