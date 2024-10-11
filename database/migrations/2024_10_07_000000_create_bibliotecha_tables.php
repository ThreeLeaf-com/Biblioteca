<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ThreeLeaf\Biblioteca\Constants\BibliotecaConstants;
use ThreeLeaf\Biblioteca\Enums\Context;
use ThreeLeaf\Biblioteca\Enums\NoteType;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Bibliography;
use ThreeLeaf\Biblioteca\Models\Book;
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Figure;
use ThreeLeaf\Biblioteca\Models\Genre;
use ThreeLeaf\Biblioteca\Models\Note;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Publisher;
use ThreeLeaf\Biblioteca\Models\Sentence;
use ThreeLeaf\Biblioteca\Models\TableOfContents;
use ThreeLeaf\Biblioteca\Models\Tag;

/** Create all the Biblioteca tables. */
return new class extends Migration {

    /** Run the migrations. */
    public function up(): void
    {

        /** Create the {@link Annotation} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'annotations', function (Blueprint $table) {
            $table->comment('Annotations for paragraphs and sentences');
            $table->uuid('annotation_id')->primary()->comment('Primary key of the annotation in UUID format');
            $table->uuid('reference_id')->comment('Reference UUID pointing to either a paragraph or a sentence');
            $table->string('reference_type')->comment('The reference type / class');
            $table->text('content')->comment('The textual content of the annotation');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the annotation was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the annotation was last updated');
        });

        /** Create the {@link Author} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'authors', function (Blueprint $table) {
            $table->comment('Authors of books');
            $table->uuid('author_id')->primary()->comment('Primary key of the author in UUID format');
            $table->string('first_name')->comment('First name of the author');
            $table->string('last_name')->comment('Last name of the author');
            $table->text('biography')->nullable()->comment('A brief biography of the author');
            $table->string('author_image_url')->nullable()->comment('URL of the author\'s image');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the annotation was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the annotation was last updated');
        });

        /** Create the {@link Series} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'series', function (Blueprint $table) {
            $table->comment('Series of books associated with an author');
            $table->uuid('series_id')->primary()->comment('Primary key of the series in UUID format');
            $table->string('name')->comment('Name of the series');
            $table->text('description')->nullable()->comment('Description of the series');
            $table->uuid('author_id')->comment('UUID of the associated author');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the series was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the series was last updated');
        });

        /** Create the {@link Bibliography} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'bibliographies', function (Blueprint $table) {
            $table->comment('Bibliography entries associated with books');
            $table->uuid('bibliography_id')->primary()->comment('Primary key of the bibliography entry in UUID format');
            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->text('content')->comment('Content of the bibliography entry');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the bibliography entry was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the bibliography entry was last updated');
        });

        /** Create the {@link Book} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'books', function (Blueprint $table) {
            $table->comment('Books with chapters, associated with authors and publishers');
            $table->uuid('book_id')->primary()->comment('Primary key of the book in UUID format');
            $table->string('title')->comment('Title of the book');
            $table->uuid('author_id')->comment('UUID of the author');
            $table->date('published_date')->nullable()->comment('Publication date of the book');
            $table->string('isbn')->nullable()->comment('ISBN of the book');
            $table->uuid('publisher_id')->nullable()->comment('UUID of the publisher');
            $table->string('edition')->nullable()->comment('Edition of the book');
            $table->string('locale')->nullable()->comment('Locale of the book (e.g., en_US)');
            $table->string('suggested_citation')->nullable()->comment('Suggested citation format for the book');
            $table->string('cover_image_url')->nullable()->comment('URL of the book cover image');
            $table->text('summary')->nullable()->comment('A brief summary of the book');
            $table->uuid('series_id')->nullable()->comment('The series this book belongs to');
            $table->integer('number_in_series')->nullable()->comment('The book’s number in a series (if applicable)');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the book record was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the book record was last updated');
            $table->foreign('series_id')->references('series_id')->on(BibliotecaConstants::TABLE_PREFIX . 'series');
        });

        /** Create the {@link Chapter} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'chapters', function (Blueprint $table) {
            $table->comment('Chapters associated with books');
            $table->uuid('chapter_id')->primary()->comment('Primary key of the chapter in UUID format');
            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->integer('chapter_number')->comment('Number of the chapter in the book');
            $table->string('title')->comment('Title of the chapter');
            $table->text('summary')->nullable()->comment('A brief summary of the chapter');
            $table->string('chapter_image_url')->nullable()->comment('URL of the chapter’s image');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the chapter record was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the chapter record was last updated');
        });

        /** Create the {@link Figure} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'figures', function (Blueprint $table) {
            $table->comment('Figures associated with chapters');
            $table->uuid('figure_id')->primary()->comment('Primary key of the figure in UUID format');
            $table->uuid('chapter_id')->comment('UUID of the associated chapter');
            $table->string('figure_label')->comment('Alphanumeric label of the figure');
            $table->string('caption')->nullable()->comment('Caption of the figure');
            $table->string('image_url')->nullable()->comment('URL of the figure image');
            $table->text('description')->nullable()->comment('Description of the figure');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the figure was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the figure was last updated');
        });

        /** Create the {@link Note} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'notes', function (Blueprint $table) {
            $table->comment('Notes associated with sentences');
            $table->uuid('note_id')->primary()->comment('Primary key of the note in UUID format');
            $table->uuid('sentence_id')->comment('UUID of the associated sentence');
            $table->text('content')->comment('Content of the note');
            $table->string('note_label')->comment('Alphanumeric label of the note');
            $table->enum('note_type', array_map(fn($case) => $case->value, NoteType::cases()))->comment('Type of the note');
            $table->enum('context', array_map(fn($case) => $case->value, Context::cases()))->comment('Context in which the note appears');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the note was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the note was last updated');
        });

        /** Create the {@link Genre} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'genres', function (Blueprint $table) {
            $table->comment('Genres associated with multiple books');
            $table->uuid('genre_id')->primary()->comment('Primary key of the genre in UUID format');
            $table->string('name')->comment('Name of the genre');
            $table->text('description')->nullable()->comment('Description of the genre');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the genre was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the genre was last updated');
        });

        /** Create the {@link Index} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'indices', function (Blueprint $table) {
            $table->comment('Index entries associated with books');
            $table->uuid('index_id')->primary()->comment('Primary key of the index entry in UUID format');
            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->string('term')->comment('Indexed term');
            $table->integer('page_number')->comment('Page number where the term is located');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the index entry was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the index entry was last updated');
        });

        /** Create the {@link Paragraph} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'paragraphs', function (Blueprint $table) {
            $table->comment('Paragraphs associated with chapters');
            $table->uuid('paragraph_id')->primary()->comment('Primary key of the paragraph in UUID format');
            $table->uuid('chapter_id')->comment('UUID of the associated chapter');
            $table->integer('paragraph_number')->comment('Number of the paragraph in the chapter');
            $table->text('content')->comment('Content of the paragraph');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the paragraph was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the paragraph was last updated');
        });

        /** Create the {@link Publisher} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'publishers', function (Blueprint $table) {
            $table->comment('Publishers associated with multiple books');
            $table->uuid('publisher_id')->primary()->comment('Primary key of the publisher in UUID format');
            $table->string('name')->comment('Name of the publisher');
            $table->string('address')->nullable()->comment('Address of the publisher');
            $table->string('website')->nullable()->comment('Website of the publisher');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the publisher was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the publisher was last updated');
        });

        /** Create the {@link Sentence} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'sentences', function (Blueprint $table) {
            $table->comment('Sentences associated with paragraphs');
            $table->uuid('sentence_id')->primary()->comment('Primary key of the sentence in UUID format');
            $table->uuid('paragraph_id')->comment('UUID of the associated paragraph');
            $table->integer('sentence_number')->comment('Number of the sentence within the paragraph');
            $table->text('content')->comment('Content of the sentence');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the sentence was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the sentence was last updated');
        });

        /** Create the {@link TableOfContents} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'table_of_contents', function (Blueprint $table) {
            $table->comment('Table of contents entries associated with books and chapters');
            $table->uuid('toc_id')->primary()->comment('Primary key of the table of contents entry in UUID format');
            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->string('title')->comment('Title of the chapter/section in the table of contents');
            $table->uuid('chapter_id')->comment('UUID of the associated chapter');
            $table->integer('page_number')->comment('Page number of the chapter/section in the book');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the table of contents entry was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the table of contents entry was last updated');
        });

        /** Create the {@link Tag} table. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'tags', function (Blueprint $table) {
            $table->comment('Tags associated with multiple books');
            $table->uuid('tag_id')->primary()->comment('Primary key of the tag in UUID format');
            $table->string('name')->comment('Name of the tag');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the tag was created');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the tag was last updated');
        });

        /** Create the pivot table for {@link Book}s and {@link Tag}s. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'book_tags', function (Blueprint $table) {
            $table->comment('Pivot table for books and tags');

            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->uuid('tag_id')->comment('UUID of the associated tag');

            $table->primary(['book_id', 'tag_id']);

            $table->foreign('book_id')->references('book_id')->on(BibliotecaConstants::TABLE_PREFIX . 'books')->onDelete('cascade');
            $table->foreign('tag_id')->references('tag_id')->on(BibliotecaConstants::TABLE_PREFIX . 'tags')->onDelete('cascade');
        });

        /** Create the pivot table for {@link Book}s and {@link Genre}s. */
        Schema::create(BibliotecaConstants::TABLE_PREFIX . 'book_genres', function (Blueprint $table) {
            $table->comment('Pivot table for books and genres');

            $table->uuid('book_id')->comment('UUID of the associated book');
            $table->uuid('genre_id')->comment('UUID of the associated genre');

            $table->primary(['book_id', 'genre_id']);

            $table->foreign('book_id')->references('book_id')->on(BibliotecaConstants::TABLE_PREFIX . 'books')->onDelete('cascade');
            $table->foreign('genre_id')->references('genre_id')->on(BibliotecaConstants::TABLE_PREFIX . 'genres')->onDelete('cascade');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'book_genres');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'book_tags');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'tags');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'table_of_contents');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'sentences');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'publishers');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'paragraphs');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'indices');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'genres');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'notes');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'figures');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'chapters');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'books');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'bibliographies');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'series');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'authors');
        Schema::dropIfExists(BibliotecaConstants::TABLE_PREFIX . 'annotations');
    }
};
