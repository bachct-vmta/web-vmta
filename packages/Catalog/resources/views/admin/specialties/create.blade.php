@extends('core::layouts.admin')

@section('title', __('catalog::catalog.specialty.create'))
@section('page-title', __('catalog::catalog.specialty.create'))

@section('content')
@php($mode = 'create')
@include('catalog::admin.specialties._fields')
@endsection
