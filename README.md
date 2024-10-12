# Biblioteca Model Library

**Biblioteca Model** is a Laravel-based library designed to provide a comprehensive model framework to organize and manage data related to many kinds of written material, with a specific focus on books. This library allows you to structure and manage
complex data, including authors, books, chapters, paragraphs, sentences, notes, and more.

[![Latest Stable Version](https://poser.pugx.org/threeleaf/biblioteca/v/stable)](https://packagist.org/packages/threeleaf/biblioteca)
[![GitHub last commit](https://img.shields.io/github/last-commit/ThreeLeaf-com/Biblioteca)](https://github.com/ThreeLeaf-com/Biblioteca/commits/main)
[![Build Status](https://github.com/ThreeLeaf-com/Biblioteca/actions/workflows/tests.yaml/badge.svg?branch=main)](https://github.com/ThreeLeaf-com/Biblioteca/actions)
![Coverage](./public/images/coverage-badge.svg)
[![PHP Version](https://img.shields.io/packagist/php-v/threeleaf/biblioteca)](https://packagist.org/packages/threeleaf/biblioteca)
[![License](https://poser.pugx.org/threeleaf/biblioteca/license)](https://packagist.org/packages/threeleaf/biblioteca)
[![Total Downloads](https://poser.pugx.org/threeleaf/biblioteca/downloads)](https://packagist.org/packages/threeleaf/biblioteca)

## Features

- **Organize Complex Written Materials**: Manage data models for books, chapters, paragraphs, sentences, notes, and much more.
- **Multi-level Data Organization**: Supports nested structures, such as books containing chapters, chapters containing paragraphs, and so on.
- **Supports CRUD Operations**: Easily create, read, update, and delete entities such as books, authors, and notes.
- **Relational Data Management**: Models are related in a structured way, allowing you to retrieve associated data, such as the author of a book or the chapters in a book.
- **OpenAPI Documentation Integration**: Prepares your data models for API usage with comprehensive OpenAPI (Swagger) annotations.

## Models Included

The Biblioteca Model library includes the following data models:

- **Author**: Represents the author of a book.
- **Book**: Represents a book, which can have multiple chapters, tags, genres, etc.
- **Chapter**: Represents a chapter in a book, containing paragraphs.
- **Paragraph**: Represents a paragraph in a chapter, containing sentences.
- **Sentence**: Represents a sentence in a paragraph.
- **Note**: Represents additional notes or annotations related to sentences.
- **Figure**: Represents a figure associated with a chapter.
- **Index**: Represents an index entry for a book, linking to a page or section.
- **Tag**: Represents tags associated with books for categorization.
- **Genre**: Represents a genre, which can be associated with multiple books.
- **Series**: Represents a series of books, linking to an author and the individual books in the series.
- **Table of Contents (ToC)**: Represents a table of contents entry for a book, linking chapters and page numbers.

## Installation

1. Install the package via Composer:

   ```bash
   composer require threeleaf/biblioteca
   ```

2. Publish the package configuration:

   ```bash
   php artisan vendor:publish --provider="ThreeLeaf\Biblioteca\BibliotecaServiceProvider"
   ```

3. Run migrations to create the necessary database tables:

   ```bash
   php artisan migrate
   ```

## Usage

After installation and configuration, you can use the models provided by the Biblioteca library to manage written materials. Below are some examples of how you can use these models in your application.

### Example: Creating a New Book with an Author

```php
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;

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
```

### Example: Adding Chapters and Paragraphs to a Book

```php
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

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
```

### Example: Querying Books by Author

```php
/* Retrieve all books by a specific author */
$booksByAuthor = Author::find($author->author_id)->books;

foreach ($booksByAuthor as $book) {
    echo $book->title;
}
```

## API Integration

If you want to expose the data through an API, the library is integrated with **l5-swagger**, making it easy to generate OpenAPI (Swagger) documentation. Use the provided controllers or create your own to work with the models in an API context.

### Example API Route for Authors

Add the following route to `routes/api.php`:

```php
use App\Http\Controllers\AuthorController;

Route::apiResource('authors', AuthorController::class);
```

Run the Swagger documentation generation command to create up-to-date documentation:

```bash
php artisan l5-swagger:generate
```

A complete example of the available API routes can be found in the Biblioteca project's [api.php](routes/api.php) file.

If you would like to use the Biblioteca project's [api.php](routes/api.php) file as-is, you can include the project API
routes into your own project by adding the following line to your project's `api.php` file:

```php
require base_path('vendor/threeleaf/biblioteca/routes/api.php'); 
```

## Database Migrations

After installing the library, run migrations to create all the necessary database tables for storing data related to books and other written material.

```bash
php artisan migrate
```

## Contributing

Contributions are welcome! Please submit a pull request or open an issue to discuss your ideas.

## License

This library is open-sourced software licensed under the [GPL-3.0+](https://www.gnu.org/licenses/gpl-3.0.html).

## Miscellaneous

### OpenApi Documentation

OpenAPI documentation can be generated within the application using the command:

```
composer generate-swagger
```
