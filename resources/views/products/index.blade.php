@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('product.index') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control"
                        value="{{ request()->has('title') ? request()->get('title') : '' }}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">Select A Variant</option>
                        @foreach ($variants as $variant)
                            @if ($variant->productVariants != null)
                                <optgroup label="{{ $variant->title }}">
                                    @foreach ($variant->productVariants as $product_variant)
                                        <option value="{{ $product_variant->id }}"
                                            {{ request()->has('variant') && request()->get('variant') == $product_variant->id ? 'selected' : '' }}>
                                            {{ $product_variant->variant }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                            class="form-control"
                            value="{{ request()->has('price_from') ? request()->get('price_from') : '' }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control"
                            value="{{ request()->has('price_to') ? request()->get('price_to') : '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control"
                        value="{{ request()->has('date') ? request()->get('date') : '' }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
                @if (request()->has('date') ||
                        request()->has('title') ||
                        request()->has('price_from') ||
                        request()->has('price_to') ||
                        request()->has('variant'))
                    <div class="col-md-1">
                        <a href="{{ route('product.index') }}" class="btn btn-danger">Clear</a>
                    </div>
                @endif
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Variant</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if ($products->count() > 0)
                            @foreach ($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->title }} <br> Created at :
                                        {{ $product->created_at->format('d-M-Y') }}</td>
                                    <td class="w-25">{{ $product->description }}</td>
                                    <td>
                                        @if ($product->variantPrices != null)
                                            @foreach ($product->variantPrices as $product_variant)
                                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">

                                                    <dt class="col-sm-3 pb-0 text-capitalize">
                                                        @if ($product_variant->productVariantOne != null)
                                                            {{ $product_variant->productVariantOne->variant }}
                                                        @endif
                                                        @if ($product_variant->productVariantTwo != null)
                                                            / {{ $product_variant->productVariantTwo->variant }}
                                                        @endif
                                                        @if ($product_variant->productVariantThree != null)
                                                            / {{ $product_variant->productVariantThree->variant }}
                                                        @endif

                                                    </dt>
                                                    <dd class="col-sm-9">
                                                        <dl class="row mb-0">
                                                            <dt class="col-sm-4 pb-0">Price :
                                                                {{ number_format($product_variant->price, 2) }}
                                                            </dt>
                                                            <dd class="col-sm-8 pb-0">InStock :
                                                                {{ number_format($product_variant->stock, 2) }}
                                                            </dd>
                                                        </dl>
                                                    </dd>
                                                </dl>
                                            @endforeach

                                            <button onclick="$('#variant').toggleClass('h-auto')"
                                                class="btn btn-sm btn-link">Show more</button>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('product.edit', $product->id) }}"
                                                class="btn btn-success">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <p class="alert alert-danger">{{ __('No product Found') }}</p>
                        @endif

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $products->firstItem() }} to
                        {{ $products->lastItem() }} out of
                        {{ $products->total() }}</p>
                </div>
                <div class="col-md-2">
                    {!! $products->withQueryString()->links('pagination::bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@endsection
