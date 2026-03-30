<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductServiceCategory;
use App\Models\Vlog;
use App\Models\Banner;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    private function getSidebarCategories()
    {
        return ProductServiceCategory::where('status', 'active')
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->where('status', 'active');
            }])
            ->get();
    }

    private function getMaxProductPrice()
    {
        return Cache::remember('max_product_price', 60, function () {
            return Product::published()->max('price') ?? 0;
        });
    }

    private function attachWishlistStatus($products)
    {
        $userId = Auth::id();
        if (!$userId) return $products;

        $productIds = $products instanceof \Illuminate\Pagination\LengthAwarePaginator 
            ? $products->pluck('id')->toArray() 
            : $products->pluck('id')->toArray();
            
        $wishlistedIds = Wishlist::where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();

        foreach ($products as $product) {
            $product->is_wishlisted = in_array($product->id, $wishlistedIds);
        }

        return $products;
    }

    public function index()
    {
        $categories = $this->getSidebarCategories();

        $featuredCategories = ProductServiceCategory::where('status', 'active')
            ->has('publishedProducts')
            ->take(8)
            ->get();

        $bestDeals = Product::published()
            ->orderByRaw('CASE WHEN discount > 0 THEN 0 ELSE 1 END')
            ->orderBy('discount', 'desc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();
        $this->attachWishlistStatus($bestDeals);

        $banners = Banner::active()->where('position', 'hero')->orderBy('id', 'desc')->get();
        $vlogs = Vlog::where('is_active', 1)->orderBy('id', 'desc')->take(6)->get();
        $vlogBottomBanners = Banner::active()->where('position', 'vlogs_bottom')->orderBy('id', 'desc')->get();

        $pageTitle = 'Home';
        return view('ecommerce.home', compact('categories', 'featuredCategories', 'bestDeals', 'banners', 'vlogs', 'vlogBottomBanners', 'pageTitle'));
    }

    public function categories()
    {
        $categories = ProductServiceCategory::where('status', 'active')
            ->whereNull('parent_id')
            ->with('children')
            ->get();
            
        $pageTitle = 'All Categories';
        return view('ecommerce.categories', compact('categories', 'pageTitle'));
    }

    public function about()
    {
        $pageTitle = 'About Us';
        return view('ecommerce.about', compact('pageTitle'));
    }

    public function bestDeals(Request $request)
    {
        $categories = $this->getSidebarCategories();
        $products = Product::published()
            ->orderByRaw('CASE WHEN discount > 0 THEN 0 ELSE 1 END')
            ->orderBy('discount', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);
        $this->attachWishlistStatus($products);

        $maxProductPrice = $this->getMaxProductPrice();
        $priceMin = $request->get('price_min', 0);
        $priceMax = $request->get('price_max', $maxProductPrice);
        $selectedCategories = $request->get('categories', []);
        $selectedRatings = $request->get('rating', []);
        $selectedSort = $request->get('sort', '');

        $pageTitle = 'Best Deals';
        return view('ecommerce.best-deal', compact('products', 'categories', 'pageTitle', 'priceMin', 'priceMax', 'maxProductPrice', 'selectedCategories', 'selectedRatings', 'selectedSort'));
    }

    public function featured(Request $request)
    {
        $categories = $this->getSidebarCategories();
        $products = Product::published()
            ->where('is_featured', 1)
            ->paginate(20);
        $this->attachWishlistStatus($products);

        $maxProductPrice = $this->getMaxProductPrice();
        $priceMin = $request->get('price_min', 0);
        $priceMax = $request->get('price_max', $maxProductPrice);
        $selectedCategories = $request->get('categories', []);
        $selectedRatings = $request->get('rating', []);
        $selectedSort = $request->get('sort', '');

        $pageTitle = 'Featured Products';
        return view('ecommerce.featured', compact('products', 'categories', 'pageTitle', 'priceMin', 'priceMax', 'maxProductPrice', 'selectedCategories', 'selectedRatings', 'selectedSort'));
    }

    public function contact()
    {
        $pageTitle = 'Contact Us';
        return view('ecommerce.contact', compact('pageTitle'));
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);
        
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function privacy()
    {
        $page = \App\Models\AdditionalPage::where('slug', 'privacy-policy')->firstOrFail();
        $pageTitle = $page->title;
        return view('ecommerce.additionalPage', compact('page', 'pageTitle'));
    }

    public function terms()
    {
        $page = \App\Models\AdditionalPage::where('slug', 'terms-conditions')->firstOrFail();
        $pageTitle = $page->title;
        return view('ecommerce.additionalPage', compact('page', 'pageTitle'));
    }

    public function shipping()
    {
        $pageTitle = 'Shipping Policy';
        return view('ecommerce.shipping', compact('pageTitle'));
    }

    public function faqs()
    {
        $pageTitle = 'Frequently Asked Questions';
        return view('ecommerce.faqs', compact('pageTitle'));
    }

    public function vlogs(Request $request)
    {
        $vlogs = Vlog::where('is_active', 1)->orderBy('id', 'desc')->paginate(12);
        $pageTitle = 'Collections';
        $sort = $request->get('sort', 'latest');
        return view('ecommerce.vlogs', compact('vlogs', 'pageTitle', 'sort'));
    }

    public function additionalPage($slug)
    {
        $page = \App\Models\AdditionalPage::where('slug', $slug)->firstOrFail();
        $pageTitle = $page->title;
        return view('ecommerce.additionalPage', compact('page', 'pageTitle'));
    }

    public function categoryWiseProducts(Request $request)
    {
        $categories = $this->getSidebarCategories();
        $category = ProductServiceCategory::where('slug', $request->slug)->firstOrFail();
        $allCategoryIds = ProductServiceCategory::getAllChildIdsForCategories([$category->id]);
        
        $products = Product::published()
            ->whereIn('category_id', $allCategoryIds)
            ->paginate(20);
        $this->attachWishlistStatus($products);

        $maxProductPrice = $this->getMaxProductPrice();
        $priceMin = $request->get('price_min', 0);
        $priceMax = $request->get('price_max', $maxProductPrice);
        $selectedCategories = [$category->slug];
        $selectedRatings = $request->get('rating', []);
        $selectedSort = $request->get('sort', '');

        $pageTitle = $category->name;
        return view('ecommerce.products', compact('products', 'category', 'categories', 'pageTitle', 'priceMin', 'priceMax', 'maxProductPrice', 'selectedCategories', 'selectedRatings', 'selectedSort'));
    }

    public function productDetails($slug)
    {
        $product = Product::published()->with(['category', 'variations.stocks.warehouse', 'galleries', 'brand'])->where('slug', $slug)->firstOrFail();
        
        $userId = Auth::id();
        $product->is_wishlisted = $userId ? Wishlist::where('user_id', $userId)->where('product_id', $product->id)->exists() : false;

        $relatedProducts = Product::published()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(8)
            ->get();
        $this->attachWishlistStatus($relatedProducts);

        $pageTitle = $product->name;
        return view('ecommerce.productDetails', compact('product', 'relatedProducts', 'pageTitle'));
    }

    public function filterProducts(Request $request)
    {
        return $this->products($request);
    }

    public function products(Request $request)
    {
        $categories = $this->getSidebarCategories();
        $query = Product::published();

        // Get current filter values
        $maxProductPrice = $this->getMaxProductPrice();
        $priceMin = $request->get('price_min', 0);
        $priceMax = $request->get('price_max', $maxProductPrice);
        $selectedCategories = $request->get('categories', []);
        $selectedRatings = $request->get('rating', []);
        $selectedSort = $request->get('sort', '');

        // Category filter - include child categories
        if ($request->has('categories') && is_array($request->categories) && count($request->categories)) {
            $categoryIds = ProductServiceCategory::whereIn('slug', $request->categories)->pluck('id')->toArray();
            $allCategoryIds = ProductServiceCategory::getAllChildIdsForCategories($categoryIds);
            $query->whereIn('category_id', $allCategoryIds);
        } elseif ($request->has('category') && $request->category) {
            $categorySlug = $request->category;
            $selectedCategories = [$categorySlug];
            $categorySub = ProductServiceCategory::where('slug', $categorySlug)->first();
            if ($categorySub) {
                $allCategoryIds = ProductServiceCategory::getAllChildIdsForCategories([$categorySub->id]);
                if (!empty($allCategoryIds)) {
                    $query->whereIn('category_id', $allCategoryIds);
                }
            }
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        switch ($selectedSort) {
            case 'newest':
                $query->orderBy('id', 'desc');
                break;
            case 'price_low':
            case 'lowToHigh':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
            case 'highToLow':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $products = $query->paginate(12);
        $this->attachWishlistStatus($products);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('ecommerce.partials.product-grid', ['products' => $products, 'hidePagination' => true])->render(),
                'hasMore' => $products->hasMorePages(),
                'currentPage' => $products->currentPage(),
                'max_price' => $maxProductPrice
            ]);
        }

        $pageTitle = 'Products';
        return view('ecommerce.products', compact('products', 'categories', 'priceMin', 'priceMax', 'maxProductPrice', 'selectedCategories', 'selectedRatings', 'selectedSort', 'pageTitle'));
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $categories = $this->getSidebarCategories();
        $query = Product::published();

        if ($q) {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        $maxProductPrice = $this->getMaxProductPrice();
        $products = $query->paginate(20);
        $this->attachWishlistStatus($products);

        $pageTitle = 'Search Results for "' . $q . '"';
        
        $priceMin = $request->get('price_min', 0);
        $priceMax = $request->get('price_max', $maxProductPrice);
        $selectedCategories = [];
        $selectedRatings = [];
        $selectedSort = '';

        return view('ecommerce.products', compact('products', 'categories', 'pageTitle', 'priceMin', 'priceMax', 'maxProductPrice', 'selectedCategories', 'selectedRatings', 'selectedSort'));
    }

    public function searchSuggestions(Request $request)
    {
        $search = $request->get('q');
        $products = Product::published()
            ->where('name', 'LIKE', "%{$search}%")
            ->take(8)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => asset($product->image),
                'url' => route('product.details', $product->slug)
            ];
        }

        return response()->json($results);
    }
}
