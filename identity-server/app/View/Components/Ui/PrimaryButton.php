<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class PrimaryButton extends Component
{
    public function __construct(
        public string $type = 'button',
        public bool $disabled = false
    ) {}

    public function render()
    {
        return view('components.ui.primary-button');
    }
}