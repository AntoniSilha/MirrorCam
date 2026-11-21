<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest cannot access profile page', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
});

test('customer can view profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Profil Saya')
        ->assertSee('Kata Sandi &amp; Keamanan', false)
        ->assertSee('Kelola Keamanan Google')
        ->assertSee('https://myaccount.google.com/security', false);
});

test('customer can update whatsapp number and avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $originalName = $user->nama_lengkap_ktp;

    Livewire::actingAs($user)
        ->test('pages::customer.profile')
        ->set('no_whatsapp', '0812-3456-7890')
        ->set('avatarUpload', UploadedFile::fake()->image('avatar.jpg'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->no_whatsapp)->toBe('081234567890')
        ->and($user->nama_lengkap_ktp)->toBe($originalName)
        ->and($user->avatar)->toStartWith('avatars/');

    Storage::disk('public')->assertExists($user->avatar);
});

test('customer cannot save invalid whatsapp number', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customer.profile')
        ->set('no_whatsapp', '12345')
        ->call('save')
        ->assertHasErrors(['no_whatsapp' => 'regex']);
});

test('customer cannot upload invalid avatar file', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customer.profile')
        ->set('avatarUpload', UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasErrors(['avatarUpload']);
});
