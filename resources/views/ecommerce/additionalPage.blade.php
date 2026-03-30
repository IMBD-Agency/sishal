@extends('ecommerce.master')

@section('main-section')
    <!-- Page Header -->
    <div class="page-header-premium mb-5">
        <div class="container container-custom">
            <h1 class="page-title-main text-white mb-0">{{ $page->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="pb-100">
        <div class="container container-custom">
            <div class="card border-0 shadow-premium overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="content-wrapper-premium">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

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

        .shadow-premium {
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            border-radius: 24px;
        }

        .content-wrapper-premium {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            line-height: 1.8;
            color: #4b5563;
        }

        .content-wrapper-premium h1, 
        .content-wrapper-premium h2, 
        .content-wrapper-premium h3 {
            color: #111827;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-family: 'Outfit', sans-serif;
        }

        .content-wrapper-premium p {
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }

        .content-wrapper-premium ul {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .content-wrapper-premium li {
            margin-bottom: 0.5rem;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255,255,255,0.3);
        }

        @media (max-width: 768px) {
            .page-header-premium {
                padding: 60px 0;
            }
            .page-title-main {
                font-size: 2rem;
            }
        }
    </style>
@endsection