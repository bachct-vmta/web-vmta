@props([
    'modalId',
    'modalTitle',
    'modalAttributes' => [],
    'inputField' => [],
    'addField' => false,
    'field' => null,
    'buttons' => [],
    'isForm' => false,
    'formFields' => [],
    'icon' => null,
    'slotHtml' => ''
])

<div class="nkd-modal nkd-modal-blur nkd-fade" id="{{ $modalId }}"
@if(count($modalAttributes))
    @foreach($modalAttributes as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
@endif
>
<div class="nkd-modal-dialog nkd-modal-dialog-centered">
    @if($isForm)
        <form action="{{ $formFields['action'] ?? '' }}" method="{{ $formFields['method'] ?? '' }}">
            @csrf
            @if(isset($formFields['method']) && $formFields['method'] != 'get' || $formFields['method'] != 'post')
                @method($formFields['method'])
            @endif
            <input type="hidden" name="id">
            <div class="nkd-modal-content">
                <div class="nkd-modal-header">
                    <h5 class="nkd-modal-title">
                        @if($icon)
                            {!! $icon !!}
                        @endif
                        {{ $modalTitle }}
                    </h5>
                    <button type="button" class="nkd-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="nkd-modal-body">
                    @if($addField === true)
                        {!! $field ?? '' !!}
                    @else
                        <div class="nkd-mb-3 nkd-position-relative">
                            <div class="nkd-input-group">
                                @if(!empty($inputField) && count($inputField) > 0)
                                    <x-core-media::input-field
                                        :type="$inputField['type'] ?? 'text'"
                                        :name="$inputField['name'] ?? ''"
                                        :id="$inputField['id'] ?? ''"
                                        :placeholder="$inputField['placeholder'] ?? ''"
                                        :class="$inputField['class'] ?? ''"
                                    />
                                @endif
                                @if(!empty($buttons) && count($buttons) > 0)
                                    @foreach($buttons as $button)
                                        <x-core-media::button-field
                                            :type="$button['type'] ?? 'button'"
                                            :class="$button['class'] ?? ''"
                                            :text="$button['text'] ?? ''"
                                            :attrs="$button['attrs'] ?? []"
                                            :icon="$button['icon'] ?? null"
                                        />
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                @if($addField === true)
                    <div class="nkd-modal-footer">
                        @if(!empty($buttons) && count($buttons) > 0)
                            @foreach($buttons as $button)
                                <x-core-media::button-field
                                    :type="$button['type'] ?? 'button'"
                                    :class="$button['class'] ?? ''"
                                    :text="$button['text'] ?? ''"
                                    :attrs="$button['attrs'] ?? []"
                                    :icon="$button['icon'] ?? null"
                                />
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>
        </form>
    @else
        <div class="nkd-modal-content">
            <div class="nkd-modal-header">
                <h5 class="nkd-modal-title">{{ $modalTitle }}</h5>
                <button type="button" class="nkd-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="nkd-modal-body">
                @if($addField === true)
                    {!! $field ?? '' !!}
                @else
                    <div class="nkd-mb-3 nkd-position-relative">
                        <div class="nkd-input-group">
                            @if(!empty($inputField) && count($inputField) > 0)
                                <x-core-media::input-field
                                    :type="$inputField['type'] ?? 'text'"
                                    :name="$inputField['name'] ?? ''"
                                    :id="$inputField['id'] ?? ''"
                                    :placeholder="$inputField['placeholder'] ?? ''"
                                    :class="$inputField['class'] ?? ''"
                                />
                            @endif
                            @if(!empty($buttons) && count($buttons) > 0)
                                @foreach($buttons as $button)
                                    <x-core-media::button-field
                                        :type="$button['type'] ?? 'button'"
                                        :class="$button['class'] ?? ''"
                                        :text="$button['text'] ?? ''"
                                        :attrs="$button['attrs'] ?? []"
                                        :icon="$button['icon'] ?? null"
                                    />
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            @if($addField === true)
                <div class="nkd-modal-footer">
                    @if(!empty($buttons) && count($buttons) > 0)
                        @foreach($buttons as $button)
                            <x-core-media::button-field
                                :type="$button['type'] ?? 'button'"
                                :class="$button['class'] ?? ''"
                                :text="$button['text'] ?? ''"
                                :attrs="$button['attrs'] ?? []"
                                :icon="$button['icon'] ?? null"
                            />
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
    @endif

</div>
</div>
