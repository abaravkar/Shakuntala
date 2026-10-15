@extends('layouts.admin')
@section('content')
<section class="admin-panel"><h1>Franchise models</h1><p>Edit model details, product lines and cost breakdowns. Hide a model by saving it as draft.</p><a class="inner-btn" href="/admin/franchise-models/new">Add model +</a><a class="inner-btn secondary" href="/franchise">View franchise page ↗</a><ul class="admin-list">@foreach($models as $model)<li><a href="/admin/franchise-models/{{ $model->id }}/edit"><b>{{ $model->name }}</b> ↗</a><p>{{ $model->space }} · {{ $model->investment }} · {{ $model->published ? 'Published' : 'Draft' }} · Order {{ $model->sort_order }}</p></li>@endforeach</ul></section>
@endsection
