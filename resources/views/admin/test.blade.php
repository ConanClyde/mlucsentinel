@extends('layouts.app')

@section('page-title', 'Test Admin Dashboard')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
        Test Admin Dashboard
    </h1>
    <p class="text-[#706f6c] dark:text-[#A1A09A]">
        This is a test to see if the content is displaying properly.
    </p>
    <p class="text-[#706f6c] dark:text-[#A1A09A] mt-2">
        User: {{ Auth::user()->name }} ({{ Auth::user()->user_type->label() }})
    </p>
</div>
@endsection
