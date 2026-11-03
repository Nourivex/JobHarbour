@props(['title' => null, 'active' => null, 'crumb' => null])

@include('layouts.workspace', ['title' => $title, 'active' => $active, 'crumb' => $crumb, 'slot' => $slot])
