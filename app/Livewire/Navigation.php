<?php

namespace App\Livewire;

use Livewire\Component;

class Navigation extends Component
{
    public $currentPage = 'dashboard';

    protected $listeners = ['changePage'];

    public function changePage($page)
    {
        $this->currentPage = $page;
        $this->emit('pageChanged', $page);
    }

    public function render()
    {
        return view('livewire.navigation');
    }
}
