<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\GetOne;

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
        if (!$actionContext->getLocale() instanceof Locale) {
            $actionContext->setLocale($this->defaultLocale);
        }

        $aggregateId = $actionContext->getAggregateId();

        $fetcher = $this->fetcherFactory->forEntity($actionContext->getTargetEntityClass());
        $filters = $actionContext->getFilters();

        $fetcher->addFilters($filters);

        $aggregate = $fetcher->getById(
            $aggregateId,
            $actionContext->getEagerMode(),
            $actionContext->getRelations()
        );

        $output = $this->createOutput($actionContext, $aggregate);

        if (!empty($actionContext->getTranslations()) && $actionContext->hasLocale()) {
            /** @var Locale $locale */
            $locale = $actionContext->getLocale();
            $output = $this->translate(
                $output,
                $actionContext->getOutputFormat(),
                $actionContext->getTranslations(),
                $locale,
                $this->translator,
                $this->serializer,
            );
        }

        $this->responseContent = $this->serializer->serialize(
            ApiFormatter::prepare(['entity' => $output]),
            $actionContext->getOutputFormat()
        );
        $this->responseHeaders = [
            'Content-Type' => "application/" . $actionContext->getOutputFormat()
        ];
    }
}
