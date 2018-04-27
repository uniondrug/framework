<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-19
 */
namespace Uniondrug\Framework\Services;

use Phalcon\Mvc\Model\Row;
use Uniondrug\Framework\Injectable;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset\Complex;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Paginator\Adapter\QueryBuilder;

/**
 * @package App\Services
 */
abstract class Service extends Injectable
{
    use ServiceTrait;

    /**
     * 获取结果集中的第1条记录
     * @param Builder $builder
     * @return Row
     */
    protected function withQueryFirst(Builder $builder)
    {
        return $this->withQueryList($builder)->getFirst();
    }

    /**
     * 列表查询
     * <code>
     * $builder = $this->modelsManager->createBuilder();
     * $builder->from(['m' => Merchant::class]);
     * $builder->innerJoin(MerchantContact::class, "m.merchantId = c.merchantId", "c");
     * return $this->withQueryList($builder);
     * </code>
     * @param Builder $builder
     * @return Simple|Complex
     */
    protected function withQueryList(Builder $builder)
    {
        /**
         * @var Query $query
         */
        $query = $builder->getQuery();
        return $query->execute();
    }

    /**
     * 分页查询
     * <code>
     * $builder = $this->modelsManager->createBuilder();
     * $builder->from(['m' => Merchant::class]);
     * $builder->innerJoin(MerchantContact::class, "m.merchantId = c.merchantId", "c");
     * return $this->withQueryPaging($builder, $struct->page, $struct->limit);
     * </code>
     * @param Builder $builder
     * @param int     $page  当前第n页
     * @param int     $limit 每页i条
     * @return \stdClass
     */
    protected function withQueryPaging(Builder $builder, int $page = 1, int $limit = 10)
    {
        $page = max(1, $page);
        $param = [
            'builder' => $builder,
            'limit' => $limit,
            'page' => $page,
        ];
        $query = new QueryBuilder($param);
        return $query->getPaginate();
    }
}
