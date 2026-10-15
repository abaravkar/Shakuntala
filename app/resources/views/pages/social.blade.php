@extends('layouts.site')
@section('content')
<main><section class="page-hero"><div class="container"><h1><x-tr en="Follow the sweetness" mr="गोड क्षणांशी जोडलेले राहा" hi="मीठे पलों से जुड़े रहें" /></h1><p><x-tr en="Brand stories, new flavours and happy moments." mr="ब्रँडच्या कथा, नव्या चवी आणि आनंदाचे क्षण." hi="ब्रांड की कहानियाँ, नए स्वाद और खुशियों के पल।" /></p></div></section>
<section class="inner-section"><div class="container catalog-grid">@foreach($items->where('type','social') as $item)<article class="social-card"><h2>{{ $item->translated('title',$locale) }}</h2>@if($item->status==='published' && $item->url)<a class="inner-btn" href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"><x-tr en="Visit profile" mr="प्रोफाइल पाहा" hi="प्रोफ़ाइल देखें" /> ↗</a>@else<p><x-tr en="Official profile link coming soon." mr="अधिकृत प्रोफाइल लिंक लवकरच." hi="आधिकारिक प्रोफ़ाइल लिंक जल्द।" /></p>@endif</article>@endforeach</div></section></main>
@endsection
