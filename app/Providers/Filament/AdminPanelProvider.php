<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin; // 1. IMPORT ADICIONADO AQUI

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandName('Konta')
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]) // 2. PARÊNTESES DE FECHAMENTO CORRIGIDO AQUI
            ->plugin(
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
                    ->config([
                        // Define o idioma para português do Brasil
                        'locale' => 'pt-br',
                        
                        // Define o primeiro dia da semana (0 = Domingo, 1 = Segunda)
                        'firstDay' => 0,
                        
                        // Formato da hora nos eventos (ex: 14:30 em vez de 2:30 PM)
                        'eventTimeFormat' => [
                            'hour' => '2-digit',
                            'minute' => '2-digit',
                            'hour12' => false,
                        ],
                        
                        // Formato da hora na barra lateral (quando na visualização de semana/dia)
                        'slotLabelFormat' => [
                            'hour' => '2-digit',
                            'minute' => '2-digit',
                            'hour12' => false,
                        ],
                        
                        // Traduz os botões do cabeçalho
                        'buttonText' => [
                            'today' => 'Hoje',
                            'month' => 'Mês',
                            'week' => 'Semana',
                            'day' => 'Dia',
                            'list' => 'Lista'
                        ],
                        
                        // Organização dos botões no topo do calendário
                        'headerToolbar' => [
                            'left' => 'prev,next today',
                            'center' => 'title',
                            'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
                        ],
                    ])
            ); // 3. PONTO E VÍRGULA FINAL CORRIGIDO AQUI
    }
}