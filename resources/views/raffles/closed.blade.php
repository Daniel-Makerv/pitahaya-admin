@extends('raffles.layout')

@section('title', 'Rifa cerrada')

@section('content')
    <div class="card-body center" style="padding-top: 40px">
        <div class="eyebrow" style="color: var(--muted)">Rifa cerrada</div>
        <h1>{{ $raffle->name }}</h1>
        <p>Esta rifa ya se sorteó y no recibe más participantes.</p>
    </div>
@endsection
