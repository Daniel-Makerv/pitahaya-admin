<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<title>Su número para la rifa PITAMEX</title>
{{--
  Confirmación con el número de rifa (diseño CorreoNumero del canvas, en tablas para que se vea igual en Outlook).
  Variables (App\Mail\RaffleNumber): $number, $firstName, $participant, $place, $prizes, $drawDate, $logoUrl, $whatsappUrl
--}}
<style>
  body{margin:0;padding:0;background:#ECEEE8;}
  table{border-collapse:collapse;}
  img{border:0;display:block;}
  @media (max-width:620px){
    .wrap{width:100% !important;}
    .px{padding-left:22px !important;padding-right:22px !important;}
    .num{font-size:56px !important;}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#ECEEE8;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Gracias por completar su registro. Guarde este número: {{ $number }}.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ECEEE8;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" class="wrap" width="600" cellpadding="0" cellspacing="0" style="width:600px;background:#FFFFFF;border-radius:20px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;color:#14211B;">

<tr><td class="px" style="padding:22px 36px;border-bottom:1px solid #EEF1EC;">
  <img src="{{ $logoUrl }}" width="88" alt="PITAMEX · Pitahaya de México" style="width:88px;height:auto;">
</td></tr>

<tr><td class="px" align="center" style="padding:40px 36px 16px;text-align:center;">
  <h1 style="margin:0 0 12px;font-size:28px;line-height:1.2;color:#173A2C;">¡Ya está participando{{ $firstName ? ', '.$firstName : '' }}!</h1>
  <p style="margin:0;font-size:16px;line-height:1.6;color:#4F5D55;">Gracias por completar su registro de productor. Este es su número para la rifa de la Expo Agroalimentaria Guanajuato 2026.</p>
</td></tr>

<tr><td class="px" style="padding:10px 36px 0;">
  <table role="presentation" width="100%" style="border:2px dashed #C8195F;border-radius:22px;background:#FDF4F8;"><tr><td align="center" style="padding:30px 20px;text-align:center;">
    <img src="{{ $logoUrl }}" width="70" alt="" style="width:70px;height:auto;margin:0 auto 8px;">
    <p style="margin:0;font-size:13px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#9E1249;">Su número</p>
    <p class="num" style="margin:6px 0;font-size:68px;font-weight:bold;letter-spacing:4px;line-height:1.05;color:#173A2C;">{{ $number }}</p>
    <p style="margin:0;font-size:14px;color:#56645C;">{{ $participant->name }}@if ($place) · {{ $place }}@endif</p>
  </td></tr></table>
</td></tr>

<tr><td class="px" style="padding:30px 36px 6px;">
  <p style="margin:0 0 12px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">Premios y cómo se elige a los ganadores</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.55;color:#34463C;">
    @if (count($prizes))
    <tr>
      <td width="40" valign="top" style="padding:6px 0;"><div style="width:28px;height:28px;line-height:28px;border-radius:8px;background:#E6EFE8;color:#173A2C;font-weight:bold;font-size:14px;text-align:center;">1</div></td>
      <td style="padding:6px 0;">Premios:
        @foreach ($prizes as $i => $prize)
          {{ ['1er lugar', '2º', '3º'][$i] ?? ($i + 1).'º' }}, {{ \Illuminate\Support\Str::lcfirst($prize) }}@if (! $loop->last) · @endif
        @endforeach
      </td>
    </tr>
    @endif
    <tr>
      <td width="40" valign="top" style="padding:6px 0;"><div style="width:28px;height:28px;line-height:28px;border-radius:8px;background:#E6EFE8;color:#173A2C;font-weight:bold;font-size:14px;text-align:center;">2</div></td>
      <td style="padding:6px 0;">
        {{ $drawDate ? 'El '.$drawDate : 'En la fecha del sorteo' }} se toma el premio mayor de la Lotería Nacional.
        Gana el número más cercano@if (count($prizes) > 1); el 2º y 3er lugar, los siguientes más cercanos@endif. Si hay empate, gana el número menor.
      </td>
    </tr>
    <tr>
      <td width="40" valign="top" style="padding:6px 0;"><div style="width:28px;height:28px;line-height:28px;border-radius:8px;background:#E6EFE8;color:#173A2C;font-weight:bold;font-size:14px;text-align:center;">3</div></td>
      <td style="padding:6px 0;">Le avisamos por WhatsApp y correo si resulta ganador.</td>
    </tr>
  </table>
</td></tr>

<tr><td class="px" style="padding:24px 36px 0;">
  <table role="presentation" width="100%" style="background:#173A2C;border-radius:18px;"><tr><td style="padding:24px;">
    <p style="margin:0 0 8px;font-size:18px;font-weight:bold;color:#FFFFFF;">Siguiente paso: evaluación inicial de su proyecto</p>
    <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#CFE0D5;">Con la información que nos compartió, un asesor de PITAMEX lo contactará para revisar su predio y las alternativas: PROPEP, análisis financiero o prueba piloto. Si prefiere adelantarse, escríbanos.</p>
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#C8195F;border-radius:12px;">
      <a href="{{ $whatsappUrl }}" style="display:inline-block;padding:14px 22px;font-size:16px;font-weight:bold;color:#FFFFFF;text-decoration:none;">Escribir por WhatsApp &rarr;</a>
    </td></tr></table>
  </td></tr></table>
</td></tr>

<tr><td class="px" style="padding:30px 36px 0;"></td></tr>
<tr><td class="px" style="background:#F5F7F3;padding:24px 36px;font-size:13.5px;line-height:1.6;color:#56645C;">
  <img src="{{ $logoUrl }}" width="64" alt="PITAMEX" style="width:64px;height:auto;margin:0 0 10px;">
  <p style="margin:0;font-weight:bold;color:#173A2C;">PITAMEX · Pitahaya Consultores de México SPR de RL</p>
  <p style="margin:2px 0 0;">Uriangato y Acámbaro, Guanajuato · pitamex@pitahayaorg.com</p>
  <p style="margin:10px 0 0;font-size:12.5px;color:#6A776F;">Un boleto por persona. Si se registró más de una vez, conserva este mismo número.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
