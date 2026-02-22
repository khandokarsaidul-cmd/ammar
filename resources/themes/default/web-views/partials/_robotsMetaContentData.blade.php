@if(!isset($product))
    {{-- SEO Meta --}}
    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_title)
        <title>{{ $robotsMetaContentData?->meta_title }}</title>
        <meta name="title" content="{{ $robotsMetaContentData?->meta_title }}">
        <meta property="og:title" content="{{ $robotsMetaContentData?->meta_title }}">
        <meta name="twitter:title" content="{{ $robotsMetaContentData?->meta_title }}">
    @else
        <meta name="title" content="{{ $web_config['company_name'] }}">
        <meta property="og:title" content="{{ $web_config['company_name'] }}">
        <meta name="twitter:title" content="{{ $web_config['company_name'] }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_description)
        <meta name="description" content="{{ $robotsMetaContentData?->meta_description }}">
        <meta property="og:description" content="{{ $robotsMetaContentData?->meta_description }}">
        <meta name="twitter:description" content="{{ $robotsMetaContentData?->meta_description }}">
    @else
        @php
            $desc = substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160);
        @endphp
        <meta name="description" content="{{ $desc }}">
        <meta property="og:description" content="{{ $desc }}">
        <meta name="twitter:description" content="{{ $desc }}">
    @endif

    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta name="twitter:url" content="{{ env('APP_URL') }}">

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_image_full_url['path'])
        <meta property="og:image" content="{{ $robotsMetaContentData?->meta_image_full_url['path'] }}">
        <meta name="twitter:image" content="{{ $robotsMetaContentData?->meta_image_full_url['path'] }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}">
        <meta name="twitter:image" content="{{ $web_config['web_logo']['path'] }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->canonicals_url)
        <link rel="canonical" href="{{ $robotsMetaContentData?->canonicals_url }}">
    @endif

    {{-- Robots Meta Directives --}}
    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->index != 'noindex')
        <meta name="robots" content="index">
    @endif

    @if(isset($robotsMetaContentData) && ($robotsMetaContentData?->no_follow || $robotsMetaContentData?->no_image_index || $robotsMetaContentData?->no_archive || $robotsMetaContentData?->no_snippet))
        <meta name="robots" content="{{ ($robotsMetaContentData?->no_follow ? 'nofollow' : '') . ($robotsMetaContentData?->no_image_index ? ' noimageindex' : '') . ($robotsMetaContentData?->no_archive ? ' noarchive' : '') . ($robotsMetaContentData?->no_snippet ? ' nosnippet' : '') }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_max_snippet)
        <meta name="robots" content="max-snippet:{{ $robotsMetaContentData?->max_snippet_value }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->max_video_preview)
        <meta name="robots" content="max-video-preview:{{ $robotsMetaContentData?->max_video_preview_value }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->max_image_preview)
        <meta name="robots" content="max-image-preview:{{ $robotsMetaContentData?->max_image_preview_value }}">
    @endif
@else
    {{-- Structured Data for Product --}}
    <div itemscope itemtype="https://schema.org/Product">
     
        <meta itemprop="brand" content="{{ $web_config['company_name'] }}">

        <div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <meta itemprop="url" content="{{ url()->current() }}">
            <meta itemprop="priceCurrency" content="BDT"> 
            <meta itemprop="availability" content="https://schema.org/InStock"> {{-- Update dynamically if needed --}}
            <meta itemprop="itemCondition" content="https://schema.org/NewCondition">
        </div>

    </div>
@endif
