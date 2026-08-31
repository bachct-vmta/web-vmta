<?php

namespace Packages\Core\Src\View\Components\Media;

use Illuminate\View\Component;

class ButtonField extends Component
{
    public function __construct(
        public string $type = 'button',
        public string $class = '',
        public string $text = '',
        public ?string $icon = '',
        public array $attrs = [],
    ) {}

    public function render()
    {
        return view('core-media::components.button-field');
    }
}
