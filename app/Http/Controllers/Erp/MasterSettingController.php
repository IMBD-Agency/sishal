<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ProductServiceCategory;
use App\Models\Brand;
use App\Models\Season;
use App\Models\Gender;
use App\Models\Unit;
use App\Models\VariationAttribute;
use Illuminate\Http\Request;

class MasterSettingController extends Controller
{
    public function index()
    {
        $stats = [
            'categories' => ProductServiceCategory::count(),
            'brands' => Brand::count(),
            'seasons' => Season::count(),
            'genders' => Gender::count(),
            'units' => Unit::count(),
            'attributes' => VariationAttribute::count(),
        ];

        return view('erp.master-settings.index', compact('stats'));
    }
}
