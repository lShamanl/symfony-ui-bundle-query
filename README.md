# Symfony UI Bundle Query
## Описание:
Данный пакет является Симфони-бандлом.

Проблема, которую решает данный пакет:
Снимает с разработчика необходимость писать повторяющийся код в UI-точках входа в приложение(Controllers, CommandBus),
далее Controller.

## Пример внешнего использования:
### Пример строки запроса:
```http request
GET /clients?filter[emails.email][like]=26d@&sort=-createdAt,updatedAt&page[number]=1&page[size]=20&filter[userId][eq]=ccf92b7a-8e05-4f4b-9f0a-e4360dbacb23&filter[name.translations.last][eq]=Tesla&lang=ru
```

### Контракты:
#### Сортировка:
##### Описание:
Сортировка задается параметром "sort".
Направление сортировки задается опциональным знаком '-' перед названием свойства, по которому предполагается сортировка.
Если знак '-' присутствует, то сортировка по этому полю ведется с модификатором DESC, иначе - ASC.
Допускается сортировка по нескольким полям агрегата. Для этого необходимо написать несколько полей, разделив их символом
','. Чем раньше было указано поле, тем больший "вес" оно имеет при выборке.
##### Пример:
```
sort='-createdAt,updatedAt'
```

#### Пагинация:
#### Сортировка:
Пагинация задается параметром "page".
Параметр имеет два поля - number и size.
- "number" указывает на номер страницы, которую запрашивает клиент. По умолчанию: 1
- "size" указывает размер страницы(сколько агрегатов должно быть отображено). По умолчанию: 20
##### Описание:
```
page[number]='1'
page[size]='20'
```

#### Фильтрация:
##### Описание:
Операторы поиска:

Название | Допустимые значения | Пример | Описание
--- | --- | --- | ---
NOT_IN | 'not-in' | filter[status][not-in][]='blocked' | Свойство не содержит ни одно из указанных значений
IN | 'in' | filter[status][in][]='active' | Свойство содержит одно из указанных значений
RANGE | 'range' | filter[rating][range]='17,42' | Свойство находится в выбранном указанном диапазоне
IS_NULL | 'is-null' | filter[gender][is-null] | Свойство равно null
NOT_NULL | 'not-null' | filter[name][not-null] | Свойство не равно null
LESS_THAN | 'less-than', '<', 'lt' | filter[rating][<]='94' | Свойство меньше указанного значения
GREATER_THAN | 'greater-than', '>', 'gt' | filter[rating][>]='42' | Свойство больше указанного значения
LESS_OR_EQUALS | 'less-or-equals', '<=', 'lte' | filter[rating][<=]='15' | Свойство меньше или равно указанному значению
GREATER_OR_EQUALS | 'greater-or-equals', '>=', 'gte' | filter[rating][>=]='97' | Свойство больше или равно указанному значению
LIKE | 'like' | filter[email][like]='26d@' | Свойство содержит часть указанного значения
NOT_LIKE | 'not-like' | filter[email][not-like]='27d@' | Свойство не содержит часть указанного значения
EQUALS | 'equals', '=', 'eq' | filter[userId][eq]='ccf92b7a-8e05-4f4b-9f0a-e4360dbacb23' | Свойство эквивалентно указанному значению
NOT_EQUALS | 'not-equals', '!=', '<>', 'neq' | filter[userId][neq]='aaf92b7a-8e05-4f4b-9f0a-e4360dbacb23' | Свойство не эквивалентно указанному значению

##### Пример:
```
filter[userId][eq]='ccf92b7a-8e05-4f4b-9f0a-e4360dbacb23'
filter[name.translations.last][eq]='Tesla'
filter[emails.email][like]='26d@'
filter[userId][eq]='ccf92b7a-8e05-4f4b-9f0a-e4360dbacb23'
filter[name.translations.last][eq]='Tesla'
filter[emails.email][in][]='0791d11b6a952a3804e7cb8a220d0a9b@mail.ru'
filter[emails.email][in][]='0891d11b6a952a3804e7cb8a220d0a9b@mail.ru'
```

## Внутреннее использование:
### Query:
#### GetOne:
Пример Read-action:
```php
use App\Path\To\Entity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\ApiFormatter;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\OutputFormat;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\GetOne\Processor as GetOneProcessor;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\GetOne\Context as GetOneContext;

class Controller {
    /**
     * @Route("/{id}.{_format}", methods={"GET"}, name=".read", defaults={"_format"="json"})
     * @OA\Response(
     *     response=200,
     *     description="Read Entity",
     *     @OA\JsonContent(
     *         allOf={
     *             @OA\Schema(ref=@Model(type=ApiFormatter::class)),
     *             @OA\Schema(type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(
     *                         property="entity",
     *                         ref=@Model(type=UseCase\CommonOutputContract::class)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     example="200"
     *                )
     *             )
     *         }
     *     )
     * )
     */
    public function read(
        string $id,
        GetOneProcessor $processor,
        OutputFormat $outputFormat
    ): Response {
        $context = new GetOneContext(
            outputFormat: $outputFormat->getFormat(),
            entityId: $id,
            targetEntityClass: User::class,
            outputDtoClass: UseCase\CommonOutputContract::class,
        );

        $processor->process($context);
        return $processor->makeResponse();
    }
```
#### Search:
Пример Search-action:
```php
use App\Path\To\Entity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use SymfonyBundle\UIBundle\Foundation\Core\Contract\ApiFormatter;
use SymfonyBundle\UIBundle\Foundation\Core\Dto\OutputFormat;
use SymfonyBundle\UIBundle\Query\Core\Contract\Filter\FilterSortPagination;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Search\Processor as SearchProcessor;
use SymfonyBundle\UIBundle\Query\Core\CQRS\Query\Search\Context as SearchContext;
use SymfonyBundle\UIBundle\Query\Core\Service\Filter\SearchQuery;

class Controller {
    /**
     * @Route(".{_format}", methods={"GET"}, name=".search", defaults={"_format"="json"})
     * @OA\Get(
     *     @OA\Parameter(
     *          name="searchParams",
     *          in="query",
     *          required=false,
     *          @OA\Schema(
     *              ref=@Model(type=FilterSortPagination::class)
     *          ),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Search by Users",
     *     @OA\JsonContent(
     *          allOf={
     *              @OA\Schema(ref=@Model(type=ApiFormatter::class)),
     *              @OA\Schema(type="object",
     *                  @OA\Property(
     *                      property="data",
     *                      type="object",
     *                      @OA\Property(
     *                          property="entities",
     *                          ref=@Model(type=UseCase\CommonOutputContract::class)
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      example="200"
     *                 )
     *              )
     *          }
     *      )
     * )
     */
    public function search(
        SearchProcessor $processor,
        SearchQuery $searchQuery,
        OutputFormat $outputFormat
    ): Response {
        $context = new SearchContext(
            targetEntityClass: User::class,
            outputFormat: $outputFormat->getFormat(),
            outputDtoClass: UseCase\CommonOutputContract::class,
            filterBlackList: ['id'],
            pagination: $searchQuery->getPagination(),
            filters: $searchQuery->getFilters(),
            sorts: $searchQuery->getSorts()
        );

        $processor->process($context);

        return $processor->makeResponse();
    }
}
```