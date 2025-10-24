<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SanctumTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_personal_access_token()
    {
        $user  = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->assertIsString($token);

        $exists = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->exists();
        $this->assertTrue($exists);
    }
}