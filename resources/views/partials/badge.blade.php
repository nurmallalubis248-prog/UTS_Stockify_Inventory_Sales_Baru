@php
$map = [
  'completed' => ['ok', 'Selesai'],
  'confirmed' => ['ok', 'Terkonfirmasi'],
  'cancelled' => ['bad', 'Dibatalkan'],
];
[$tone, $label] = $map[$status] ?? ['', ucfirst($status)];
@endphp
<span class="badge {{ $tone }}">{{ $label }}</span>
