<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class WakafUtilizationReport extends Model {
    use HasFactory;
    protected $fillable = ['wakaf_asset_id', 'report_year', 'income_generated', 'maintenance_cost', 'net_benefit', 'impact_description', 'reported_by'];
    protected $casts = ['income_generated' => 'decimal:2', 'maintenance_cost' => 'decimal:2', 'net_benefit' => 'decimal:2'];
}
