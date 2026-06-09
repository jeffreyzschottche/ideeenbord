@extends('emails.layout', ['title' => 'Je hebt gewonnen!'])

@section('content')
    <h1 style="margin:0 0 16px; font-size:24px; color:#1f2937;">Gefeliciteerd! 🎉</h1>
    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
        Je hebt de quiz <strong>“{{ $quiz->title }}”</strong> gewonnen van het merk
        <strong>{{ $quiz->brand->title }}</strong>.
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td style="background-color:#f3f4f6; border-left:4px solid #f78a1d; border-radius:8px; padding:16px;">
                <span style="font-size:13px; color:#6b7280;">Jouw prijs</span><br>
                <span style="font-size:18px; font-weight:bold; color:#1f2937;">🎁 {{ $quiz->prize ?? 'Wordt bekendgemaakt' }}</span>
            </td>
        </tr>
    </table>
    <p style="margin:0; font-size:14px; line-height:1.6; color:#6b7280;">
        Bedankt voor je deelname — blijf ideeën delen en maak vaker kans!
    </p>
@endsection
