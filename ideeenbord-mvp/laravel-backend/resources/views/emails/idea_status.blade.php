@extends('emails.layout', ['title' => 'Statusupdate voor jouw idee'])

@section('content')
    <h1 style="margin:0 0 16px; font-size:22px; color:#1f2937;">Statusupdate voor jouw idee</h1>
    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
        Je idee <strong>“{{ $idea->title }}”</strong> heeft een nieuwe status gekregen:
    </p>
    <p style="margin:0 0 20px;">
        <span style="display:inline-block; background-color:#f78a1d; color:#ffffff; font-weight:bold; padding:8px 16px; border-radius:999px; font-size:14px;">
            {{ ucfirst(str_replace('_', ' ', $idea->status)) }}
        </span>
    </p>
    @if($idea->description)
        <p style="margin:0; font-size:14px; line-height:1.6; color:#6b7280;">
            {{ $idea->description }}
        </p>
    @endif
@endsection
