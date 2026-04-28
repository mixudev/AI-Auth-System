@extends('layouts.app-dashboard')

@section('title', 'User Management')
@section('page-title', 'Users')
@section('page-sub', 'Kelola, monitor, dan kontrol akses semua pengguna sistem')

@section('content')

{{-- TOOLBAR --}}
@include('identity::users.partials.toolbar')

{{-- STAT CARDS --}}
@include('identity::users.partials.stats')

{{-- FILTER & SEARCH BAR --}}
@include('identity::users.partials.filters')

{{-- BULK ACTION BAR --}}
@include('identity::users.partials.bulk_bar')

{{-- TABLE --}}
@include('identity::users.table')

{{-- MODALS --}}
@include('identity::users.modal_create')
@include('identity::users.modal_edit')
@include('identity::users.modal_block')

{{-- SCRIPTS --}}
@include('identity::users.partials.scripts')

@endsection