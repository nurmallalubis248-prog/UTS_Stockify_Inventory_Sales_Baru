@php
$paths = [
  'home'      => 'M3 11l9-8 9 8M5 10v10h14V10',
  'box'       => 'M21 8l-9-5-9 5v8l9 5 9-5V8zM3 8l9 5 9-5M12 13v8',
  'alert'     => 'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z',
  'tag'       => 'M20 12l-8 8-9-9V3h8l9 9zM7.5 7.5h.01',
  'truck'     => 'M1 5h13v11H1zM14 9h4l4 3.5V16h-8zM6 20a2 2 0 100-4 2 2 0 000 4zM18 20a2 2 0 100-4 2 2 0 000 4z',
  'purchase'  => 'M12 3v12m0 0l-4-4m4 4l4-4M4 17v3h16v-3',
  'sale'      => 'M12 21V9m0 0l-4 4m4-4l4 4M4 7V4h16v3',
  'users'     => 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.9M16 3.1a4 4 0 010 7.8',
  'logout'    => 'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9',
];
@endphp
<svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $paths[$name] ?? '' }}"/></svg>
