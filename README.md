<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://github.com/victorftrdba/back-zehticket/blob/master/public/assets/logo.png?raw=true" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sobre o ZehTicket

Um painel desenvolvido para atender as demandas de produtores de eventos.
Nele você pode vender ingressos para eventos, agendar eventos futuros e detalhar sobre como será o show.

## Rodando o projeto e informações importantes

    - configurar .env
    - composer install
    - php artisan key:generate
    - php artisan queue:work (em um terminal separado)
    - php artisan serve
    
Seguindo esses passos o projeto deverá rodar sem problemas.
Utilizei o MySQL como Banco de Dados.
Interessante utilizar também o mailhog para ver como seus e-mails serão entregues aos usuários!
