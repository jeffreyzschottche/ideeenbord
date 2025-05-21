<h1>Gefeliciteerd!</h1>
<p>Je hebt de quiz "{{ $quiz->title }}" gewonnen van het merk "{{ $quiz->brand->title }}".</p>
<p>🎁 Prijs: {{ $quiz->prize ?? 'onbekend' }}</p>
<p>Bedankt voor je deelname!</p>
