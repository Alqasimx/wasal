<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditLogAppendOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_cannot_be_updated(): void
    {
        $log = AuditLog::create([
            'action' => 'test.created',
            'entity_type' => 'test',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        $this->expectException(
            LogicException::class
        );

        $log->update([
            'action' => 'test.changed',
        ]);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'action' => 'test.created',
            'entity_type' => 'test',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        $this->expectException(
            LogicException::class
        );

        $log->delete();
    }
}