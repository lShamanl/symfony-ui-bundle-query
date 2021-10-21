<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query;

use SymfonyBundle\UIBundle\Foundation\Core\Contract\LocalizationOutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\OutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Query\Core\Components\Interfaces\QueryContextInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractProcessor extends \SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractProcessor
{
    protected EventDispatcherInterface $dispatcher;
    protected SerializerInterface $serializer;
    protected EntityManagerInterface $entityManager;
    protected TranslatorInterface $translator;
    protected Locale $defaultLocale;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        Locale $defaultLocale
    ) {
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->defaultLocale = $defaultLocale;
    }

    protected function createOutput(QueryContextInterface $actionContext, object $entity): OutputContractInterface
    {
        $outputDtoClass = $actionContext->getOutputDtoClass();
        $outputDtoIsLocalization = is_subclass_of($outputDtoClass, LocalizationOutputContractInterface::class);
        if ($outputDtoIsLocalization && $actionContext->hasLocale()) {
            return new $outputDtoClass($entity, $actionContext->getLocale()?->getPriorityLang());
        }

        return new $outputDtoClass($entity);
    }
}
