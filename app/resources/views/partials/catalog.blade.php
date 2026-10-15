<main><section class="page-hero"><div class="container"><span class="pill">शुद्धतेचा मधुर गोडवा</span><h1>
@if($type==='product')<x-tr en="Our sweet delights" mr="आमचे गोड प्रकार" hi="हमारे मीठे स्वाद" />
@elseif($type==='branch')<x-tr en="Find Shakuntala near you" mr="तुमच्या जवळची शकुंतला" hi="आपके पास शकुंतला" />
@else<x-tr en="Stories and updates" mr="कथा आणि बातम्या" hi="कहानियाँ और समाचार" />@endif
</h1><p><x-tr en="From Baramati, with sweetness." mr="बारामतीचा गोडवा, तुमच्यासाठी." hi="बारामती की मिठास, आपके लिए।" /></p></div></section>
<section class="inner-section"><div class="container">
@if($type==='product')<p class="notice"><x-tr en="Indicative starting prices from supplied menus; confirm prices and availability with the outlet." mr="दिलेल्या मेनूवर आधारित प्रारंभिक किंमती; शाखेत किंमत आणि उपलब्धता तपासा." hi="प्राप्त मेन्यू के अनुसार शुरुआती कीमतें; शाखा से कीमत और उपलब्धता की पुष्टि करें।" /></p>@endif
@if($type==='branch')<p class="notice"><x-tr en="Outlet details were supplied on 11 October 2026. Reported outlets and planned openings require confirmation. Closed outlets are omitted." mr="शाखेची माहिती ११ ऑक्टोबर २०२६ रोजी मिळाली. शाखेचा दर्जा व उद्घाटनाची खात्री करा. बंद शाखा दाखवलेल्या नाहीत." hi="शाखा विवरण 11 अक्टूबर 2026 को मिले। स्थिति और उद्घाटन की पुष्टि करें। बंद शाखाएँ नहीं दिखाई गई हैं।" /></p>@endif
<div class="catalog-grid">
@forelse($items->where('type',$type)->filter(fn($item)=>$type==='branch'?in_array($item->status,['reported','planned','unverified','published']):$item->status==='published') as $item)
 <article class="catalog-card {{ $type === 'product' ? 'menu-product' : '' }}">
 @if($item->image)<img src="{{ asset('assets/'.$item->image) }}" alt="{{ $item->translated('title',$locale) }}" loading="lazy">@endif
 <div class="card-copy"><h2 data-mr="{{ $item->title['mr'] }}" data-hi="{{ $item->title['hi'] }}" data-en="{{ $item->title['en'] }}">{{ $item->translated('title',$locale) }}</h2>
 <p data-mr="{{ $item->body['mr'] ?? '' }}" data-hi="{{ $item->body['hi'] ?? '' }}" data-en="{{ $item->body['en'] ?? '' }}">{{ $item->translated('body',$locale) }}</p>
 @if($type==='product')<strong>₹{{ $item->price }} <x-tr en="onwards" mr="पासून" hi="से" /></strong>@endif
 @if($type==='branch')<span class="status-chip">@switch($item->status)@case('planned')<x-tr en="Planned · date pending" mr="नियोजित · तारीख प्रलंबित" hi="प्रस्तावित · तारीख लंबित" />@break @case('unverified')<x-tr en="Status unconfirmed" mr="दर्जा अपुष्ट" hi="स्थिति अपुष्ट" />@break @case('reported')<x-tr en="Reported outlet" mr="कळवलेली शाखा" hi="बताई गई शाखा" />@break @default<x-tr en="Confirmed outlet" mr="पुष्टी केलेली शाखा" hi="पुष्ट शाखा" />@endswitch</span>@endif
 @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"><x-tr en="View details" mr="माहिती पाहा" hi="विवरण देखें" /> ↗</a>@endif
 </div></article>
@empty<div class="empty-state"><span class="eyebrow">SHAKUNTALA</span><h2><x-tr en="Good things are on their way" mr="नव्या गोड गोष्टी लवकरच" hi="नई मीठी बातें जल्द" /></h2><p><x-tr en="We’ll share our latest news and sweet moments here. In the meantime, discover your next favourite dessert." mr="नव्या बातम्या आणि गोड क्षण येथे शेअर करू. तोपर्यंत तुमची आवडती चव शोधा." hi="हम यहाँ नई खबरें और मीठे पल साझा करेंगे। तब तक अपना पसंदीदा स्वाद खोजें।" /></p><a class="inner-btn" href="/menu"><x-tr en="Explore the menu" mr="मेनू पाहा" hi="मेन्यू देखें" /> ↗</a></div>@endforelse
</div></div></section></main>
