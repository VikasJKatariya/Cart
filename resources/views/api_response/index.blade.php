@extends('layouts.app')
@section('content')
@section('pageTitle', 'API')
@foreach($data->toArray() as $as)
<div><div><div class="highlight-code"><pre class="example microlight"><span style="">{</span><span style="color: #555; font-weight: bold;">
  </span><span style="color: #555;">"sku_id"</span><span style="">:</span><span style="color: #555; font-weight: bold;"> </span><span style="color: #555; font-weight: bold;">{{$as['sku_id']}}</span><span style="">,</span><span style="color: #555; font-weight: bold;">
  </span><span style="color: #555;">"sum"</span><span style="">:</span><span style="color: #555; font-weight: bold;"> </span><span style="color: #555;">{{ $as['sum'] }}</span><span style="color: #555; font-weight: bold;"></span><span style="">
}</span></pre></div></div></div>
@endforeach
@endsection