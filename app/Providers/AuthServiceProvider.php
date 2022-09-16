<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
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
    public function boot(): void
    {
        $this->registerPolicies();

        if (Schema::hasTable('permissions')) {
            $permissions = Permission::with('roles')->get();

            foreach ($permissions as $permission) {
                Gate::define($permission->id, static function (User $user) use ($permission) {
                    return $user->hasPermission($permission) ? Response::allow() : Response::deny();
                });
            }
        }

        VerifyEmail::toMailUsing(static fn($notifiable, $url) => (new MailMessage)
            ->greeting('Olá!')
            ->subject('Verifique seu e-mail!')
            ->line('Clique no botão abaixo para confirmar seu e-mail e ter acesso total ao ZehTicket.')
            ->action('Verificar meu e-mail', $url)
            ->line('Obrigado por utilizar nosso sistema!')
            ->salutation('Equipe ZehTicket')
        );

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return 'https://zehticket.com.br/?token=' . $token;
        });

        ResetPassword::toMailUsing(static fn($notifiable, $token) => (new MailMessage)
            ->greeting('Olá!')
            ->subject('Redefinição de senha')
            ->line('Clique no botão abaixo para resetar sua senha.')
            ->action('Alterar minha senha', 'https://zehticket.com.br/?token=' . $token)
            ->line('Obrigado por utilizar nosso sistema!')
            ->salutation('Equipe ZehTicket')
        );
    }
}
