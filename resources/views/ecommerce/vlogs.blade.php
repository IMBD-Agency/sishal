@extends('ecommerce.master')

@section('main-section')
    <!-- Page Header -->
    <div class="page-header-premium mb-5">
        <div class="container container-custom">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="page-title-main text-white mb-0">Visual Stories</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Visual Stories</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <form method="GET" class="d-flex justify-content-lg-end">
                        <select name="sort" class="form-select w-auto filter-select-premium" onchange="this.form.submit()">
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Featured</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .page-header-premium {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        .page-header-premium::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: var(--primary-color, #7fad39);
            filter: blur(150px);
            opacity: 0.1;
        }
        .page-title-main {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            letter-spacing: -1px;
        }
        .filter-select-premium {
            background-color: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: white !important;
            border-radius: 12px !important;
            padding: 10px 20px !important;
        }
        .filter-select-premium option {
            background-color: #111827;
            color: white;
        }
    </style>

    <section class="py-5">
        <div class="container">
            <div class="row g-4 mb-4">
                @forelse($vlogs as $vlog)
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="ratio ratio-16x9">
                                {!! $vlog->frame_code !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">No vlogs found.</div>
                @endforelse
            </div>

            @if($vlogs->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $vlogs->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </section>
@endsection

