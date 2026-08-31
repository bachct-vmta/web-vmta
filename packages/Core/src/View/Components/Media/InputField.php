<?php

namespace Packages\Core\Src\View\Components\Media;

use Illuminate\View\Component;

class InputField extends Component
{
    public function __construct(
        public string $name,
        public string $type = 'text',
        public string $value = '',
        public string $placeholder = '',
        public bool $required = false,
        public string $class = '',
        public string $id = '',
    ) {}

    public function render()
    {
        return view('core-media::components.input-field');
    }
}
