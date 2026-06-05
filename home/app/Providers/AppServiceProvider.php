<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Producto;
use App\Observers\ProductoObserver;
use \Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ProductoVariante;
use App\Observers\ProductoVarianteObserver;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
        Producto::observe(ProductoObserver::class);

        \Illuminate\Support\Facades\Blade::directive('currency', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', '.') . ' €'; ?>";
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica tu correo electrónico')
                ->greeting('¡Hola!')
                ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
                ->action('Verificar dirección de correo', $url)
                ->line('Si no creaste una cuenta, no es necesario realizar ninguna otra acción.')
                ->salutation('Saludos, ' . config('app.name'));
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = config('app.url') . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
            
            return (new MailMessage)
                ->subject('Restablecer contraseña - ' . config('app.name'))
                ->greeting('¡Hola!')
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace para restablecer la contraseña expirará en 60 minutos.')
                ->line('Si no solicitaste un cambio de contraseña, no es necesario realizar ninguna otra acción.')
                ->salutation('Saludos, ' . config('app.name'));
        });
        ProductoVariante::observe(ProductoVarianteObserver::class);
    }
}
