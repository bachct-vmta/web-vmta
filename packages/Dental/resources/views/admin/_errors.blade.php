@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
@endif
