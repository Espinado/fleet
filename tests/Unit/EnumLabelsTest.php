<?php

namespace Tests\Unit;

use App\Enums\DriverStatus;
use App\Enums\OdometerEventType;
use App\Enums\StepDocumentType;
use App\Enums\TripDocumentType;
use App\Enums\TripExpenseCategory;
use App\Enums\TripStatus;
use App\Enums\TripStepStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumLabelsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trip_status_has_translated_labels(): void
    {
        foreach (TripStatus::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "TripStatus::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function trip_step_status_has_translated_labels(): void
    {
        foreach (TripStepStatus::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "TripStepStatus::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function driver_status_has_translated_labels(): void
    {
        foreach (DriverStatus::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "DriverStatus::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function trip_expense_category_has_translated_labels(): void
    {
        foreach (TripExpenseCategory::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "TripExpenseCategory::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function step_document_type_has_translated_labels(): void
    {
        foreach (StepDocumentType::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "StepDocumentType::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function trip_document_type_has_translated_labels(): void
    {
        foreach (TripDocumentType::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "TripDocumentType::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function odometer_event_type_has_translated_labels(): void
    {
        foreach (OdometerEventType::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "OdometerEventType::{$case->name} should have a label");
            $this->assertIsString($label);
        }
    }

    /** @test */
    public function trip_status_color_is_string(): void
    {
        foreach (TripStatus::cases() as $case) {
            $color = $case->color();
            $this->assertNotEmpty($color);
            $this->assertIsString($color);
        }
    }
}
