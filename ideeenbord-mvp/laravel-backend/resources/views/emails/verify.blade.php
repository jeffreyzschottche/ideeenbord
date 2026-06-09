@extends('emails.layout', ['title' => $heading ?? 'Verifieer je e-mailadres'])

@section('content')
    <h1 style="margin:0 0 16px; font-size:22px; color:#1f2937;">{{ $heading ?? 'Verifieer je e-mailadres' }}</h1>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#374151;">
        {{ $intro ?? 'Klik op de knop hieronder om je e-mailadres te bevestigen en je account te activeren.' }}
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td style="border-radius:12px; background:linear-gradient(180deg,#fb923c,#ea580c);">
                <a href="{{ $url }}"
                   style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:12px;">
                    {{ $button ?? 'Verifieer e-mailadres' }}
                </a>
            </td>
        </tr>
    </table>
    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">
        Werkt de knop niet? Kopieer dan deze link in je browser:
    </p>
    <p style="margin:0 0 20px; font-size:12px; word-break:break-all;">
        <a href="{{ $url }}" style="color:#f78a1d;">{{ $url }}</a>
    </p>
    <p style="margin:0; font-size:13px; color:#9ca3af;">
        {{ $outro ?? 'Heb je geen account aangemaakt? Dan kun je deze e-mail negeren.' }}
    </p>
@endsection
