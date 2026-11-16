<?php

/*
 * Configuración de las rifas / registro de productores PITAMEX.
 * Las ligas pendientes de PITAMEX se ponen en el .env sin tocar código.
 */
return [

    // Logo en PNG con URL pública HTTPS (Outlook no muestra WebP)
    'logo_url' => env('RAFFLE_LOGO_URL', env('APP_URL').'/images/raffles/logo-pitamex.png'),

    // PDF del temario (botón "Descargar temario" del correo)
    'temario_url' => env('RAFFLE_TEMARIO_URL', 'https://admin-pitahaya.brounieapps.com/temario'),

    // Aviso de privacidad (pie del correo y casilla del registro)
    'privacy_url' => env('RAFFLE_PRIVACY_URL', 'https://pitamex.org'),

    // Darse de baja: por default un correo a PITAMEX
    'unsubscribe_url' => env('RAFFLE_UNSUBSCRIBE_URL', 'mailto:pitamex@pitahayaorg.com?subject=Baja%20de%20correos'),

    // WhatsApp de PITAMEX (botón del correo con el número)
    'whatsapp_url' => env('RAFFLE_WHATSAPP_URL', 'https://wa.me/524451063437'),

    // Segundos entre cada correo al invitar en lote (Resend permite 2 por segundo)
    'send_interval' => (int) env('RAFFLE_SEND_INTERVAL', 1),
];
