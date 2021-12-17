<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Aggregate;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\ApiFormatter;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\AbstractProcessor;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Search\Context;
use SymfonyBundle\UIBundle\Query\Core\Dto\Filters;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\FetcherFactory;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\Filter;

class Processor extends AbstractProcessor
{
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

    /**
     * @param Context $actionContext
     * @throws \JsonException
     */
    public function process(AbstractContext $actionContext): void
    {
//        if (!$actionContext->getLocale() instanceof Locale) {
//            $actionContext->setLocale($this->defaultLocale);
//        }

        #todo: flow
        # - получили id аггрегата, получили список полей, которые хотим затянуть жадно в точечной нотации
        # - получили фильтра, чтобы отсеять не нужное
        # - сделали один запрос к БД
        # - намаппили результат на entity, прокинули в outputContract
        # - сделали сериалайз, выплюнули в контроллер
//        $aggregateId = $actionContext->getAggregateId();
//
//        $fetcher = $this->fetcherFactory->forEntity($actionContext->getTargetEntityClass());
//        $filters = $this->extractFilters($actionContext);
//
//        $fetcher->addFilters($filters);
//
//        $aggregateData = $fetcher->getById(
//            $aggregateId,
//            true
//        );
//
//        $aggregate = $aggregateData; #todo: тут намаппить на сущность данные
//
//        $output = $this->createOutput($actionContext, $aggregate);
//
//        if (!empty($actionContext->getTranslations()) && $actionContext->hasLocale()) {
//            /** @var Locale $locale */
//            $locale = $actionContext->getLocale();
//            $output = $this->translate(
//                $output,
//                $actionContext->getOutputFormat(),
//                $actionContext->getTranslations(),
//                $locale,
//                $this->translator,
//                $this->serializer,
//            );
//        }
//
//        $this->responseContent = $this->serializer->serialize(
//            ApiFormatter::prepare(['entity' => $output]),
//            $actionContext->getOutputFormat()
//        );
//        $this->responseHeaders = [
//            'Content-Type' => "application/" . $actionContext->getOutputFormat()
//        ];
    }

    private function extractFilters(AbstractContext $actionContext): Filters
    {
        /** @var Context $actionContext */
        $filters = $actionContext->getFilters();
        $this->applyFilterFieldAliases($filters, $actionContext->getFilterAliases());
        return $this->deleteFilterInBlackList($filters, $actionContext->getFilterBlackList());
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

    public function deleteFilterInBlackList(Filters $filters, array $blackList): Filters
    {
        $filterList = array_filter($filters->toArray(), static function (Filter $filter) use ($blackList) {
            return !in_array($filter->getProperty(), $blackList);
        });

        return new Filters($filterList);
    }
}
