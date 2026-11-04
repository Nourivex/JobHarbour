@props(['title' => null, 'active' => null, 'crumb' => null])

@include('layouts.admin', ['title' => $title, 'active' => $active, 'crumb' => $crumb, 'slot' => $slot])
