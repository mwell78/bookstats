<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('books currently being read appear first in the index', function () {
    $user = User::factory()->create();
    $unread = $user->books()->create([
        'title' => 'A New Book',
        'status' => 'Ungelesen',
    ]);
    $finished = $user->books()->create([
        'title' => 'B Finished Book',
        'status' => 'Gelesen',
        'finished_at' => '2025-10-15',
    ]);
    $reading = $user->books()->create([
        'title' => 'Z Current Book',
        'status' => 'Am lesen',
    ]);

    $this->actingAs($user)
        ->get(route('books.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Books/Index')
            ->where('books.data.0.id', $reading->id)
            ->where('books.data.1.id', $finished->id)
            ->where('books.data.2.id', $unread->id));
});

test('currently reading books stay first when another sort order is selected', function () {
    $user = User::factory()->create();
    $alphabeticalFirst = $user->books()->create([
        'title' => 'A New Book',
        'status' => 'Ungelesen',
    ]);
    $reading = $user->books()->create([
        'title' => 'Z Current Book',
        'status' => 'Am lesen',
    ]);

    $this->actingAs($user)
        ->get(route('books.index', ['sort_by' => 'title', 'sort_order' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Books/Index')
            ->where('books.data.0.id', $reading->id)
            ->where('books.data.1.id', $alphabeticalFirst->id));
});
