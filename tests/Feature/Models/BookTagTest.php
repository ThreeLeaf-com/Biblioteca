<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Test {@link BookTag}. */
class BookTagTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Book::tags()} attach. */
    public function attachTags(): void
    {
        $book = Book::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Fiction']);
        $tag2 = Tag::factory()->create(['name' => 'Best Seller']);

        $book->tags()->attach([$tag1->tag_id, $tag2->tag_id]);

        $this->assertTrue($book->tags->contains($tag1));
        $this->assertTrue($book->tags->contains($tag2));
    }

    /** @test {@link Tag::books()}. */
    public function tagBooks(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Classic']);

        $tag->books()->attach([$book1->book_id, $book2->book_id]);

        $this->assertTrue($tag->books->contains($book1));
        $this->assertTrue($tag->books->contains($book2));
    }

    /** @test {@link Book::tags()} detach. */
    public function detachTags(): void
    {
        $book = Book::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Science Fiction']);

        $book->tags()->attach($tag->tag_id);
        $book->tags()->detach($tag->tag_id);

        $this->assertFalse($book->tags->contains($tag));
    }

    /** @test {@link Book::tags()} sync. */
    public function syncTags(): void
    {
        $book = Book::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Adventure']);
        $tag2 = Tag::factory()->create(['name' => 'Romance']);
        $tag3 = Tag::factory()->create(['name' => 'Thriller']);

        /* Initially attach two tags to the book */
        $book->tags()->sync([$tag1->tag_id, $tag2->tag_id]);

        /* Verify initial tags are attached */
        $this->assertTrue($book->tags->contains($tag1));
        $this->assertTrue($book->tags->contains($tag2));

        /* Sync to replace with a new set of tags */
        $book->tags()->sync([$tag2->tag_id, $tag3->tag_id]);

        /* Refresh the book instance and verify the tags */
        $book->refresh();
        $this->assertFalse($book->tags->contains($tag1));
        $this->assertTrue($book->tags->contains($tag2));
        $this->assertTrue($book->tags->contains($tag3));
    }
}
