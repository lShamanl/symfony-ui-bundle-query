<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\GetOne;

use SymfonyBundle\UIBundle\Foundation\Core\Contract\ApiFormatter;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\AbstractProcessor;
use Doctrine\ORM\EntityNotFoundException;

class Processor extends AbstractProcessor
{
    /**
     * @param Context $actionContext
     * @throws EntityNotFoundException
     * @throws \JsonException
     */
    public function process(AbstractContext $actionContext): void
    {
        if (!$actionContext->getLocale() instanceof Locale) {
            $actionContext->setLocale($this->defaultLocale);
        }

        #todo: тут можно сделать жадную загрузку как в Search по ID
        $entity = $this->getEntityById(
            $actionContext->getEntityId(),
            $actionContext->getTargetEntityClass()
        );

        $output = $this->createOutput($actionContext, $entity);

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
            ['Content-Type' => "application/" . $actionContext->getOutputFormat()]
        ];
    }

    /**
     * @param string $id
     * @param class-string $entityClass
     * @return object
     * @throws EntityNotFoundException
     */
    protected function getEntityById(string $id, string $entityClass): object
    {
        /** @var string $idPropertyName */
        $idPropertyName = current($this->entityManager->getClassMetadata($entityClass)->identifier);
        if (!$entity = $this->entityManager->getRepository($entityClass)->findOneBy([$idPropertyName => $id])) {
            $classnameExplode = explode('\\', $entityClass);
            $classname = end($classnameExplode);
            throw new EntityNotFoundException("{$classname} with id {$id} not exist");
        }

        return $entity;
    }
}
