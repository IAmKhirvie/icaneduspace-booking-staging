<?php

return [
    App\Providers\AppServiceProvider::class,
    // Filament admin panel disabled — all dashboards now use the customer Blade design.
    // App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
];
