<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
use ThreeLeaf\Biblioteca\Models\TableOfContents;
use ThreeLeaf\Biblioteca\Models\Tag;

class BibliotecaTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_and_tag_relationship()
    {
        $author = Author::factory()->create();
        $series = Series::factory()
            ->for($author)
            ->create();
        $book = Book::factory()
            ->for($author)
            ->for($series)
            ->create();
        $tag = Tag::factory()->create();

        $book->tags()->attach($tag);

        $this->assertDatabaseHas(BibliotecaConstants::TABLE_PREFIX . 'book_tags', [
            'book_id' => $book->book_id,
            'tag_id' => $tag->tag_id,
        ]);

        $this->assertTrue($book->tags->contains($tag));
    }

    /**
     * Test the creation of all related models.
     *
     * @return void
     */
    public function test_create_all_models_with_factories()
    {
        $publisher = Publisher::factory()->create();
        $author = Author::factory()->create();
        $series = Series::factory()
            ->for($author)
            ->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()
            ->for($author)
            ->for($publisher)
            ->for($series)
            ->hasAttached($genre)
            ->create();
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

    public function test_annotation_for_paragraph()
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

    public function test_annotation_for_sentence()
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

    public function test_create_bibliography()
    {
        $author = Author::factory()->create();
        $series = Series::factory()
            ->for($author)
            ->create();
        $book = Book::factory()
            ->for($author)
            ->for($series)
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
            'first_name' => 'John',
            'last_name' => 'Marsh',
            'biography' => 'John Marsh is a prolific writer...',
        ]);

        $book = Book::create([
            'title' => 'The Great Adventure',
            'author_id' => $author->author_id,
            'published_date' => now(),
            'summary' => 'A thrilling tale of adventure...',
        ]);

        $bookAuthor = $book->author;
        echo "Book Author: $bookAuthor->first_name $bookAuthor->last_name";

        $this->assertEquals('John', $bookAuthor->first_name);

        $chapter = Chapter::create([
            'book_id' => $book->book_id,
            'chapter_number' => 1,
            'title' => 'Chapter 1: The Beginning',
        ]);

        $paragraph = Paragraph::create([
            'chapter_id' => $chapter->chapter_id,
            'paragraph_number' => 1,
            'content' => 'This is the first paragraph of the chapter...',
        ]);

        $booksByAuthor = Author::find($author->author_id)->books;

        foreach ($booksByAuthor as $book) {
            echo $book->title;
        }
    }
}
