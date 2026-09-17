@php
    $messages = $topbarMessages ?? [];
    $phone = config('company.phone');
    $phoneTel = config('company.phone_tel');
@endphp
<div class="lv-navbar__topbar">
    <div class="lv-container lv-navbar__topbar-inner">
        @if (count($messages) > 0)
            <nav class="lv-navbar__ticker" aria-label="Informações da loja">
                <ul class="visually-hidden">
                    @foreach ($messages as $message)
                        <li>
                            @if (! empty($message['url']))
                                <a href="{{ $message['url'] }}">{{ $message['text'] }}</a>
                            @else
                                {{ $message['text'] }}
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="lv-navbar__ticker-viewport" aria-hidden="true">
                    <div class="lv-navbar__ticker-track">
                        @for ($copy = 0; $copy < 2; $copy++)
                            <span class="lv-navbar__ticker-copy">
                                @foreach ($messages as $message)
                                    @if (! empty($message['url']))
                                        <a class="lv-navbar__ticker-item" href="{{ $message['url'] }}" tabindex="-1">{{ $message['text'] }}</a>
                                    @else
                                        <span class="lv-navbar__ticker-item">{{ $message['text'] }}</span>
                                    @endif
                                    <span class="lv-navbar__ticker-dot"></span>
                                @endforeach
                            </span>
                        @endfor
                    </div>
                </div>
            </nav>
        @endif

        <a href="tel:{{ $phoneTel }}" class="lv-navbar__topbar-phone">
            <span class="lv-navbar__topbar-phone-label">Tel.</span>
            <span>{{ $phone }}</span>
        </a>
    </div>
</div>
<div class="lv-navbar__mobile-offset" aria-hidden="true"></div>
