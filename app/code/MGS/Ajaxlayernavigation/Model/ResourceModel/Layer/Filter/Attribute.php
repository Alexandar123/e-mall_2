<?php
namespace MGS\Ajaxlayernavigation\Model\ResourceModel\Layer\Filter;

class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
    }

    public function getCount($filter, $stateFilters)
    {
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $connection = $this->getConnection();
        $attribute = $filter->getAttributeModel();

        // add state filter to select
        $attributeFilters = false;
        foreach ($stateFilters as $stateFilter) {
            if ($stateFilter['code'] === $attribute->getAttributeCode()) {
                $attributeFilters = $stateFilter['values'];
                continue;
            }
            if ('price' == $stateFilter['code']) {
                $priceFilter = $stateFilter['values'];
                $priceFromTo = explode('-', $priceFilter[0]);
                $select->where("price_index.min_price > ? ", $priceFromTo[0]);
                $select->where("price_index.max_price < ? ", $priceFromTo[1]);
            } else {
                $stateTable = sprintf('%s_idx', $stateFilter['code']);
                $stateConditions = [
                    "{$stateTable}.entity_id = e.entity_id",
                    $connection->quoteInto("{$stateTable}.attribute_id = ?", $stateFilter['id']),
                    $connection->quoteInto("{$stateTable}.store_id = ?", $filter->getStoreId()),
                    $connection->quoteInto("{$stateTable}.value in (?)", $stateFilter['values']),
                ];

                $select->join(
                    [$stateTable => $this->getMainTable()],
                    join(' AND ', $stateConditions),
                    []
                );
            }
        }
        // end add state filter to select

        $tableAlias = sprintf('%s_idx1', $attribute->getAttributeCode());

        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        ];

        $select->join(
            [$tableAlias => $this->getMainTable()],
            join(' AND ', $conditions),
            ['value', 'products' => new \Zend_Db_Expr("GROUP_CONCAT({$tableAlias}.entity_id SEPARATOR ',')")]
        );

        $searchEntityIds = $filter->getSearchIds();
        if ($searchEntityIds) {
            $select->where('e.entity_id in (?)', $searchEntityIds);
        }

        $select->group("{$tableAlias}.value");
        // delete search result tmp data from select
        $selectString = strtolower($select->__toString());
        if ($attributeFilters) {
            $from = $select->getPart('FROM');
            if (!empty($from['search_result'])) {
                $joinData = $from['search_result'];
                $remove = strtolower(sprintf(
                    "%s `%s` AS `%s` ON %s",
                    $joinData['joinType'],
                    $joinData['tableName'],
                    'search_result',
                    $joinData['joinCondition']
                ));
                $selectString = str_replace($remove, '', $selectString);
            }
        }

        $result = $connection->fetchPairs($selectString);

        return $result;
    }
}
