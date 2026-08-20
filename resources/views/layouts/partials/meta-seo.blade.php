@php
    $metaDescription = trim($__env->yieldContent('meta_description', config('app.name').' — Portal profil pertamanan dan ruang terbuka hijau Kota Batam.'));
    $metaTitle = trim($__env->yieldContent('title')).' — '.config('app.name');
    $metaUrl = url()->current();
    $metaImage = asset('images/hero-taman-hijau.jpg');
@endphp
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $metaUrl }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta name="twitter:card" content="summary_large_image">
