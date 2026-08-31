<?php

namespace Packages\Core\Src\View\Components\Media;

use Illuminate\View\Component;

class ActionFormModal extends Component
{
    public function __construct(
        public string $modalId,
        public string $modalTitle,
        public string $actionUrl = '',
        public string $method = 'POST',
        public string $submitText = 'Submit',
        public array $inputs = [],
    ) {}

    public function render()
    {
        return view('core-media::components.action-form-modal');
    }
}
