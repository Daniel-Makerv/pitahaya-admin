<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Encuesta {{ $registro['name'] }}</title>
    <style>
        /* DejaVu Sans viene con dompdf y soporta acentos y ñ */
        * { font-family: "DejaVu Sans", sans-serif; }

        @page { margin: 40px 48px; }

        body { color: #0f172a; font-size: 11px; line-height: 1.5; }

        .header { border-left: 4px solid #2563eb; padding: 4px 0 4px 14px; margin-bottom: 24px; }
        .eyebrow { color: #64748b; font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .title { font-size: 20px; font-weight: bold; margin: 2px 0 4px; }
        .meta { color: #64748b; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; }
        tr { page-break-inside: avoid; }
        td { padding: 10px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .number { width: 28px; color: #94a3b8; font-weight: bold; }
        .question { color: #475569; font-size: 10px; margin-bottom: 2px; }
        .answer { font-size: 12px; font-weight: bold; }
        .empty { color: #94a3b8; font-weight: normal; font-style: italic; }

        .footer { margin-top: 24px; color: #94a3b8; font-size: 9px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">{{ $registro['type'] }}</div>
        <div class="title">{{ $registro['name'] }}</div>
        <div class="meta">
            @if ($registro['answered_at'])
                Contestada el {{ $registro['answered_at'] }}
            @endif
            · Registro #{{ $registro['id'] }}
        </div>
    </div>

    @if (count($registro['answers']))
        <table>
            @foreach ($registro['answers'] as $index => $item)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>
                        <div class="question">{{ $item['question'] }}</div>
                        <div class="answer {{ $item['answer'] === 'Sin respuesta' ? 'empty' : '' }}">
                            {{ $item['answer'] }}
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="empty">Este registro no tiene respuestas.</p>
    @endif

    <div class="footer">Generado el {{ now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</div>
</body>
</html>
