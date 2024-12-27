<?php

namespace Tests\Feature\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Repositories\ParagraphRepository;

/** Test {@link ParagraphRepository}. */
class ParagraphRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var ParagraphRepository The test repository. */
    private ParagraphRepository $paragraphRepository;

    /** @test {@link ParagraphRepository::create()} with missing chapter ID. */
    public function requireMissingChapterId()
    {
        $data = [
            'paragraph_number' => $this->faker->unique()->numberBetween(1, 1000),
            'content' => $this->faker->paragraph(),
        ];

        $this->expectException(QueryException::class);
        $this->paragraphRepository->create($data);
    }

    /** @test {@link ParagraphRepository::create()} does not allow duplicate paragraph number. */
    public function disallowDuplicateParagraphNumber()
    {
        $paragraph1 = Paragraph::factory()->create(['paragraph_number' => 1]);
        $chapterId = $paragraph1->chapter_id;
        $paragraph2 = Paragraph::factory()->create([
            'chapter_id' => $chapterId,
            'paragraph_number' => 2,
        ]);
        $newParagraphData = [
            'chapter_id' => $chapterId,
            'content' => $this->faker->paragraph(),
            'paragraph_number' => 2,
        ];

        $this->expectException(UniqueConstraintViolationException::class);

        $this->paragraphRepository->create($newParagraphData);
    }

    /** @test {@link ParagraphRepository::read()}. */
    public function read()
    {
        $paragraph = Paragraph::factory()->create();

        $paragraphRetrieved = $this->paragraphRepository->read($paragraph->paragraph_id);

        $this->assertTrue($paragraph->is($paragraphRetrieved));
    }

    /** @test {@link ParagraphRepository::read()} non-existent. */
    public function readNonExistentParagraphId()
    {
        $nonExistentParagraphId = 'non-existent-paragraph-id';

        $paragraph = $this->paragraphRepository->read($nonExistentParagraphId);

        $this->assertNull($paragraph);
    }

    /** @test {@link ParagraphRepository::readOrFail()} non-existent. */
    public function readOrFail()
    {
        $paragraph = Paragraph::factory()->create();

        $retrievedParagraph = $this->paragraphRepository->readOrFail($paragraph->paragraph_id);

        $this->assertTrue($paragraph->is($retrievedParagraph));
    }

    /** @test {@link ParagraphRepository::readOrFail()} non-existent. */
    public function readOrFailDoesNotExist()
    {
        $nonExistentParagraphId = 'non-existent-paragraph-id';

        $this->expectException(ModelNotFoundException::class);

        $this->paragraphRepository->readOrFail($nonExistentParagraphId);
    }

    /** @test {@link ParagraphRepository::readAll()} */
    public function readAll()
    {
        $numberOfParagraphs = $this->faker->numberBetween(1, 10);
        Paragraph::factory()->count($numberOfParagraphs)->create();

        $paragraphs = $this->paragraphRepository->readAll();

        $this->assertCount($numberOfParagraphs, $paragraphs);
    }

    /** @test {@link ParagraphRepository::readAll()} no paragraphs. */
    public function readAllNoParagraphs()
    {
        $this->assertEmpty($this->paragraphRepository->readAll());
    }

    /** @test {@link ParagraphRepository::readAll()} with Chapter::$id. */
    public function readAllChapterId()
    {
        $chapter1 = Chapter::factory()->create();
        $chapter2 = Chapter::factory()->create();
        Paragraph::factory()->count(3)->create(['chapter_id' => $chapter1->chapter_id]);
        Paragraph::factory()->count(2)->create(['chapter_id' => $chapter2->chapter_id]);

        $paragraphsForChapter1 = $this->paragraphRepository->readAll($chapter1->chapter_id);

        $this->assertCount(3, $paragraphsForChapter1);

        $paragraphIdsForChapter1 = $chapter1->paragraphs()->pluck('paragraph_id')->toArray();
        $paragraphIdsForChapter1Database = collect($paragraphsForChapter1)->pluck('paragraph_id')->toArray();

        $this->assertEquals($paragraphIdsForChapter1Database, $paragraphIdsForChapter1);
    }

    /** @test {@link ParagraphRepository::update()} no paragraphs. */
    public function update()
    {
        $paragraph = Paragraph::factory()->create();
        $newData = [
            'content' => $this->faker->paragraph(),
            'paragraph_number' => $this->faker->unique()->numberBetween(1, 1000),
            'chapter_id' => $paragraph->chapter_id,
        ];

        $this->assertNotEquals($newData, $paragraph->only(['content', 'paragraph_number', 'chapter_id']));

        $updatedParagraph = $this->paragraphRepository->update($paragraph, $newData);

        $this->assertEquals($paragraph->paragraph_id, $updatedParagraph->paragraph_id);
        $this->assertEquals($newData, $paragraph->only(['content', 'paragraph_number', 'chapter_id']));
    }

    /** @test {@link ParagraphRepository::delete()}. */
    public function deleteParagraphId()
    {
        $paragraph = Paragraph::factory()->create();

        $this->assertTrue($this->paragraphRepository->delete($paragraph->paragraph_id));
    }

    /** @test {@link ParagraphRepository::updateOrCreate()}. */
    public function updateOrCreateNew()
    {
        $chapterId = Chapter::factory()->create()->chapter_id;
        $paragraphNumber = $this->faker->numberBetween(1, 10);
        $content = $this->faker->paragraph();

        $newParagraphData = [
            'chapter_id' => $chapterId,
            'paragraph_number' => $paragraphNumber,
            'content' => $content,
        ];

        $paragraph = $this->paragraphRepository->updateOrCreate($newParagraphData);

        $this->assertDatabaseHas('b_paragraphs', [
            'chapter_id' => $chapterId,
            'paragraph_number' => $paragraphNumber,
            'content' => $content,
        ]);
        $this->assertEquals($chapterId, $paragraph->chapter_id);
        $this->assertEquals($paragraphNumber, $paragraph->paragraph_number);
        $this->assertEquals($content, $paragraph->content);
    }

    /** @test {@link ParagraphRepository::updateOrCreate()} for existing paragraph. */
    public function updateOrCreateExisting()
    {
        $existingParagraph = Paragraph::factory()->create();
        $newContent = $this->faker->paragraph();
        $newParagraphData = [
            'chapter_id' => $existingParagraph->chapter_id,
            'paragraph_number' => $existingParagraph->paragraph_number,
            'content' => $newContent,
        ];

        $updatedParagraph = $this->paragraphRepository->updateOrCreate($newParagraphData);

        $this->assertEquals($existingParagraph->paragraph_id, $updatedParagraph->paragraph_id);
        $this->assertEquals($newContent, $updatedParagraph->content);
        $this->assertDatabaseHas('b_paragraphs', [
            'paragraph_id' => $existingParagraph->paragraph_id,
            'content' => $newContent,
        ]);
    }

    /** @test {@link ParagraphRepository::delete()} non-existent. */
    public function deleteParagraphDoesNotExist()
    {
        $nonExistentParagraphId = 'non-existent-paragraph-id';

        $this->assertFalse($this->paragraphRepository->delete($nonExistentParagraphId));
    }

    /** @test {@link ParagraphRepository::deleteAllSentences()}. */
    public function deleteAllSentences()
    {
        $paragraph = Paragraph::factory()->create();
        Sentence::factory()->count(3)->create(['paragraph_id' => $paragraph->paragraph_id]);

        $this->assertCount(3, $paragraph->sentences);

        $this->paragraphRepository->deleteAllSentences($paragraph);

        $this->assertCount(0, Paragraph::find($paragraph->paragraph_id)->sentences);
    }

    /** @test {@link ParagraphRepository::addSentences()}. */
    public function addSentences()
    {
        $paragraph = Paragraph::factory()->create();
        $sentences = Sentence::factory()->count(3)->create()->all();

        $this->assertCount(0, $paragraph->sentences);

        $this->paragraphRepository->addSentences($paragraph, $sentences);

        $this->assertCount(3, Paragraph::find($paragraph->paragraph_id)->sentences);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->paragraphRepository = app(ParagraphRepository::class);
    }
}
