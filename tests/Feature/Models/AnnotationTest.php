<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Bibliography;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Index;
use ThreeLeaf\Biblioteca\Models\Note;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Models\Series;
use ThreeLeaf\Biblioteca\Models\SeriesBook;
use ThreeLeaf\Biblioteca\Models\TableOfContents;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Test several models at once. */
class AnnotationTest extends TestCase
{
    use RefreshDatabase;

    /** @test the creation of all related models. */
    public function attachTagToBook()
    {
        $book = Book::factory()
            ->create();
        $tag = Tag::factory()->create();

        $book->tags()->attach($tag);

        $this->assertDatabaseHas(BibliotecaConstants::TABLE_PREFIX . 'book_tags', [
            'book_id' => $book->book_id,
            'tag_id' => $tag->tag_id,
        ]);

        $this->assertTrue($book->tags->contains($tag));
    }

    /** @test the creation of all related models. */
    public function allModelFactories()
    {
        $publisher = Publisher::factory()->create();
        $author = Author::factory()->create();
        $series = Series::factory()
            ->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()
            ->for($author)
            ->for($publisher)
            ->hasAttached($genre)
            ->create();
        $seriesBook = SeriesBook::create([
            'series_id' => $series->series_id,
            'book_id' => $book->book_id,
            'number' => 1,
        ]);
        $chapter = Chapter::factory()
            ->for($book)
            ->create();
        $paragraph = Paragraph::factory()
            ->for($chapter)
            ->create();
        $sentences = Sentence::factory()->count(3)->for($paragraph)->create();
        $toc = TableOfContents::factory()
            ->for($book)
            ->for($chapter)
            ->create();
        $figure = Figure::factory()
            ->for($chapter)
            ->create();
        $index = Index::factory()
            ->for($book)
            ->create();
        $note = Note::factory()
            ->for($sentences[0])
            ->create();
        $tag = Tag::factory()->create();
        $book->tags()->attach($tag);

        $this->assertDatabaseHas(Book::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseHas(Chapter::TABLE_NAME, ['chapter_id' => $chapter->chapter_id]);
        $this->assertDatabaseHas(Paragraph::TABLE_NAME, ['paragraph_id' => $paragraph->paragraph_id]);
        $this->assertDatabaseHas(Sentence::TABLE_NAME, ['paragraph_id' => $paragraph->paragraph_id]);
        $this->assertDatabaseHas(TableOfContents::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseHas(Figure::TABLE_NAME, ['chapter_id' => $chapter->chapter_id]);
        $this->assertDatabaseHas(Index::TABLE_NAME, ['book_id' => $book->book_id]);
        $this->assertDatabaseHas(Note::TABLE_NAME, ['sentence_id' => $sentences[0]->sentence_id]);
        $this->assertDatabaseHas(Tag::TABLE_NAME, ['tag_id' => $tag->tag_id]);
    }

    /** @test the creation of all related models. */
    public function attachAnnotationToParagraph()
    {
        $paragraph = Paragraph::factory()->create();

        $annotation = Annotation::factory()->create([
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_id' => $paragraph->paragraph_id,
            'reference_type' => Paragraph::class,
        ]);

        $this->assertTrue($annotation->reference()->is($paragraph));
    }

    /** @test the creation of all related models. */
    public function attachAnnotationToSentence()
    {
        $sentence = Sentence::factory()->create();
        $annotation = Annotation::factory()->create([
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::class,
        ]);

        $this->assertDatabaseHas(Annotation::TABLE_NAME, [
            'annotation_id' => $annotation->annotation_id,
            'reference_id' => $sentence->sentence_id,
            'reference_type' => Sentence::class,
        ]);

        $this->assertTrue($annotation->reference()->is($sentence));
    }

    /** @test attach Bibliography to Book. */
    public function attachBibliographyToBook()
    {
        $author = Author::factory()->create();
        $series = Series::factory()
            ->create();
        $book = Book::factory()
            ->for($author)
            ->create();
        $bibliography = Bibliography::factory()
            ->for($book)
            ->create();

        $this->assertDatabaseHas(Bibliography::TABLE_NAME, [
            'bibliography_id' => $bibliography->bibliography_id,
            'book_id' => $book->book_id,
        ]);
    }

    /** @test README examples. */
    public function readmeExamples()
    {
        $author = Author::create([
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'biography' => implode("\n", fake()->paragraphs(3)),
        ]);

        $book = Book::create([
            'title' => fake()->sentence(6),
            'author_id' => $author->author_id,
            'published_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'summary' => implode("\n", fake()->paragraphs(3)),
        ]);

        $bookAuthor = $book->author;
        echo "Book Author: $bookAuthor->first_name $bookAuthor->last_name";

        $this->assertEquals($author->first_name, $bookAuthor->first_name);

        $chapter = Chapter::create([
            'book_id' => $book->book_id,
            'chapter_number' => fake()->randomNumber(2),
            'title' => fake()->sentence(6),
        ]);

        $paragraph = Paragraph::create([
            'chapter_id' => $chapter->chapter_id,
            'paragraph_number' => fake()->randomNumber(2),
            'content' => implode("\n", fake()->paragraphs(3)),
        ]);

        $booksByAuthor = Author::find($author->author_id)->books;

        foreach ($booksByAuthor as $book) {
            echo $book->title;
        }
    }
}
