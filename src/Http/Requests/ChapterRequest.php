<?php

namespace ThreeLeaf\Biblioteca\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use OpenApi\Attributes as OA;
use ThreeLeaf\Biblioteca\Http\Requests\Concerns\ReadsUuidInput;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;

/**
 * The {@link Chapter} {@link FormRequest} class used to validate incoming requests.
 */
#[OA\Schema(
    schema: 'ChapterRequest',
    required: ['book_id'],
    properties: [
        new OA\Property(
            property: 'book_id',
            type: 'string',
            format: 'uuid',
            example: 'f7f9d3e0-434b-11ed-b878-0242ac120002',
            description: 'UUID of the associated book',
        ),
        new OA\Property(
            property: 'chapter_number',
            type: 'integer',
            example: 1,
            description: 'Number of the chapter in the book (will auto-increment if not specified)',
        ),
        new OA\Property(
            property: 'title',
            type: 'string',
            example: 'The Mysterious Beginning',
            description: 'Title of the chapter',
        ),
        new OA\Property(
            property: 'summary',
            type: 'string',
            example: 'This chapter introduces the main mystery.',
            description: 'A brief summary of the chapter',
        ),
        new OA\Property(
            property: 'chapter_image_url',
            type: 'string',
            example: 'https://example.com/chapter1.jpg',
            description: 'URL of the chapter’s image',
        ),
        new OA\Property(
            property: 'content',
            type: 'string',
            example: 'This is the first paragraph of the chapter.\nParagraph two.',
            description: 'Content of the chapter',
        ),
    ],
)]
class ChapterRequest extends FormRequest
{
    use ReadsUuidInput;

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
     * @return array<string, mixed> The validation rules for the chapter request.
     */
    public function rules(): array
    {
        return [
            'book_id' => [
                'bail',
                'required',
                'uuid',
                'exists:' . Book::TABLE_NAME . ',book_id',
                ...$this->keptChapterNumberRules(),
            ],
            'chapter_number' => [
                'bail',
                'sometimes',
                'integer',
                'min:1',
                Rule::unique(Chapter::TABLE_NAME)
                    ->where('book_id', $this->uuidInput('book_id'))
                    ->ignore($this->uuidRouteParameter('chapter_id'), 'chapter_id'),
            ],
            'title' => ['string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'chapter_image_url' => ['nullable', 'url'],
            'content' => ['nullable', 'string'],
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
            'chapter_number.unique' => 'This book already has a chapter with this number.',
            'book_id.unique' => 'The target book already has a chapter with this chapter\'s number.',
        ];
    }

    /**
     * Get the unique rule for an update that keeps the stored chapter number.
     *
     * An update that omits <code>chapter_number</code> keeps the stored number, because only a create
     * assigns a new one. When such an update moves the chapter to another book, the stored number must
     * be free in that book. The rule is placed on <code>book_id</code>, the input that causes the clash.
     *
     * @return array<int, Unique> The unique rule, or an empty array for a create, for a request that
     *                            sends <code>chapter_number</code>, or for an unknown chapter.
     */
    private function keptChapterNumberRules(): array
    {
        $chapterId = $this->uuidRouteParameter('chapter_id');
        if ($chapterId === null || $this->has('chapter_number')) {
            return [];
        }

        $chapterNumber = Chapter::whereKey($chapterId)->value('chapter_number');
        if ($chapterNumber === null) {
            return [];
        }

        return [
            Rule::unique(Chapter::TABLE_NAME)
                ->where('chapter_number', $chapterNumber)
                ->ignore($chapterId, 'chapter_id'),
        ];
    }
}
