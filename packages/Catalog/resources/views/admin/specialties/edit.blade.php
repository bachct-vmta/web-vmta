@extends('core::layouts.admin')

@section('title', __('catalog::catalog.specialty.edit'))
@section('page-title', __('catalog::catalog.specialty.edit'))

@section('content')
@php($mode = 'edit')
@include('catalog::admin.specialties._fields')
@endsection
