<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\Service\Filter;

use Doctrine\ORM\QueryBuilder;
use SymfonyBundle\UIBundle\Foundation\Core\Components\Exception\DomainException;
use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Dto\Sorts;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;

class Fetcher
{
    private const AGGREGATE_ALIAS = 'entity';

    private EntityManagerInterface $entityManager;
    private FetcherContext $context;
    private ClassMetadata $entityClassMetadata;

    /**
     * FetcherInstance constructor.
     * @param EntityManagerInterface $entityManager
     * @param class-string $entityClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $entityClass
    ) {
        $this->entityManager = $entityManager;

        $entityClassMetadata = $this->entityManager->getClassMetadata($entityClass);
        $entityRepository = $this->entityManager->getRepository($entityClass);
        $queryBuilder = $entityRepository->createQueryBuilder(self::AGGREGATE_ALIAS);
        $filterSqlBuilder = new FilterSqlBuilder($queryBuilder);
        $this->entityClassMetadata = $entityClassMetadata;

        $this->context = new FetcherContext(
            $this->entityManager,
            $queryBuilder,
            $entityClass,
            $entityClassMetadata,
            $filterSqlBuilder
        );
    }

    public function addSorts(Sorts $sorts): void
    {
        $this->context->filterSqlBuilder->addSorts(
            $this->context->filterAllowSorts($sorts)
        );
    }

    public function addFilters(Filters $filters): void
    {
        $aggregateAlias = self::AGGREGATE_ALIAS;

        AutowareFilters::autoware(
            $this->context->fetchFiltersForEntity($filters),
            $this->context->filterSqlBuilder,
            $aggregateAlias
        );

        $filtersForRelations = $this->context->fetchFiltersForRelations($filters);
        foreach ($this->context->fetchJoinList($filtersForRelations) as $propertyPath) {
            $explodePropertyPath = explode('.', $propertyPath);
            for ($level = 1, $levelMax = count($explodePropertyPath); $level <= $levelMax; $level++) {
                $relationPath = Helper::makeRelationPath($explodePropertyPath, $level);
                $path = Helper::makeAliasPathFromPropertyPath("$aggregateAlias.$relationPath");
                $alias = Helper::pathToAlias($path);

                $this->context->queryBuilder->leftJoin($path, $alias);
            }
        }

        if (!empty($filtersForRelations->toArray())) {
            $this->context->queryBuilder->distinct(true);
        }

        AutowareFilters::autoware(
            $filtersForRelations,
            $this->context->filterSqlBuilder,
            $aggregateAlias
        );
    }

    public function paginate(Pagination $pagination): void
    {
        $this->context->filterSqlBuilder->setPagination($pagination);
    }

    public function getSearchQuery(): Query
    {
        return $this->context->queryBuilder->getQuery();
    }

    public function count(): int
    {
        $idPropertyName = current($this->context->entityClassMetadata->identifier);
        $aggregateAlias = self::AGGREGATE_ALIAS;
        return (clone $this->context->queryBuilder)
            ->select("count(distinct({$aggregateAlias}.{$idPropertyName}))")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getContext(): ?FetcherContext
    {
        return $this->context;
    }

    public function getById(string $id, bool $eager = true, array $relations = []): object
    {
        $aggregateAlias = self::AGGREGATE_ALIAS;
        $idPropertyName = $this->entityClassMetadata->identifier[0];

        $qb = $this->entityManager->getRepository($this->context->entityClass)
            ->createQueryBuilder($aggregateAlias)
            ->where("$aggregateAlias.{$idPropertyName} = :id")
            ->setParameter('id', $id);

        if ($eager) {
            $this->addEagerQueryToRelations($relations, $qb);
        }
        $result = $qb->getQuery()->getResult();
        if (empty($result)) {
            throw new DomainException("Entity with {$idPropertyName} '{$id}' not exist");
        }
        return current($result);
    }

    public function getByIds(array $ids, Sorts $sorts, bool $eager = true, array $relations = []): array
    {
        $aggregateAlias = self::AGGREGATE_ALIAS;
        $idPropertyName = $this->entityClassMetadata->identifier[0];

        $idsPrepared = array_map(static function (string $id) {
            return "'$id'";
        }, $ids);
        if (empty($idsPrepared)) {
            return [];
        }

        $qb = $this->entityManager->getRepository($this->context->entityClass)
            ->createQueryBuilder($aggregateAlias)
            ->where("$aggregateAlias.{$idPropertyName} IN (" . implode(',', $idsPrepared) . ')');

        foreach ($sorts->toArray() as $sort) {
            $qb->addOrderBy("{$aggregateAlias}.{$sort->getField()}", $sort->getDirection());
        }

        if ($eager) {
            $this->addEagerQueryToRelations($relations, $qb);
        }

        return $qb->getQuery()->getResult();
    }

    protected function addEagerQueryToRelations(array $relations, QueryBuilder $qb): void
    {
        $aggregateAlias = self::AGGREGATE_ALIAS;
        $uniqueAssocRelations = array_unique(
            array_map(static function (string $property) {
                $explodeProperty = explode('.', $property);
                array_pop($explodeProperty);
                return implode('.', $explodeProperty);
            }, $this->context->getEntityAssociationWhiteList())
        );

        if (empty($relations)) {
            $assocRelations = $uniqueAssocRelations;
        } else {
            $assocRelations = array_intersect($relations, $uniqueAssocRelations);
        }

        $joins = [];
        foreach ($assocRelations as $propertyPath) {
            $explodePropertyPath = explode('.', $propertyPath);
            for ($level = 1, $levelMax = count($explodePropertyPath); $level <= $levelMax; $level++) {
                $relationPath = Helper::makeRelationPath($explodePropertyPath, $level);
                $path = Helper::makeAliasPathFromPropertyPath("$aggregateAlias.$relationPath");
                $alias = Helper::pathToAlias($path);

                if (in_array($alias, $joins, true)) {
                    continue;
                }
                $qb->leftJoin($path, $alias)->addSelect($alias);
                $joins[] = $alias;
            }
        }
    }

    /**
     * @return array<string>
     * @throws Exception
     */
    public function searchEntityIds(): array
    {
        // ???????? ?????????? ?????????????? ???? ?????????? ID ?? ???? ?????????????????????? EntityManager, ?????? ?????????? ?????????????????? ?????? ???????????????????? ??????-????????????,
        // ????????????????, ?????????? ?????????????????? Elastic ?? ???????????????? ???? ???????? ?????????? ?????????? ?????????? ????????????
        $query = $this->getSearchQuery();
        $idColumnName = current($this->entityClassMetadata->identifier);
        return array_map(static function (array $result) use ($idColumnName) {
            return $result["{$idColumnName}_0"];
        }, $this->entityManager->getConnection()
            ->executeQuery(
                $query->getSQL(),
                array_map(
                    static function (Parameter $parameter) {
                        return $parameter->getValue();
                    },
                    $query->getParameters()->toArray()
                )
            )
            ->fetchAllAssociative());
    }
}
