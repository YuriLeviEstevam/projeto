<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase; use App\Health;
final class HealthTest extends TestCase { public function testHealthOk(): void { $this->assertSame('ok', Health::status()['status'] ?? null); } }
