<?php

/**
 * This API Route file is intended to be an example only. POST, PUT, and DELETE endpoints
 * will likely require authentication and authorization, depending on the specific application.
 *
 * for example:
 * <code>
 * Route::middleware(['auth', 'role:admin'])->group(function () {
 *     Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
 *     Route::put('publishers/{publisher_id}', [PublisherController::class, 'update'])->name('publishers.update');
 *     Route::delete('publishers/{publisher_id}', [PublisherController::class, 'destroy'])->name('publishers.destroy');
 * });
 * </code>
 */

use Illuminate\Support\Facades\Route;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AnnotationController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AuthorController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\BookController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\ChapterController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\FigureController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\GenreController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\LibraryController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\ParagraphController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\PublisherController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\SentenceController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\SeriesController;
use ThreeLeaf\Biblioteca\Http\Controllers\Api\TagController;

Route::get('library', [LibraryController::class, 'index'])->name('library.index');

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('authors/{author_id}', [AuthorController::class, 'show'])->name('authors.show');

Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
Route::put('authors/{author_id}', [AuthorController::class, 'update'])->name('authors.update');
Route::delete('authors/{author_id}', [AuthorController::class, 'destroy'])->name('authors.destroy');

Route::get('books', [BookController::class, 'index'])->name('books.index');
Route::get('books/{book_id}', [BookController::class, 'show'])->name('books.show');

Route::post('books', [BookController::class, 'store'])->name('books.store');
Route::put('books/{book_id}', [BookController::class, 'update'])->name('books.update');
Route::delete('books/{book_id}', [BookController::class, 'destroy'])->name('books.destroy');

Route::post('books/{book_id}/tags', [BookController::class, 'addTags'])->name('books.addTags');
Route::delete('books/{book_id}/tags/{tag_id}', [BookController::class, 'removeTag'])->name('books.removeTag');

Route::post('books/{book_id}/genres', [BookController::class, 'addGenres'])->name('books.addGenres');
Route::delete('books/{book_id}/genres/{genre_id}', [BookController::class, 'removeGenre'])->name('books.removeGenre');

Route::get('publishers', [PublisherController::class, 'index'])->name('publishers.index');
Route::get('publishers/{publisher_id}', [PublisherController::class, 'show'])->name('publishers.show');

Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
Route::put('publishers/{publisher_id}', [PublisherController::class, 'update'])->name('publishers.update');
Route::delete('publishers/{publisher_id}', [PublisherController::class, 'destroy'])->name('publishers.destroy');

Route::get('series', [SeriesController::class, 'index'])->name('series.index');
Route::get('series/{series_id}', [SeriesController::class, 'show'])->name('series.show');

Route::post('series', [SeriesController::class, 'store'])->name('series.store');
Route::put('series/{series_id}', [SeriesController::class, 'update'])->name('series.update');
Route::delete('series/{series_id}', [SeriesController::class, 'destroy'])->name('series.destroy');

Route::get('tags', [TagController::class, 'index'])->name('tags.index');
Route::get('tags/{tag_id}', [TagController::class, 'show'])->name('tags.show');

Route::post('tags', [TagController::class, 'store'])->name('tags.store');
Route::put('tags/{tag_id}', [TagController::class, 'update'])->name('tags.update');
Route::delete('tags/{tag_id}', [TagController::class, 'destroy'])->name('tags.destroy');

Route::get('genres', [GenreController::class, 'index'])->name('genres.index');
Route::get('genres/{tag_id}', [GenreController::class, 'show'])->name('genres.show');

Route::post('genres', [GenreController::class, 'store'])->name('genres.store');
Route::put('genres/{tag_id}', [GenreController::class, 'update'])->name('genres.update');
Route::delete('genres/{tag_id}', [GenreController::class, 'destroy'])->name('genres.destroy');

Route::get('chapters', [ChapterController::class, 'index'])->name('chapters.index');
Route::get('chapters/{chapter_id}', [ChapterController::class, 'show'])->name('chapters.show');

Route::post('chapters', [ChapterController::class, 'store'])->name('chapters.store');
Route::put('chapters/{chapter_id}', [ChapterController::class, 'update'])->name('chapters.update');
Route::delete('chapters/{chapter_id}', [ChapterController::class, 'destroy'])->name('chapters.destroy');

Route::get('paragraphs', [ParagraphController::class, 'index'])->name('paragraphs.index');
Route::get('paragraphs/{paragraph_id}', [ParagraphController::class, 'show'])->name('paragraphs.show');

Route::post('paragraphs', [ParagraphController::class, 'store'])->name('paragraphs.store');
Route::put('paragraphs/{paragraph_id}', [ParagraphController::class, 'update'])->name('paragraphs.update');
Route::delete('paragraphs/{paragraph_id}', [ParagraphController::class, 'destroy'])->name('paragraphs.destroy');

Route::get('sentences', [SentenceController::class, 'index'])->name('sentences.index');
Route::get('sentences/{sentence_id}', [SentenceController::class, 'show'])->name('sentences.show');

Route::post('sentences', [SentenceController::class, 'store'])->name('sentences.store');
Route::put('sentences/{sentence_id}', [SentenceController::class, 'update'])->name('sentences.update');
Route::delete('sentences/{sentence_id}', [SentenceController::class, 'destroy'])->name('sentences.destroy');

Route::get('figures', [FigureController::class, 'index'])->name('figures.index');
Route::get('figures/{figure_id}', [FigureController::class, 'show'])->name('figures.show');

Route::post('figures', [FigureController::class, 'store'])->name('figures.store');
Route::put('figures/{figure_id}', [FigureController::class, 'update'])->name('figures.update');
Route::delete('figures/{figure_id}', [FigureController::class, 'destroy'])->name('figures.destroy');

Route::get('annotations', [AnnotationController::class, 'index'])->name('annotations.index');
Route::get('annotations/{annotation_id}', [AnnotationController::class, 'show'])->name('annotations.show');

Route::post('annotations', [AnnotationController::class, 'store'])->name('annotations.store');
Route::put('annotations/{annotation_id}', [AnnotationController::class, 'update'])->name('annotations.update');
Route::delete('annotations/{annotation_id}', [AnnotationController::class, 'destroy'])->name('annotations.destroy');
