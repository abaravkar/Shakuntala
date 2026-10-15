@if(session('saved'))
<div class="notice success" role="status"><x-tr en="Your details have been saved. Reference:" mr="तुमची माहिती जतन झाली. संदर्भ:" hi="आपकी जानकारी सहेज ली गई। संदर्भ:" /> <strong>{{ session('receipt') }}</strong>
@if($page === 'application')<p><x-tr en="Payment is pending. No money was charged and no agreement has been signed." mr="पेमेंट प्रलंबित आहे. शुल्क घेतलेले नाही आणि करारावर सही झालेली नाही." hi="भुगतान लंबित है। कोई शुल्क नहीं लिया गया और समझौते पर हस्ताक्षर नहीं हुए।" /></p>@endif</div>
@endif
@if($errors->any())<div class="notice error" role="alert"><x-tr en="Please correct the following fields:" mr="कृपया खालील माहिती दुरुस्त करा:" hi="कृपया ये जानकारी ठीक करें:" /><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
