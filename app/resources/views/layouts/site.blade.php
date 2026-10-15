<!doctype html>
<html lang="{{ $locale }}">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="theme-color" content="#15110f">
 <link rel="icon" type="image/png" href="/assets/favicon.png">
 <link rel="apple-touch-icon" href="/assets/favicon.png">
 <meta name="referrer" content="strict-origin-when-cross-origin">
 @if($page === 'application-status')<meta name="robots" content="noindex,nofollow">@endif
 <title>{{ ucfirst($page === 'index' ? 'Home' : $page) }} · Shakuntala Rabdi Mastani</title>
 <meta name="description" content="Shakuntala Rabdi Mastani — menus, outlets, franchise and distribution enquiries. शुद्धतेचा मधुर गोडवा.">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $locale }} {{ $page === 'index' ? 'homepage' : 'inner-page' }}">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header"><div class="container header-row">
 <a class="brand" href="/"><img class="website-logo" src="/assets/website-logo.png" width="2172" height="724" alt="Shakuntala Rabdi Mastani"></a>
 <button type="button" id="mobileToggle" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">☰</button>
 <nav id="site-nav" aria-label="Main navigation">
 @foreach(['about'=>['Our story','आमची कहाणी','हमारी कहानी'],'menu'=>['Menu','मेनू','मेन्यू'],'locations'=>['Locations','शाखा','शाखाएँ'],'franchise'=>['Franchise','फ्रँचायझी','फ्रेंचाइज़ी'],'distribution'=>['Distribution','वितरण','वितरण'],'moments'=>['Moments','क्षण','पल'],'blog'=>['Blog','ब्लॉग','ब्लॉग'],'contact'=>['Contact','संपर्क','संपर्क']] as $slug=>$label)
 <a href="/{{ $slug }}" @if($page === $slug || ($slug === 'distribution' && $page === 'distribution-application') || ($slug === 'franchise' && in_array($page, ['application','agreement','onboarding']))) aria-current="page" @endif><x-tr :en="$label[0]" :mr="$label[1]" :hi="$label[2]" /></a>
 @endforeach
 </nav>
 <div class="langs" aria-label="Language"><button data-setlang="mr" aria-pressed="{{ $locale==='mr'?'true':'false' }}">मराठी</button><button data-setlang="hi" aria-pressed="{{ $locale==='hi'?'true':'false' }}">हिंदी</button><button data-setlang="en" aria-pressed="{{ $locale==='en'?'true':'false' }}">EN</button></div>
</div></header>
<div id="main-content">@yield('content')</div>
<section class="social-strip container"><x-tr en="Follow the sweetness" mr="गोड क्षणांशी जोडलेले राहा" hi="मीठे पलों से जुड़े रहें" /> · <a href="/social"><x-tr en="Social channels" mr="सोशल चॅनेल" hi="सोशल चैनल" /></a> · <a href="/instagram">Instagram Gallery ↗</a></section>
<footer class="inner-footer"><div class="container"><div class="footer-grid">
<div><a class="brand" href="/"><img class="website-logo" src="/assets/website-logo.png" width="2172" height="724" alt="Shakuntala Rabdi Mastani"></a><p class="footer-note"><x-tr en="The sweet taste of Baramati, made for moments worth sharing." mr="बारामतीचा गोडवा, आपल्या गोड क्षणांसाठी." hi="बारामती की मिठास, आपके मीठे पलों के लिए।" /></p></div>
<div><h3><x-tr en="Explore Shakuntala" mr="शकुंतला जाणून घ्या" hi="शकुंतला को जानें" /></h3><nav aria-label="Footer navigation"><a href="/about"><x-tr en="Our story" mr="आमची कहाणी" hi="हमारी कहानी" /></a><a href="/menu"><x-tr en="Menu" mr="मेनू" hi="मेन्यू" /></a><a href="/locations"><x-tr en="Locations" mr="शाखा" hi="शाखाएँ" /></a><a href="/moments"><x-tr en="Moments" mr="क्षण" hi="पल" /></a><a href="/franchise"><x-tr en="Franchise" mr="फ्रँचायझी" hi="फ्रेंचाइज़ी" /></a><a href="/distribution"><x-tr en="Distribution" mr="वितरण" hi="वितरण" /></a></nav></div>
<div><h3><x-tr en="Let’s make it sweet" mr="चला, गोड बोलूया" hi="आइए, मीठी बात करें" /></h3><a href="tel:+917798418380">+91 77984 18380</a><p class="footer-note"><x-tr en="Menu, outlets, celebrations and business enquiries." mr="मेनू, शाखा, उत्सव आणि व्यवसाय चौकशी." hi="मेन्यू, शाखाओं, उत्सव और व्यवसाय की पूछताछ।" /></p><a href="/contact"><x-tr en="Get in touch" mr="संपर्क साधा" hi="संपर्क करें" /> ↗</a></div>
</div><div class="footer-bottom"><span>© {{ date('Y') }} Shakuntala Rabdi Mastani</span><div class="footer-signoff">@include('partials.footer-icons')<span>शुद्धतेचा मधुर गोडवा</span></div></div></div></footer>

</body></html>


