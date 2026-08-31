@extends('core::layouts.admin')

@section('title', __('catalog::catalog.specialty_lead.index'))
@section('page-title', __('catalog::catalog.specialty_lead.index'))

@section('content')
{!! $table !!}
@endsection
