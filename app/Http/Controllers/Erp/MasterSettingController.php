<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ProductServiceCategory;
use App\Models\Brand;
use App\Models\Season;
use App\Models\Gender;
use App\Models\Unit;
use App\Models\VariationAttribute;
use App\Models\Attribute;
use Illuminate\Http\Request;

class MasterSettingController extends Controller
{
    public function index()
    {
        $stats = [
            'categories' => ProductServiceCategory::whereNull('parent_id')->count(),
            'subcategories' => ProductServiceCategory::whereNotNull('parent_id')->count(),
            'brands' => Brand::count(),
            'seasons' => Season::count(),
            'genders' => Gender::count(),
            'units' => Unit::count(),
            'variation_attributes' => VariationAttribute::count(),
            'attributes' => Attribute::count(),
        ];

        return view('erp.master-settings.index', compact('stats'));
    }
}
