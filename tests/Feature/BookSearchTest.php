<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('Google Books title search returns normalized results', function () {
    config(['services.google_books.key' => 'test-api-key']);

    Http::fake([
        'www.googleapis.com/books/v1/volumes*' => Http::response([
            'items' => [[
                'id' => 'google-volume-id',
                'volumeInfo' => [
                    'title' => 'Der Hobbit',
                    'authors' => ['J. R. R. Tolkien'],
                    'industryIdentifiers' => [
                        ['type' => 'ISBN_13', 'identifier' => '9783608939842'],
                    ],
                    'pageCount' => 480,
                    'publishedDate' => '2012-09-20',
                    'imageLinks' => ['thumbnail' => 'http://books.google.test/cover.jpg'],
                ],
            ]],
        ]),
    ]);

    $response = $this->actingAs(User::factory()->create())->get(route('books.search', [
        'title' => 'Der Hobbit',
        'source' => 'google',
    ]));

    $response->assertOk()
        ->assertJsonPath('docs.0.title', 'Der Hobbit')
        ->assertJsonPath('docs.0.author_name.0', 'J. R. R. Tolkien')
        ->assertJsonPath('docs.0.isbn.0', '9783608939842')
        ->assertJsonPath('docs.0.number_of_pages_median', 480)
        ->assertJsonPath('docs.0.first_publish_year', '2012')
        ->assertJsonPath('docs.0.cover_url', 'https://books.google.test/cover.jpg');

    Http::assertSent(fn ($request) => $request['q'] === 'Der Hobbit'
        && $request['langRestrict'] === 'de'
        && $request['key'] === 'test-api-key');
});

test('Google Books ISBN search returns a normalized book', function () {
    config(['services.google_books.key' => 'test-api-key']);

    Http::fake([
        'www.googleapis.com/books/v1/volumes*' => Http::response([
            'items' => [[
                'id' => 'google-volume-id',
                'volumeInfo' => [
                    'title' => 'Der Hobbit',
                    'authors' => ['J. R. R. Tolkien'],
                    'industryIdentifiers' => [
                        ['type' => 'ISBN_13', 'identifier' => '9783608939842'],
                    ],
                ],
            ]],
        ]),
    ]);

    $response = $this->actingAs(User::factory()->create())->get(route('books.searchByIsbn', [
        'isbn' => '9783608939842',
        'source' => 'google',
    ]));

    $response->assertOk()
        ->assertJsonPath('title', 'Der Hobbit')
        ->assertJsonPath('author_name.0', 'J. R. R. Tolkien');

    Http::assertSent(fn ($request) => $request['q'] === 'isbn:9783608939842'
        && $request['key'] === 'test-api-key');
});

test('Google Books search reports when its API key is missing', function () {
    config(['services.google_books.key' => null]);

    $response = $this->actingAs(User::factory()->create())->get(route('books.search', [
        'title' => 'Der Hobbit',
        'source' => 'google',
    ]));

    $response->assertStatus(503)
        ->assertJsonPath('message', 'Bitte hinterlege GOOGLE_BOOKS_API_KEY in der .env-Datei.');
});
