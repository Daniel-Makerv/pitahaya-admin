<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Registro abierto (sin correo de invitación):
 * - raffles.public_code         código del link público: /rifa/{public_code}
 * - raffle_participants.email   ahora opcional (quien se registra por el link puede no tener correo)
 * - raffle_participants.source  de dónde llegó: form | manual | open
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->string('public_code', 16)->nullable()->unique()->after('id');
        });

        // Código para las rifas que ya existen
        DB::table('raffles')->whereNull('public_code')->orderBy('id')->each(function ($raffle) {
            DB::table('raffles')->where('id', $raffle->id)->update(['public_code' => self::code()]);
        });

        Schema::table('raffle_participants', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('source', 20)->default('form')->after('form_id');
        });

        // Los que no vienen de un formulario se agregaron a mano
        DB::table('raffle_participants')->whereNull('form_id')->update(['source' => 'manual']);
    }

    public function down(): void
    {
        Schema::table('raffle_participants', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('raffles', function (Blueprint $table) {
            $table->dropUnique(['public_code']);
            $table->dropColumn('public_code');
        });
    }

    private static function code(): string
    {
        // Sin 0/O ni 1/I para que se pueda dictar o copiar a mano sin errores
        return collect(str_split(str_repeat('ABCDEFGHJKLMNPQRSTUVWXYZ23456789', 2)))
            ->shuffle()->take(8)->implode('');
    }
};
