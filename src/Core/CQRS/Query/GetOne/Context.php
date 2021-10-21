<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query\Core\CQRS\Query\GetOne;

use SymfonyBundle\UIBundle\Foundation\Core\Components\AbstractContext;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\OutputContractInterface;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\Locale;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\TranslationDto;
use SymfonyBundle\UIBundle\Query\Core\Components\Interfaces\QueryContextInterface;

class Context extends AbstractContext implements QueryContextInterface
{
    protected string $entityId;
    /** @var class-string */
    protected string $targetEntityClass;
    /** @var class-string<OutputContractInterface> */
    protected string $outputDtoClass;
    protected array $translations;
    protected ?Locale $locale;
    protected string $outputFormat;

    /**
     * Context constructor.
     * @param string $outputFormat
     * @param string $entityId
     * @param class-string $targetEntityClass
     * @param class-string<OutputContractInterface> $outputDtoClass
     * @param array $translations
     * @param Locale|null $locale
     */
    public function __construct(
        string $outputFormat,
        string $entityId,
        string $targetEntityClass,
        string $outputDtoClass,
        array $translations = [],
        ?Locale $locale = null,
    ) {
        $this->entityId = $entityId;
        $this->targetEntityClass = $targetEntityClass;
        $this->outputDtoClass = $outputDtoClass;
        $this->outputFormat = $outputFormat;
        $this->translations = $translations;
        $this->locale = $locale;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function setOutputFormat(string $outputFormat): self
    {
        $this->outputFormat = $outputFormat;
        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(TranslationDto $translations): self
    {
        $this->translations = $translations->getRules();
        return $this;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return class-string
     */
    public function getTargetEntityClass(): string
    {
        return $this->targetEntityClass;
    }

    /**
     * @param class-string $targetEntityClass
     * @return $this
     */
    public function setTargetEntityClass(string $targetEntityClass): self
    {
        $this->targetEntityClass = $targetEntityClass;
        return $this;
    }

    public function getOutputDtoClass(): string
    {
        return $this->outputDtoClass;
    }

    public function setOutputDtoClass(string $outputDtoClass): self
    {
        $this->outputDtoClass = $outputDtoClass;
        return $this;
    }

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function hasLocale(): bool
    {
        return $this->locale !== null;
    }
}
