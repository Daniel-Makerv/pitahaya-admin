<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<title>Gracias por su visita a PITAMEX · Expo Agroalimentaria Guanajuato 2026</title>
{{--
  Carta de PITAMEX (versión aprobada por Javier) · invitación al registro + rifa.
  Variables (las manda App\Mail\RaffleInvitation):
    $registerUrl     liga personal del registro (token del participante)
    $prizes          premios de la rifa, uno por lugar
    $logoUrl, $temarioUrl, $privacyUrl, $unsubscribeUrl  (config/raffles.php / .env)
--}}
<style>
  body{margin:0;padding:0;background:#ECEEE8;}
  table{border-collapse:collapse;}
  img{border:0;display:block;}
  @media (max-width:620px){
    .wrap{width:100% !important;}
    .px{padding-left:22px !important;padding-right:22px !important;}
    .h1{font-size:26px !important;}
    .col{display:block !important;width:100% !important;}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#ECEEE8;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Requerimientos, potencial productivo y cómo evaluar su proyecto de pitahaya.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ECEEE8;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" class="wrap" width="600" cellpadding="0" cellspacing="0" style="width:600px;background:#FFFFFF;border-radius:16px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;color:#14211B;">

<tr><td class="px" style="padding:20px 36px;border-bottom:1px solid #EEF1EC;">
  <img src="{{ $logoUrl }}" width="96" alt="PITAMEX · Pitahaya de México" style="width:96px;height:auto;">
</td></tr>

<tr><td class="px" style="background:#173A2C;padding:40px 36px;">
  <p style="margin:0 0 14px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#F2C230;">Estimado productor</p>
  <h1 class="h1" style="margin:0 0 16px;font-size:30px;line-height:1.18;color:#FFFFFF;">Gracias por su visita a PITAMEX en la Expo Agroalimentaria Guanajuato 2026</h1>
  <p style="margin:0 0 26px;font-size:16px;line-height:1.6;color:#D3E3D8;">Agradecemos el interés mostrado en conocer las oportunidades que ofrece el cultivo de pitahaya. Le compartimos información preliminar sobre sus requerimientos agroclimáticos, productivos y económicos, para que pueda evaluar si este cultivo representa una alternativa de inversión para su proyecto agrícola.</p>
  <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#C8195F;border-radius:10px;">
    <a href="{{ $registerUrl }}" style="display:inline-block;padding:16px 26px;font-size:16px;font-weight:bold;color:#FFFFFF;text-decoration:none;">Completar mi registro y participar &rarr;</a>
  </td></tr></table>
</td></tr>

@if (count($prizes))
@php
    $palettes = [
        ['bg' => '#FBE9F0', 'label' => '#9E1249', 'text' => '#3A2430'],
        ['bg' => '#FDF5DA', 'label' => '#7A5B07', 'text' => '#3A2E0B'],
        ['bg' => '#E6EFE8', 'label' => '#24563F', 'text' => '#173A2C'],
    ];
    $placeLabels = ['1er lugar', '2º lugar', '3er lugar'];
    $width = (int) floor(100 / max(1, min(3, count($prizes))));
@endphp
<tr><td class="px" style="padding:28px 36px 4px;">
  <p style="margin:0 0 12px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">Rifa para productores registrados</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
    @foreach ($prizes as $i => $prize)
      @php $c = $palettes[$i % 3]; @endphp
      <td class="col" width="{{ $width }}%" valign="top" style="padding:4px;"><table role="presentation" width="100%" style="background:{{ $c['bg'] }};border-radius:12px;"><tr><td style="padding:14px;"><p style="margin:0;font-size:12px;font-weight:bold;color:{{ $c['label'] }};">{{ $placeLabels[$i] ?? ($i + 1).'º lugar' }}</p><p style="margin:4px 0 0;font-size:14px;font-weight:bold;color:{{ $c['text'] }};">{{ $prize }}</p></td></tr></table></td>
      @if ($i % 3 === 2 && ! $loop->last)</tr><tr>@endif
    @endforeach
  </tr></table>
</td></tr>
@endif

<tr><td class="px" style="padding:30px 36px 4px;">
  <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">Nuestra visión</p>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.65;color:#4F5D55;">En PITAMEX entendemos que una relación comercial exitosa no comienza con la venta de una planta, sino con el conocimiento profundo del productor, su proyecto, sus expectativas y su visión de largo plazo.</p>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.65;color:#4F5D55;">No buscamos únicamente incrementar el número de productores que adquieren plantas, insumos o servicios. Nuestra meta es construir una red de productores profesionales, responsables y técnicamente preparados, capaces de desarrollar proyectos productivos sostenibles y de ofrecer fruta que pueda competir en mercados cada vez más exigentes.</p>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.65;color:#4F5D55;">Cada productor que se incorpora a nuestra red representa potencialmente muchos años de relación con PITAMEX. Por ello, una buena decisión comercial no debe evaluarse solo por el tamaño de la inversión inicial, sino también por la compatibilidad de objetivos, principios y formas de trabajo.</p>
  <p style="margin:0;font-size:15px;line-height:1.65;color:#4F5D55;">Valoramos especialmente la <strong style="color:#173A2C;">disciplina, la apertura al aprendizaje, el compromiso, la transparencia y la visión de largo plazo</strong>. Un productor que comienza con una superficie pequeña, pero comparte estos principios, puede convertirse con el tiempo en uno de los integrantes más importantes de nuestra red.</p>
</td></tr>

<tr><td class="px" style="padding:30px 36px 4px;">
  <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">1. Condiciones agroclimáticas de referencia</p>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#4F5D55;">Para proyectos de pitahaya en el centro de México consideramos favorable el siguiente perfil:</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.5;">
    <tr><td style="padding:10px 12px;background:#F5F7F3;font-weight:bold;color:#173A2C;width:34%;">Altitud</td><td style="padding:10px 12px;background:#F5F7F3;color:#34463C;">Preferentemente menor a 2,000 msnm</td></tr>
    <tr><td style="padding:10px 12px;font-weight:bold;color:#173A2C;">Clima</td><td style="padding:10px 12px;color:#34463C;">Templado a semicálido, con humedad moderada y precipitaciones medias</td></tr>
    <tr><td style="padding:10px 12px;background:#F5F7F3;font-weight:bold;color:#173A2C;">Temperatura</td><td style="padding:10px 12px;background:#F5F7F3;color:#34463C;">Máximas cercanas a 30 °C y mínimas de referencia de 1 a 5 °C. Las heladas ocasionales requieren evaluación particular y medidas de protección</td></tr>
    <tr><td style="padding:10px 12px;font-weight:bold;color:#173A2C;">Agua</td><td style="padding:10px 12px;color:#34463C;">Disponibilidad confiable, preferentemente pozo profundo</td></tr>
    <tr><td style="padding:10px 12px;background:#F5F7F3;font-weight:bold;color:#173A2C;">Riego</td><td style="padding:10px 12px;background:#F5F7F3;color:#34463C;">Goteo con equipo de inyección para fertirrigación</td></tr>
    <tr><td style="padding:10px 12px;font-weight:bold;color:#173A2C;">Consumo hídrico</td><td style="padding:10px 12px;color:#34463C;">Hasta 40,000 litros semanales por hectárea, según clima, edad del cultivo y necesidades reales de riego</td></tr>
    <tr><td style="padding:10px 12px;background:#F5F7F3;font-weight:bold;color:#173A2C;">Suelo</td><td style="padding:10px 12px;background:#F5F7F3;color:#34463C;">Franco o franco-arcilloso, buen drenaje, pH cercano a neutro y baja concentración de sodio y sales</td></tr>
    <tr><td style="padding:10px 12px;font-weight:bold;color:#173A2C;">Densidad</td><td style="padding:10px 12px;color:#34463C;">Aproximadamente 5,600 plantas por hectárea, según el diseño del proyecto</td></tr>
  </table>
  <p style="margin:12px 0 0;font-size:13.5px;line-height:1.6;color:#56645C;">Estas características son un perfil de referencia y no requisitos universales. Existen numerosas variedades y selecciones con diferente adaptación a temperatura, humedad, salinidad y estrés ambiental, por lo que un predio que no cumpla exactamente estos parámetros no necesariamente debe descartarse.</p>
</td></tr>

<tr><td class="px" style="padding:30px 36px 4px;">
  <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">2. Potencial productivo y económico</p>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#4F5D55;">Referencia preliminar para una hectárea en etapa productiva:</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border:1px solid #E6EAE3;">
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;">Inversión inicial</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;">$700,000 MXN/ha</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">Inicio de etapa comercial</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">Aprox. 3 años</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;">Vida productiva potencial</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;">25–30 años</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">Producción anual de referencia</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">15–40 t/ha</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;">Escenario de producción promedio</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;">25 t/ha</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">Precio promedio al productor, temporada 2026</td><td align="right" style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #EEF1EC;background:#FAFBF8;">$50 MXN/kg</td></tr>
    <tr><td style="padding:10px 14px;color:#56645C;">Costo productivo estimado</td><td align="right" style="padding:10px 14px;font-weight:bold;">$15 MXN/kg</td></tr>
  </table>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;"><tr>
    <td class="col" width="50%" style="padding:4px 4px 4px 0;"><table role="presentation" width="100%" style="background:#173A2C;border-radius:12px;"><tr><td style="padding:16px;"><p style="margin:0;font-size:12px;color:#A9C2B2;">Ingreso bruto estimado</p><p style="margin:4px 0 0;font-size:22px;font-weight:bold;color:#FFFFFF;">$1,250,000 MXN/año</p></td></tr></table></td>
    <td class="col" width="50%" style="padding:4px 0 4px 4px;"><table role="presentation" width="100%" style="background:#173A2C;border-radius:12px;"><tr><td style="padding:16px;"><p style="margin:0;font-size:12px;color:#A9C2B2;">Margen productivo preliminar</p><p style="margin:4px 0 0;font-size:22px;font-weight:bold;color:#F2C230;">$875,000 MXN</p></td></tr></table></td>
  </tr></table>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;font-size:13.5px;line-height:1.5;color:#34463C;background:#F5F7F3;border-radius:10px;">
    <tr><td style="padding:12px 14px 2px;">Ingresos: 25,000 kg × $50 = <strong>$1,250,000</strong></td></tr>
    <tr><td style="padding:2px 14px;">Costos: 25,000 kg × $15 = <strong>$375,000</strong></td></tr>
    <tr><td style="padding:2px 14px 12px;">Diferencia: $1,250,000 − $375,000 = <strong style="color:#173A2C;">$875,000</strong></td></tr>
  </table>
  <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#6A776F;">Con 25 t/ha y los valores anteriores, antes de otros gastos, impuestos y recuperación de la inversión inicial. Cálculos exclusivamente ilustrativos: rendimientos, precios y costos pueden variar significativamente según variedad, tamaño, calidad, temporada, condiciones de producción y mercado.</p>
</td></tr>

<tr><td class="px" style="padding:30px 36px 4px;">
  <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">3. Evaluación de su proyecto</p>
  <p style="margin:0;font-size:15px;line-height:1.65;color:#4F5D55;">Cada proyecto debe analizarse individualmente antes de realizar una inversión. Por ello ponemos a su disposición nuestro <strong style="color:#173A2C;">Programa de Evaluación de Proyectos Productivos (PROPEP)</strong>, el <strong style="color:#173A2C;">análisis financiero</strong> y la posibilidad de iniciar con una <strong style="color:#173A2C;">prueba piloto</strong>, según las características de su predio y sus objetivos.</p>
</td></tr>

<tr><td class="px" style="padding:30px 36px 4px;">
  <p style="margin:0 0 10px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#C8195F;">4. Servicios PITAMEX para su proyecto</p>
  <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#4F5D55;">Si le interesa avanzar, estos son los programas con los que lo acompañamos, desde conocer la huerta hasta vender su fruta con nosotros.</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7F3;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">1</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Visita a huertos PITAMEX</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">Recorrido de 1 hora por nuestra huerta</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Conozca de cerca la planta de pitahaya y su proceso, desde el cultivo hasta el empaque.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20agendar%20una%20visita%20a%20la%20huerta." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Agendar visita por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">$2,000<br><span style="font-size:11.5px;font-weight:normal;color:#6A776F;">2 personas</span></td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">2</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Asistencia técnica para su cultivo</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">Asesoría de 0 a 10 años de edad de la planta</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Plan de manejo, plagas, nutrición, polinización y cosecha, con análisis de savia bimestral.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20la%20asistencia%20t%C3%A9cnica%20para%20mi%20cultivo." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Pedir información por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">$3,000<br><span style="font-size:11.5px;font-weight:normal;color:#6A776F;">mensual</span></td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7F3;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">3</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Valoración de huertos nuevos</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">¿Es apto su predio para pitahaya?</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Un técnico evalúa clima, suelo, agua y logística, y emite un dictamen positivo o negativo.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20quiero%20valorar%20mi%20predio%20para%20pitahaya." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Valorar mi predio por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">desde $1,500<br><span style="font-size:11.5px;font-weight:normal;color:#6A776F;">remota</span></td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">4</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Proyección de huerto nuevo (PROPEP)</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">Más de 10 años de experiencia</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Plan de acción para invertir. Se bonifica el 50&nbsp;% en productos PITAMEX al comprar su primera hectárea.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20la%20proyecci%C3%B3n%20para%20establecer%20un%20huerto%20nuevo." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Pedir mi proyección por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">$15,000<br><span style="font-size:11.5px;font-weight:normal;color:#6A776F;">por predio</span></td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7F3;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">5</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Corrida financiera personalizada</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">Proyección financiera de su huerto</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Inversión, costos e ingresos calculados con los datos de su predio.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20me%20interesa%20una%20corrida%20financiera%20para%20mi%20huerto." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Solicitar corrida por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">Cotizar</td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:0 0 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border:1px solid #E1E5DE;border-radius:12px;"><tr>
        <td width="44" valign="top" style="padding:16px 0 16px 14px;"><div style="width:32px;height:32px;line-height:32px;border-radius:9px;background:#FBE9F0;color:#C8195F;font-weight:bold;font-size:15px;text-align:center;">6</div></td>
        <td valign="top" style="padding:14px 12px;">
          <p style="margin:0;font-size:15px;font-weight:bold;color:#14211B;">Integración para proyectos externos</p>
          <p style="margin:2px 0 6px;font-size:13px;color:#6A776F;">¿Ya produce pitahaya? Venda su fruta con PITAMEX</p>
          <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#34463C;">Entregue su fruta con cita; precio spot de mercado y comisión PITAMEX del 20&nbsp;%.</p>
          <a href="https://wa.me/524451063437?text=Hola%20PITAMEX%2C%20ya%20produzco%20pitahaya%20y%20me%20interesa%20entregar%20mi%20fruta." style="font-size:13.5px;font-weight:bold;color:#C8195F;text-decoration:none;">Vender mi fruta por WhatsApp &rarr;</a>
        </td>
        <td width="96" valign="top" align="right" style="padding:14px 14px 14px 0;font-size:14.5px;font-weight:bold;color:#173A2C;text-align:right;">Consignación</td>
      </tr></table>
    </td></tr>
  </table>
  <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:8px;"><tr>
    <td style="border:2px solid #173A2C;border-radius:10px;">
      <a href="{{ $registerUrl }}#servicios" style="display:inline-block;padding:12px 20px;font-size:14.5px;font-weight:bold;color:#173A2C;text-decoration:none;">Ver el detalle de cada servicio &rarr;</a>
    </td>
    <td width="10"></td>
    <td>
      <a href="{{ $temarioUrl }}" style="display:inline-block;padding:12px 4px;font-size:14px;font-weight:bold;color:#56645C;text-decoration:underline;">Descargar temario (PDF)</a>
    </td>
  </tr></table>
</td></tr></table>
</td></tr>

<tr><td class="px" style="padding:30px 36px 0;">
  <table role="presentation" width="100%" style="background:#FBE9F0;border-radius:14px;"><tr><td align="center" style="padding:26px 22px;">
    <img src="{{ $logoUrl }}" width="88" alt="PITAMEX" style="width:88px;height:auto;margin:0 auto 12px;">
    <p style="margin:0 0 8px;font-size:19px;font-weight:bold;color:#173A2C;">Continuemos la conversación que iniciamos en la Expo</p>
    <p style="margin:0 0 18px;font-size:14.5px;line-height:1.6;color:#3A2430;">Le pedimos amablemente completar su registro: nos ayuda a conocer su proyecto para identificar las alternativas más convenientes, y con él participa en la rifa.</p>
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#C8195F;border-radius:10px;"><a href="{{ $registerUrl }}" style="display:inline-block;padding:16px 26px;font-size:16px;font-weight:bold;color:#FFFFFF;text-decoration:none;">Completar mi registro &rarr;</a></td></tr></table>
    <p style="margin:12px 0 0;font-size:12.5px;color:#6A5560;">Unos 5 minutos · desde el celular</p>
  </td></tr></table>
</td></tr>

<tr><td class="px" style="padding:28px 36px 8px;font-size:15px;line-height:1.6;color:#34463C;">
  Quedamos a sus órdenes para programar una evaluación inicial.<br><br>Atentamente,<br><strong style="color:#173A2C;">PITAMEX – Pitahaya Consultores de México SPR de RL</strong>
</td></tr>

<tr><td class="px" style="background:#F5F7F3;padding:24px 36px;font-size:13px;line-height:1.6;color:#56645C;">
  <img src="{{ $logoUrl }}" width="72" alt="PITAMEX" style="width:72px;height:auto;margin:0 0 10px;">
  <p style="margin:0;">Uriangato y Acámbaro, Guanajuato · WhatsApp +52 445 106 3437</p>
  <p style="margin:4px 0 0;">pitamex@pitahayaorg.com · <a href="https://pitamex.org" style="color:#C8195F;">pitamex.org</a></p>
  <p style="margin:12px 0 0;font-size:12px;color:#6A776F;">Recibe este correo porque nos dejó sus datos en la Expo Agroalimentaria Guanajuato 2026. <a href="{{ $unsubscribeUrl }}" style="color:#56645C;">Darme de baja</a> · <a href="{{ $privacyUrl }}" style="color:#56645C;">Aviso de privacidad</a></p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
