<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_database_connection_uses_testing_database(): void
    {
        $database = DB::selectOne('SELECT DATABASE() AS nama_database');

        $this->assertSame(
            'sibansos_testing',
            $database->nama_database
        );
    }
}
