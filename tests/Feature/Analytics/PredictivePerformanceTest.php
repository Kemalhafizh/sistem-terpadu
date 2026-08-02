<?php

namespace Tests\Feature\Analytics;

use App\Models\AcademicBenchmark;
use App\Models\EngagementMetric;
use App\Models\FinancialForecast;
use App\Models\MlPerformancePrediction;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictivePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_predictive_analytics_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.predictive.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Predictive Student Performance Engine');
    }

    public function test_can_view_parent_engagement_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.engagement.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Parent Engagement Analytics Dashboard');
    }

    public function test_can_view_financial_forecasting_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.finance.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Financial Forecasting & Cash Flow Projection', false);
    }

    public function test_can_view_academic_benchmarking_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.benchmarking.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Academic Benchmarking & National Comparison Engine', false);
    }
}
