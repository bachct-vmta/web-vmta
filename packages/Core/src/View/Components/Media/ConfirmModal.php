<?php

namespace Packages\Core\Src\View\Components\Media;

use Illuminate\View\Component;

class ConfirmModal extends Component
{
    public function __construct(
        public string $modalId,
        public string $modalTitle,
        public array $modalAttributes = [],
        public array $inputField = [],
        public bool $addField = false,
        public ?string $field = '',
        public array $buttons = [],
        public bool $isForm = false,
        public array $formFields = [],
        public ?string $icon = null,
        public string $slotHtml = '',
    ) {}

    public function render()
    {
        return view('core-media::components.confirm-modal');
    }
}
