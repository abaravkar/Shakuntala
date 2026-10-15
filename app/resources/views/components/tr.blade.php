@props(['en', 'mr', 'hi'])
<span data-en="{{ $en }}" data-mr="{{ $mr }}" data-hi="{{ $hi }}">{{ ['en'=>$en,'mr'=>$mr,'hi'=>$hi][$locale ?? 'mr'] }}</span>
