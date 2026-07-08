<?php

namespace Tests\Unit\Http\Requests\Admin;

use App\Http\Requests\Admin\StoreModuleRequest;
use Tests\TestCase;

class StoreModuleRequestTest extends TestCase
{
    public function test_build_rules_for_users_returns_arrays_not_strings(): void
    {
        $rules = StoreModuleRequest::buildRules('users', true);

        $this->assertArrayHasKey('email', $rules);
        $this->assertIsArray($rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('unique:app_users,email', $rules['email']);
    }
}
