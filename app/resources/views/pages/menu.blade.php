@extends('layouts.site')
@section('content')
@include('partials.catalog', ['type' => 'product'])
<section class="flavours section-pad"><div class="container"><span class="eyebrow"><x-tr en="FIND YOUR FAVOURITE" mr="तुमची आवडती चव शोधा" hi="अपना पसंदीदा स्वाद खोजें" /></span><h2 class="title"><x-tr en="A flavour for every craving" mr="प्रत्येकाची आवडती चव" hi="हर पसंद के लिए एक स्वाद" /></h2><p class="sub"><x-tr en="Availability varies by outlet. Ask the team about your favourite." mr="शाखेनुसार उपलब्धता बदलते. तुमच्या आवडत्या चवीबद्दल विचारा." hi="उपलब्धता शाखा के अनुसार बदलती है। अपने पसंदीदा स्वाद के बारे में पूछें।" /></p><div class="flavour-row" id="flavours"></div></div></section>
@endsection
