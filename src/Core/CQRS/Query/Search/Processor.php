<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Search;

use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\ApiFormatter;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\AbstractProcessor;
use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\FetcherFactory;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Filter;
use Closure;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Pagination;

class Processor extends AbstractProcessor
{
    private FetcherFactory $fetcherFactory;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        Locale $defaultLocale,
        FetcherFactory $fetcherFactory
    ) {
        parent::__construct($dispatcher, $serializer, $entityManager, $translator, $defaultLocale);
        $this->fetcherFactory = $fetcherFactory;
    }

    public function deleteFilterInBlackList(Filters $filters, array $blackList): Filters
    {
        $filterList = array_filter($filters->toArray(), static function (Filter $filter) use ($blackList) {
            return !in_array($filter->getProperty(), $blackList, true);
        });

        return new Filters($filterList);
    }

    public function applyFilterFieldAliases(Filters $filters, array $aliases): void
    {
        array_map(static function (Filter $filter) use ($aliases) {
            if (array_key_exists($filter->getProperty(), $aliases)) {
                $filter->setPropertyName(
                    $aliases[$filter->getProperty()]
                );
            }
        }, $filters->toArray());
    }

    /**
     * @param Context $actionContext
     * @throws Exception
     * @throws \JsonException
     */
    public function process(AbstractContext $actionContext): void
    {
        if (!$actionContext->getLocale() instanceof Locale) {
            $actionContext->setLocale($this->defaultLocale);
        }

        $fetcher = $this->fetcherFactory->forEntity($actionContext->getTargetEntityClass());
        $filters = $this->extractFilters($actionContext);
        $sorts = $actionContext->getSorts();
        $pagination = $actionContext->getPagination();

        $fetcher->addFilters($filters);
        $count = $fetcher->count();

        $fetcher->addSorts($sorts);
        $fetcher->paginate($pagination);

        $searchResult = $fetcher->getByIds(
            $fetcher->searchEntityIds(),
            $sorts,
            $actionContext->getEagerMode()
        );

        $this->applyEntityCallback(
            $actionContext->getEntityCallback(),
            $searchResult
        );

        $entities = [];
        foreach ($searchResult as $entity) {
            $entities[] = $this->createOutput($actionContext, $entity);
        }

        if (!empty($actionContext->getTranslations()) && $actionContext->getLocale() !== null) {
            $entities = array_map(function (object $entity) use ($actionContext) {
                return $this->translate(
                    $entity,
                    $actionContext->getOutputFormat(),
                    $actionContext->getTranslations(),
                    $actionContext->getLocale(),
                    $this->translator,
                    $this->serializer,
                );
            }, $entities);
        }

        $outputDataPrepareCallback = $actionContext->getOutputDataPrepareCallback() === null
            ? $this->outputDataPrepareCallback()
            : $actionContext->getOutputDataPrepareCallback()
        ;

        $this->responseContent = $this->serializer->serialize(
            $outputDataPrepareCallback($entities, $count, $pagination),
            $actionContext->getOutputFormat()
        );
        $this->responseHeaders = [
            'Content-Type' => "application/" . $actionContext->getOutputFormat()
        ];
    }

    private function extractFilters(AbstractContext $actionContext): Filters
    {
        /** @var Context $actionContext */
        $filters = $actionContext->getFilters();
        $this->applyFilterFieldAliases($filters, $actionContext->getFilterAliases());
        return $this->deleteFilterInBlackList($filters, $actionContext->getFilterBlackList());
    }

    /**
     * @param Closure|null $callback
     * @param array<int, object> $entities
     */
    private function applyEntityCallback(?Closure $callback, array $entities): void
    {
        if ($callback !== null) {
            foreach ($entities as $entity) {
                $callback($entity);
            }
        }
    }

    protected function outputDataPrepareCallback(): callable
    {
        return static function (array $entities, int $count, Pagination $pagination) {
            return ApiFormatter::prepare([
                'data' => $entities,
                'pagination' => [
                    'count' => $count,
                    'totalPages' => (int) ceil($count / $pagination->getPageSize()),
                    'page' => $pagination->getPageNumber(),
                    'size' => count($entities),
                ]
            ]);
        };
    }
}
