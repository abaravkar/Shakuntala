@extends('layouts.site')
@section('content')
<main><section class="page-hero"><div class="container"><span class="eyebrow">PARTNER WITH SHAKUNTALA</span><h1><x-tr en="Distribution application" mr="वितरण अर्ज" hi="वितरण आवेदन" /></h1><p><x-tr en="Tell us about your territory and business in three simple steps." mr="तीन सोप्या टप्प्यांत कार्यक्षेत्र व व्यवसायाची माहिती द्या." hi="तीन आसान चरणों में क्षेत्र और व्यवसाय की जानकारी दें।" /></p></div></section><section class="inner-section"><div class="container">@include('partials.distribution-wizard', ['kind'=>'distribution'])<a class="text-link" href="/partners/onboarding"><x-tr en="View onboarding stages" mr="ऑनबोर्डिंग टप्पे पाहा" hi="ऑनबोर्डिंग चरण देखें" /> ↗</a></div></section></main>
@endsection
