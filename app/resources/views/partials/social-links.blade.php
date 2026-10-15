<div class="social-links" aria-label="Social media">
@foreach($items->where('type','social') as $social)
@if($social->status === 'published' && $social->url)
<a class="social-link" href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"><span aria-hidden="true" class="social-icon">{{ match($social->slug) {'instagram'=>'◎','facebook'=>'f','youtube'=>'▶','linkedin'=>'in',default=>'↗'} }}</span>{{ $social->translated('title', $locale) }} <span aria-hidden="true">↗</span></a>
@else
<a class="social-link" href="{{ match($social->slug) {'instagram'=>'https://www.instagram.com/','facebook'=>'https://www.facebook.com/','youtube'=>'https://www.youtube.com/','linkedin'=>'https://www.linkedin.com/',default=>'/social'} }}" target="_blank" rel="noopener noreferrer"><span aria-hidden="true" class="social-icon">{{ match($social->slug) {'instagram'=>'◎','facebook'=>'f','youtube'=>'▶','linkedin'=>'in',default=>'↗'} }}</span>{{ $social->translated('title', $locale) }} <span aria-hidden="true">↗</span></a>
@endif
@endforeach
</div>
