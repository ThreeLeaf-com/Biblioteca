<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use ThreeLeaf\Biblioteca\Http\Requests\Concerns\ReadsUuidInput;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Series;

/**
 * The {@link Series} {@link FormRequest} class used to validate incoming requests.
 */
#[OA\Schema(
    schema: 'SeriesRequest',
    required: ['title', 'author_id'],
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            example: 'Mystery Chronicles',
            description: 'The title of the series',
        ),
        new OA\Property(
            property: 'subtitle',
            type: 'string',
            example: 'The Beginning',
            description: 'The subtitle of the series',
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            example: 'A captivating series of mystery books.',
            description: 'The description of the series',
        ),
        new OA\Property(
            property: 'author_id',
            type: 'string',
            format: 'uuid',
            example: 'f7f9d3e0-434b-11ed-b878-0242ac120002',
            description: 'The unique ID of the associated author or editor',
        ),
        new OA\Property(
            property: 'book_ids',
            type: 'array',
            maxItems: SeriesRequest::MAX_BOOK_IDS,
            uniqueItems: true,
            items: new OA\Items(type: 'string', format: 'uuid'),
            description: 'The ordered array of book unique IDs to include in the series',
        ),
    ],
)]
class SeriesRequest extends FormRequest
{
    use ReadsUuidInput;

    /** The largest number of books one request may link to a series. */
    public const MAX_BOOK_IDS = 1000;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the user is authorized, otherwise false.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules for the series request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Series::TABLE_NAME)
                    ->where('author_id', $this->uuidInput('author_id'))
                    ->ignore($this->uuidRouteParameter('series_id'), 'series_id'),
            ],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'author_id' => ['bail', 'required', 'uuid', 'exists:' . Author::TABLE_NAME . ',author_id'],
            'book_ids' => ['nullable', 'array', 'max:' . self::MAX_BOOK_IDS],
            'book_ids.*' => ['bail', 'uuid', 'distinct:ignore_case', 'exists:' . Book::TABLE_NAME . ',book_id'],
        ];
    }

    /**
     * Get the custom validation messages for the composite unique key.
     *
     * @return array<string, string> The messages, keyed by attribute and rule.
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function messages(): array
    {
        return [
            'title.unique' => 'This author already has a series with this title.',
        ];
    }
}
