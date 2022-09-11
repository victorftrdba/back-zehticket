<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (Schema::hasTable('permissions')) {
            $permissions = Permission::with('roles')->get();

            foreach ($permissions as $permission) {
                Gate::define($permission->id, function (User $user) use ($permission) {
                    return $user->hasPermission($permission) ? Response::allow() : Response::deny();
                });
            }
        }

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->greeting('Olá!')
                ->subject('Verifique seu e-mail!')
                ->line('Clique no botão abaixo para confirmar seu e-mail e ter acesso total ao ZehTicket.')
                ->action('Verificar meu e-mail', $url)
                ->line('Obrigado por utilizar nosso sistema!')
                ->salutation('Equipe ZehTicket');
        });
    }
}
