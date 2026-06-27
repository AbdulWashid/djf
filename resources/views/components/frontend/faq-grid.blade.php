@props([
    'items' => [],
    'category' => null,
    'location' => null,
    'columnsOf' => null,
])

@php
    $collection = collect($items);
    $rand = uniqid('faq-');

    $rowCount = $columnsOf
        ? (int) $columnsOf
        : max(1, (int) ceil($collection->count() / 2));

    $resolveId = function ($faq, $index) {
        if (is_array($faq)) {
            return $faq['id'] ?? 'item-' . $index;
        }

        return $faq->id ?? 'item-' . $index;
    };

    $resolveQuestion = function ($faq) use ($category, $location) {
        $question = is_array($faq) ? ($faq['question'] ?? '') : ($faq->question ?? '');

        return strtr($question, [
            '{category-name}' => $category ?? '',
            '{place-name}' => $location ?? '',
        ]);
    };

    $resolveAnswer = function ($faq) use ($category, $location) {
        $answer = is_array($faq) ? ($faq['answer'] ?? '') : ($faq->answer ?? '');

        return strtr($answer, [
            '{category-name}' => $category ?? '',
            '{place-name}' => $location ?? '',
        ]);
    };
@endphp

@if ($collection->isEmpty())
    <h5 class="text-center">No FAQs found</h5>
@else
    <div class="theme-faq-grid wow animate__animated animate__fadeInUp">
        <div class="theme-faq-grid-inner" style="--faq-rows: {{ $rowCount }}">
            @foreach ($collection as $index => $faq)
                @php
                    $faqId = $resolveId($faq, $index);
                    $collapseId = "theme-faq-collapse-{$rand}-{$faqId}";
                    $headingId = "theme-faq-heading-{$rand}-{$faqId}";
                @endphp
                <div class="theme-faq-card">
                    <div class="theme-faq-header" id="{{ $headingId }}">
                        <button
                            class="theme-faq-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}"
                        >
                            <span class="theme-faq-question">{{ $resolveQuestion($faq) }}</span>
                            <span class="theme-faq-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div
                        id="{{ $collapseId }}"
                        class="collapse theme-faq-collapse"
                        aria-labelledby="{{ $headingId }}"
                    >
                        <div class="theme-faq-body">
                            {!! $resolveAnswer($faq) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @once
        <style>
            .theme-faq-grid {
                margin-top: 10px;
                width: 100%;
                max-width: 100%;
            }

            .theme-faq-grid-inner {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-template-rows: repeat(var(--faq-rows, 1), auto);
                grid-auto-flow: column;
                gap: 16px;
                align-items: start;
                width: 100%;
            }

            .theme-faq-card {
                background: #fff;
                border: 1px solid #e8edf5;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
                transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
                min-width: 0;
            }

            .theme-faq-card:hover {
                border-color: #c9dcff;
                box-shadow: 0 8px 24px rgba(81, 146, 255, 0.12);
                transform: translateY(-2px);
            }

            .theme-faq-header {
                margin: 0;
            }

            .theme-faq-button {
                width: 100%;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding: 18px 20px;
                border: 0;
                background: transparent;
                text-align: left;
                cursor: pointer;
                color: #000;
                font-family: "Montserrat", sans-serif;
                font-size: 16px;
                font-weight: 600;
                line-height: 1.5;
            }

            .theme-faq-button:focus {
                outline: none;
                box-shadow: inset 0 0 0 2px rgba(81, 146, 255, 0.35);
            }

            .theme-faq-button:not(.collapsed) {
                background: linear-gradient(90deg, rgba(81, 146, 255, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
            }

            .theme-faq-question {
                flex: 1;
                min-width: 0;
                color: #000;
                word-wrap: break-word;
            }

            .theme-faq-icon {
                flex-shrink: 0;
                width: 28px;
                height: 28px;
                margin-top: 2px;
                border-radius: 50%;
                border: 1px solid #dbe4f2;
                position: relative;
                transition: transform 0.25s ease, background-color 0.25s ease, border-color 0.25s ease;
            }

            .theme-faq-icon::before,
            .theme-faq-icon::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                background: #5192ff;
                border-radius: 2px;
                transform: translate(-50%, -50%);
                transition: transform 0.25s ease, opacity 0.25s ease;
            }

            .theme-faq-icon::before {
                width: 12px;
                height: 2px;
            }

            .theme-faq-icon::after {
                width: 2px;
                height: 12px;
            }

            .theme-faq-button:not(.collapsed) .theme-faq-icon {
                background: #5192ff;
                border-color: #5192ff;
                transform: rotate(180deg);
            }

            .theme-faq-button:not(.collapsed) .theme-faq-icon::before,
            .theme-faq-button:not(.collapsed) .theme-faq-icon::after {
                background: #fff;
            }

            .theme-faq-button:not(.collapsed) .theme-faq-icon::after {
                transform: translate(-50%, -50%) scaleY(0);
                opacity: 0;
            }

            .theme-faq-body {
                padding: 0 20px 18px 20px;
                color: #000;
                font-family: "Open Sans", sans-serif;
                font-size: 15px;
                line-height: 1.7;
            }

            .theme-faq-body p,
            .theme-faq-body li,
            .theme-faq-body span,
            .theme-faq-body a {
                color: #000;
            }

            .theme-faq-body a {
                color: #5192ff;
                text-decoration: underline;
            }

            @media (max-width: 991px) {
                .theme-faq-grid-inner {
                    grid-template-columns: 1fr;
                    grid-template-rows: none;
                    grid-auto-flow: row;
                }

                .theme-faq-button {
                    font-size: 15px;
                    padding: 16px;
                }

                .theme-faq-body {
                    padding: 0 16px 16px 16px;
                }
            }
        </style>
    @endonce
@endif
