<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Uuid;

return new class extends Migration {
    public function up(): void
    {

        $uuid = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_X500), 'CN=John A. Marsh');
        DB::table('b_authors')->insert([
            'author_id' => (string)\Illuminate\Support\Str::uuid(),
            'first_name' => 'John',
            'last_name' => 'Marsh',
            'biography' => 'John Marsh is a renowned author known for his works in fiction.',
            'author_image_url' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
};
