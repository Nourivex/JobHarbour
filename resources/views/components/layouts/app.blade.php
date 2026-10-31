@props(['title' => null, 'heroNav' => false])
@include('layouts.app', ['title' => $title, 'heroNav' => $heroNav, 'slot' => $slot])
